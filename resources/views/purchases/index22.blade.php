@extends('layouts.index')

@section('link')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">
            Purchases
        </h3>
        <div class="ms-md-auto py-2 py-md-0">
            <a href="{{ route('purchases.create') }}" class="btn btn-primary btn-round">Create Purchase Order</a>
        </div>
    </div>

    <div class="card">
        <table id="basic-datatables" class="display table table-striped table-hover dataTable" role="grid"
            aria-describedby="basic-datatables_info">
            <thead>
                <tr role="row">
                    <th>Invoice</th>
                    <th>Purchase Date</th>
                    <th>Supplier</th>


                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchases as $purchase)
                    <tr>
                        <td>{{ $purchase->invoice_number }}</td>
                        <td>{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d M Y') }}</td>
                        <td>{{ $purchase->supplier->company_name }}</td>
                        <td>Rp {{ number_format($purchase->total_price, 0, ',', '.') }}</td>
                        <td>{{ $purchase->status }}</td>
                        <td>
                            <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-info btn-sm">View</a>
                            @if ($purchase->status == 'Pending')
                                <form action="{{ route('purchases.destroy', $purchase->id) }}" method="POST"
                                    style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>



    </div>
@endsection
