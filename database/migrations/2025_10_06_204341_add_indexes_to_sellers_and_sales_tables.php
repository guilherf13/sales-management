<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->index('name', 'idx_sellers_name');
            $table->index('email', 'idx_sellers_email');
        });

        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasIndex('sales', 'idx_sales_seller_id')) {
                $table->index('seller_id', 'idx_sales_seller_id');
            }
            
            $table->index('sale_date', 'idx_sales_sale_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropIndex('idx_sellers_name');
            $table->dropIndex('idx_sellers_email');
        });

        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasIndex('sales', 'idx_sales_seller_id')) {
                $table->dropIndex('idx_sales_seller_id');
            }
            $table->dropIndex('idx_sales_sale_date');
        });
    }
};
