<?php

namespace App\Http\Controllers;

use App\Models\SaleDetail;
use Exception;
use Illuminate\Http\Request;

class SaleDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($sale_id)
    {
        $details = SaleDetail::where('sale_id', $sale_id)->get();
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
    public function store(array $saleDetail)
{
    try {
        $sale = new SaleDetail();
        $sale->subtotal = $saleDetail['subtotal'];
        $sale->quantity = $saleDetail['quantity'];
        $sale->sales_id = $saleDetail['sales_id'];
        $sale->product_id = $saleDetail['products_id'];
        $sale->discount_value = $saleDetail['discount_value'] ?? null;
        $sale->save();
        
    } catch (Exception $e) {
        throw new Exception("Error in " . __FUNCTION__ . ": " . $e->getMessage());
    }
}

    /**
     * Display the specified resource.
     */
    public function show(SaleDetail $saleDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SaleDetail $saleDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SaleDetail $saleDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SaleDetail $saleDetail)
    {
        //
    }
}
