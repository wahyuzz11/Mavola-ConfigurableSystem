@extends('layouts.index')

@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">
            Product Batches for {{ $product->product_name }}
        </h3>
    </div>

    <!-- Product Information Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <p><strong>Product Name:</strong> {{ $product->product_name }}</p>
                    <p><strong>Total Stock:</strong> {{ $product->total_stock }} {{ $product->unit_name }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Expiry Date Settings:</strong> {{ $product->expired_date_settings }} days</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Cost:</strong> Rp {{ number_format($product->cost, 0, ',', '.') }}</p>
                    <p><strong>Price:</strong> Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Batches Table -->
    <div class="card">
        <div class="card-header">
            <h5>Batch Details</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>Serial Code</th>
                        <th>Stock</th>
                        <th>Cost per Batch</th>
                        <th>Purchase Date</th>
                        <th>Expired Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($batches as $batch)
                        @if($batch->empty_status == 0)
                            <tr>
                                <td>{{ $batch->serial_code }}</td>
                                <td>{{ $batch->stock }} {{ $product->unit_name }}</td>
                                <td>Rp {{ number_format($batch->cost_per_batch, 0, ',', '.') }}</td>
                                <td>{{ \Carbon\Carbon::parse($batch->purchase_date)->format('d M Y') }}</td>
                                <td @if($batch->expired_date < now()) style="color: red;" @endif>
                                    {{ \Carbon\Carbon::parse($batch->expired_date)->format('d M Y') }}
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No batches found for this product</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection