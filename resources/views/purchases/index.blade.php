@extends('layouts.index')

@section('content')
<div class="page-header">
    <h3 class="fw-bold mb-3">Purchases</h3>
    <div class="ms-md-auto py-2 py-md-0">
        <a href="{{ route('purchases.create') }}" class="btn btn-primary btn-round">Create Purchase Order</a>
    </div>
</div>


<!-- In Delivery Purchases -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">In Delivery Purchases</h4>
        <div class="text-muted">
            <small>Total: {{ $inDeliveryCount }} orders | Rp {{ number_format($inDeliveryTotalAmount, 0, ',', '.') }}</small>
        </div>
    </div>
    <div class="card-body">
        @if($inDeliveryPurchases->count() > 0)
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Purchase Date</th>
                        <th>Supplier</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($inDeliveryPurchases as $purchase)
                    <tr>
                        <td>{{ $purchase->invoice_number }}</td>
                        <td>{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d M Y') }}</td>
                        <td>{{ $purchase->supplier->company_name }}</td>
                        <td>Rp {{ number_format($purchase->total_price, 0, ',', '.') }}</td>
                        <td><span class="badge bg-primary">{{ $purchase->status }}</span></td>
                        <td>
                            <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-info btn-sm">View</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <!-- Pagination for In Delivery -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    <small>Showing {{ $inDeliveryPurchases->firstItem() ?? 0 }} to {{ $inDeliveryPurchases->lastItem() ?? 0 }} of {{ $inDeliveryCount }} results</small>
                </div>
                <div>
                    {{ $inDeliveryPurchases->links() }}
                </div>
            </div>
        @else
            <p class="text-muted">No purchases in delivery found.</p>
        @endif
    </div>
</div>

<!-- Completed Purchases -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">Completed Purchases</h4>
        <div class="text-muted">
            <small>Total: {{ $completedCount }} orders | Rp {{ number_format($completedTotalAmount, 0, ',', '.') }}</small>
        </div>
    </div>
    <div class="card-body">
        @if($completedPurchases->count() > 0)
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Purchase Date</th>
                        <th>Supplier</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($completedPurchases as $purchase)
                    <tr>
                        <td>{{ $purchase->invoice_number }}</td>
                        <td>{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d M Y') }}</td>
                        <td>{{ $purchase->supplier->company_name }}</td>
                        <td>Rp {{ number_format($purchase->total_price, 0, ',', '.') }}</td>
                        <td><span class="badge bg-success">{{ $purchase->status }}</span></td>
                        <td>
                            <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-info btn-sm">View</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <!-- Pagination for Completed -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    <small>Showing {{ $completedPurchases->firstItem() ?? 0 }} to {{ $completedPurchases->lastItem() ?? 0 }} of {{ $completedCount }} results</small>
                </div>
                <div>
                    {{ $completedPurchases->links() }}
                </div>
            </div>
        @else
            <p class="text-muted">No completed purchases found.</p>
        @endif
    </div>
</div>

@endsection