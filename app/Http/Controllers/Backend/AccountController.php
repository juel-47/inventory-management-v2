<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\OrderPaymentReceipt;
use App\Models\GeneralSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

class AccountController extends Controller
{
    /**
     * Display a listing of all transactions (Ledger).
     */
    public function index(\App\DataTables\OrderPaymentDataTable $dataTable)
    {
        return $dataTable->render('backend.accounts.index');
    }

    /**
     * Display orders that have a due balance.
     */
    public function dueOrders(\App\DataTables\DueOrderDataTable $dataTable)
    {
        return $dataTable->render('backend.accounts.due_orders');
    }

    /**
     * Download payment history PDF (filtered).
     */
    public function paymentHistoryPdf(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $method = $request->input('method');
        $search = trim((string) $request->input('search', ''));

        $query = OrderPayment::query()->with(['order', 'receipts']);

        if (!empty($startDate)) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if (!empty($endDate)) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        if (!empty($method)) {
            $query->where('payment_method', $method);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', '%' . $search . '%')
                    ->orWhere('payment_method', 'like', '%' . $search . '%')
                    ->orWhereHas('order', function ($oq) use ($search) {
                        $oq->where('order_no', 'like', '%' . $search . '%')
                            ->orWhere('billing_name', 'like', '%' . $search . '%')
                            ->orWhere('billing_phone', 'like', '%' . $search . '%');
                    });
            });
        }

        $count = (clone $query)->count();
        $maxRows = 1000;
        if ($count > $maxRows) {
            Toastr::error('Too many records for PDF. Please filter by date or method to reduce results.');
            return redirect()->back();
        }

        $payments = $query->orderByDesc('id')->get();
        $settings = GeneralSetting::first();
        $logoData = $this->resolveLogoData($settings);
        $generatedAt = Carbon::now();

        $summary = [
            'count' => $payments->count(),
            'total_amount' => $payments->sum('amount'),
        ];

        $pdf = Pdf::loadView('backend.accounts.payment_history_pdf', [
            'payments' => $payments,
            'settings' => $settings,
            'logoData' => $logoData,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'method' => $method,
                'search' => $search,
            ],
            'summary' => $summary,
            'generatedAt' => $generatedAt,
        ])->setPaper('a4', 'portrait');

        $fileName = 'payment_history_' . now()->format('Ymd_His') . '.pdf';
        return $pdf->download($fileName);
    }

    /**
     * View payment history with print/close controls.
     */
    public function paymentHistoryPdfView(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $method = $request->input('method');
        $search = trim((string) $request->input('search', ''));

        $query = OrderPayment::query()->with(['order', 'receipts']);

        if (!empty($startDate)) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if (!empty($endDate)) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        if (!empty($method)) {
            $query->where('payment_method', $method);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', '%' . $search . '%')
                    ->orWhere('payment_method', 'like', '%' . $search . '%')
                    ->orWhereHas('order', function ($oq) use ($search) {
                        $oq->where('order_no', 'like', '%' . $search . '%')
                            ->orWhere('billing_name', 'like', '%' . $search . '%')
                            ->orWhere('billing_phone', 'like', '%' . $search . '%');
                    });
            });
        }

        $count = (clone $query)->count();
        $maxRows = 1000;
        if ($count > $maxRows) {
            Toastr::error('Too many records for view. Please filter by date or method to reduce results.');
            return redirect()->back();
        }

        $payments = $query->orderByDesc('id')->get();
        $settings = GeneralSetting::first();
        $logoData = $this->resolveLogoData($settings);
        $generatedAt = Carbon::now();

        $summary = [
            'count' => $payments->count(),
            'total_amount' => $payments->sum('amount'),
        ];

        $downloadUrl = route('admin.accounts.payments.pdf', array_filter([
            'start_date' => $startDate ?: null,
            'end_date' => $endDate ?: null,
            'method' => $method ?: null,
            'search' => $search ?: null,
        ]));

        return view('backend.accounts.payment_history_view', [
            'payments' => $payments,
            'settings' => $settings,
            'logoData' => $logoData,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'method' => $method,
                'search' => $search,
            ],
            'summary' => $summary,
            'generatedAt' => $generatedAt,
            'downloadUrl' => $downloadUrl,
        ]);
    }

    /**
     * Download a single payment PDF.
     */
    public function paymentSinglePdf(OrderPayment $payment)
    {
        $payment->load(['order', 'receipts']);
        $settings = GeneralSetting::first();
        $logoData = $this->resolveLogoData($settings);
        $generatedAt = Carbon::now();

        $pdf = Pdf::loadView('backend.accounts.payment_single_pdf', [
            'payment' => $payment,
            'settings' => $settings,
            'logoData' => $logoData,
            'generatedAt' => $generatedAt,
        ])->setPaper('a4', 'portrait');

        $fileName = 'payment_' . ($payment->order?->order_no ?? $payment->id) . '_' . now()->format('Ymd_His') . '.pdf';
        return $pdf->download($fileName);
    }

    /**
     * View a single payment with print/close controls.
     */
    public function paymentSingleView(OrderPayment $payment)
    {
        $payment->load(['order', 'receipts']);
        $settings = GeneralSetting::first();
        $logoData = $this->resolveLogoData($settings);
        $generatedAt = Carbon::now();
        $downloadUrl = route('admin.accounts.payments.single.pdf', $payment->id);

        return view('backend.accounts.payment_single_view', [
            'payment' => $payment,
            'settings' => $settings,
            'logoData' => $logoData,
            'generatedAt' => $generatedAt,
            'downloadUrl' => $downloadUrl,
        ]);
    }

    /**
     * Download all payments for a specific order.
     */
    public function paymentOrderPdf(Order $order)
    {
        $order->load(['payments', 'user']);
        $payments = $order->payments->sortByDesc('id')->values();

        $maxRows = 1000;
        if ($payments->count() > $maxRows) {
            Toastr::error('Too many payments for PDF. Please narrow down.');
            return redirect()->back();
        }

        $settings = GeneralSetting::first();
        $logoData = $this->resolveLogoData($settings);
        $generatedAt = Carbon::now();

        $summary = [
            'count' => $payments->count(),
            'total_amount' => $payments->sum('amount'),
            'paid_amount' => $order->paid_amount,
            'due_amount' => $order->due_amount,
        ];

        $pdf = Pdf::loadView('backend.accounts.payment_order_pdf', [
            'order' => $order,
            'payments' => $payments,
            'settings' => $settings,
            'logoData' => $logoData,
            'generatedAt' => $generatedAt,
            'summary' => $summary,
        ])->setPaper('a4', 'portrait');

        $fileName = 'order_payments_' . $order->order_no . '_' . now()->format('Ymd_His') . '.pdf';
        return $pdf->download($fileName);
    }

    /**
     * View all payments for a specific order with print/close controls.
     */
    public function paymentOrderView(Order $order)
    {
        $order->load(['payments', 'user']);
        $payments = $order->payments->sortByDesc('id')->values();

        $maxRows = 1000;
        if ($payments->count() > $maxRows) {
            Toastr::error('Too many payments for view. Please narrow down.');
            return redirect()->back();
        }

        $settings = GeneralSetting::first();
        $logoData = $this->resolveLogoData($settings);
        $generatedAt = Carbon::now();

        $summary = [
            'count' => $payments->count(),
            'total_amount' => $payments->sum('amount'),
            'paid_amount' => $order->paid_amount,
            'due_amount' => $order->due_amount,
        ];

        $downloadUrl = route('admin.accounts.orders.payments.pdf', $order->id);

        return view('backend.accounts.payment_order_view', [
            'order' => $order,
            'payments' => $payments,
            'settings' => $settings,
            'logoData' => $logoData,
            'generatedAt' => $generatedAt,
            'summary' => $summary,
            'downloadUrl' => $downloadUrl,
        ]);
    }

    private function resolveLogoData(?GeneralSetting $settings): ?string
    {
        $logoPath = $settings?->site_logo ?: 'uploads/logo.png';
        $logoFullPath = public_path(ltrim($logoPath, '/'));
        if (!is_file($logoFullPath)) {
            return null;
        }

        $ext = strtolower(pathinfo($logoFullPath, PATHINFO_EXTENSION) ?: 'png');
        $mime = in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp'], true) ? $ext : 'png';
        return 'data:image/' . $mime . ';base64,' . base64_encode(file_get_contents($logoFullPath));
    }

    /**
     * Show the manual payment entry page.
     */
    public function create()
    {
        return view('backend.accounts.create');
    }

    /**
     * Search for an order by number for manual payment.
     */
    public function searchOrder(Request $request)
    {
        $request->validate([
            'order_no' => 'required|string',
        ]);

        $order = Order::with('user')->where('order_no', $request->order_no)->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found!',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'order_no' => $order->order_no,
                'customer_name' => $order->billing_name,
                'total_amount' => number_format($order->total_amount, 2),
                'paid_amount' => number_format($order->paid_amount, 2),
                'due_amount' => number_format($order->due_amount, 2),
                'due_raw' => $order->due_amount,
                'status' => ucfirst($order->status),
            ]
        ]);
    }

    /**
     * Store a new payment for an order.
     */
    public function storePayment(Request $request, Order $order)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string',
            'note' => 'nullable|string',
            'receipts' => 'nullable|array',
            'receipts.*' => 'file|mimes:jpg,jpeg,png,pdf,webp|max:5120',
        ]);

        $amount = (float) $request->amount;

        if ($amount > $order->due_amount) {
            Toastr::error('Payment amount cannot be greater than the due amount!');
            return redirect()->back();
        }

        // Create the payment record
        $payment = OrderPayment::create([
            'order_id' => $order->id,
            'amount' => $amount,
            'payment_method' => $request->payment_method,
            'transaction_id' => $request->transaction_id,
            'note' => $request->note,
        ]);

        if ($request->hasFile('receipts')) {
            foreach ($request->file('receipts') as $file) {
                if (!$file || !$file->isValid()) {
                    continue;
                }

                $storedPath = $file->store("order-payments/{$payment->id}", 'public');
                OrderPaymentReceipt::create([
                    'order_payment_id' => $payment->id,
                    'file_path' => $storedPath,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        // Update the order totals
        $order->paid_amount += $amount;
        $order->due_amount -= $amount;

        if ($order->due_amount <= 0) {
            $order->payment_status = 'paid';
        } else {
            $order->payment_status = 'partial';
        }

        $order->save();

        Toastr::success('Payment recorded successfully!');

        if ($request->filled('source') && $request->source === 'central_entry') {
            return redirect()->route('admin.accounts.index');
        }

        return redirect()->back();
    }

    public function downloadReceipt(OrderPaymentReceipt $receipt)
    {
        if (!Storage::disk('public')->exists($receipt->file_path)) {
            Toastr::error('Receipt file not found.');
            return redirect()->back();
        }

        $downloadName = $receipt->original_name ?: basename($receipt->file_path);
        return Storage::disk('public')->download($receipt->file_path, $downloadName);
    }

    public function destroyReceipt(OrderPaymentReceipt $receipt)
    {
        if ($receipt->file_path && Storage::disk('public')->exists($receipt->file_path)) {
            Storage::disk('public')->delete($receipt->file_path);
        }

        $receipt->delete();
        $message = 'Receipt deleted successfully.';

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => $message,
            ]);
        }

        Toastr::success($message);
        return redirect()->back();
    }
}
