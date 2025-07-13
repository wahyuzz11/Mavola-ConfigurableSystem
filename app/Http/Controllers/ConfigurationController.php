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

            // query data lengkap konfigurasi yang dipilih
            $newInventoryMethod = SubConfiguration::findOrFail($request->input('inventory_method'));

            // If switching to periodic
            if (strtolower($newInventoryMethod->code) === 'INV-T-02') {
                $activeBatch = ProductBatch::where('stock', '>', 0)
                    ->where('empty_status', 0)
                    ->exists();

                if ($activeBatch) {
                    throw new \Exception("Cannot switch to periodic while active product batches exist.");
                }

                // Recalculate periodic stock
                $this->recalculatePeriodicStock();

                //Disable COGS config in configurationn table
                Configuration::where('code', 'COGS')->update(['status' => 0]);
                // Disable all COGS related configurations
                SubConfiguration::whereHas('configuration', function ($query) {
                    $query->where('name', 'cogs_method');
                })->update(['status' => 0]);
            }

            // Update inventory method
            // reset semua status konfigurasi ke 0
            SubConfiguration::whereHas('configuration', function ($query) {
                $query->where('name', 'inventory_tracking_method');
            })->update(['status' => 0]);

            SubConfiguration::where('id', $request->input('inventory_method'))
                ->update(['status' => 1]);

            // Update COGS method only if enabled and inventory method is perpetual
            if ($newInventoryMethod->code == 'INV-T-01') {
                // Update expired status
                SubConfiguration::where('code', 'EXP-01')
                    ->update(['status' => $request->has('expired_status') ? 1 : 0]);

                // Update COGS activation

                // $enableCogs = $request->has('enable_cogs') ? 1 : 0;

                SubConfiguration::whereHas('configuration', function ($query) {
                    $query->where('name', 'cogs_method');
                })->update(['status' => 0]); // First disable all

                SubConfiguration::where('id', $request->input('cogs_method'))
                    ->update(['status' => 1]);
            }

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

        return view('settings.purchase', compact('paymentMethods', 'receivingMethods'));
    }

    public function updatePurchaseConfiguration(Request $request)
    {
        try {
            DB::beginTransaction();

            // Update metode pembayaran (konfigurasi purchase_payment)
            $purchasePaymentConfig = Configuration::where('name', 'purchase_payment')->first();
            $receivingConfig =  Configuration::where('name', 'receiving_purchase_method')->first();
            // Ambil semua sub-konfigurasi pembayaran
            $paymentMethods = SubConfiguration::where('configurations_id',   $purchasePaymentConfig->id)->get();
            $receivingMethods = SubConfiguration::where('configurations_id', $receivingConfig->id)->get();

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

            // Update status metode penerimaan barang
            foreach ($receivingMethods as $method) {
                $status = 0;
                // Metode wajib selalu diaktifkan
                if ($method->types == 'mandatory') {
                    $status = 1;
                }
                // Untuk non-wajib, cek apakah ada di request
                elseif (in_array($method->id, $request->input('receiving_method', []))) {
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

        $shippingMethods = SubConfiguration::with('configuration')
            ->whereHas('configuration', function ($query) {
                $query->where('name', 'shipping_sale_method');
            })
            ->get();

        $discountStatus = configuration::where('name', 'sale_discount')->first();
        $discStatus = $discountStatus ? $discountStatus->status : 0;
        $discountMethods = SubConfiguration::with('configuration')
            ->whereHas('configuration', function ($query) {
                $query->where('name', 'sale_discount');
            })
            ->get();


        return view('settings.sale', compact('paymentMethods', 'discStatus', 'discountMethods', 'shippingMethods'));
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

            // Update shipping methods
            $shippingConfig = Configuration::where('name', 'shipping_sale_method')->first();
            $shippingMethods = SubConfiguration::where('configurations_id', $shippingConfig->id)->get();

            foreach ($shippingMethods as $method) {
                $status = ($method->types == 'mandatory' || in_array($method->id, $request->input('shipping_method', []))) ? 1 : 0;
                $method->update(['status' => $status]);
            }

            // Update discount configuration
            $saleDiscountConfig = Configuration::where('name', 'sale_discount')->first();
            $discountStatus = $request->has('discount_status') ? 1 : 0;
            $saleDiscountConfig->update(['status' => $discountStatus]);

            // Process discount methods with mutual exclusivity validation
            $discountMethods = SubConfiguration::where('configurations_id', $saleDiscountConfig->id)->get();
            $discountValues = $request->input('discount_value', []);
            $selectedDiscountMethods = $request->input('discount_method', []);

            // Check for mutual exclusivity between global and product-specific discounts
            $globalDiscountMethod = $discountMethods->where('code', 'DISC-02')->first();
            $productDiscountMethod = $discountMethods->where('code', 'DISC-01')->first();

            $globalSelected = $globalDiscountMethod && in_array($globalDiscountMethod->id, $selectedDiscountMethods);
            $productSelected = $productDiscountMethod && in_array($productDiscountMethod->id, $selectedDiscountMethods);

            // If both are selected, prioritize the last one in the array (or implement your own logic)
            if ($globalSelected && $productSelected) {
                // Find which one comes last in the selected array
                $globalIndex = array_search($globalDiscountMethod->id, $selectedDiscountMethods);
                $productIndex = array_search($productDiscountMethod->id, $selectedDiscountMethods);

                if ($globalIndex > $productIndex) {
                    // Global discount was selected last, remove product discount
                    $selectedDiscountMethods = array_diff($selectedDiscountMethods, [$productDiscountMethod->id]);
                } else {
                    // Product discount was selected last, remove global discount
                    $selectedDiscountMethods = array_diff($selectedDiscountMethods, [$globalDiscountMethod->id]);
                }
            }

            foreach ($discountMethods as $method) {
                $updateData = [
                    'status' => $discountStatus && in_array($method->id, $selectedDiscountMethods) ? 1 : 0,
                    'value' => 0 // Default value
                ];

                // Only update value if method is active
                if ($updateData['status'] && isset($discountValues[$method->id])) {
                    $updateData['value'] = (int)$discountValues[$method->id]; // Direct integer conversion
                }

                $method->update($updateData);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Configuration updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update configuration: ' . $e->getMessage());
        }
    }


    // berfungsi untuk mengambil subconfig yang aktif
    public function getOneConfigMethod($name)
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


    public function recalculatePeriodicStock()
    {
        try {
            $products = Product::all();
            $processedProducts = 0;
            $updatedProducts = [];

            foreach ($products as $product) {
                $initialStock = $product->total_stock;

                $purchasedQty = PurchaseDetail::where('products_id', $product->id)
                    ->whereNull('recalculate_date')
                    ->sum('quantity');

                $soldQty = SaleDetail::where('products_id', $product->id)
                    ->whereNull('recalculate_date')
                    ->sum('quantity');

                $adjustedStock = $product->total_stock + $purchasedQty - $soldQty;

                // Only update if there's a change
                if ($adjustedStock != $initialStock) {
                    $product->update([
                        'total_stock' => $adjustedStock
                    ]);

                    $updatedProducts[] = [
                        'name' => $product->product_name,
                        'old_stock' => $initialStock,
                        'new_stock' => $adjustedStock,
                        'difference' => $adjustedStock - $initialStock
                    ];
                }

                // Update semua detail sebagai sudah direkalkulasi
                PurchaseDetail::where('products_id', $product->id)
                    ->whereNull('recalculate_date')
                    ->update(['recalculate_date' => Carbon::now()]);

                SaleDetail::where('products_id', $product->id)
                    ->whereNull('recalculate_date')
                    ->update(['recalculate_date' => Carbon::now()]);

                $processedProducts++;
            }

            // Return success response
            return [
                'success' => true,
                'message' => "Stock recalculation completed successfully! {$processedProducts} products processed, " . count($updatedProducts) . " products updated.",
                'processed_products' => $processedProducts,
                'updated_products' => count($updatedProducts),
                'details' => $updatedProducts
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error during stock recalculation: ' . $e->getMessage()
            ];
        }
    }
}
