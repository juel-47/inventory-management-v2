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
    public $timeout = 3600; // 1 hour timeout for large PDFs

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
        set_time_limit(0); // Infinite time limit for CLI process

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
        
        // Optimize images in PI Info blocks for faster PDF rendering
        if (isset($piInfo['blocks']) && is_array($piInfo['blocks'])) {
            foreach ($piInfo['blocks'] as &$block) {
                if (!empty($block['image'])) {
                    $block['optimized_image'] = PdfImageHelper::optimize($block['image'], 80, 80);
                }
            }
        }

        $piTotals = PiInfoSupport::summarize($piInfo);
        $hasSavedPiInfo = PiInfoSupport::hasContent($order->pi_info);

        $itemCount = $order->items->count();

        \Illuminate\Support\Facades\Log::info("GeneratePdfJob: Processing {$itemCount} items for Invoice Order #{$order->order_no}");
        $processed = 0;
        foreach ($order->items as $item) {
            $item->optimized_image = PdfImageHelper::optimize($item->product_image, 80, 80);
            $processed++;
            if ($processed % 500 === 0) {
                \Illuminate\Support\Facades\Log::info("GeneratePdfJob: Processed {$processed}/{$itemCount} items for Invoice Order #{$order->order_no}");
            }
        }
        \Illuminate\Support\Facades\Log::info("GeneratePdfJob: Rendering DOMPDF for Invoice Order #{$order->order_no}...");

        $pdf = Pdf::setOption([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'sans-serif',
            'enable_remote' => false,
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
        
        // Optimize images in PI Info blocks for faster PDF rendering
        if (isset($piInfo['blocks']) && is_array($piInfo['blocks'])) {
            foreach ($piInfo['blocks'] as &$block) {
                if (!empty($block['image'])) {
                    $block['optimized_image'] = PdfImageHelper::optimize($block['image'], 80, 80);
                }
            }
        }

        $piTotals = PiInfoSupport::summarize($piInfo);
        $hasSavedPiInfo = PiInfoSupport::hasContent($order->pi_info);

        $itemCount = $order->items->count();

        $logoPath = optional($settings)->site_logo ?: 'uploads/logo.png';
        $settings->optimized_logo = PdfImageHelper::optimize($logoPath, 160, 40);

        \Illuminate\Support\Facades\Log::info("GeneratePdfJob: Processing {$itemCount} items for PI Invoice Order #{$order->order_no}");
        $processed = 0;
        foreach ($order->items as $item) {
            $item->optimized_image = PdfImageHelper::optimize($item->product_image, 80, 80);
            $processed++;
            if ($processed % 500 === 0) {
                \Illuminate\Support\Facades\Log::info("GeneratePdfJob: Processed {$processed}/{$itemCount} items for PI Invoice Order #{$order->order_no}");
            }
        }
        \Illuminate\Support\Facades\Log::info("GeneratePdfJob: Rendering DOMPDF for PI Invoice Order #{$order->order_no}...");

        $pdf = Pdf::setOption([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'sans-serif',
            'enable_remote' => false,
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

        \Illuminate\Support\Facades\Log::info("GeneratePdfJob: Processing {$itemCount} items for Customer Invoice Order #{$order->order_no}");
        $processed = 0;
        foreach ($order->items as $item) {
            $item->optimized_image = PdfImageHelper::optimize($item->product_image, 60, 60);
            $processed++;
            if ($processed % 500 === 0) {
                \Illuminate\Support\Facades\Log::info("GeneratePdfJob: Processed {$processed}/{$itemCount} items for Customer Invoice Order #{$order->order_no}");
            }
        }
        \Illuminate\Support\Facades\Log::info("GeneratePdfJob: Rendering DOMPDF for Customer Invoice Order #{$order->order_no}...");

        $pdf = Pdf::setOption([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'sans-serif',
            'enable_remote' => false,
        ])->loadView('backend.orders.customer_invoice', compact('order', 'settings', 'itemCount'));
        
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
