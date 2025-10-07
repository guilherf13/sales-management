<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'amount',
        'commission',
        'sale_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'commission' => 'decimal:2',
        'sale_date' => 'date',
    ];

    /**
     * Get the seller that owns the sale.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    /**
     * Calculate commission based on amount (8.5%).
     */
    public static function calculateCommission(float $amount): float
    {
        return round($amount * 0.085, 2);
    }

    /**
     * Boot method to automatically calculate commission.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sale) {
            if (empty($sale->commission)) {
                $sale->commission = self::calculateCommission($sale->amount);
            }
        });

        static::updating(function ($sale) {
            if ($sale->isDirty('amount')) {
                $sale->commission = self::calculateCommission($sale->amount);
            }
        });
    }
}
