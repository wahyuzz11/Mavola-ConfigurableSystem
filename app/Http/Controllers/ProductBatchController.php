<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\PurchaseDetail;
use App\Models\SaleDetail;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductBatchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($productId)
    {
        $batchs = ProductBatch::where('products_id', $productId)->get();
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(PurchaseDetail $purchaseDetail)
    {
        DB::beginTransaction();
        try {
            $configCtrl = new ConfigurationController();
            $inventoryMethod = $configCtrl->getOneConfigMethod('inventory_tracking_method');
            $cogsMethod = $configCtrl->getOneConfigMethod('cogs_method');
            $productBatch = new ProductBatch();
            $productBatch->serial_code = $this->generateSerialCode($purchaseDetail->products_id);
            $product = Product::find($purchaseDetail->products_id);

            $purchaseDate = Carbon::parse($purchaseDetail->purchase->purchase_date);
            $expiredDate = $purchaseDate->copy()->addDays($product->expired_date_settings);
            $bestBeforeDate = $purchaseDate->copy()->addDays($product->best_before_date_settings);

            if ($inventoryMethod == 'perpetual') {
                $productBatch->stock = $purchaseDetail->quantity;
                $productBatch->purchase_date = $purchaseDetail->purchase->purchase_date;
                $productBatch->expired_date = $expiredDate;
                $productBatch->best_before_date = $bestBeforeDate;
                $productBatch->empty_status = 0;
                $productBatch->product_id = $purchaseDetail->products_id;
                $productBatch->purchase_details_id = $purchaseDetail->id;
                $productBatch->save();
            } else {
                if ($cogsMethod == 'FIFO') {
                    $productBatch->stock = $purchaseDetail->quantity;
                    $productBatch->purchase_date = $purchaseDetail->purchase->purchase_date;
                    $productBatch->expired_date = $expiredDate;
                    $productBatch->best_before_date = $bestBeforeDate;
                    $productBatch->empty_status = 0;
                    $productBatch->product_id = $purchaseDetail->products_id;
                    $productBatch->purchase_details_id = $purchaseDetail->id;
                    $productBatch->save();

                    $oldStock = $product->total_stock;
                    $oldCost = $product->cost;

                    if ($oldStock == 0) {
                        $newCost = $purchaseDetail->purchase_price;
                        $newStock = $purchaseDetail->quantity;
                    } else {
                        $newCost = $oldCost;
                        $newStock = $oldStock + $purchaseDetail->quantity;
                    }

                    $product->update([
                        'total_stock' => $newStock,
                        'cost' => $newCost
                    ]);
                } else if ($cogsMethod == 'LIFO') {
                    $productBatch->stock = $purchaseDetail->quantity;
                    $productBatch->purchase_date = $purchaseDetail->purchase->purchase_date;
                    $productBatch->expired_date = $expiredDate;
                    $productBatch->best_before_date = $bestBeforeDate;
                    $productBatch->empty_status = 0;
                    $productBatch->product_id = $purchaseDetail->products_id;
                    $productBatch->purchase_details_id = $purchaseDetail->id;
                    $productBatch->save();

                    $oldStock = $product->total_stock;
                    $oldCost = $product->cost;

                    if ($oldStock == 0) {
                        $newCost = $purchaseDetail->purchase_price;
                        $newStock = $purchaseDetail->quantity;
                    } else {
                        $newCost = $purchaseDetail->purchase_price;
                        $newStock = $oldStock + $purchaseDetail->quantity;
                    }

                    $product->update([
                        'total_stock' => $newStock,
                        'cost' => $newCost
                    ]);
                } else if ($cogsMethod == 'standard') {
                    $productBatch->stock = $purchaseDetail->quantity;
                    $productBatch->purchase_date = $purchaseDetail->purchase->purchase_date;
                    $productBatch->expired_date = $expiredDate;
                    $productBatch->best_before_date = $bestBeforeDate;
                    $productBatch->empty_status = 0;
                    $productBatch->product_id = $purchaseDetail->products_id;
                    $productBatch->purchase_details_id = $purchaseDetail->id;
                    $productBatch->save();

                    $oldStock = $product->total_stock;
                    $oldCost = $product->cost;

                    if ($oldStock == 0) {
                        $newCost = $purchaseDetail->purchase_price;
                        $newStock = $purchaseDetail->quantity;
                    } else {
                        $newCost = $purchaseDetail->purchase_price;
                        $newStock = $oldStock + $purchaseDetail->quantity;
                    }


                    $product->update([
                        'total_stock' => $newStock,
                        'cost' => $newCost
                    ]);
                } else if ($cogsMethod == 'avarage') {
                    $productBatch->stock = $purchaseDetail->quantity;
                    $productBatch->purchase_date = $purchaseDetail->purchase->purchase_date;
                    $productBatch->expired_date = $expiredDate;
                    $productBatch->best_before_date = $bestBeforeDate;
                    $productBatch->empty_status = 0;
                    $productBatch->product_id = $purchaseDetail->products_id;
                    $productBatch->purchase_details_id = $purchaseDetail->id;
                    $productBatch->save();

                    $oldStock = $product->total_stock;


                    if ($oldStock == 0) {
                        $newStock = $purchaseDetail->quantity;
                    } else {
                        $newStock = $oldStock + $purchaseDetail->quantity;
                    }

                    $product->update([
                        'total_stock' => $newStock
                    ]);
                }
                DB::commit();
            }
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Error in:" . __FUNCTION__ . ": " . $e->getMessage());
        }
    }


    public function show(String $productId)
    {
        $batches = ProductBatch::where('products_id', $productId)->get();
        $product = Product::find($productId);
        return view('products.batch', compact('batches','product'));
    }


    public function edit(ProductBatch $productBatch) {}


    public function update(SaleDetail $saleDetail)
    {
        try {
            $configCtrl = new ConfigurationController();
            $inventoryMethod = $configCtrl->getConfiguration('inventory_tracking_method');
            $cogsMethod = $configCtrl->getConfiguration('cogs_method');
            $product = Product::find($saleDetail->products_id);
            $productBatch = ProductBatch::find($saleDetail->product_batch_id);
            if ($inventoryMethod == 'perpetual') {
            } else {
                if ($cogsMethod == 'FIFO') {
                    $productBatch->stock -= $saleDetail->quantity;
                    $productBatch->save();

                    $oldStock = $product->total_stock;
                    $newStock = $oldStock - $saleDetail->quantity;
                    $product->update([
                        'total_stock' => $newStock,

                    ]);
                } else if ($cogsMethod == 'LIFO') {
                    $productBatch->stock -= $saleDetail->quantity;
                    $productBatch->save();

                    $oldStock = $product->total_stock;
                    $newStock = $oldStock - $saleDetail->quantity;

                    $product->update([
                        'total_stock' => $newStock,

                    ]);
                } else if ($cogsMethod == 'standard') {
                    $productBatch->stock -= $saleDetail->quantity;
                    $productBatch->save();

                    $oldStock = $product->total_stock;
                    $newStock = $oldStock - $saleDetail->quantity;
                    $product->update([
                        'total_stock' => $newStock,

                    ]);
                } else if ($cogsMethod == 'avarage') {
                    $productBatch->stock -= $saleDetail->quantity;
                    $productBatch->save();

                    $oldStock = $product->total_stock;
                    $newStock = $oldStock - $saleDetail->quantity;

                    if ($oldStock == 0) {
                        $newStock = $saleDetail->quantity;
                    } else {
                        $newStock = $oldStock + $saleDetail->quantity;
                    }


                    $product->update([
                        'total_stock' => $newStock
                    ]);
                }
            }
        } catch (Exception $e) {
            throw new Exception("Error in:" . __FUNCTION__ . ":");
        }
    }



    public function destroy(String $id) {
        $productBatch = ProductBatch::find($id);
        $productBatch->delete();
        return redirect()->back()->with('status', 'Product batch has been successfully deleted!');
    }


    public function generateSerialCode($products_id)
    {

        $datePart = date('ymd');
        $dailyCount = ProductBatch::whereDate('created_at', now()->toDateString())
            ->where('products_id', $products_id)
            ->count() + 1;
        $countPart = str_pad($dailyCount, 2, '0', STR_PAD_LEFT);

        $serialCode = "PB{$datePart}-{$products_id}-{$countPart}";

        return $serialCode;
    }

    public function getBatch($productBatchId, $date)
    {
        try {
            $productBatch = ProductBatch::where('id', $productBatchId)
                ->whereDate('purchase_date', $date)
                ->firstOrFail();

            return $productBatch;
        } catch (Exception $e) {
            throw new Exception("Error in:" . __FUNCTION__ . ": " . $e->getMessage());
        }
    }

    private function calculateFifo() {}

    private function calculateLifo() {}

    private function calculateAvarage() {}

    private function calculateStandard() {}

    private function calculatePerpetual() {}
}
