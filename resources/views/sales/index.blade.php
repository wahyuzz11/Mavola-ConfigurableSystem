@extends('layouts.index')

@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">Sales</h3>
        <div class="ms-md-auto py-2 py-md-0">
            <a href="{{ route('sales.create') }}" class="btn btn-primary btn-round">Create Sales Order</a>
        </div>
    </div>


    <!-- In Delivery Sales -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">In Delivery Sales</h4>
            <div class="text-muted">
                <small>Total: {{ $inDeliveryCount }} orders | Rp
                    {{ number_format($inDeliveryTotalAmount, 0, ',', '.') }}</small>
            </div>
        </div>
        <div class="card-body">
            @if ($inDeliverySales->count() > 0)
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Sale Date</th>
                            <th>Customer</th>
                            <th>Recipient</th>
                            <th>Delivery Address</th>
                            <th>Total Amount</th>
                            <th>Shipped Date</th>
                            <th>Status</th>
                            @if ($inventory_tracking == 'perpetual')
                                <th>
                                    COGS/HPP
                                </th>
                                <th>
                                    COGS Method
                                </th>
                            @endif

                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($inDeliverySales as $sale)
                            <tr>
                                <td>{{ $sale->invoice_number }}</td>
                                <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d M Y') }}</td>
                                <td>{{ $sale->customer->name ?? 'N/A' }}</td>
                                <td>{{ $sale->recipient_name }}</td>
                                <td>{{ $sale->customer_address }}</td>
                                <td>Rp {{ number_format($sale->total_price, 0, ',', '.') }}</td>
                                <td>{{ $sale->shipped_date ? \Carbon\Carbon::parse($sale->shipped_date)->format('d M Y') : 'Not shipped' }}
                                </td>
                                <td><span class="badge bg-primary">{{ $sale->status }}</span></td>
                                @if ($inventory_tracking == 'perpetual')
                                    <th>
                                        Rp {{ number_format($sale->sale_details_sum_cogs_sale, 0, ',', '.') }}
                                    </th>
                                    <th>
                                        {{ $sale->cogs_method }}
                                    </th>
                                @endif
                                <td>
                                    <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-info btn-sm">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <!-- Pagination for In Delivery -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        <small>Showing {{ $inDeliverySales->firstItem() ?? 0 }} to {{ $inDeliverySales->lastItem() ?? 0 }}
                            of {{ $inDeliveryCount }} results</small>
                    </div>
                    <div>
                        {{ $inDeliverySales->links() }}
                    </div>
                </div>
            @else
                <p class="text-muted">No sales in delivery found.</p>
            @endif
        </div>
    </div>

    <!-- Completed Sales -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Completed Sales</h4>
            <div class="text-muted">
                <small>Total: {{ $completedCount }} orders | Rp
                    {{ number_format($completedTotalAmount, 0, ',', '.') }}</small>
            </div>
        </div>
        <div class="card-body">
            @if ($completedSales->count() > 0)
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Sale Date</th>
                            <th>Customer</th>
                            <th>Recipient</th>
                            <th>Delivery Address</th>
                            <th>Total Amount</th>
                            <th>Shipped Date</th>
                            <th>Status</th>
                            @if ($inventory_tracking == 'perpetual')
                                <th>
                                    COGS/HPP
                                </th>
                                <th>
                                    COGS Method
                                </th>
                            @endif
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($completedSales as $sale)
                            <tr>
                                <td>{{ $sale->invoice_number }}</td>
                                <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d M Y') }}</td>
                                <td>{{ $sale->customer->name ?? 'N/A' }}</td>
                                <td>{{ $sale->recipient_name }}</td>
                                <td>{{ $sale->customer_address }}</td>
                                <td>Rp {{ number_format($sale->total_price, 0, ',', '.') }}</td>
                                <td>{{ $sale->shipped_date ? \Carbon\Carbon::parse($sale->shipped_date)->format('d M Y') : 'Not shipped' }}
                                </td>
                                <td><span class="badge bg-success">{{ $sale->status }}</span></td>
                                @if ($inventory_tracking == 'perpetual')
                                    <th>
                                        Rp {{ number_format($sale->sale_details_sum_cogs_sale, 0, ',', '.') }}
                                    </th>
                                    <th>
                                        {{ $sale->cogs_method }}
                                    </th>
                                @endif
                                <td>
                                    <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-info btn-sm">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <!-- Pagination for Completed -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        <small>Showing {{ $completedSales->firstItem() ?? 0 }} to {{ $completedSales->lastItem() ?? 0 }}
                            of {{ $completedCount }} results</small>
                    </div>
                    <div>
                        {{ $completedSales->links() }}
                    </div>
                </div>
            @else
                <p class="text-muted">No completed sales found.</p>
            @endif
        </div>
    </div>

@endsection
