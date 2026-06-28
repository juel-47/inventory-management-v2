<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use App\Models\Order;
use App\Services\OrderService;
use App\Support\PiInfoSupport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function index(Request $request)
    {
        $params = ['panel' => 'orders'];
        if ($request->filled('page')) {
            $params['page'] = (int) $request->query('page');
        }

        return redirect()->route('account.index', $params);
    }

    public function show(Order $order)
    {
        abort_if((int) $order->user_id !== (int) Auth::id(), 403);

        $order->load(['items.product', 'items.variant', 'user']);

        return view('frontend.pages.orders.show', compact('order'));
    }

    public function piInvoice(Order $order)
    {
        abort_if((int) $order->user_id !== (int) Auth::id(), 403);

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
            'user',
        ]);

        $settings = GeneralSetting::first();
        $piInfo = PiInfoSupport::prepare($order->pi_info, $order->items, 'quantity');
        $piTotals = PiInfoSupport::summarize($piInfo);
        $hasSavedPiInfo = PiInfoSupport::hasContent($order->pi_info);

        return view('backend.orders.pi_invoice', [
            'order' => $order,
            'settings' => $settings,
            'piInfo' => $piInfo,
            'piTotals' => $piTotals,
            'hasSavedPiInfo' => $hasSavedPiInfo,
            'isFrontend' => true,
            'backUrl' => route('orders.show', $order->id),
            'downloadUrl' => route('orders.pi-invoice.download', $order->id),
        ]);
    }

    public function downloadPiInvoice(Order $order)
    {
        abort_if((int) $order->user_id !== (int) Auth::id(), 403);

        $path = 'invoices/pi-invoice-' . $order->order_no . '.pdf';

        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->download($path);
        }

        \App\Jobs\GeneratePdfJob::dispatch($order->id, 'pi_invoice', Auth::id());

        return redirect()->back()->with('success', 'PI Invoice generation has been queued. Please refresh the page and download again when ready.');
    }

    public function reorder(Order $order)
    {
        $user = Auth::user();
        abort_if((int) $order->user_id !== (int) $user->id, 403);

        $result = $this->orderService->reorder($order, $user);

        if (!$result['success']) {
            return redirect()
                ->route('orders.show', $order->id)
                ->with('error', $result['error']);
        }

        return redirect()
            ->route('orders.show', $result['order']->id)
            ->with('success', 'Reorder placed successfully. Reference: ' . $result['order']->order_no);
    }
}
