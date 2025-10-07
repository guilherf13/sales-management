<?php

namespace App\Console\Commands;

use App\Jobs\SendDailyCommissionJob;
use App\Jobs\SendDailySalesReportJob;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Console\Command;

class SendDailyEmailsCommand extends Command
{
    protected $signature = 'sales:send-daily-emails {--date=}';
    protected $description = 'Send daily commission emails to sellers and sales report to admins';

    public function handle(): int
    {
        $date = $this->option('date') ?: now()->subDay()->format('Y-m-d');

        $this->info("Sending daily emails for date: {$date}");

        // Send commission emails to all sellers
        $sellers = Seller::all();
        $this->info("Found {$sellers->count()} sellers");

        foreach ($sellers as $seller) {
            SendDailyCommissionJob::dispatch($seller, $date);
            $this->line("Queued commission email for: {$seller->name}");
        }

        // Send daily sales report to all admins (Gestor)
        $admins = User::where('perfil', 'Gestor')->get();
        $this->info("Found {$admins->count()} admin(s)");

        foreach ($admins as $admin) {
            SendDailySalesReportJob::dispatch($date, $admin->email);
            $this->line("Queued sales report for admin: {$admin->name} ({$admin->email})");
        }

        $this->info('All emails have been queued successfully!');
        return Command::SUCCESS;
    }
}
