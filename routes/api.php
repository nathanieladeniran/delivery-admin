<?php

use App\Http\Controllers\Api\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DeliveryController;
use App\Http\Controllers\Api\RevenueController;
use App\Http\Controllers\Api\RiderController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\DashboardKpiController;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\UserManagementKpiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('auth')->group(function () {
    Route::post('/create-admin', [AdminController::class, 'addNewAdmin'])->name('create-admin');
    Route::post('/verify-otp/{email}', [AdminController::class, 'verifyAdmin'])->name('verify-otp');
    Route::post('/verify-reset-otp/{email}', [AdminController::class, 'verifyPasswordResetOtp'])->name('verify-password-otp');
    Route::post('/login', [AuthController::class, 'signInAdmin'])->name('login');
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout')->middleware('auth:sanctum');

    Route::prefix('recovery')->group(function () {
        Route::post('/reset-link', [AdminController::class, 'sendResetLink'])->name('password.email');
        Route::post('/password/reset', [AdminController::class, 'completePasswordReset'])->name('password.reset');
    });
});

Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [AdminController::class, 'getTotalAdminUsers']);
    Route::get('/loggedin-admin', [AuthController::class, 'loggedInAdmin']);
    Route::get('export-excel', [DeliveryController::class, 'exportExcel']);
    Route::get('export-pdf', [DeliveryController::class, 'exportPdfFormat']);

    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'getTotalUsers']);
        Route::get('/user/{uuid}', [UserController::class, 'singleUser']);
        Route::post('/{uuid}/ban', [UserController::class, 'banUser']);
        Route::post('/{uuid}/unban', [UserController::class, 'unbanUser']);
        Route::get('/kpi', [UserManagementKpiController::class, 'userManagementKpi']); 
        Route::get('/search', [UserController::class, 'searchFilter']); 
    });

    Route::prefix('customers')->group(function () {
        Route::get('/', [CustomerController::class, 'getTotalCustomers']);
        Route::get('/customer/{uuid}', [CustomerController::class, 'singleCustomer']);
        Route::get('/search', [CustomerController::class, 'searchFilter']); 
    });

    Route::prefix('riders')->group(function () {
        Route::get('/', [RiderController::class, 'getTotalRiders']);
        Route::get('/rider/{uuid}', [RiderController::class, 'singleRider']);
        Route::post('/{uuid}/ban', [RiderController::class, 'banRider']);
        Route::post('/{uuid}/unban', [RiderController::class, 'unbanRider']);
        Route::get('/search', [RiderController::class, 'searchFilter']); 
    });

    Route::prefix('deliveries')->group(function () {
        Route::get('/', [DeliveryController::class, 'getDeliveries']);
        Route::get('/delivery/{uuid}', [DeliveryController::class, 'getDeliveryByUuid']);
        Route::post('/delete/{uuid}', [DeliveryController::class, 'deleteDeliveryByUuid']);
        Route::get('/completed-deliveries', [DeliveryController::class, 'getCompletedDeliveries']);
        Route::get('/pending-deliveries', [DeliveryController::class, 'getPendingDeliveries']);
        Route::get('/cancelled-deliveries', [DeliveryController::class, 'getCancelledDeliveries']);   
        Route::get('/search', [DeliveryController::class, 'searchFilter']); 
        Route::get('/kpi', [DashboardKpiController::class, 'showDeliveryKpi']); 
    });
    Route::prefix('revenue')->group(function () {
        Route::get('metrics', [RevenueController::class, 'index']);
        Route::get('services', [RevenueController::class, 'service']);
        Route::get('sms', [RevenueController::class, 'sms']);
        Route::get('subscription', [RevenueController::class, 'subscription']);
    });
    Route::prefix('payout')->group(function () {
        Route::get('/payout-metrics', [PayoutController::class, 'totalPayoutAndNextPayout']); 
        Route::get('/customers-payout', [PayoutController::class, 'userPayoutList']); 
        Route::get('/{business_id}/payment-history', [PayoutController::class, 'paymentHistory']); 
        Route::get('/search', [PayoutController::class, 'searchFilter']); 
    });
});
