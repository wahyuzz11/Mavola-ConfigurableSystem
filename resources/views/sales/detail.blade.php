@extends('layouts.index')

@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">
            Sales Details - Invoice #{{ $sale->invoice_number }}
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

    <!-- Sales Information Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <p><strong>Invoice Number:</strong> {{ $sale->invoice_number }}</p>
                    <p><strong>Customer:</strong> {{ $sale->customer->name ?? $sale->customer->customer_name }}</p>
                    <p><strong>Payment Method:</strong>
                        @if ($sale->payment_methods == 'S-PAY-01')
                            Cash
                        @elseif ($sale->payment_methods == 'S-PAY-02')
                            Transfer
                        @elseif ($sale->payment_methods == 'S-PAY-03')
                            QRIS
                        @endif
                    </p>
                </div>
                <div class="col-md-4">
                    <p><strong>Sale Date:</strong>
                        {{ \Carbon\Carbon::parse($sale->sale_date)->format('d M Y H:i') }}</p>
                    <p><strong>Delivery Method:</strong>
                        {{ $sale->delivery_method == 'DEL-01' ? 'Pickup' : 'Delivery' }}</p>
                    @if ($sale->delivery_method == 'DEL-02')
                        <p><strong>Shipped Date:</strong>
                            {{ $sale->shipped_date ? \Carbon\Carbon::parse($sale->shipped_date)->format('d M Y H:i') : 'Not shipped yet' }}
                        </p>
                    @endif
                </div>
                <div class="col-md-4">
                    @if ($sale->global_discount > 0)
                        <p><strong>Global Discount:</strong> Rp {{ number_format($sale->global_discount, 0, ',', '.') }}
                        </p>
                    @endif
                    @if ($sale->discount_cashback > 0)
                        <p><strong>Cashback:</strong> {{ $sale->discount_cashback }}%</p>
                    @endif
                    @if ($sale->delivery_method == 'DEL-02' && $sale->delivery_cost > 0)
                        <p><strong>Delivery Cost:</strong> Rp {{ number_format($sale->delivery_cost, 0, ',', '.') }}</p>
                    @endif
                    <p><strong>Total Price:</strong> Rp {{ number_format($sale->total_price, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Delivery Information Card (Only for DEL-02) -->
    @if ($sale->delivery_method == 'DEL-02')
        <div class="card mb-4">
            <div class="card-header bg-info">
                <h5>Delivery Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Recipient Name:</strong> {{ $sale->recipient_name }}</p>
                        <p><strong>Delivery Address:</strong> {{ $sale->customer_address }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Delivery Cost:</strong> Rp {{ number_format($sale->delivery_cost ?? 0, 0, ',', '.') }}
                        </p>
                        <p><strong>Status:</strong>
                            <span class="badge {{ $sale->shipped_date ? 'bg-success' : 'bg-warning' }}">
                                {{ $sale->shipped_date ? 'Shipped' : 'Pending Shipment' }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Shipping Confirmation Card (Only for DEL-02 and not shipped yet) -->
    @if ($sale->delivery_method == 'DEL-02' && !$sale->shipped_date)
        <div class="card mb-4">
            <div class="card-header bg-warning">
                <h5>Confirm Shipment</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Note:</strong> Confirming shipment will reduce the product quantities from inventory.
                </div>
                <form action="{{ route('sales.ship', $sale->id) }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="shipped_date">Shipped Date</label>
                                <input type="datetime-local" class="form-control" id="shipped_date" name="shipped_date"
                                    required value="{{ old('shipped_date', now()->format('Y-m-d\TH:i')) }}">
                            </div>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-shipping-fast"></i> Confirm Shipment
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Sales Details Table -->
    <div class="card">
        <div class="card-header">
            <h5>Sold Items</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        @if ($sale->saleDetails->where('discount_value', '>', 0)->count() > 0)
                            <th>Discount</th>
                        @endif
                        <th>Subtotal</th>
                        <th>COGS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sale->saleDetails as $detail)
                        <tr>
                            <td>{{ $detail->product->product_name }}</td>
                            <td>{{ $detail->quantity }}</td>
                            <td>Rp
                                {{ number_format(($detail->subtotal + $detail->discount_value) / $detail->quantity, 0, ',', '.') }}
                            </td>
                            @if ($sale->saleDetails->where('discount_value', '>', 0)->count() > 0)
                                <td>
                                    @if ($detail->discount_value > 0)
                                        Rp {{ number_format($detail->discount_value, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                            @endif
                            <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($detail->cogs_sale ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $sale->saleDetails->where('discount_value', '>', 0)->count() > 0 ? '6' : '5' }}"
                                class="text-center">
                                No items found for this sale
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="{{ $sale->saleDetails->where('discount_value', '>', 0)->count() > 0 ? '4' : '3' }}"
                            class="text-right">
                            Items Subtotal:
                        </th>
                        <th>Rp {{ number_format($sale->saleDetails->sum('subtotal'), 0, ',', '.') }}</th>
                        <th>Rp {{ number_format($sale->saleDetails->sum('cogs_sale'), 0, ',', '.') }}</th>
                    </tr>
                    @if ($sale->global_discount > 0)
                        <tr>
                            <th colspan="{{ $sale->saleDetails->where('discount_value', '>', 0)->count() > 0 ? '4' : '3' }}"
                                class="text-right">
                                Global Discount:
                            </th>
                            <th>- Rp {{ number_format($sale->global_discount, 0, ',', '.') }}</th>
                            <th>-</th>
                        </tr>
                    @endif
                    @if ($sale->delivery_method == 'DEL-02' && $sale->delivery_cost > 0)
                        <tr>
                            <th colspan="{{ $sale->saleDetails->where('discount_value', '>', 0)->count() > 0 ? '4' : '3' }}"
                                class="text-right">
                                Delivery Cost:
                            </th>
                            <th>Rp {{ number_format($sale->delivery_cost, 0, ',', '.') }}</th>
                            <th>-</th>
                        </tr>
                    @endif
                    <tr class="table-success">
                        <th colspan="{{ $sale->saleDetails->where('discount_value', '>', 0)->count() > 0 ? '4' : '3' }}"
                            class="text-right">
                            Grand Total:
                        </th>
                        <th>Rp {{ number_format($sale->total_price, 0, ',', '.') }}</th>
                        <th>Rp {{ number_format($sale->saleDetails->sum('cogs_sale'), 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="card mt-4">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <a href="{{ route('sales.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Sales List
                </a>
                <div>
                    @if ($sale->delivery_method == 'DEL-02' && $sale->shipped_date)
                        <span class="badge bg-success fs-6">
                            <i class="fas fa-check-circle"></i> Order Shipped
                        </span>
                    @elseif($sale->delivery_method == 'DEL-01')
                        <span class="badge bg-info fs-6">
                            <i class="fas fa-store"></i> Pickup Order
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
