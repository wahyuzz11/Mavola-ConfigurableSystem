<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductBatchController;
use App\Http\Controllers\DebtHistoryController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\SubConfigurationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
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
Route::patch('/product-batches/{productBatch}/expired-date', [ProductBatchController::class, 'updateExpiredDate'])
    ->name('productBatches.updateExpiredDate');

    
Route::get('/purchases/query', [PurchaseController::class, 'productQuery'])->name('purchases.query');
Route::get('/purchases/suppliers', [SupplierController::class, 'index'])->name('purchases.suppliers');
Route::post('/purchases/suppliers/store', [SupplierController::class, 'store'])->name('suppliers.store');
Route::post('/purchases/{purchase}/receive', [PurchaseController::class, 'confirmReceipt'])->name('purchases.receive');
Route::post('/purchase/{debt}/debtDetail', [DebtHistoryController::class, 'show'])->name('debts.show');
Route::post('/purchases/store-ajax', [PurchaseController::class, 'storeAjax'])->name('purchases.storeAjax');

Route::post('/debts/{debt}/mark-paid', [DebtHistoryController::class, 'markAsPaid'])
    ->name('debts.mark-paid');
Route::get('/debts/check-pending', [DebtHistoryController::class, 'checkPendingDebts'])
    ->name('debts.check-pending');

Route::get('/api/suppliers', [SupplierController::class, 'findSupplier'])->name('findSupplier');
Route::get('/sales/customers/query', [CustomerController::class, 'findCustomer'])->name('findCustomer');
Route::get('/sales/customers', [CustomerController::class, 'index'])->name('sales.customers');
Route::get('/sales/query', [SaleController::class, 'query'])->name('sales.query');
Route::post('/sales/customers/store', [CustomerController::class, 'store'])->name('customers.store');
Route::post('/sales/store-ajax', [SaleController::class, 'storeAjax'])->name('sales.storeAjax');

Route::get('/accounting/{journal}/journalDetail', [JournalEntryController::class, 'detail'])
    ->name('accounting.detail');
Route::get('/accounting/query', [JournalEntryController::class, 'queryAccounts'])
    ->name('accounting.query');
Route::get('/accounting/accounts', [AccountController::class, 'index'])->name('accounts.index');


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
Route::resource('accounting', JournalEntryController::class);



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
    Route::get('/home', function (Request $request) {
        // Hitung total penjualan
        $totalSales = DB::table('sale_details')->whereNull('deleted_at')->sum('subtotal');
        // Hitung total pembelian
        $totalPurchases = DB::table('purchase_details')->whereNull('deleted_at')->sum('subtotal');
        // Jumlah transaksi
        $totalSalesTransactions = DB::table('sales')->whereNull('deleted_at')->count();
        $totalPurchaseTransactions = DB::table('purchases')->whereNull('deleted_at')->count();
        // Ambil setting inventory


        $expirySettings = ConfigurationController::getOneConfigMethod('expired_date_settings');
        $expiringSoonBatch = DB::table('product_batchs')
            ->join('products', 'products.id', '=', 'product_batchs.products_id')
            ->select(
                'product_batchs.*',
                'products.product_name as product_name'
            )
            ->where('product_batchs.empty_status', 0)
            ->whereNull('product_batchs.deleted_at')
            ->whereDate('product_batchs.expired_date', '>=', now()->toDateString())
            ->whereDate('product_batchs.expired_date', '<=', now()->addDays(7)->toDateString())
            ->orderBy('product_batchs.expired_date', 'asc')
            ->get();

        $expiringBatchCount = $expiringSoonBatch->count();

        // ================= FILTER PERIODE (untuk perhitungan dari jurnal) =================
        $period = $request->input('period', 'month'); // today | week | month | custom
        $startDateInput = $request->input('start_date');
        $endDateInput = $request->input('end_date');

        switch ($period) {
            case 'today':
                $start = Carbon::today();
                $end   = Carbon::today()->endOfDay();
                break;

            case 'week':
                $start = Carbon::now()->startOfWeek(Carbon::SUNDAY);
                $end   = Carbon::now()->endOfWeek(Carbon::SATURDAY)->endOfDay();
                break;

            case 'custom':
                $start = Carbon::parse($startDateInput)->startOfDay();
                $end   = Carbon::parse($endDateInput)->endOfDay();
                break;

            case 'month':
            default:
                $start = Carbon::now()->startOfMonth();
                $end   = Carbon::now()->endOfMonth()->endOfDay();
                break;
        }

        $profit = null;
        $revenue = 0;
        $cogs = 0;
        $grossProfit = 0;

        // ===== METODE BARU: dari Jurnal (Revenue, HPP, Laba Kotor saja) =====
        $accountBalances = DB::table('journal_entries_details as jed')
            ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entries_id')
            ->join('accounts as a', 'a.id', '=', 'jed.accounts_id')
            ->whereNull('jed.deleted_at')
            ->whereNull('je.deleted_at')
            ->whereBetween('je.created_at', [$start, $end])
            ->whereIn('a.code', ['4101', '5101']) // Penjualan, HPP
            ->select(
                'a.code',
                DB::raw('SUM(jed.debit) as total_debit'),
                DB::raw('SUM(jed.credit) as total_credit')
            )
            ->groupBy('a.code')
            ->get()
            ->keyBy('code');

        // Penjualan (4101) - saldo normal KREDIT
        $revenue = isset($accountBalances['4101'])
            ? $accountBalances['4101']->total_credit - $accountBalances['4101']->total_debit
            : 0;

        // HPP (5101) - saldo normal DEBIT
        $cogs = isset($accountBalances['5101'])
            ? $accountBalances['5101']->total_debit - $accountBalances['5101']->total_credit
            : 0;

        $grossProfit = $revenue - $cogs;
        $profit = $grossProfit;


        // Kirim semua data ke blade
        return view('home.index', [
            'user' => Auth::user(),
            'employee' => Auth::user()->employee,
            'totalSales' => $totalSales,
            'totalPurchases' => $totalPurchases,
            'totalSalesTransactions' => $totalSalesTransactions,
            'totalPurchaseTransactions' => $totalPurchaseTransactions,
            'expiringSoonBatch' => $expiringSoonBatch,
            'expiringBatchCount' => $expiringBatchCount,
            'profit' => $profit,
            'revenue' => $revenue,
            'expirySettings' => $expirySettings,
            'cogs' => $cogs,
            'grossProfit' => $grossProfit,
            'period' => $period,
            'start' => $start,
            'end' => $end,
        ]);
    })->name('home.index');

    Route::post('/logout', [UserController::class, 'logout'])->name('logout');
});
