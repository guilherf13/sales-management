<?php

use App\Models\User;
use App\Models\Seller;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use App\Jobs\SendDailyCommissionJob;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['perfil' => 'Gestor']);
    $this->token = $this->user->createToken('auth_token')->plainTextToken;
});

describe('Sales API', function () {
    
    test('pode listar todas as vendas com paginação', function () {
        $seller = Seller::factory()->create();
        Sale::factory()->count(25)->create(['seller_id' => $seller->id]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/sales');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'seller_id',
                        'amount',
                        'commission',
                        'sale_date',
                        'created_at',
                        'updated_at',
                        'seller'
                    ]
                ],
                'links',
                'meta'
            ]);
    });

    test('pode filtrar vendas por vendedor', function () {
        $seller1 = Seller::factory()->create();
        $seller2 = Seller::factory()->create();
        
        Sale::factory()->count(3)->create(['seller_id' => $seller1->id]);
        Sale::factory()->count(2)->create(['seller_id' => $seller2->id]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/sales?seller_id={$seller1->id}");

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    });

    test('pode filtrar vendas por data de início', function () {
        $seller = Seller::factory()->create();
        
        Sale::factory()->create(['seller_id' => $seller->id, 'sale_date' => '2024-01-10']);
        Sale::factory()->create(['seller_id' => $seller->id, 'sale_date' => '2024-01-15']);
        Sale::factory()->create(['seller_id' => $seller->id, 'sale_date' => '2024-01-20']);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/sales?date_from=2024-01-15');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    });

    test('pode filtrar vendas por data de fim', function () {
        $seller = Seller::factory()->create();
        
        Sale::factory()->create(['seller_id' => $seller->id, 'sale_date' => '2024-01-10', 'amount' => 100]);
        Sale::factory()->create(['seller_id' => $seller->id, 'sale_date' => '2024-01-14', 'amount' => 200]);
        Sale::factory()->create(['seller_id' => $seller->id, 'sale_date' => '2024-01-16', 'amount' => 300]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/sales?date_to=2024-01-15');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    });

    test('pode filtrar vendas por valor mínimo', function () {
        $seller = Seller::factory()->create();
        
        Sale::factory()->create(['seller_id' => $seller->id, 'amount' => 100.00]);
        Sale::factory()->create(['seller_id' => $seller->id, 'amount' => 500.00]);
        Sale::factory()->create(['seller_id' => $seller->id, 'amount' => 1000.00]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/sales?amount_min=500');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    });

    test('pode filtrar vendas por valor máximo', function () {
        $seller = Seller::factory()->create();
        
        Sale::factory()->create(['seller_id' => $seller->id, 'amount' => 100.00]);
        Sale::factory()->create(['seller_id' => $seller->id, 'amount' => 500.00]);
        Sale::factory()->create(['seller_id' => $seller->id, 'amount' => 1000.00]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/sales?amount_max=500');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    });

    test('pode criar uma nova venda', function () {
        $seller = Seller::factory()->create();
        
        $saleData = [
            'seller_id' => $seller->id,
            'amount' => 1000.00,
            'sale_date' => '2024-01-15',
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/sales', $saleData);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'seller_id',
                    'amount',
                    'commission',
                    'sale_date',
                    'created_at',
                    'updated_at',
                    'seller'
                ]
            ])
            ->assertJsonPath('data.amount', '1000.00')
            ->assertJsonPath('data.commission', '85.00'); // 8.5% de 1000

        $this->assertDatabaseHas('sales', [
            'seller_id' => $seller->id,
            'amount' => 1000.00,
            'commission' => 85.00,
        ]);
    });

    test('comissão é calculada automaticamente ao criar venda', function () {
        $seller = Seller::factory()->create();
        
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/sales', [
                'seller_id' => $seller->id,
                'amount' => 2000.00,
                'sale_date' => now()->format('Y-m-d'),
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.commission', '170.00'); // 8.5% de 2000
    });

    test('criação de venda requer seller_id', function () {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/sales', [
                'amount' => 1000.00,
                'sale_date' => '2024-01-15',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['seller_id']);
    });

    test('criação de venda requer seller existente', function () {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/sales', [
                'seller_id' => 99999,
                'amount' => 1000.00,
                'sale_date' => '2024-01-15',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['seller_id']);
    });

    test('criação de venda requer valor válido', function () {
        $seller = Seller::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/sales', [
                'seller_id' => $seller->id,
                'amount' => -100.00,
                'sale_date' => '2024-01-15',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    });

    test('criação de venda requer data válida', function () {
        $seller = Seller::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/sales', [
                'seller_id' => $seller->id,
                'amount' => 1000.00,
                'sale_date' => 'invalid-date',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sale_date']);
    });

    test('criação de venda não permite data futura', function () {
        $seller = Seller::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/sales', [
                'seller_id' => $seller->id,
                'amount' => 1000.00,
                'sale_date' => now()->addDay()->format('Y-m-d'),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sale_date']);
    });

    test('pode visualizar uma venda específica', function () {
        $seller = Seller::factory()->create();
        $sale = Sale::factory()->create([
            'seller_id' => $seller->id,
            'amount' => 1000.00,
            'commission' => 85.00,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/sales/{$sale->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $sale->id)
            ->assertJsonPath('data.amount', '1000.00')
            ->assertJsonPath('data.commission', '85.00');
    });

    test('retorna 404 ao buscar venda inexistente', function () {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/sales/99999');

        $response->assertNotFound();
    });

    test('pode atualizar uma venda', function () {
        $seller1 = Seller::factory()->create();
        $seller2 = Seller::factory()->create();
        $sale = Sale::factory()->create([
            'seller_id' => $seller1->id,
            'amount' => 1000.00,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->putJson("/api/sales/{$sale->id}", [
                'seller_id' => $seller2->id,
                'amount' => 2000.00,
                'sale_date' => '2024-01-20',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.seller_id', $seller2->id)
            ->assertJsonPath('data.amount', '2000.00')
            ->assertJsonPath('data.commission', '170.00'); // 8.5% de 2000
    });

    test('comissão é recalculada ao atualizar valor da venda', function () {
        $seller = Seller::factory()->create();
        $sale = Sale::factory()->create([
            'seller_id' => $seller->id,
            'amount' => 1000.00,
            'commission' => 85.00,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->putJson("/api/sales/{$sale->id}", [
                'amount' => 1500.00,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.commission', '127.50'); // 8.5% de 1500
    });

    test('pode deletar uma venda', function () {
        $seller = Seller::factory()->create();
        $sale = Sale::factory()->create(['seller_id' => $seller->id]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->deleteJson("/api/sales/{$sale->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('sales', [
            'id' => $sale->id,
        ]);
    });

    test('pode obter estatísticas do dashboard', function () {
        $seller = Seller::factory()->create();
        Sale::factory()->count(5)->create(['seller_id' => $seller->id]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/dashboard/stats');

        $response->assertOk()
            ->assertJsonStructure([
                'total_sellers',
                'total_sales',
                'total_revenue',
                'total_commission',
                'recent_sales',
                'current_month' => [
                    'sellers',
                    'sales',
                    'revenue',
                    'commission',
                ],
                'previous_month' => [
                    'sellers',
                    'sales',
                    'revenue',
                    'commission',
                ]
            ]);
    });

    test('pode obter resumo diário de vendas', function () {
        $seller = Seller::factory()->create();
        $date = now()->format('Y-m-d');
        
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

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/sales/daily-summary?date={$date}");

        $response->assertOk()
            ->assertJsonStructure([
                'date',
                'total_amount',
                'total_commission',
                'sales_count',
                'sellers_summary'
            ]);

        expect($response->json('total_amount'))->toEqual(1500.0);
        expect($response->json('total_commission'))->toEqual(127.5);
        expect($response->json('sales_count'))->toEqual(2);
    });

    test('pode reenviar email de comissão para vendedor', function () {
        Queue::fake();
        
        $seller = Seller::factory()->create();
        $date = '2024-01-15';
        Sale::factory()->create([
            'seller_id' => $seller->id,
            'sale_date' => $date,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/sellers/{$seller->id}/resend-commission", [
                'date' => $date,
            ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'seller',
                'date'
            ])
            ->assertJsonPath('seller', $seller->name)
            ->assertJsonPath('date', $date);

        Queue::assertPushed(SendDailyCommissionJob::class, function ($job) use ($seller, $date) {
            return $job->seller->id === $seller->id && $job->date === $date;
        });
    });

    test('reenvio de email usa data da última venda se não informada', function () {
        Queue::fake();
        
        $seller = Seller::factory()->create();
        $latestDate = '2024-01-20';
        
        Sale::factory()->create(['seller_id' => $seller->id, 'sale_date' => '2024-01-15', 'amount' => 100]);
        Sale::factory()->create(['seller_id' => $seller->id, 'sale_date' => $latestDate, 'amount' => 200]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/sellers/{$seller->id}/resend-commission");

        $response->assertOk();
        
        // A data retornada pode estar no formato ISO 8601
        $responseDate = $response->json('date');
        expect($responseDate)->toContain($latestDate);

        Queue::assertPushed(SendDailyCommissionJob::class);
    });

    test('usuário não autenticado não pode acessar sales', function () {
        $response = $this->getJson('/api/sales');

        $response->assertUnauthorized();
    });

    test('vendas são ordenadas por data decrescente', function () {
        $seller = Seller::factory()->create();
        
        Sale::factory()->create(['seller_id' => $seller->id, 'sale_date' => '2024-01-10']);
        Sale::factory()->create(['seller_id' => $seller->id, 'sale_date' => '2024-01-20']);
        Sale::factory()->create(['seller_id' => $seller->id, 'sale_date' => '2024-01-15']);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/sales');

        $response->assertOk();
        
        $dates = collect($response->json('data'))->pluck('sale_date')->toArray();
        expect($dates[0])->toBe('2024-01-20')
            ->and($dates[1])->toBe('2024-01-15')
            ->and($dates[2])->toBe('2024-01-10');
    });
});

