<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $accounts = Account::orderBy('code')->get();
        return view('accounting.chartOfAccount', compact('accounts'));
    }

    /**th
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('');
    }

    /**
     * Store a newly created resource in storage.
    
     */


    public function show(Account $account)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Account $account)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Account $account)
    {
        try {
        } catch (\Throwable $th) {
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Account $account)
    {
        //
    }

    public function query(Request $request)
    {
        $search = $request->input('search', '');

        $accounts = Account::where('is_active', 1)
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->select('id', 'code', 'name', 'type')
            ->orderBy('code')
            ->limit(20)
            ->get();

        return response()->json($accounts);
    }
}
