<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewProductsAnnouncementMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param array<int, array<string, mixed>> $products
     */
    public function __construct(
        public string $recipientName,
        public string $source,
        public int $totalProducts,
        public array $products,
        public int $hiddenCount = 0
    ) {
    }

    public function envelope(): Envelope
    {
        $subject = $this->source === 'imported'
            ? 'New Products Imported (' . $this->totalProducts . ')'
            : ($this->totalProducts === 1
                ? 'New Product Added'
                : 'New Products Added (' . $this->totalProducts . ')');

        return new Envelope(
            subject: $subject . ' | ' . config('app.name')
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.new_products_announcement',
            with: [
                'recipientName' => $this->recipientName,
                'source' => $this->source,
                'totalProducts' => $this->totalProducts,
                'products' => $this->products,
                'hiddenCount' => $this->hiddenCount,
                'shopUrl' => url('/shop'),
            ]
        );
    }

    /**
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
