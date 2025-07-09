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
        $inventoryTracking = $configController->GetOneConfigMethod("inventory_tracking_method");
        $categories = Category::all();
        $products = Product::all();
        return view('products.index', compact('products', 'cogsMethod', 'categories', 'inventoryTracking'));
    }


    public function create()
    {
        $categories = Category::all();
        $configController = new ConfigurationController();
        $cogsMethod = $configController->GetOneConfigMethod("cogs_method");
        $inventoryTracking = $configController->GetOneConfigMethod("inventory_tracking_method");
        return view('products.addProduct', compact('categories', 'cogsMethod', 'inventoryTracking'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $configController = new ConfigurationController();
            $cogsMethod = $configController->GetOneConfigMethod("cogs_method");
            $newProduct = new Product();
            $newProduct->product_name = $request->get("name");
            $newProduct->description = $request->get("description");
            $newProduct->minimum_total_stock = $request->get("minimum_total_stock");
            $newProduct->total_stock = $request->get("total_stock");
            $newProduct->unit_name = $request->get("unit_name");
            $newProduct->expired_date_settings = $request->get("expired_date_settings");
            if ($cogsMethod == 'FIFO' || $cogsMethod == 'avarage') {
                $newProduct->cost = 0;
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

            return redirect(url()->previous())->with('status', 'Product data has been successfully created!');
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
        $inventoryTracking = $configController->GetOneConfigMethod("inventory_tracking_method");
        return view('products.edit', compact('product', 'categories', 'cogsMethod', 'inventoryTracking'));
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
            $updatedProduct->expired_date_settings = $request->expired_date_settings;
            $updatedProduct->price = $request->price;

            // Get configuration
            $configController = new ConfigurationController();
            $cogsMethod = $configController->GetOneConfigMethod("cogs_method");
            $inventoryTracking = $configController->GetOneConfigMethod("inventory_tracking_method");

            // Update stock only if periodic inventory tracking
            if ($inventoryTracking == 'periodic') {
                $updatedProduct->total_stock = $request->total_stock;
            }

            // Update cost if needed
            if ($cogsMethod == 'standard' || $inventoryTracking == 'periodic') {
                $updatedProduct->cost = $request->cost;
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
}
