<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\TryCatch;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers = Customer::all();
        return view('sales.customer', compact('customers'));
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
        $newCustomer = new Customer();
        $newCustomer->name = $request->get("name");
        $newCustomer->contact1 = $request->get("contact1");
        $newCustomer->contact2 = $request->get("contact2") ?? null;
        $newCustomer->email = $request->get("email") ?? null;
        $newCustomer->address = $request->get("address");
        $newCustomer->status_active = 1;
        $newCustomer->blacklist_status = 0;
        $newCustomer->late_payment = 0;
        $newCustomer->save();

        return redirect(url()->previous())->with('status', 'Customer Data has been successfully created!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        //
    }

    public function findCustomer(Request $request)
    {
        $search = $request->get('search', '');

        $query = \App\Models\Customer::whereNull('deleted_at');

        // If search term provided, filter by it
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('contact1', 'LIKE', "%{$search}%")
                    ->orWhere('contact2', 'LIKE', "%{$search}%");
            });
        }

        $customers = $query->limit(20) // Increase limit for better UX
            ->get()
            ->map(function ($customer) {
                // Build display text with available contact info
                $displayText = $customer->name;

                if ($customer->email) {
                    $displayText .= ' - ' . $customer->email;
                } elseif ($customer->contact1) {
                    $displayText .= ' - ' . $customer->contact1;
                }

                return [
                    'id' => $customer->id,
                    'text' => $displayText,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'contact1' => $customer->contact1,
                    'contact2' => $customer->contact2,
                    'address' => $customer->address,
                ];
            });

        return response()->json($customers);
    }
}
