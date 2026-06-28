<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Product;
use App\Models\PricingRule;
use App\Models\Purchase;
use App\Models\PurchaseAttachment;
use App\Models\Vendor;
use App\Services\PurchaseService;
use App\Support\StoredFileSupport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Brian2694\Toastr\Facades\Toastr;

class PurchaseController extends Controller
{
    public function __construct(
        private PurchaseService $purchaseService
    ) {}

    public function index()
    {
        $purchases = Purchase::with(['vendor', 'user', 'details', 'attachments'])->orderBy('id', 'desc')->get();
        return view('backend.purchase.index', compact('purchases'));
    }

    public function create(Request $request)
    {
        $vendors = Vendor::active()->get();
        $products = Product::active()->with('variants.color', 'variants.size')->orderByDesc('id')->get();

        $pricingRules = PricingRule::active()->orderByDesc('is_default')->orderBy('name')->get();
        $defaultPricingRuleId = optional($pricingRules->firstWhere('is_default', true))->id;

        $bookings = Booking::with('vendor')
            ->where('status', 'pending')
            ->select(
                'booking_no',
                'vendor_id',
                \Illuminate\Support\Facades\DB::raw('MIN(id) as id'),
                \Illuminate\Support\Facades\DB::raw('count(product_id) as product_count')
            )
            ->groupBy('booking_no', 'vendor_id')
            ->orderByDesc('id')
            ->get();

        $selectedIds = [];
        if ($request->has('ids')) {
            $selectedIds = explode(',', $request->ids);
        }

        return view('backend.purchase.create', compact('vendors', 'products', 'bookings', 'pricingRules', 'defaultPricingRuleId', 'selectedIds'));
    }

    public function getBookingDetails(Request $request)
    {
        $booking = Booking::with(['product', 'vendor', 'unit'])->findOrFail($request->id);

        $bookings = Booking::with(['product', 'vendor', 'unit'])
            ->where('booking_no', $booking->booking_no)
            ->get();

        $response = $bookings->toArray();
        if (count($response) > 0 && isset($response[0]['shipping_method'])) {
            foreach ($response as &$item) {
                $item['shipping_method'] = $response[0]['shipping_method'];
            }
        }

        return response()->json($response);
    }

    public function store(\App\Http\Requests\Purchase\PurchaseStoreRequest $request)
    {
        try {
            $purchase = $this->purchaseService->createPurchase(
                $request->validated(),
                $request->input('items', []),
                $request->file('invoice_attachment')
            );

            Toastr::success('Purchase Created Successfully!');
            return redirect()->route('admin.purchases.index');
        } catch (\Exception $e) {
            Toastr::error('Something went wrong: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function show(string $id)
    {
        $purchase = Purchase::with(['vendor', 'user', 'details.product', 'attachments', 'payments.receipts'])->findOrFail($id);
        return view('backend.purchase.show', compact('purchase'));
    }

    public function viewInvoice(string $id)
    {
        $purchase = Purchase::with(['vendor', 'user', 'details.product', 'attachments'])->findOrFail($id);
        $settings = \App\Models\GeneralSetting::first();
        return view('backend.purchase.invoice', compact('purchase', 'settings'));
    }

    public function downloadPdf(string $id)
    {
        $purchase = Purchase::findOrFail($id);
        $path = 'purchases/purchase_' . $purchase->invoice_no . '.pdf';

        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->download($path);
        }

        \App\Jobs\GeneratePurchasePdfJob::dispatch($purchase->id, Auth::id());

        Toastr::info('Purchase PDF is generating in the background. Please refresh and click download again after a minute.');
        return redirect()->back();
    }

    public function uploadAttachments(\App\Http\Requests\Purchase\PurchaseUploadAttachmentsRequest $request, string $id)
    {
        $purchase = Purchase::findOrFail($id);

        try {
            $this->purchaseService->uploadAttachments($purchase, $request->file('invoice_attachments', []));
            Toastr::success('Invoice attachment uploaded successfully.');
        } catch (\Exception $e) {
            Toastr::error('Upload failed: ' . $e->getMessage());
        }

        return redirect()->back();
    }

    public function deleteAttachment(string $id, string $attachmentId)
    {
        $purchase = Purchase::findOrFail($id);

        try {
            $this->purchaseService->deleteAttachment($purchase, (int) $attachmentId);
            Toastr::success('Attachment deleted successfully.');
        } catch (\Exception $e) {
            Toastr::error('Delete failed: ' . $e->getMessage());
        }

        return redirect()->back();
    }

    public function downloadAttachment(string $id, string $attachmentId)
    {
        $purchase = Purchase::findOrFail($id);
        $attachment = PurchaseAttachment::where('purchase_id', $purchase->id)->findOrFail($attachmentId);
        $downloadName = $attachment->original_name ?: basename($attachment->file_path);
        $response = StoredFileSupport::download($attachment->file_path, $downloadName);

        if (!$response) {
            Toastr::error('Attachment file not found.');
            return redirect()->back();
        }

        return $response;
    }

    public function downloadLegacyAttachment(string $id)
    {
        $purchase = Purchase::findOrFail($id);
        $downloadName = $purchase->invoice_attachment ? basename($purchase->invoice_attachment) : null;
        $response = StoredFileSupport::download($purchase->invoice_attachment, $downloadName);

        if (!$response) {
            Toastr::error('Attachment file not found.');
            return redirect()->back();
        }

        return $response;
    }

    public function destroy(string $id)
    {
        try {
            $this->purchaseService->deletePurchase((int) $id);
            return response(['status' => 'success', 'message' => 'Purchase Deleted and Stock Reverted Successfully!']);
        } catch (\Exception $e) {
            return response(['status' => 'error', 'message' => 'Something went wrong: ' . $e->getMessage()]);
        }
    }
}
