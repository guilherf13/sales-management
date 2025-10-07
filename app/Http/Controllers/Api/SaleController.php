<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Requests\UpdateSaleRequest;
use App\Http\Resources\SaleResource;
use App\Jobs\SendDailyCommissionJob;
use App\Models\Sale;
use App\Models\Seller;
use App\Services\SaleService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class SaleController extends Controller
{
    public function __construct(protected SaleService $saleService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $sales = $this->saleService->getAll($request);
        return SaleResource::collection($sales);
    }

    public function store(StoreSaleRequest $request): SaleResource
    {
        $sale = $this->saleService->create($request->user(), $request->validated());
        return SaleResource::make($sale->load('seller'));
    }

    public function show(Sale $sale): SaleResource
    {
        return SaleResource::make($sale->load('seller'));
    }

    public function update(UpdateSaleRequest $request, Sale $sale): SaleResource
    {
        $this->saleService->update($request->user(), $sale, $request->validated());
        return SaleResource::make($sale->fresh()->load('seller'));
    }

    public function destroy(Request $request, Sale $sale): Response
    {
        $this->saleService->delete($request->user(), $sale);
        return response()->noContent();
    }

    public function dashboardStats(): array
    {
        return $this->saleService->getDashboardStats();
    }

    public function dailySummary(Request $request): array
    {
        $date = $request->input('date', now()->format('Y-m-d'));
        return $this->saleService->getDailySalesSummary($date);
    }

    public function resendCommissionEmail(Request $request, Seller $seller): JsonResponse
    {
        // Usa a data fornecida ou a data da venda mais recente do vendedor
        $date = $request->input('date');
        
        if (!$date) {
            $latestSale = $seller->sales()->latest('sale_date')->first();
            $date = $latestSale ? $latestSale->sale_date : now()->format('Y-m-d');
        }
        
        SendDailyCommissionJob::dispatch($seller, $date);
        
        return response()->json([
            'message' => 'E-mail de comissão foi agendado para reenvio',
            'seller' => $seller->name,
            'date' => $date
        ]);
    }
}
