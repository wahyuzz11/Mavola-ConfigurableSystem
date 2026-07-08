<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $configController = new ConfigurationController();
        $cogsMethod = $configController->GetOneConfigMethod("cogs_method");
        $categories = Category::all();
        $products = Product::all();
        return view('products.index', compact('products', 'cogsMethod', 'categories'));
    }


    public function create()
    {
        $categories = Category::all();
        $configController = new ConfigurationController();
        $cogsMethod = $configController->GetOneConfigMethod("cogs_method");
        $expiredDateSetting = $configController->getOneConfigMethod("expired_date_settings");
        return view('products.addProduct', compact('categories', 'cogsMethod', 'expiredDateSetting'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {

            $configController = new ConfigurationController();
            $newProduct = new Product();
            $newProduct->product_name = $request->get("name");
            $newProduct->description = $request->get("description");
            $newProduct->minimum_total_stock = $request->get("minimum_total_stock");
            $newProduct->total_stock = 0;
            $newProduct->unit_name = $request->get("unit_name");


            if ($request->has(["expired_active_setting", "expired_date_setting"])) {
                $newProduct->expired_date_active = $request->get("expired_active_setting");
                $newProduct->expired_date_setting = $request->get("expired_date_setting");
            } else {
                $newProduct->expired_date_active = 0;
            }

            if ($request->hasFile('file_image')) {
                $file = $request->file('file_image');
                $filename = time() . "_" . preg_replace('/\s+/', '_', $file->getClientOriginalName());
                $file->move('assets/img/product', $filename);
            }

            $newProduct->image = isset($filename) ? $filename : null;
            $newProduct->price = $request->get('price');
            $newProduct->categories_id = $request->get('categories_id');
            $newProduct->save();

            //  return redirect()->back()->with('success', 'Configuration updated successfully!');
            return redirect(url()->previous())->with('success', 'Product data has been successfully created!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->with('error', 'Failed to store: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(String $id)
    {
        $product = Product::find($id);
        $categories = Category::all();
        $configController = new ConfigurationController();
        $cogsMethod = $configController->GetOneConfigMethod("cogs_method");
        $expiredDateSetting = $configController->getOneConfigMethod("expired_date_settings");
        return view('products.edit', compact('product', 'categories', 'cogsMethod', 'expiredDateSetting'));
    }

    public function edit(Product $product) {}


    public function update(Request $request, string $id)
    {
        try {
            $updatedProduct = Product::find($id);

            if (!$updatedProduct) {
                return redirect()->back()->with('error', 'Product not found!');
            }

            // Update basic fields
            $updatedProduct->product_name = $request->name;
            $updatedProduct->description = $request->description;
            $updatedProduct->categories_id = $request->categories_id;
            $updatedProduct->minimum_total_stock = $request->minimum_total_stock;
            $updatedProduct->unit_name = $request->unit_name;
            $updatedProduct->price = $request->price;

            if ($request->has('cost')) {
                $updatedProduct->cost = $request->get('cost');
            }

            if ($request->has('total_stock')) {
                $updatedProduct->total_stock = $request->get('total_stock');
            }

            if ($request->has(['expired_active_setting', 'expired_date_setting'])) {
                $updatedProduct->expired_date_active = $request->get('expired_active_setting');
                $updatedProduct->expired_date_setting = $request->get('expired_date_setting');
            } else {
                $updatedProduct->expired_date_active = 0;
            }

            // Handle file upload
            if ($request->hasFile('file_image')) {
                $file = $request->file('file_image');
                $filename = time() . "_" . preg_replace('/\s+/', '_', $file->getClientOriginalName());
                $file->move('assets/img/product', $filename);
                $updatedProduct->image = $filename;
            }

            $updatedProduct->save();

            return redirect()->route('products.index')->with('status', 'Your product is successfully updated!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update product: ' . $e->getMessage());
        }
    }

    public function destroy(Product $product)
    {
        // Cek apakah masih ada batch yang belum kosong (empty_status == 1)
        $hasActiveBatch = $product->productBatchs()
            ->where('empty_status', 1)
            ->exists();

        if ($hasActiveBatch) {
            return redirect()->back()->with('error', 'Produk "' . $product->product_name . '" tidak dapat dihapus karena masih terdapat batch yang belum kosong.');
        }

        $product->delete();
        return back()->with('success', 'Produk berhasil dihapus.');
    }
}
