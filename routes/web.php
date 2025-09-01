<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\ResetPassword;
use App\Http\Controllers\ChangePassword;      
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StockInController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SystemSettingController;

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\TransactionHistoryController;
use App\Http\Controllers\SearchController;

            

Route::get('/', function () {return redirect('/dashboard');})->middleware('auth');
	Route::get('/register', [RegisterController::class, 'create'])->middleware('guest')->name('register');
	Route::post('/register', [RegisterController::class, 'store'])->middleware('guest')->name('register.perform');
	Route::get('/login', [LoginController::class, 'show'])->middleware('guest')->name('login');
	Route::post('/login', [LoginController::class, 'login'])->middleware('guest')->name('login.perform');
	Route::get('/reset-password', [ResetPassword::class, 'show'])->middleware('guest')->name('reset-password');
	Route::post('/reset-password', [ResetPassword::class, 'send'])->middleware('guest')->name('reset.perform');
	Route::get('/change-password', [ChangePassword::class, 'show'])->middleware('guest')->name('change-password');
	Route::post('/change-password', [ChangePassword::class, 'update'])->middleware('guest')->name('change.perform');
	Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware(['auth', 'approved']);

// Routes for all approved users (Basic authenticated routes)
Route::group(['middleware' => ['auth', 'approved', 'prevent.back']], function () {
	Route::get('/virtual-reality', [PageController::class, 'vr'])->name('virtual-reality');
	Route::get('/rtl', [PageController::class, 'rtl'])->name('rtl');
	Route::get('/profile', [UserProfileController::class, 'show'])->name('profile');
	Route::post('/profile', [UserProfileController::class, 'update'])->name('profile.update');
	Route::get('/profile-static', [PageController::class, 'profile'])->name('profile-static'); 
	Route::get('/sign-in-static', [PageController::class, 'signin'])->name('sign-in-static');
	Route::get('/sign-up-static', [PageController::class, 'signup'])->name('sign-up-static'); 


	
	Route::post('logout', [LoginController::class, 'logout'])->name('logout');
});

