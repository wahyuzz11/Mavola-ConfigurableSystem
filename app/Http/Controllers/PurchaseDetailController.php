<?php

namespace App\Http\Controllers;


use App\Models\PurchaseDetail;
use Exception;
use Illuminate\Http\Request;

class PurchaseDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($purchaseId)
    {
        $details = PurchaseDetail::where('purchase_id', $purchaseId)->get();
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
    public function store(array $purchaseDetail,$purchaseId)
    {
        try {
            $purchaseDetail = new PurchaseDetail();
            $purchaseDetail->purchases_id = $purchaseId;
            $purchaseDetail->products_id = $purchaseDetail['products_id'];
            $purchaseDetail->quantity = $purchaseDetail['quantity'];
            $purchaseDetail->subtotal = $purchaseDetail['total'];
            $purchaseDetail->save();
            
        } catch (Exception $e) {
            throw new Exception("Error in" . __FUNCTION__ . ": " . $e->getMessage());
        }
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}



