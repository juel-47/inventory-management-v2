<?php

namespace App\Jobs;

use App\Models\GeneralSetting;
use App\Models\Order;
use App\Support\PdfImageHelper;
use App\Support\PiInfoSupport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GeneratePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $orderId;
    public $type;
    public $timeout = 600; // 10 minutes max execution time for very large PDFs

    /**
     * Create a new job instance.
     */
    public function __construct($orderId, $type)
    {
        $this->orderId = $orderId;
        $this->type = $type;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $order = Order::find($this->orderId);
        if (!$order) {
            return;
        }

        $settings = GeneralSetting::first();

        if ($this->type === 'invoice') {
            $this->generateInvoice($order, $settings);
        } elseif ($this->type === 'pi_invoice') {
            $this->generatePiInvoice($order, $settings);
        } elseif ($this->type === 'customer_invoice') {
            $this->generateCustomerInvoice($order, $settings);
        }
    }

    private function generateInvoice(Order $order, $settings)
    {
        $order->load(['items.product', 'items.variant.color', 'items.variant.size', 'user']);
        $piInfo = PiInfoSupport::prepare($order->pi_info, $order->items, 'quantity');
        $piTotals = PiInfoSupport::summarize($piInfo);
        $hasSavedPiInfo = PiInfoSupport::hasContent($order->pi_info);

        $itemCount = $order->items->count();

        if ($itemCount <= 500) {
            foreach ($order->items as $item) {
                $item->optimized_image = PdfImageHelper::optimize($item->product_image, 80, 80);
            }
        }

        $pdf = Pdf::setOption([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'sans-serif',
        ])->loadView('backend.orders.print_pdf', compact('order', 'settings', 'piInfo', 'piTotals', 'hasSavedPiInfo', 'itemCount'));

        $path = 'invoices/invoice-' . $order->order_no . '.pdf';
        Storage::disk('public')->put($path, $pdf->output());
    }

    private function generatePiInvoice(Order $order, $settings)
    {
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
        
        $piInfo = PiInfoSupport::prepare($order->pi_info, $order->items, 'quantity');
        $piTotals = PiInfoSupport::summarize($piInfo);
        $hasSavedPiInfo = PiInfoSupport::hasContent($order->pi_info);

        $itemCount = $order->items->count();

        $logoPath = optional($settings)->site_logo ?: 'uploads/logo.png';
        $settings->optimized_logo = PdfImageHelper::optimize($logoPath, 160, 40);

        if ($itemCount <= 500) {
            foreach ($order->items as $item) {
                $item->optimized_image = PdfImageHelper::optimize($item->product_image, 80, 80);
            }
        }

        $pdf = Pdf::setOption([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'sans-serif',
        ])->loadView('backend.orders.pi_invoice', compact('order', 'settings', 'piInfo', 'piTotals', 'hasSavedPiInfo', 'itemCount') + ['isPdf' => true]);

        $path = 'invoices/pi-invoice-' . $order->order_no . '.pdf';
        Storage::disk('public')->put($path, $pdf->output());
    }

    private function generateCustomerInvoice(Order $order, $settings)
    {
        $order->load(['items.product', 'items.variant.color', 'items.variant.size', 'user']);
        $itemCount = $order->items->count();

        $logoPath = optional($settings)->site_logo ?: 'uploads/logo.png';
        $settings->optimized_logo = PdfImageHelper::optimize($logoPath, 120, 30);

        if ($itemCount <= 500) {
            foreach ($order->items as $item) {
                $item->optimized_image = PdfImageHelper::optimize($item->product_image, 60, 60);
            }
        }

        $pdf = Pdf::loadView('backend.orders.customer_invoice', compact('order', 'settings', 'itemCount'));
        
        $path = 'invoices/customer-invoice-' . $order->order_no . '.pdf';
        Storage::disk('public')->put($path, $pdf->output());
    }
}
