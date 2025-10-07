<?php

namespace App\Mail;

use App\Models\Seller;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailyCommissionMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Seller $seller,
        public string $date,
        public int $salesCount,
        public float $totalAmount,
        public float $totalCommission
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Relatório Diário de Comissões - ' . $this->date,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.daily-commission',
            with: [
                'seller' => $this->seller,
                'date' => $this->date,
                'salesCount' => $this->salesCount,
                'totalAmount' => $this->totalAmount,
                'totalCommission' => $this->totalCommission,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
