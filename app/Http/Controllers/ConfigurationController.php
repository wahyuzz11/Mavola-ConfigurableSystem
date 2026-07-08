<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use App\Models\SubConfiguration;
use App\Models\ProductBatch;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\PurchaseDetail;
use App\Models\SaleDetail;
use Illuminate\Support\Carbon;


class ConfigurationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getSalesConfiguration() {}



    public function getInventoryConfiguration()
    {


        $inventoryMethods = SubConfiguration::with('configuration')
            ->whereHas('configuration', function ($query) {
                $query->where('name', 'inventory_tracking_method');
            })
            ->get();

        $activeExpireConfig = Subconfiguration::where('code', 'EXP-01')->first();

        $activeCogsConfig = Configuration::where('code', 'COGS')->first();
        $cogsMethods = SubConfiguration::with('configuration')
            ->whereHas('configuration', function ($query) {
                $query->where('name', 'cogs_method');
            })
            ->get();
        $activeInventoryTracking = SubConfiguration::where('configurations_id', 2)
            ->where('status', 1)->first();



        return view('settings.inventory', compact('cogsMethods', 'inventoryMethods', 'activeInventoryTracking', 'activeCogsConfig', 'activeExpireConfig'));
    }

    public function updateInventoryConfiguration(Request $request)
    {
        DB::beginTransaction();

        try {

            SubConfiguration::where('code', 'EXP-01')
                ->update(['status' => $request->has('expired_status') ? 1 : 0]);

            // Update COGS activation

            // $enableCogs = $request->has('enable_cogs') ? 1 : 0;

            SubConfiguration::whereHas('configuration', function ($query) {
                $query->where('name', 'cogs_method');
            })->update(['status' => 0]); // First disable all

            SubConfiguration::where('id', $request->input('cogs_method'))
                ->update(['status' => 1]);

            DB::commit();
            return redirect()->back()->with('success', 'Configuration updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update configuration: ' . $e->getMessage());
        }
    }


    public function getPurchaseConfiguration()
    {
        $paymentMethods = SubConfiguration::with('configuration')
            ->whereHas('configuration', function ($query) {
                $query->where('name', 'purchase_payment');
            })
            ->get();

        $receivingMethods = SubConfiguration::with('configuration')
            ->whereHas('configuration', function ($query) {
                $query->where('name', 'receiving_purchase_method');
            })
            ->get();

        $activePurchaseShippingConfig = Subconfiguration::where('code', 'SHP-P-01')->first();

        return view('settings.purchase', compact('paymentMethods', 'receivingMethods', 'activePurchaseShippingConfig'));
    }

    public function updatePurchaseConfiguration(Request $request)
    {
        try {
            DB::beginTransaction();

            // Update metode pembayaran (konfigurasi purchase_payment)
            $purchasePaymentConfig = Configuration::where('name', 'purchase_payment')->first();
 
            // Ambil semua sub-konfigurasi pembayaran
            $paymentMethods = SubConfiguration::where('configurations_id',   $purchasePaymentConfig->id)->get();


            SubConfiguration::where('code', 'SHP-P-01')
                ->update(['status' => $request->has('purchase_shipping_expense') ? 1 : 0]);

            // Update status metode pembayaran
            foreach ($paymentMethods as $method) {
                $status = 0;
                // Metode wajib selalu diaktifkan
                if ($method->types == 'mandatory') {
                    $status = 1;
                }
                // Untuk non-mandatory, cek apakah ada di request
                elseif (in_array($method->id, $request->input('payment_method', []))) {
                    $status = 1;
                }

                $method->update(['status' => $status]);
            }
            

            DB::commit();

            return redirect()->back()->with('success', 'Konfigurasi berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui konfigurasi: ' . $e->getMessage());
        }
    }

    public function getSaleConfiguration()
    {

        $paymentMethods = SubConfiguration::with('configuration')
            ->whereHas('configuration', function ($query) {
                $query->where('name', 'sale_payment');
            })
            ->get();

        $activeSaleShippingConfig = Subconfiguration::where('code', 'SHP-S-01')->first();

        $discountStatus = configuration::where('name', 'sale_discount')->first();
        $discStatus = $discountStatus ? $discountStatus->status : 0;
        $discountMethods = SubConfiguration::with('configuration')
            ->whereHas('configuration', function ($query) {
                $query->where('name', 'sale_discount');
            })
            ->get();


        return view('settings.sale', compact('paymentMethods', 'discStatus', 'discountMethods', 'activeSaleShippingConfig'));
    }

    public function updateSaleConfiguration(Request $request)
    {
        try {
            DB::beginTransaction();

            // Update payment methods
            $salePaymentConfig = Configuration::where('name', 'sale_payment')->first();
            $paymentMethods = SubConfiguration::where('configurations_id', $salePaymentConfig->id)->get();

            foreach ($paymentMethods as $method) {
                $status = ($method->types == 'mandatory' || in_array($method->id, $request->input('payment_method', []))) ? 1 : 0;
                $method->update(['status' => $status]);
            }

            SubConfiguration::where('code', 'SHP-S-01')
                ->update(['status' => $request->has('sale_shipping_expense') ? 1 : 0]);

            // Update discount configuration
            $saleDiscountConfig = Configuration::where('name', 'sale_discount')->first();
            $discountStatus = $request->has('discount_status') ? 1 : 0;
            $saleDiscountConfig->update(['status' => $discountStatus]);

            // Process discount methods (single selection via radio button)
            $discountMethods = SubConfiguration::where('configurations_id', $saleDiscountConfig->id)->get();

            // discount_method dikirim dari radio button (single value), bukan array.
            $selectedDiscountMethods = $request->filled('discount_method')
                ? [(int) $request->input('discount_method')]
                : [];

            foreach ($discountMethods as $method) {
                $method->update([
                    'status' => $discountStatus && in_array($method->id, $selectedDiscountMethods) ? 1 : 0,
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Configuration updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update configuration: ' . $e->getMessage());
        }
    }


    // berfungsi untuk mengambil subconfig yang aktif
    public static function getOneConfigMethod($name)
    {
        try {
            $configMethod = DB::table('sub_configurations as sc')
                ->join('configurations as c', 'sc.configurations_id', '=', 'c.id')
                ->select('sc.name')
                ->where('c.name', '=', $name)
                ->where('sc.status', '=', 1)
                ->value('sc.name');

            return $configMethod;
        } catch (Exception $e) {
            throw new Exception("Error in" . __FUNCTION__ . ": " . $e->getMessage());
        }
    }




    public function checkBatchStatus()
    {
        $hasActiveBatch = ProductBatch::where('stock', '>', 0)
            ->where('empty_status', 0)
            ->exists();

        return response()->json([
            'hasActiveBatch' => $hasActiveBatch
        ]);
    }
}
