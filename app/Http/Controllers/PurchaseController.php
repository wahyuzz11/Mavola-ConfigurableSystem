<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\SubConfiguration;
use App\Models\DebtHistory;
use App\Models\Account;
use App\Services\AccountingService;
use App\Services\PurchaseService;
use Carbon\Carbon;
use Carbon\Exceptions\Exception;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Twilio\Domain;

class PurchaseController extends Controller
{


    public function __construct( private readonly PurchaseService $purchase,
        private readonly AccountingService $accounting)
    {}
       

    public function index()
    {
        $purchase = Purchase::orderBy('invoice_number','DESC')->simplePaginate(10,['*'],'completed_page');
        $purchaseCount = Purchase::count();
        $totalPurchaseTransaction = Purchase::sum('total_price');

        return view('purchases.index',compact('purchase,purchaseCount','totalPurchaseTransaction'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('purchases.create',$this->purchase->createFormData() + [
            'checkAccount' => $this->accounting->assetAccountsAreEmpty() ? 'kosong' : null,
        ]);
    }
    
    public function storeAjax(StorePurchaseRequest $request){
        try{
            $purchase = $this->purchase->create($request->validated());
        }
        catch(DomainException $e){
            return response()->json(['error' => $e->getMessage()], 422);
        }
        catch(\Throwable $e){
            report($e);
            return response()->json(['error' => 'Terjadi kesalahan saat menyimpan pembelian'], 500);
        }

        return response()->json([
            'success' => true,
            'purchase_id' => $purchase->id,
            'redirect' => route('purchases.index'),
        ]);
    }


    /**
     * Display the specified resource.
     */
    public function show(Purchase $purchase)
    {
       $purchase->load(['purchaseDetails','suppliers']);

       return view('purchases.detail',[
        'purchase' => $purchase,
        'purchaseDetail' => $purchase->purchaseDetails,
        'supplier' => $purchase->suppliers,
       ]);
    }


    public function confirmReceipt(Request $request, Purchase $purchase){
        $validated = $request->validate(['receive_date' => 'required|date']);

        try{
            $this->purchase->confirmReceipt($purchase,$validated['receive_date']);
        }catch(DomainException $e){ 
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function receive() {}
}
