<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $suppliers = Supplier::all();
        return view('purchases.suppliers', compact('suppliers'));
    }

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
        $newSupplier = new Supplier();

        $newSupplier->company_name = $request->get('company_name');
        $newSupplier->owner_name = $request->get('owner_name');
        $newSupplier->phone_number = $request->get('phone_number');
        $newSupplier->email = $request->get('email') ?? null;
        $newSupplier->address = $request->get('address');
        $newSupplier->status_active = 1;
        $newSupplier->save();

        return redirect(url()->previous())->with('status', 'Product data has been successfully created!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $supplier)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        //
    }

    public function findSupplier(Request $request)
    {
        $search = $request->input('search');

        // Query suppliers table for matching names
        $suppliers = Supplier::where('company_name', 'LIKE', '%' . $search . '%')
            ->where('status_active', 1) // Optional: Only active suppliers
            ->select('id', 'company_name')
            ->get();

        // Format data for Select2
        $formattedSuppliers = $suppliers->map(function ($supplier) {
            return [
                'id' => $supplier->id,
                'text' => $supplier->company_name,
            ];
        });

        return response()->json($formattedSuppliers);
    }
}
