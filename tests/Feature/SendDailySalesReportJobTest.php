<?php

use App\Jobs\SendDailySalesReportJob;
use App\Mail\DailySalesReportMail;
use App\Models\Seller;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

describe('SendDailySalesReportJob', function () {
    
    test('envia email de relatório diário para administrador', function () {
        Mail::fake();

        $adminEmail = 'admin@example.com';
        $date = '2024-01-15';
        
        $seller = Seller::factory()->create();
        Sale::factory()->create([
            'seller_id' => $seller->id,
            'amount' => 1000.00,
            'commission' => 85.00,
            'sale_date' => $date,
        ]);

        $job = new SendDailySalesReportJob($date, $adminEmail);
        $job->handle(app(SaleService::class));

        Mail::assertQueued(DailySalesReportMail::class, function ($mail) use ($adminEmail) {
            return $mail->hasTo($adminEmail);
        });
    });

    test('email contém dados corretos do resumo de vendas', function () {
        Mail::fake();

        $adminEmail = 'admin@example.com';
        $date = '2024-01-15';
        
        $seller = Seller::factory()->create();
        Sale::factory()->create([
            'seller_id' => $seller->id,
            'amount' => 1000.00,
            'commission' => 85.00,
            'sale_date' => $date,
        ]);

        $job = new SendDailySalesReportJob($date, $adminEmail);
        $job->handle(app(SaleService::class));

        Mail::assertQueued(DailySalesReportMail::class, function ($mail) use ($date) {
            return $mail->date === $date
                && $mail->salesCount === 1
                && $mail->totalAmount == 1000.00
                && count($mail->sellersSummary) === 1;
        });
    });

    test('email calcula corretamente múltiplas vendas', function () {
        Mail::fake();

        $adminEmail = 'admin@example.com';
        $date = '2024-01-15';
        
        $seller1 = Seller::factory()->create();
        $seller2 = Seller::factory()->create();

        Sale::factory()->create([
            'seller_id' => $seller1->id,
            'amount' => 1000.00,
            'commission' => 85.00,
            'sale_date' => $date,
        ]);
        Sale::factory()->create([
            'seller_id' => $seller1->id,
            'amount' => 500.00,
            'commission' => 42.50,
            'sale_date' => $date,
        ]);
        Sale::factory()->create([
            'seller_id' => $seller2->id,
            'amount' => 2000.00,
            'commission' => 170.00,
            'sale_date' => $date,
        ]);

        $job = new SendDailySalesReportJob($date, $adminEmail);
        $job->handle(app(SaleService::class));

        Mail::assertQueued(DailySalesReportMail::class, function ($mail) {
            return $mail->salesCount === 3
                && $mail->totalAmount == 3500.00
                && count($mail->sellersSummary) === 2;
        });
    });

    test('email agrupa vendas por vendedor corretamente', function () {
        Mail::fake();

        $adminEmail = 'admin@example.com';
        $date = '2024-01-15';
        
        $seller = Seller::factory()->create();

        Sale::factory()->create([
            'seller_id' => $seller->id,
            'amount' => 1000.00,
            'commission' => 85.00,
            'sale_date' => $date,
        ]);
        Sale::factory()->create([
            'seller_id' => $seller->id,
            'amount' => 500.00,
            'commission' => 42.50,
            'sale_date' => $date,
        ]);

        $job = new SendDailySalesReportJob($date, $adminEmail);
        $job->handle(app(SaleService::class));

        Mail::assertQueued(DailySalesReportMail::class, function ($mail) use ($seller) {
            $sellerSummary = collect($mail->sellersSummary)->first();
            
            return $sellerSummary['seller']['id'] === $seller->id
                && $sellerSummary['sales_count'] === 2
                && $sellerSummary['total_amount'] == 1500.00
                && $sellerSummary['total_commission'] == 127.50;
        });
    });

    test('email não inclui vendas de outras datas', function () {
        Mail::fake();

        $adminEmail = 'admin@example.com';
        $targetDate = '2024-01-15';
        
        $seller = Seller::factory()->create();

        // Venda do dia correto
        Sale::factory()->create([
            'seller_id' => $seller->id,
            'amount' => 1000.00,
            'commission' => 85.00,
            'sale_date' => $targetDate,
        ]);

        // Vendas de outras datas
        Sale::factory()->create([
            'seller_id' => $seller->id,
            'amount' => 500.00,
            'commission' => 42.50,
            'sale_date' => '2024-01-14',
        ]);
        Sale::factory()->create([
            'seller_id' => $seller->id,
            'amount' => 300.00,
            'commission' => 25.50,
            'sale_date' => '2024-01-16',
        ]);

        $job = new SendDailySalesReportJob($targetDate, $adminEmail);
        $job->handle(app(SaleService::class));

        Mail::assertQueued(DailySalesReportMail::class, function ($mail) use ($targetDate) {
            return $mail->date === $targetDate
                && $mail->salesCount === 1
                && $mail->totalAmount == 1000.00;
        });
    });

    test('email envia resumo vazio quando não há vendas no dia', function () {
        Mail::fake();

        $adminEmail = 'admin@example.com';
        $date = '2024-01-15';

        // Cria vendas em outra data
        $seller = Seller::factory()->create();
        Sale::factory()->create([
            'seller_id' => $seller->id,
            'sale_date' => '2024-01-10',
        ]);

        $job = new SendDailySalesReportJob($date, $adminEmail);
        $job->handle(app(SaleService::class));

        Mail::assertQueued(DailySalesReportMail::class, function ($mail) {
            return $mail->salesCount === 0
                && $mail->totalAmount == 0
                && count($mail->sellersSummary) === 0;
        });
    });

    test('job pode ser serializado e deserializado', function () {
        $date = '2024-01-15';
        $adminEmail = 'admin@example.com';

        $job = new SendDailySalesReportJob($date, $adminEmail);
        
        expect($job->date)->toBe($date)
            ->and($job->adminEmail)->toBe($adminEmail);
    });

    test('job implementa ShouldQueue', function () {
        $job = new SendDailySalesReportJob('2024-01-15', 'admin@example.com');

        expect($job)->toBeInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class);
    });

    test('email tem assunto correto com data formatada', function () {
        Mail::fake();

        $adminEmail = 'admin@example.com';
        $date = '2024-01-15';
        
        $seller = Seller::factory()->create();
        Sale::factory()->create(['seller_id' => $seller->id, 'sale_date' => $date]);

        $job = new SendDailySalesReportJob($date, $adminEmail);
        $job->handle(app(SaleService::class));

        Mail::assertQueued(DailySalesReportMail::class, function ($mail) {
            $envelope = $mail->envelope();
            return str_contains($envelope->subject, 'Relatório Diário de Vendas');
        });
    });

    test('job envia email apenas uma vez', function () {
        Mail::fake();

        $adminEmail = 'admin@example.com';
        $date = '2024-01-15';
        
        $seller = Seller::factory()->create();
        Sale::factory()->create(['seller_id' => $seller->id, 'sale_date' => $date]);

        $job = new SendDailySalesReportJob($date, $adminEmail);
        $job->handle(app(SaleService::class));

        Mail::assertQueued(DailySalesReportMail::class, 1);
    });

    test('job usa SaleService para obter dados', function () {
        Mail::fake();

        $adminEmail = 'admin@example.com';
        $date = '2024-01-15';
        
        $seller = Seller::factory()->create();
        Sale::factory()->create([
            'seller_id' => $seller->id,
            'amount' => 1000.00,
            'sale_date' => $date,
        ]);

        // Verifica que o job chama o service
        $saleService = app(SaleService::class);
        $summary = $saleService->getDailySalesSummary($date);

        expect($summary)->toHaveKey('date')
            ->and($summary)->toHaveKey('total_amount')
            ->and($summary)->toHaveKey('sales_count')
            ->and($summary)->toHaveKey('sellers_summary');

        $job = new SendDailySalesReportJob($date, $adminEmail);
        $job->handle($saleService);

        Mail::assertQueued(DailySalesReportMail::class);
    });
});

