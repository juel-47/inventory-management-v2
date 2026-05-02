<?php

namespace App\Jobs;

use App\Models\GeneralSetting;
use App\Models\Booking;
use App\Support\PdfImageHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateBookingPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $bookingId;
    public $timeout = 3600; // 1 hour timeout

    public function __construct($bookingId)
    {
        $this->bookingId = $bookingId;
    }

    public function handle(): void
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0); // Infinite time limit for CLI process

        $targetBooking = Booking::find($this->bookingId);
        if (!$targetBooking) {
            return;
        }

        $orderGroup = Booking::where('booking_no', $targetBooking->booking_no)
            ->with(['product.variants.color', 'product.variants.size', 'vendor', 'unit'])
            ->get();
        
        $settings = GeneralSetting::first();

        $logoPath = optional($settings)->site_logo ?: 'uploads/logo.png';
        $settings->optimized_logo = PdfImageHelper::optimize($logoPath, 180, 46);

        foreach ($orderGroup as $item) {
            if ($item->product && $item->product->thumb_image) {
                $item->product->optimized_image = PdfImageHelper::optimize($item->product->thumb_image, 60, 60);
            }
        }

        $pdf = Pdf::loadView('backend.booking.print_pdf', compact('orderGroup', 'targetBooking', 'settings'));
        
        $path = 'bookings/booking_' . $targetBooking->booking_no . '.pdf';
        Storage::disk('public')->put($path, $pdf->output());

        // We could notify the user here if needed
        // Auth is not available here, but we can assume admins generate bookings
    }
}
