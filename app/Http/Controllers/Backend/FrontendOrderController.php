<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\OrderDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\FrontendOrder\OrderSavePiInfoRequest;
use App\Http\Requests\FrontendOrder\OrderUpdateStatusRequest;
use App\Models\GeneralSetting;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use App\Support\PiInfoSupport;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class FrontendOrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function index(OrderDataTable $dataTable)
    {
        $users = User::role(['Outlet User', 'User'])->active()->orderBy('name')->get(['id', 'name', 'outlet_name']);
        return $dataTable->render('backend.orders.index', compact('users'));
    }

    public function show(Order $order)
    {
        $order->reconcileTotals();
        $order->refresh();
        $order->load(['items.product', 'items.variant.color', 'items.variant.size', 'items.vendor', 'user', 'payments.receipts']);

        $items = $this->orderService->getIssuedItems($order);
        $piInfo = PiInfoSupport::prepare($order->pi_info, $items, 'quantity');
        $piTotals = PiInfoSupport::summarize($piInfo);
        $hasSavedPiInfo = PiInfoSupport::hasContent($order->pi_info);

        return view('backend.orders.show', compact('order', 'piInfo', 'piTotals', 'hasSavedPiInfo', 'items'));
    }

    public function viewInvoice(Order $order)
    {
        $order->reconcileTotals();
        $order->refresh();
        $order->load(['items.product', 'items.variant.color', 'items.variant.size', 'user']);
        $settings = GeneralSetting::first();

        $issuedItems = $this->orderService->getIssuedItems($order);
        $piInfo = PiInfoSupport::prepare($order->pi_info, $issuedItems, 'quantity');
        $piTotals = PiInfoSupport::summarize($piInfo);
        $hasSavedPiInfo = PiInfoSupport::hasContent($order->pi_info);

        return view('backend.orders.invoice', compact('order', 'settings', 'piInfo', 'piTotals', 'hasSavedPiInfo', 'issuedItems'));
    }

    public function piInvoice(Order $order)
    {
        $order->reconcileTotals();
        $order->refresh();
        $order->load([
            'items.product.category',
            'items.product.subCategory',
            'items.product.childCategory',
            'items.product.brand',
            'items.product.vendor',
            'items.product.unit',
            'items.product.productType',
            'items.variant.color',
            'items.variant.size',
            'user'
        ]);
        $settings = GeneralSetting::first();

        $issuedItems = $this->orderService->getIssuedItems($order);
        $piInfo = PiInfoSupport::prepare($order->pi_info, $issuedItems, 'quantity');
        $piTotals = PiInfoSupport::summarize($piInfo);
        $hasSavedPiInfo = PiInfoSupport::hasContent($order->pi_info);

        $downloadUrl = route('admin.orders.pi-invoice.download', $order->id);

        return view('backend.orders.pi_invoice', compact('order', 'settings', 'piInfo', 'piTotals', 'hasSavedPiInfo', 'downloadUrl', 'issuedItems'));
    }

    public function savePiInfo(OrderSavePiInfoRequest $request, Order $order)
    {
        $this->orderService->savePiInfo($order, $request->validated());

        Toastr::success('PI info saved successfully!');
        return redirect()->route('admin.orders.show', $order->id);
    }

    public function downloadInvoice(Order $order)
    {
        $path = 'invoices/invoice-' . $order->order_no . '.pdf';

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->download($path);
        }

        \App\Jobs\GeneratePdfJob::dispatchSync($order->id, 'invoice', \Illuminate\Support\Facades\Auth::id());
        return Storage::disk('public')->download($path);
    }

    public function downloadPiInvoice(Order $order)
    {
        $path = 'invoices/pi-invoice-' . $order->order_no . '.pdf';

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->download($path);
        }

        \App\Jobs\GeneratePdfJob::dispatch($order->id, 'pi_invoice', \Illuminate\Support\Facades\Auth::id());

        Toastr::info('PI Invoice is generating in the background. Please refresh and click download again after a minute.');
        return redirect()->back();
    }

    public function downloadCustomerInvoice(Order $order)
    {
        $path = 'invoices/customer-invoice-' . $order->order_no . '.pdf';

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->download($path);
        }

        \App\Jobs\GeneratePdfJob::dispatch($order->id, 'customer_invoice', \Illuminate\Support\Facades\Auth::id());

        Toastr::info('Customer Invoice is generating in the background. Please refresh and click download again after a minute.');
        return redirect()->back();
    }

    public function updateStatus(OrderUpdateStatusRequest $request, Order $order)
    {
        $order->status = $request->validated()['status'];
        $order->save();

        Toastr::success('Order status updated successfully!');
        return redirect()->route('admin.orders.show', $order->id);
    }

    public function destroy(Order $order): JsonResponse
    {
        $order->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Order deleted successfully!',
        ]);
    }
}
