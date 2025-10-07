<?php

namespace App\Jobs;

use App\Mail\DailySalesReportMail;
use App\Services\SaleService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendDailySalesReportJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $date,
        public string $adminEmail
    ) {}

    public function handle(SaleService $saleService): void
    {
        $summary = $saleService->getDailySalesSummary($this->date);

        Mail::to($this->adminEmail)->send(
            new DailySalesReportMail(
                $this->date,
                $summary['total_amount'],
                $summary['sales_count'],
                $summary['sellers_summary']->toArray()
            )
        );
    }
}
