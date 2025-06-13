<?php

use App\Http\Controllers\BikerOrderController;
use App\Http\Controllers\BikerSaleController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DealerRequestController;
use App\Http\Controllers\DealerStockBalanceController;
use App\Http\Controllers\DealerStockController;
use App\Http\Controllers\LogInController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::get('/', [UserController::class, 'unauthorized'])->name('login');
Route::post('/login', [LogInController::class, 'index']);
Route::post('/login/app', [LogInController::class, 'app']);
Route::post('/logout', [LogInController::class, 'destroy']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/is_auth', [UserController::class, 'authorized']);

    Route::middleware(['check.user_type:super_admin'])->group(function () {

        Route::apiResource('companies', CompanyController::class);
    
        Route::post('/register/superadmin', [RegistrationController::class, 'super']);
        Route::post('/register/superdealer', [RegistrationController::class, 'dealer']);

        // Route::get('/superadmins', [SuperAdminController::class, 'index']);
        Route::apiResource('superadmins', SuperAdminController::class);
        Route::get('companies/{id}/superdealers', [SuperAdminController::class, 'superdealers']);
        Route::get('companies/{id}/bikers', [SuperAdminController::class, 'bikers']);
        Route::get('companies/{id}/products', [SuperAdminController::class, 'products']);
        Route::get('superdealers/{id}/bikers', [SuperAdminController::class, 'superBikers']);

    });
    Route::post('/register/biker', [RegistrationController::class, 'biker'])->middleware('check.user_type:super_dealer');

    Route::apiResource('products', ProductController::class);
    Route::post('/orders/request', [DealerRequestController::class, 'request']);
    Route::post('/orders/status', [DealerStockController::class, 'status']);
    Route::post('/orders/fulfill', [DealerStockController::class, 'store']);


    Route::prefix('stock')->group(function () {
        Route::get('balance', [DealerStockBalanceController::class, 'index']);
        Route::get('orders', [DealerStockBalanceController::class, 'orders']);
        Route::get('orders/{order_id}/items', [DealerStockBalanceController::class, 'items']);
    });



    Route::prefix('biker/order/')->group(function () {
        Route::post('request', [BikerOrderController::class, 'store']);
        Route::put('activate/{id}', [BikerOrderController::class, 'update']);
        Route::delete('delete/{id}', [BikerOrderController::class, 'destroy']);
        Route::put('complete/{id}', [BikerSaleController::class, 'complete']);
        Route::put('close/{id}', [BikerSaleController::class, 'destroy']);
    });

    Route::post('biker/sell', [BikerSaleController::class, 'sell']);
    Route::post('biker/return', [BikerSaleController::class, 'returnItems']);
});


