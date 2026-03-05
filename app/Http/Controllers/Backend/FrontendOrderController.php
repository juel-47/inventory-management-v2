<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\OrderDataTable;
use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FrontendOrderController extends Controller
{
    /**
     * Display a listing of frontend orders.
     */
    public function index(OrderDataTable $dataTable)
    {
        return $dataTable->render('backend.orders.index');
    }

    /**
     * Display the specified order details.
     */
    public function show(Order $order)
    {
        $order->load(['items.product', 'items.variant', 'items.vendor', 'user', 'payments']);
        return view('backend.orders.show', compact('order'));
    }

    /**
     * View order invoice in browser (HTML).
     */
    public function viewInvoice(Order $order)
    {
        $order->load(['items.product', 'items.variant', 'user']);
        $settings = GeneralSetting::first();

        return view('backend.orders.invoice', compact('order', 'settings'));
    }

    /**
     * View order PI invoice in browser (HTML).
     */
    public function piInvoice(Order $order)
    {
        $order->load([
            'items.product.category',
            'items.product.subCategory',
            'items.product.childCategory',
            'items.product.brand',
            'items.product.vendor',
            'items.product.unit',
            'items.product.productType',
            'items.variant',
            'user'
        ]);
        $settings = GeneralSetting::first();

        return view('backend.orders.pi_invoice', compact('order', 'settings'));
    }

    /**
     * Download order invoice as PDF.
     */
    public function downloadInvoice(Order $order)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $order->load(['items.product', 'items.variant', 'user']);
        $settings = GeneralSetting::first();

        $pdf = Pdf::setOption([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'sans-serif',
        ])->loadView('backend.orders.print_pdf', compact('order', 'settings'));

        return $pdf->download('order-' . $order->order_no . '.pdf');
    }

    /**
     * Update status for an order.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,cancelled',
        ]);

        $order->status = $validated['status'];
        $order->save();

        Toastr::success('Order status updated successfully!');
        return redirect()->route('admin.orders.show', $order->id);
    }

    /**
     * Remove an order from storage.
     */
    public function destroy(Order $order): JsonResponse
    {
        $order->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Order deleted successfully!',
        ]);
    }
}
