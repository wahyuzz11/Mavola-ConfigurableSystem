<?php


namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $totalSales = DB::table('sales_details')->whereNull('deleted_at')->sum('subtotal');
        $totalPurchases = DB::table('purchase_details')->whereNull('deleted_at')->sum('subtotal');
        $totalSalesTransactions = DB::table('sales')->whereNull('deleted_at')->count();
        $totalPurchaseTransactions = DB::table('purchases')->whereNull('deleted_at')->count();

        $inventoryTracking = DB::table('configurations')->where('key', 'inventory_tracking')->value('value');

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

        return view('home.index', compact(
            'totalSales',
            'totalPurchases',
            'totalSalesTransactions',
            'totalPurchaseTransactions',
            'expiringSoonBatch',
            'expiringBatchCount',
            'inventoryTracking',
            'profit'
        ));
    }
}
