<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\SubConfiguration;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::all();
        return view('products.category', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $newCategory = new Category();
        $newCategory->name = $request->get("name");

        $newCategory->save();
        return redirect(url()->previous())->with('status', 'New Category has been successfully created!');
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
    public function update(Request $request)
    {
        $selectedSubConfigs = $request->input('sub_configurations', []); // Array of selected IDs

        // Example: Save to database (adjust logic as needed)
        foreach ($selectedSubConfigs as $subConfigId) {
            SubConfiguration::find($subConfigId)->update(['is_selected' => true]);
        }

        return redirect()->back()->with('success', 'Settings saved!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function category() {}
}
