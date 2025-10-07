<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class DailySalesReportMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $date,
        public float $totalAmount,
        public int $salesCount,
        public array $sellersSummary
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Relatório Diário de Vendas - ' . Carbon::parse($this->date)->format('d/m/Y'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.daily-sales-report',
            with: [
                'date' => $this->date,
                'totalAmount' => $this->totalAmount,
                'salesCount' => $this->salesCount,
                'sellersSummary' => $this->sellersSummary,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
