<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DebtHistory;
use App\Models\Supplier;
use App\Models\Purchase;
use Illuminate\Auth\Events\Validated;
use Illuminate\Support\Facades\DB;



class DebtHistoryController extends Controller
{
    public function index()
    {

        $pendingDebts = DebtHistory::where('status', 'pending')->orderBy('due_date', 'DESC')->simplePaginate(3, ['*'], 'pending_page');
        $paidDebts = DebtHIstory::where('status', 'paid')->orderBy('bill_date', 'DESC')->simplePaginate(3, ['*'], 'paid_page');
        $lateDebts = DebtHistory::where('status', 'late')->orderBy('bill_date', 'DESC')->simplePaginate(3, ['*'], 'late_page');

        $pendingCount = DebtHistory::where('status', 'pending')->count();
        $paidCount = DebtHistory::where('status', 'paid')->count();
        $lateCount = DebtHistory::where('status', 'late')->count();

        return view('purchases.debt', compact(
            'pendingDebts',
            'paidDebts',
            'lateDebts',
            'pendingCount',
            'paidCount',
            'lateCount',
        ));
    }

    /**
     * Show the form for creating a new debt history.
     */
    public function create()
    {
        $suppliers = Supplier::all();
        $purchases = Purchase::all();
        return view('debt_histories.create', compact('suppliers', 'purchases'));
    }

    /**
     * Display the specified debt history.
     */
    public function show(string $id)    
    {
        $debtHistory = DebtHistory::with([
            'purchase',
            'purchase.purchaseDetails',
            'purchase.purchaseDetails.product',
            'supplier'
        ])->findOrFail($id);

        return view('purchases.debtDetail', compact('debtHistory'));
    }

    /**
     * Show the form for editing the specified debt history.
     */
    public function edit(DebtHistory $debtHistory)
    {
        $suppliers = Supplier::all();
        $purchases = Purchase::all();
        $statuses = ['pending', 'paid', 'late'];

        return view('debt_histories.edit', compact('debtHistory', 'suppliers', 'purchases', 'statuses'));
    }

    /**
     * Update the specified debt history in storage.
     */
    public function update(Request $request, DebtHistory $debtHistory)
    {
        $validated = $request->validate([
            'debt_nominal' => 'required|numeric',
            'bill_date' => 'required|date',
            'due_date' => 'required|date|after:bill_date',
            'status' => 'required|in:pending,paid,late',
            'supplier_id' => 'required|exists:suppliers,id',
            'purchases_id' => 'required|exists:purchases,id'
        ]);

        $debtHistory->update($validated);

        return redirect()->route('debt-histories.index')
            ->with('success', 'Debt history updated successfully.');
    }

    /**
     * Remove the specified debt history from storage.
     */
    public function destroy(DebtHistory $debtHistory)
    {
        $debtHistory->delete();

        return redirect()->route('debt-histories.index')
            ->with('success', 'Debt history deleted successfully.');
    }

    public function markAsPaid(DebtHistory $debt, Request $request)
    {
        try {
            $validated = $request->validate([
                'paymentMethod' => 'required|string',
            ]);

            $status = now()->gt($debt->due_date) ? 'late' : 'paid';

            DB::beginTransaction();

            $debt->update([
                'status'         => $status,
                'payment_method' => $validated['paymentMethod'],
                'updated_at'     => now(),
            ]);

            
            // $debt->payment_method = $validated['paymentMethod'];
            $debt->save();

            $newJournal = JournalEntryController::storeJournalDebt($debt);
            $account    = JournalEntryController::checkDebtAccount($debt);

            foreach ($account as $acc) {
                JournalEntryController::storeJournalEntry(
                    $acc['code'],
                    $acc['position'],
                    $debt->debt_nominal,
                    $newJournal->id
                );
            }

            DB::commit();

            $message = $status === 'paid'
                ? 'Debt marked as paid successfully'
                : 'Debt marked as late (payment after due date)';

            return response()->json([
                'success'  => true,
                'message'  => $message,
                'redirect' => route('debts.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update debt status: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function checkPendingDebts()
    {
        $hasPendingDebts = DebtHistory::whereIn('status', ['pending'])->exists();

        return response()->json([
            'has_pending_debts' => $hasPendingDebts
        ]);
    }
}
