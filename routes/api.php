<?php

use App\Http\Controllers\ApplicationSettingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\CompanyOwnerController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EmailVerifyController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentReceiptController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportingController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SerialNumberController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\SupplierProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SupplyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Routes avec rate limiting pour la connexion / le refresh
Route::middleware(['ip.blocked', 'throttle:20,1'])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware(['throttle:20,1'])->group(function () {
    Route::post('/refresh', [AuthController::class, 'refresh']);
});

// Vérification du code de connexion (OTP après déblocage d'IP)
Route::middleware(['throttle:10,1'])->group(function () {
    Route::post('/login/verify-otp', [AuthController::class, 'verifyLoginOtp']);
});
Route::middleware(['throttle:5,10'])->group(function () {
    Route::post('/login/resend-otp', [AuthController::class, 'resendLoginOtp']);
});

Route::post('/logout', [AuthController::class, 'logout']);

// Routes publiques sans rate limiting spécifique
Route::get('/app-setting', [ApplicationSettingController::class, 'index']);
Route::get('/product/available/{storeId}', [ProductController::class, 'getProductAvailableByStore']);
Route::get('/store/info/{id}', [StoreController::class, 'getStoreInfo']);

// Routes de réinitialisation de mot de passe avec rate limiting standard
Route::middleware(['throttle:5,1'])->group(function () {
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
    Route::post('reset-password', [ResetPasswordController::class, 'reset']);
});

// Routes de vérification d'email
Route::get('/email/verify/{id}/{hash}', [EmailVerifyController::class, 'verify'])->name('verification.verify');
Route::post('/email/resend', [EmailVerifyController::class, 'resend'])->name('verification.resend');

Route::get('reset-password/{token}', function (string $token) {
    return redirect(env('FRONTEND_URL') . '/reset-password?token=' . $token);
})->name('password.reset');

// Route pour envoyer l'email de vérification
Route::middleware('auth:jwt')->post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return response()->json(['message' => 'Verification link sent!']);
})->name('verification.send');


Route::middleware(['auth:jwt'])->group(function () {
    Route::get('/authenticate', [AuthController::class, 'authenticate']);
    Route::put('/update-app-setting/{id}', [ApplicationSettingController::class, 'update']);
    Route::get('/roles', [RoleController::class, 'index']);
    Route::put('/change-password/{id}', [ChangePasswordController::class, 'changePassword']);

    Route::prefix('user')->group(function () {
        Route::get('/list', [UserController::class, 'index']);
        Route::post('/add', [UserController::class, 'store']);
        Route::get('/disable/{id}', [UserController::class, 'disable']);
        Route::put('/update/{id}', [UserController::class, 'update']);
    });

    Route::prefix('company')->group(function () {
        Route::get('/list', [CompanyOwnerController::class, 'index']);
        Route::post('/add', [CompanyOwnerController::class, 'store']);
        Route::put('/update/{id}', [CompanyOwnerController::class, 'update']);
        Route::put('/update-info-company/{id}', [CompanyOwnerController::class, 'updateCompany']);
    });

    Route::prefix('supplier')->group(function () {
        Route::get('/list', [SupplierProductController::class, 'index']);
        Route::post('/add', [SupplierProductController::class, 'store']);
        Route::put('/update/{id}', [SupplierProductController::class, 'update']);
        Route::delete('/delete/{id}', [SupplierProductController::class, 'destroy']);
    });

    Route::prefix('customer')->group(function () {
        Route::get('/list', [CustomerController::class, 'index']);
        Route::post('/add', [CustomerController::class, 'store']);
        Route::put('/update/{id}', [CustomerController::class, 'update']);
        Route::delete('/delete/{id}', [CustomerController::class, 'destroy']);
    });

    Route::prefix('category')->group(function () {
        Route::get('/all', [CategoryController::class, 'allCategories']);
        Route::get('/list', [CategoryController::class, 'index']);
        Route::post('/add', [CategoryController::class, 'store']);
        Route::put('/update/{id}', [CategoryController::class, 'update']);
        Route::delete('/delete/{id}', [CategoryController::class, 'delete']);
    });

    Route::prefix('store')->group(function () {
        Route::get('/list/{id}', [StoreController::class, 'index']);
        Route::post('/add', [StoreController::class, 'store']);
        Route::put('/update/{id}', [StoreController::class, 'update']);
        Route::get('/change-status/{id}', [StoreController::class, 'changeStatus']);
    });

    Route::prefix('product')->group(function () {
        Route::get('/list', [ProductController::class, 'index']);
        Route::post('/add', [ProductController::class, 'store']);
        Route::put('/update/{id}', [ProductController::class, 'update']);
        Route::delete('/delete/{id}', [ProductController::class, 'delete']);
        Route::get('/available', [ProductController::class, 'getProductAvailable']);
    });

    Route::prefix('sale')->group(function () {
        Route::get('/list', [SaleController::class, 'index']);
        Route::get('/show/{id}', [SaleController::class, 'show']);
        Route::post('/add', [SaleController::class, 'store']);
        Route::post('/validate/{id}', [SaleController::class, 'validateSale']);
        Route::post('/cancel/{id}', [SaleController::class, 'cancel']);
    });

    Route::prefix('procurement')->group(function () {
        Route::get('/list', [SupplyController::class, 'index']);
        Route::post('/add', [SupplyController::class, 'store']);
        Route::post('/validate/{id}', [SupplyController::class, 'validateSupply']);
        Route::post('/cancel/{id}', [SupplyController::class, 'cancelSupply']);
        Route::put('/update/{id}', [SupplyController::class, 'update']);
    });

    Route::prefix('expense')->group(function () {
        Route::get('/list', [ExpenseController::class, 'index']);
        Route::post('/add', [ExpenseController::class, 'store']);
        Route::put('/update/{id}', [ExpenseController::class, 'update']);
        Route::delete('/delete/{id}', [ExpenseController::class, 'destroy']);
    });

    Route::prefix('invoice')->group(function () {
        Route::get('/list', [InvoiceController::class, 'index']);
        Route::post('/paid/{id}', [InvoiceController::class, 'paidInvoice']);
        Route::get('/{id}/details', [InvoiceController::class, 'getInvoiceWithPayments']);
    });

    Route::prefix('payment')->group(function () {
        Route::get('/list', [PaymentReceiptController::class, 'index']);
        Route::get('/export-pdf', [PaymentReceiptController::class, 'exportPdf']);
    });

    Route::prefix('imei')->group(function () {
        Route::get('/list', [SerialNumberController::class, 'index']);
        Route::put('/update/{id}', [SerialNumberController::class, 'update']);
        // Route::get('/{id}/details', [SerialNumberController::class, 'getInvoiceWithPayments']);
    });

    Route::get('/reporting', [ReportingController::class, 'getDashboardData']);
});

// php artisan cache:clear && php artisan config:clear && php artisan migrate:fresh && php artisan db:seed && php artisan serve
