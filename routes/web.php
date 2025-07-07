<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\ProductBatchController;
use App\Http\Controllers\DebtHistoryController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Configuration;
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
    return view('home.index');
})->name('home');


Route::get('/products/category', [CategoryController::class, 'index'])->name('products.category');
Route::post('/products/category/store', [CategoryController::class, 'store'])->name('category.store');
Route::get('/configuration/check-batch-status', [ConfigurationController::class, 'checkBatchStatus'])->name('configuration.checkBatchStatus');


Route::get('/purchases/query', [PurchaseController::class, 'productQuery'])->name('purchases.query');
Route::get('/purchases/suppliers', [SupplierController::class, 'index'])->name('purchases.suppliers');
Route::post('/purchases/suppliers/store', [SupplierController::class, 'store'])->name('suppliers.store');
Route::post('/purchases/{purchase}/receive', [PurchaseController::class, 'confirmReceipt'])->name('purchases.receive');
Route::post('/purchase/{debt}/debtDetail', [DebtHistoryController::class, 'show'])->name('debts.show');
Route::post('/debts/{debt}/mark-paid', [DebtHistoryController::class, 'markAsPaid'])
    ->name('debts.mark-paid');
Route::get('/debts/check-pending', [DebtHistoryController::class, 'checkPendingDebts'])
    ->name('debts.check-pending');

Route::get('/api/suppliers', [SupplierController::class, 'findSupplier'])->name('findSupplier');
Route::get('/sales/customers/query', [CustomerController::class, 'findCustomer'])->name('findCustomer');
Route::get('/sales/customers', [CustomerController::class, 'index'])->name('sales.customers');
Route::get('/sales/query', [SaleController::class, 'query'])->name('sales.query');
Route::post('/sales/customers/store', [CustomerController::class, 'store'])->name('customers.store');


route::get('/settings/inventory', [ConfigurationController::class, 'getInventoryConfiguration'])->name('settings.inventory');
route::post('/updateInventoryConfig', [ConfigurationController::class, 'updateInventoryConfiguration'])->name('configuration.updateInventory');
route::get('/settings/purchase', [ConfigurationController::class, 'getPurchaseConfiguration'])->name('settings.purchase');
route::post('/updatePurchaseConfig', [ConfigurationController::class, 'updatePurchaseConfiguration'])->name('configuration.updatePurchase');
route::get('/settings/sale', [ConfigurationController::class, 'getSaleConfiguration'])->name('settings.sale');
route::post('/updateSaleConfig', [ConfigurationController::class, 'updateSaleConfiguration'])->name('configuration.updateSale');

Route::resource('layouts', MenuController::class);
Route::resource('sales', SaleController::class);
Route::resource('purchases', PurchaseController::class);
Route::resource('products', ProductController::class);
Route::resource('settings', ConfigurationController::class);
Route::resource('batches', ProductBatchController::class);
Route::resource('debts', DebtHistoryController::class);


Route::post('/login', [UserController::class, 'login']);
Route::post('/register', [UserController::class, 'register']);
Route::post('/logout', [UserController::class, 'logout'])->middleware('auth');


// Redirect root to login
Route::redirect('/', '/login');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::post('/login', [UserController::class, 'login']);
});

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/home', function () {
        // Hitung total penjualan
        $totalSales = DB::table('sale_details')->whereNull('deleted_at')->sum('subtotal');

        // Hitung total pembelian
        $totalPurchases = DB::table('purchase_details')->whereNull('deleted_at')->sum('subtotal');

        // Jumlah transaksi
        $totalSalesTransactions = DB::table('sales')->whereNull('deleted_at')->count();
        $totalPurchaseTransactions = DB::table('purchases')->whereNull('deleted_at')->count();

        // Ambil setting inventory
        $inventoryTracking = DB::table('configurations')->where('name', 'inventory_tracking')->value('name');

        $expiringSoonBatch = [];
        $expiringBatchCount = 0;

        if ($inventoryTracking !== 'periodic') {
            $expiringSoonBatch = DB::table('product_batchs')
                ->whereNull('deleted_at')
                ->whereDate('expired_date', '>=', now())
                ->whereDate('expired_date', '<=', now()->addDays(7))
                ->get();

            $expiringBatchCount = $expiringSoonBatch->count();
        }

        $profit = null;
        if ($inventoryTracking === 'periodic') {
            $products = DB::table('products')
                ->whereNull('deleted_at')
                ->whereNotNull('starting_stock_periodic')
                ->get();

            $cogs = 0;
            foreach ($products as $product) {
                $totalPurchaseCost = DB::table('purchase_details')
                    ->whereNull('deleted_at')
                    ->where('products_id', $product->id)
                    ->whereDate('created_at', '>=', $product->periodic_start_date)
                    ->sum('subtotal');

                $totalStartingCost = $product->starting_stock_periodic * $product->cost;
                $cogs += $totalStartingCost + $totalPurchaseCost;
            }

            $profit = $totalSales - $cogs;
        }

        // Kirim semua data ke blade
        return view('home.index', [
            'user' => Auth::user(),
            'employee' => Auth::user()->employee,
            'totalSales' => $totalSales,
            'totalPurchases' => $totalPurchases,
            'totalSalesTransactions' => $totalSalesTransactions,
            'totalPurchaseTransactions' => $totalPurchaseTransactions,
            'inventoryTracking' => $inventoryTracking,
            'expiringSoonBatch' => $expiringSoonBatch,
            'expiringBatchCount' => $expiringBatchCount,
            'profit' => $profit
        ]);
    })->name('home.index');



     Route::post('/logout', [UserController::class, 'logout'])->name('logout');
});
