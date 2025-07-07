@extends('layouts.index')

@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">
            Debt Details - Invoice #{{ $debtHistory->purchase->invoice_number }}
        </h3>
    </div>

    <!-- Debt Information Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <p><strong>Debt Status:</strong>
                        <span
                            class="badge bg-{{ $debtHistory->status == 'paid' ? 'success' : ($debtHistory->status == 'late' ? 'danger' : 'warning') }}">
                            {{ ucfirst($debtHistory->status) }}
                        </span>
                    </p>
                    <p><strong>Supplier:</strong> {{ $debtHistory->supplier->company_name }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Bill Date:</strong> {{ \Carbon\Carbon::parse($debtHistory->bill_date)->format('d M Y') }}</p>
                    <p><strong>Due Date:</strong>
                        <span @if ($debtHistory->due_date < now()) class="text-danger" @endif>
                            {{ \Carbon\Carbon::parse($debtHistory->due_date)->format('d M Y') }}
                        </span>
                    </p>
                </div>
                <div class="col-md-4">
                    <p><strong>Debt Amount:</strong> Rp {{ number_format($debtHistory->debt_nominal, 0, ',', '.') }}</p>
                    @if ($debtHistory->status == 'paid')
                        <p><strong>Paid Date:</strong>
                            {{ \Carbon\Carbon::parse($debtHistory->updated_at)->format('d M Y') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase Information Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Purchase Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <p><strong>Invoice Number:</strong> {{ $debtHistory->purchase->invoice_number }}</p>
                    <p><strong>Purchase Date:</strong>
                        {{ \Carbon\Carbon::parse($debtHistory->purchase->purchase_date)->format('d M Y') }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Status:</strong> {{ ucfirst($debtHistory->purchase->status) }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Total Price:</strong> Rp
                        {{ number_format($debtHistory->purchase->total_price, 0, ',', '.') }}</p>
                    @if ($debtHistory->purchase->receive_method == 'RE-02')
                        <p><strong>Delivery Cost:</strong> Rp
                            {{ number_format($debtHistory->purchase->delivery_cost, 0, ',', '.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase Details Table -->
    <div class="card">
        <div class="card-header">
            <h5>Purchased Items</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($debtHistory->purchase->purchaseDetails as $detail)
                        <tr>
                            <td>{{ $detail->product->product_name }}</td>
                            <td>{{ $detail->quantity }}</td>
                            <td>Rp {{ number_format($detail->purchase_price, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-right">Grand Total:</th>
                        <th>Rp {{ number_format($debtHistory->purchase->total_price, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="mt-4">
        @if ($debtHistory->status != 'paid')
            <form action="{{ route('debts.mark-paid', $debtHistory->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check-circle"></i> Mark as Paid
                </button>
            </form>
        @endif
        <a href="{{ route('debts.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
@endsection