// Routes for Super Admin Only
Route::group(['middleware' => ['auth', 'superadmin', 'prevent.back']], function () {
	// User Management Routes
	Route::resource('user-management', UserManagementController::class);
	Route::post('/user-management/{user}/approve', [UserManagementController::class, 'approve'])->name('user-management.approve');
	Route::post('/user-management/{user}/reject', [UserManagementController::class, 'reject'])->name('user-management.reject');
	Route::post('/user-management/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('user-management.toggle-status');
	
	// Reports Routes
	Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
	Route::get('/reports/create', [ReportController::class, 'create'])->name('reports.create');
	Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
	Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');
	Route::get('/reports/{report}/export', [ReportController::class, 'export'])->name('reports.export');
	Route::get('/reports/{report}/download', [ReportController::class, 'download'])->name('reports.download');
	Route::delete('/reports/{report}', [ReportController::class, 'destroy'])->name('reports.destroy');
});

// Routes for Admin and Super Admin (Inventory Management)
Route::group(['middleware' => ['auth', 'admin', 'prevent.back']], function () {
	// Categories CRUD Routes - Admin only
	Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
	Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
	Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
	Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
	Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
	Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
	Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
	
	// Items CRUD Routes - Admin only
	Route::get('/items', [ItemController::class, 'index'])->name('items.index');
	Route::get('/items/create', [ItemController::class, 'create'])->name('items.create');
	Route::post('/items', [ItemController::class, 'store'])->name('items.store');
	Route::get('/items/import', [ItemController::class, 'showImport'])->name('items.import.show');
	Route::post('/items/import', [ItemController::class, 'import'])->name('items.import');
	Route::get('/items/test-import', [ItemController::class, 'testImport'])->name('items.test.import');
	Route::get('/items/test-clean-price', [ItemController::class, 'testCleanPrice'])->name('items.test.clean-price');
	Route::post('/items/bulk-delete', [ItemController::class, 'bulkDelete'])->name('items.bulk-delete');

	Route::get('/items/{item}', [ItemController::class, 'show'])->name('items.show');
	Route::get('/items/{item}/edit', [ItemController::class, 'edit'])->name('items.edit');
	Route::put('/items/{item}', [ItemController::class, 'update'])->name('items.update');
	Route::delete('/items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');
	
	// Suppliers CRUD Routes - Admin only
	Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
	Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
	Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
	Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
	Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
	Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
	Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
	
	// Stock In CRUD Routes - Admin only
	Route::get('/stock-in', [StockInController::class, 'index'])->name('stock-in.index');
	Route::get('/stock-in/create', [StockInController::class, 'create'])->name('stock-in.create');
	Route::post('/stock-in', [StockInController::class, 'store'])->name('stock-in.store');
	Route::get('/stock-in/{stockIn}', [StockInController::class, 'show'])->name('stock-in.show');
	Route::get('/stock-in/{stockIn}/edit', [StockInController::class, 'edit'])->name('stock-in.edit');
	Route::put('/stock-in/{stockIn}', [StockInController::class, 'update'])->name('stock-in.update');
	Route::delete('/stock-in/{stockIn}', [StockInController::class, 'destroy'])->name('stock-in.destroy');
	
	// Stock Adjustment CRUD Routes - Admin only
	Route::get('/stock-adjustments', [StockAdjustmentController::class, 'index'])->name('stock-adjustments.index');
	Route::get('/stock-adjustments/create', [StockAdjustmentController::class, 'create'])->name('stock-adjustments.create');
	Route::post('/stock-adjustments', [StockAdjustmentController::class, 'store'])->name('stock-adjustments.store');
	Route::get('/stock-adjustments/{stockAdjustment}', [StockAdjustmentController::class, 'show'])->name('stock-adjustments.show');
	Route::get('/stock-adjustments/{stockAdjustment}/edit', [StockAdjustmentController::class, 'edit'])->name('stock-adjustments.edit');
	Route::put('/stock-adjustments/{stockAdjustment}', [StockAdjustmentController::class, 'update'])->name('stock-adjustments.update');
	Route::delete('/stock-adjustments/{stockAdjustment}', [StockAdjustmentController::class, 'destroy'])->name('stock-adjustments.destroy');
	
	// Barcode Management Routes (Admin and Super Admin only)
	Route::get('/barcodes/create', [BarcodeController::class, 'create'])->name('barcodes.create');
	Route::post('/barcodes', [BarcodeController::class, 'store'])->name('barcodes.store');
	Route::get('/barcodes/bulk-generate', [BarcodeController::class, 'bulkGenerateForm'])->name('barcodes.bulk-generate-form');
	Route::post('/barcodes/bulk-generate', [BarcodeController::class, 'bulkGenerate'])->name('barcodes.bulk-generate');
	Route::get('/barcodes/generation-stats', [BarcodeController::class, 'getGenerationStats'])->name('barcodes.generation-stats');
	Route::get('/barcodes/{barcode}/edit', [BarcodeController::class, 'edit'])->name('barcodes.edit');
	Route::put('/barcodes/{barcode}', [BarcodeController::class, 'update'])->name('barcodes.update');
	Route::delete('/barcodes/{barcode}', [BarcodeController::class, 'destroy'])->name('barcodes.destroy');
	Route::get('/barcodes/{barcode}/generate', [BarcodeController::class, 'generate'])->name('barcodes.generate');
	Route::post('/barcodes/generate-ajax', [BarcodeController::class, 'generateAjax'])->name('barcodes.generate-ajax');
	Route::post('/barcodes/bulk-print', [BarcodeController::class, 'bulkPrint'])->name('barcodes.bulk-print');
});

// Routes for Kasir, Admin, and Super Admin (Sales and Barcode viewing)
Route::group(['middleware' => ['auth', 'kasir', 'prevent.back']], function () {
	// Sales CRUD Routes
	Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
	Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
	Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
	Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
	Route::get('/sales/{sale}/edit', [SaleController::class, 'edit'])->name('sales.edit');
	Route::put('/sales/{sale}', [SaleController::class, 'update'])->name('sales.update');
	Route::delete('/sales/{sale}', [SaleController::class, 'destroy'])->name('sales.destroy');
	
	// Barcode Routes (View and Search for all)
	Route::get('/barcodes', [BarcodeController::class, 'index'])->name('barcodes.index');
	Route::post('/barcodes/search', [BarcodeController::class, 'search'])->name('barcodes.search');
	Route::get('/barcodes/{barcode}', [BarcodeController::class, 'show'])->name('barcodes.show');
	Route::get('/barcodes/{barcode}/print', [BarcodeController::class, 'print'])->name('barcodes.print');
	
	// Kasir Routes
	Route::get('/kasir', [KasirController::class, 'index'])->name('kasir.index');
	Route::post('/kasir/search-barcode', [KasirController::class, 'searchByBarcode'])->name('kasir.search-barcode');
	Route::post('/kasir/transaction', [KasirController::class, 'store'])->name('kasir.store');
	Route::get('/kasir/receipt/{transaction}', [KasirController::class, 'receipt'])->name('kasir.receipt');
	Route::get('/kasir/today-transactions', [KasirController::class, 'todayTransactions'])->name('kasir.today-transactions');
	Route::get('/kasir/low-stock-items', [KasirController::class, 'getLowStockItems'])->name('kasir.low-stock-items');
	Route::get('/kasir/popular-items', [KasirController::class, 'getPopularItems'])->name('kasir.popular-items');
	
	// Transaction History Routes
	Route::get('/transaction-history', [TransactionHistoryController::class, 'index'])->name('transaction-history.index');
	Route::get('/transaction-history/{transaction}', [TransactionHistoryController::class, 'show'])->name('transaction-history.show');
	Route::get('/transaction-history/{transaction}/edit', [TransactionHistoryController::class, 'edit'])->name('transaction-history.edit');
	Route::put('/transaction-history/{transaction}', [TransactionHistoryController::class, 'update'])->name('transaction-history.update');
	Route::post('/transaction-history/fix-cashier-data', [TransactionHistoryController::class, 'fixCashierData'])->name('transaction-history.fix-cashier-data');
	Route::post('/transaction-history/fix-transaction-cashier/{transaction}', [TransactionHistoryController::class, 'fixSpecificTransactionCashier'])->name('transaction-history.fix-transaction-cashier');
	Route::get('/transaction-history-export', [TransactionHistoryController::class, 'export'])->name('transaction-history.export');
	Route::get('/api/recent-transactions', [TransactionHistoryController::class, 'getRecentTransactions'])->name('api.recent-transactions');
	
	// Search Routes
	Route::get('/api/search/global', [SearchController::class, 'globalSearch'])->name('api.search.global');
	Route::get('/api/search/items', [SearchController::class, 'quickItemSearch'])->name('api.search.items');
});

// Fallback route for other pages (approved users only)
Route::group(['middleware' => ['auth', 'approved']], function () {
	Route::get('/{page}', [PageController::class, 'index'])->name('page');
});