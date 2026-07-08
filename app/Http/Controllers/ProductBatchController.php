<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\SaleDetail;
use Exception;
use Illuminate\Http\Request;

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
    public function show(String $productId)
    {
        $expirySettings = ConfigurationController::getOneConfigMethod('expired_date_settings');
        $cogsSettings = ConfigurationController::getOneConfigMethod('cogs_method');
        $product = Product::find($productId);
        if ($expirySettings == "tanggal kadaluarsa" && $cogsSettings == 'average') {
            $batches = ProductBatch::where('products_id', $productId)->orderBy('expired_date', 'ASC')->get();
        } else {
            $batches = ProductBatch::where('products_id', $productId)->orderBy('purchase_date', 'ASC')->get();
        }


        return view('products.batch', compact('batches', 'product', 'expirySettings'));
    }


    public function edit(ProductBatch $productBatch) {}


    public function update(SaleDetail $saleDetail) {}



    public function destroy(String $id)
    {
        $productBatch = ProductBatch::find($id);
        $productBatch->delete();
        return redirect()->back()->with('status', 'Product batch has been successfully deleted!');
    }


    public static function generateSerialCode($products_id): string
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

    public function updateExpiredDate(Request $request, ProductBatch $productBatch)
    {
        $validated = $request->validate([
            'expired_date' => [
                'required',
                'date',
                'after_or_equal:' . $productBatch->purchase_date,
            ],
        ], [
            'expired_date.after_or_equal' => 'Tanggal kadaluarsa tidak boleh lebih awal dari tanggal pembelian.',
        ]);

        $productBatch->update([
            'expired_date' => $validated['expired_date'],
        ]);

        return redirect()->back()->with('success', 'Tanggal kadaluarsa berhasil diperbarui');
    }
}
