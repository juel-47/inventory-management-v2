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
    public $timeout = 0; // 0 means infinite timeout, will never fail due to time

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
        ini_set('max_execution_time', '1200');
        set_time_limit(1200);

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

        // Notify the user via Cache without using database tables
        /*
        if ($order && $order->user_id) {
            $title = 'PDF Ready';
            $message = 'Your PDF is ready to download.';
            $link = '#';

            if ($this->type === 'invoice') {
                $title = 'Invoice Ready';
                $message = "Invoice for Order #{$order->order_no} is ready.";
                $link = route('admin.orders.download-invoice', $order->id);
            } elseif ($this->type === 'pi_invoice') {
                $title = 'PI Invoice Ready';
                $message = "PI Invoice for Order #{$order->order_no} is ready.";
                $link = route('admin.orders.pi-invoice.download', $order->id); 
            } elseif ($this->type === 'customer_invoice') {
                $title = 'Customer Invoice Ready';
                $message = "Customer Invoice for Order #{$order->order_no} is ready.";
                $link = route('admin.orders.download-customer-invoice', $order->id);
            }

            $this->addCacheNotification($order->user_id, [
                'type' => 'pdf_ready',
                'title' => $title,
                'desc' => $message,
                'url' => $link,
                'icon' => 'fas fa-file-pdf',
                'class' => 'bg-success',
            ]);
        }
        */
    }

    private function generateInvoice(Order $order, $settings)
    {
        $order->load(['items.product', 'items.variant.color', 'items.variant.size', 'user']);
        $piInfo = PiInfoSupport::prepare($order->pi_info, $order->items, 'quantity');
        $piTotals = PiInfoSupport::summarize($piInfo);
        $hasSavedPiInfo = PiInfoSupport::hasContent($order->pi_info);

        $itemCount = $order->items->count();

        foreach ($order->items as $item) {
            $item->optimized_image = PdfImageHelper::optimize($item->product_image, 80, 80);
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

        foreach ($order->items as $item) {
            $item->optimized_image = PdfImageHelper::optimize($item->product_image, 80, 80);
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

        foreach ($order->items as $item) {
            $item->optimized_image = PdfImageHelper::optimize($item->product_image, 60, 60);
        }

        $pdf = Pdf::loadView('backend.orders.customer_invoice', compact('order', 'settings', 'itemCount'));
        
        $path = 'invoices/customer-invoice-' . $order->order_no . '.pdf';
        Storage::disk('public')->put($path, $pdf->output());
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        /*
        $order = Order::find($this->orderId);
        if ($order && $order->user_id) {
            $this->addCacheNotification($order->user_id, [
                'type' => 'pdf_failed',
                'title' => 'PDF Generation Failed',
                'desc' => "Failed to generate PDF for Order #{$order->order_no}. Please try again.",
                'url' => '#',
                'icon' => 'fas fa-times-circle',
                'class' => 'bg-danger',
            ]);
        }
        */
    }

    /**
     * Add notification to cache
     */
    private function addCacheNotification($userId, $data)
    {
        /*
        $notifications = \Illuminate\Support\Facades\Cache::get('user_notifications_' . $userId, []);
        
        $data['time'] = now()->diffForHumans();
        $data['timestamp'] = now()->timestamp;
        $data['is_unread'] = true;
        $data['is_out_of_stock'] = false;
        
        $notifications[] = $data;
        
        // Keep only the latest 20 notifications in cache
        $notifications = array_slice($notifications, -20);
        
        \Illuminate\Support\Facades\Cache::put('user_notifications_' . $userId, $notifications, now()->addDays(7));
        */
    }
}
