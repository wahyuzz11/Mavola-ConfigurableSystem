@extends('layouts.index')

@section('content')


    <div class="page-header">
        <h3 class="fw-bold mb-3">
            Purchase Details - Invoice #{{ $purchase->invoice_number }}
        </h3>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    <script>
                        console.error("Laravel error: {{ $error }}");
                    </script>
                @endforeach
            </ul>
        </div>
    @endif


    <!-- Purchase Information Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <p><strong>Invoice Number:</strong> {{ $purchase->invoice_number }}</p>
                    <p><strong>Supplier:</strong> {{ $supplier->company_name }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Purchase Date:</strong>
                        {{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d M Y H:i') }}</p>
                    <p><strong>Receive Date:</strong>
                        {{ $purchase->receive_date ? \Carbon\Carbon::parse($purchase->receive_date)->format('d M Y H:i') : 'Not received yet' }}
                    </p>
                </div>
                <div class="col-md-4">
                    <p><strong>Total Price:</strong> Rp {{ number_format($purchase->total_price, 0, ',', '.') }}</p>
                    <p><strong>Status:</strong> {{ $purchase->status }}</p>
                </div>
            </div>
        </div>
    </div>

    @if ($purchase->status == 'In delivery')
        <div class="card mb-4">
            <div class="card-header bg-warning">
                <h5>Confirm Receipt</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('purchases.receive', $purchase->id) }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="receive_date">Receive Date</label>
                                <input type="datetime-local" class="form-control" id="receive_date" name="receive_date"
                                    required value="{{ old('receive_date', now()->format('Y-m-d\TH:i')) }}">
                            </div>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check-circle"></i> Confirm Receipt
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif


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
                    @forelse($purchaseDetails as $detail)
                        <tr>
                            <td>{{ $detail->product->product_name }}</td>
                            <td>{{ $detail->quantity }}</td>
                            <td>Rp {{ number_format($detail->purchase_price, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No items found for this purchase</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-right">Grand Total:</th>
                        <th>Rp {{ number_format($purchase->total_price, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
