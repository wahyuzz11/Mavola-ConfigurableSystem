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
    
}



