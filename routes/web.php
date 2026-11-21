<?php
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PosController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\SalesReportController;


Route::get('/', [ProductController::class, 'index'])->name('home');


Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('products', AdminProductController::class)->except('show');
        Route::resource('stock', StockController::class)->only(['index', 'create', 'store']);
        Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('sales', [SalesReportController::class, 'index'])->name('sales.index');
        Route::get('sales/pdf', [SalesReportController::class, 'pdf'])->name('sales.pdf');

    });

//pos related routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
});


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', fn (Request $request) => redirect(
        $request->user()->is_admin ? '/admin/dashboard' : '/pos'
    ))->name('dashboard');

    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/checkout', [SaleController::class, 'store'])->name('sales.store');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
});






require __DIR__.'/settings.php';