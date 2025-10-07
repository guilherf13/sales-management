<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class SaleService
{
    public function getAll(Request $request): LengthAwarePaginator
    {
        $query = Sale::with('seller');

        // Filter by seller
        if ($request->filled('seller_id')) {
            $query->where('seller_id', $request->input('seller_id'));
        }
        
        // Filter by date from
        if ($request->filled('date_from')) {
            $query->where('sale_date', '>=', $request->input('date_from'));
        }
        
        // Filter by date to
        if ($request->filled('date_to')) {
            $query->where('sale_date', '<=', $request->input('date_to'));
        }
        
        // Filter by amount min
        if ($request->filled('amount_min')) {
            $query->where('amount', '>=', $request->input('amount_min'));
        }
        
        // Filter by amount max
        if ($request->filled('amount_max')) {
            $query->where('amount', '<=', $request->input('amount_max'));
        }

        $perPage = $request->input('per_page', 20);
        
        return $query->orderBy('sale_date', 'desc')->paginate($perPage);
    }

    public function create(User $user, array $data): Sale
    {
        // Ensure commission is calculated
        if (!isset($data['commission'])) {
            $data['commission'] = Sale::calculateCommission($data['amount']);
        }
        
        return Sale::create($data);
    }

    public function update(User $user, Sale $sale, array $data): bool
    {
        // Recalculate commission if amount is being updated
        if (isset($data['amount'])) {
            $data['commission'] = Sale::calculateCommission($data['amount']);
        }
        
        return $sale->update($data);
    }

    public function delete(User $user, Sale $sale): void
    {
        // Log para debug
        Log::info('Deleting Sale', [
            'sale_id' => $sale->id,
            'seller_id' => $sale->seller_id,
            'seller_name' => $sale->seller?->name ?? 'Unknown',
            'amount' => $sale->amount
        ]);
        
        $sale->delete();
        
        Log::info('Sale deleted successfully', ['sale_id' => $sale->id]);
    }

    public function getSalesBySeller(Seller $seller, Request $request): Collection
    {
        $query = $seller->sales();

        if ($request->has('date_from')) {
            $query->whereDate('sale_date', '>=', $request->input('date_from'));
        }
        if ($request->has('date_to')) {
            $query->whereDate('sale_date', '<=', $request->input('date_to'));
        }

        return $query->get();
    }

    public function getDashboardStats(): array
    {
        // Conta TODOS os sellers cadastrados no sistema
        $totalSellers = Seller::count();
        
        // Estatísticas TOTAIS (acumuladas de todas as vendas)
        $allSales = Sale::all();
        $totalSales = $allSales->count();
        $totalRevenue = $allSales->sum('amount');
        $totalCommission = $allSales->sum('commission');
        
        // Data do mês atual
        $currentMonthStart = now()->startOfMonth();
        $currentMonthEnd = now()->endOfMonth();
        
        // Data do mês anterior
        $previousMonthStart = now()->subMonth()->startOfMonth();
        $previousMonthEnd = now()->subMonth()->endOfMonth();
        
        // Estatísticas do mês atual
        $currentMonthSales = Sale::whereBetween('sale_date', [$currentMonthStart, $currentMonthEnd])->get();
        $currentMonthStats = [
            'sellers' => Seller::whereHas('sales', function($query) use ($currentMonthStart, $currentMonthEnd) {
                $query->whereBetween('sale_date', [$currentMonthStart, $currentMonthEnd]);
            })->count(),
            'sales' => $currentMonthSales->count(),
            'revenue' => $currentMonthSales->sum('amount'),
            'commission' => $currentMonthSales->sum('commission'),
        ];
        
        // Estatísticas do mês anterior
        $previousMonthSales = Sale::whereBetween('sale_date', [$previousMonthStart, $previousMonthEnd])->get();
        $previousMonthStats = [
            'sellers' => Seller::whereHas('sales', function($query) use ($previousMonthStart, $previousMonthEnd) {
                $query->whereBetween('sale_date', [$previousMonthStart, $previousMonthEnd]);
            })->count(),
            'sales' => $previousMonthSales->count(),
            'revenue' => $previousMonthSales->sum('amount'),
            'commission' => $previousMonthSales->sum('commission'),
        ];
        
        // Get recent sales (últimas 20 vendas)
        $recentSales = Sale::with('seller')
            ->orderBy('sale_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return [
            'total_sellers' => $totalSellers,
            'total_sales' => $totalSales,
            'total_revenue' => $totalRevenue,
            'total_commission' => $totalCommission,
            'recent_sales' => $recentSales,
            'current_month' => $currentMonthStats,
            'previous_month' => $previousMonthStats,
        ];
    }

    public function getDailySalesSummary(string $date): array
    {
        $sales = Sale::with('seller')
            ->whereDate('sale_date', $date)
            ->get();

        $totalAmount = $sales->sum('amount');
        $totalCommission = $sales->sum('commission');
        $salesCount = $sales->count();

        $sellersSummary = $sales->groupBy('seller_id')->map(function ($sellerSales) use ($date) {
            $seller = $sellerSales->first()->seller;
            return [
                'seller' => $seller,
                'sales_count' => $sellerSales->count(),
                'total_amount' => $sellerSales->sum('amount'),
                'total_commission' => $sellerSales->sum('commission'),
            ];
        });

        return [
            'date' => $date,
            'total_amount' => $totalAmount,
            'total_commission' => $totalCommission,
            'sales_count' => $salesCount,
            'sellers_summary' => $sellersSummary,
        ];
    }
}
