<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Api\SellerController;
use App\Http\Controllers\Api\SaleController;

/*
|--------------------------------------------------------------------------
| Rotas da API
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Sellers routes
    Route::apiResource('sellers', SellerController::class);
    Route::get('sellers/{seller}/sales', [SellerController::class, 'sales']);

    // Sales routes
    Route::get('dashboard/stats', [SaleController::class, 'dashboardStats']);
    Route::get('sales/daily-summary', [SaleController::class, 'dailySummary']);
    Route::apiResource('sales', SaleController::class);
    Route::post('sellers/{seller}/resend-commission', [SaleController::class, 'resendCommissionEmail']);
});