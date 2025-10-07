<?php

namespace App\Jobs;

use App\Mail\DailyCommissionMail;
use App\Models\Seller;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendDailyCommissionJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Seller $seller,
        public string $date
    ) {}

    public function handle(): void
    {
        $salesCount = $this->seller->getSalesCountForDate($this->date);
        $totalAmount = $this->seller->getTotalSalesForDate($this->date);
        $totalCommission = $this->seller->getTotalCommissionForDate($this->date);

        Mail::to($this->seller->email)->send(
            new DailyCommissionMail(
                $this->seller,
                $this->date,
                $salesCount,
                $totalAmount,
                $totalCommission
            )
        );
    }
}
