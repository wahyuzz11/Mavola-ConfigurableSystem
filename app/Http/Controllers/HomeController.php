<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // ================= SALES & PURCHASES SUMMARY (existing) =================
        $totalSalesTransactions = DB::table('sales')->whereNull('deleted_at')->count();
        $totalPurchaseTransactions = DB::table('purchases')->whereNull('deleted_at')->count();

        // ================= EXPIRING BATCH (existing) =================
        $expiringSoonBatch = DB::table('product_batchs')
            ->join('products', 'products.id', '=', 'product_batchs.products_id')
            ->select(
                'product_batchs.*',
                'products.name as product_name'
            )
            ->where('product_batchs.empty_status', 0)
            ->whereNull('product_batchs.deleted_at')
            ->whereDate('product_batchs.expired_date', '>=', now()->toDateString())
            ->whereDate('product_batchs.expired_date', '<=', now()->addDays(7)->toDateString())
            ->orderBy('product_batchs.expired_date', 'asc')
            ->get();

        $expiringBatchCount = $expiringSoonBatch->count();

        // ================= REVENUE, HPP, GROSS PROFIT (dari jurnal, periode fleksibel) =================
        $period = $request->input('period', 'month'); // default: bulan ini
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

        $accountBalances = DB::table('journal_entries_details as jed')
            ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entries_id')
            ->join('accounts as a', 'a.id', '=', 'jed.accounts_id')
            ->whereNull('jed.deleted_at')
            ->whereNull('je.deleted_at')
            ->whereBetween('je.created_at', [$start, $end])
            ->whereIn('a.code', ['4101', '4102', '5101']) // Penjualan, Diskon Penjualan, HPP
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

        // Diskon Penjualan (4102) - contra revenue, saldo normal DEBIT
        $salesDiscount = isset($accountBalances['4102'])
            ? $accountBalances['4102']->total_debit - $accountBalances['4102']->total_credit
            : 0;

        // HPP (5101) - saldo normal DEBIT
        $cogs = isset($accountBalances['5101'])
            ? $accountBalances['5101']->total_debit - $accountBalances['5101']->total_credit
            : 0;

        $netRevenue = $revenue - $salesDiscount;
        $grossProfit = $netRevenue - $cogs;
        $grossMarginPercent = $netRevenue > 0
            ? round(($grossProfit / $netRevenue) * 100, 2)
            : 0;
        $profit = $grossProfit;

        return view('home.index', compact(
            'totalSalesTransactions',
            'totalPurchaseTransactions',
            'expiringSoonBatch',
            'expiringBatchCount',
            'profit',
            'revenue',
            'salesDiscount',
            'netRevenue',
            'cogs',
            'grossProfit',
            'grossMarginPercent',
            'period',
            'start',
            'end',
        ));
    }
}
