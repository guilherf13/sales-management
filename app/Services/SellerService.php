<?php

namespace App\Services;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Pagination\LengthAwarePaginator;

class SellerService
{
    public function getAll(Request $request): LengthAwarePaginator
    {
        $query = Seller::query()
            ->select([
                'id',
                'name',
                'email',
                'created_at',
                'updated_at',
            ])
            ->withCount('sales')
            ->withSum('sales', 'commission');

        if ($request->has('search') && $request->input('search') !== '') {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', $search . '%')
                  ->orWhere('email', 'like', $search . '%');
            });
        }

        $perPage = $request->input('per_page', 20);
        
        return $query->paginate($perPage);
    }

    public function create(User $user, array $data): Seller
    {
        return Seller::create($data);
    }

    public function update(User $user, Seller $seller, array $data): bool
    {
        return $seller->update($data);
    }

    public function delete(User $user, Seller $seller): void
    {
        $seller->delete();
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
}
