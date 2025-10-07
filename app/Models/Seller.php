<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seller extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the sales for the seller.
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Calculate total sales amount for a specific date.
     */
    public function getTotalSalesForDate(string $date): float
    {
        return $this->sales()
            ->whereDate('sale_date', $date)
            ->sum('amount');
    }

    /**
     * Calculate total commission for a specific date.
     */
    public function getTotalCommissionForDate(string $date): float
    {
        return $this->sales()
            ->whereDate('sale_date', $date)
            ->sum('commission');
    }

    /**
     * Get sales count for a specific date.
     */
    public function getSalesCountForDate(string $date): int
    {
        return $this->sales()
            ->whereDate('sale_date', $date)
            ->count();
    }
}
