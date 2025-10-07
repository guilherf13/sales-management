<?php

use App\Jobs\SendDailyCommissionJob;
use App\Mail\DailyCommissionMail;
use App\Models\Seller;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

describe('SendDailyCommissionJob', function () {
    
    test('envia email de comissão para vendedor', function () {
        Mail::fake();

        $seller = Seller::factory()->create([
            'name' => 'João Silva',
            'email' => 'joao@example.com',
        ]);

        $date = '2024-01-15';
        Sale::factory()->create([
            'seller_id' => $seller->id,
            'amount' => 1000.00,
            'commission' => 85.00,
            'sale_date' => $date,
        ]);

        $job = new SendDailyCommissionJob($seller, $date);
        $job->handle();

        Mail::assertQueued(DailyCommissionMail::class, function ($mail) use ($seller) {
            return $mail->hasTo($seller->email);
        });
    });

    test('email contém dados corretos do vendedor', function () {
        Mail::fake();

        $seller = Seller::factory()->create([
            'name' => 'João Silva',
            'email' => 'joao@example.com',
        ]);

        $date = '2024-01-15';
        Sale::factory()->create([
            'seller_id' => $seller->id,
            'amount' => 1000.00,
            'commission' => 85.00,
            'sale_date' => $date,
        ]);

        $job = new SendDailyCommissionJob($seller, $date);
        $job->handle();

        Mail::assertQueued(DailyCommissionMail::class, function ($mail) use ($seller, $date) {
            return $mail->seller->id === $seller->id
                && $mail->date === $date
                && $mail->salesCount === 1
                && (float)$mail->totalAmount == 1000.00
                && (float)$mail->totalCommission == 85.00;
        });
    });

    test('email calcula corretamente múltiplas vendas do mesmo dia', function () {
        Mail::fake();

        $seller = Seller::factory()->create();
        $date = '2024-01-15';

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
        Sale::factory()->create([
            'seller_id' => $seller->id,
            'amount' => 300.00,
            'commission' => 25.50,
            'sale_date' => $date,
        ]);

        $job = new SendDailyCommissionJob($seller, $date);
        $job->handle();

        Mail::assertQueued(DailyCommissionMail::class, function ($mail) {
            return $mail->salesCount === 3
                && (float)$mail->totalAmount == 1800.00
                && (float)$mail->totalCommission == 153.00;
        });
    });

    test('email não inclui vendas de outras datas', function () {
        Mail::fake();

        $seller = Seller::factory()->create();
        $targetDate = '2024-01-15';

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

        $job = new SendDailyCommissionJob($seller, $targetDate);
        $job->handle();

        Mail::assertQueued(DailyCommissionMail::class, function ($mail) use ($targetDate) {
            return $mail->date === $targetDate
                && $mail->salesCount === 1
                && (float)$mail->totalAmount == 1000.00
                && (float)$mail->totalCommission == 85.00;
        });
    });

    test('email envia zero vendas quando não há vendas no dia', function () {
        Mail::fake();

        $seller = Seller::factory()->create();
        $date = '2024-01-15';

        // Cria vendas em outra data
        Sale::factory()->create([
            'seller_id' => $seller->id,
            'sale_date' => '2024-01-10',
            'amount' => 100,
        ]);

        $job = new SendDailyCommissionJob($seller, $date);
        $job->handle();

        Mail::assertQueued(DailyCommissionMail::class, function ($mail) {
            return $mail->salesCount === 0
                && (float)$mail->totalAmount == 0
                && (float)$mail->totalCommission == 0;
        });
    });

    test('job pode ser serializado e deserializado', function () {
        $seller = Seller::factory()->create();
        $date = '2024-01-15';

        $job = new SendDailyCommissionJob($seller, $date);
        
        expect($job->seller)->toBe($seller)
            ->and($job->date)->toBe($date);
    });

    test('job implementa ShouldQueue', function () {
        $job = new SendDailyCommissionJob(
            Seller::factory()->create(),
            '2024-01-15'
        );

        expect($job)->toBeInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class);
    });

    test('email tem assunto correto', function () {
        Mail::fake();

        $seller = Seller::factory()->create();
        $date = '2024-01-15';
        Sale::factory()->create(['seller_id' => $seller->id, 'sale_date' => $date, 'amount' => 100]);

        $job = new SendDailyCommissionJob($seller, $date);
        $job->handle();

        Mail::assertQueued(DailyCommissionMail::class, function ($mail) use ($date) {
            $envelope = $mail->envelope();
            return $envelope->subject === "Relatório Diário de Comissões - {$date}";
        });
    });

    test('job envia email apenas uma vez', function () {
        Mail::fake();

        $seller = Seller::factory()->create();
        $date = '2024-01-15';
        Sale::factory()->create(['seller_id' => $seller->id, 'sale_date' => $date, 'amount' => 100]);

        $job = new SendDailyCommissionJob($seller, $date);
        $job->handle();

        Mail::assertQueued(DailyCommissionMail::class, 1);
    });
});

