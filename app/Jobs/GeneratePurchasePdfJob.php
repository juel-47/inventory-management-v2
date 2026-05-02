<?php

namespace App\Jobs;

use App\Models\GeneralSetting;
use App\Models\Purchase;
use App\Support\PdfImageHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GeneratePurchasePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $purchaseId;
    public $timeout = 0; // Infinite timeout

    public function __construct($purchaseId)
    {
        $this->purchaseId = $purchaseId;
    }

    public function handle(): void
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '1200');
        set_time_limit(1200);

        $purchase = Purchase::with(['vendor', 'user', 'details.product', 'attachments'])->find($this->purchaseId);
        if (!$purchase) {
            return;
        }

        $settings = GeneralSetting::first();
        
        $logoPath = optional($settings)->site_logo ?: 'uploads/logo.png';
        $settings->optimized_logo = PdfImageHelper::optimize($logoPath, 180, 46);

        foreach ($purchase->details as $detail) {
            if ($detail->product && $detail->product->thumb_image) {
                $detail->product->optimized_image = PdfImageHelper::optimize($detail->product->thumb_image, 60, 60);
            }
        }

        $pdf = Pdf::loadView('backend.purchase.print_pdf', compact('purchase', 'settings'));
        
        $path = 'purchases/purchase_' . $purchase->invoice_no . '.pdf';
        Storage::disk('public')->put($path, $pdf->output());

        // Notify user if needed (reusing the same notification concept, or generic)
        if ($purchase->user) {
            // Re-using PdfReadyNotification
            // $purchase->user->notify(new \App\Notifications\PdfReadyNotification($purchase, 'purchase'));
        }
    }
}
