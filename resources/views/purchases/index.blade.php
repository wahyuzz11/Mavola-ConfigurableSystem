@extends('layouts.index')

@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">Laporan Pembelian</h3>
        <div class="ms-md-auto py-2 py-md-0">
            <a href="{{ route('purchases.create') }}" class="btn btn-primary btn-round">Tambah Pembelian Baru</a>
        </div>
    </div>
    <br>
    <br>
    <!-- Completed Purchases -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Transaksi Pembelian</h4>
            <div class="text-muted">
                <small>Total: {{ $purchasesCount }} orders | Rp
                    {{ number_format($totalPurchasesTransaction, 0, ',', '.') }}</small>
            </div>
        </div>
        <div class="card-body">
            @if ($purchases->count() > 0)
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Purchase Date</th>
                            <th>Supplier</th>
                            <th>Total Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($purchases as $p)
                            <tr>
                                <td>{{ $p->invoice_number }}</td>
                                <td>{{ \Carbon\Carbon::parse($p->purchase_date)->format('d M Y') }}</td>
                                <td>{{ $p->supplier->company_name }}</td>
                                <td>Rp {{ number_format($p->total_price, 0, ',', '.') }}</td>
                                {{-- <td><span class="badge bg-success">{{ $purchase->status }}</span></td> --}}
                                <td>
                                    <a href="{{ route('purchases.show', $p->id) }}" class="btn btn-info btn-sm">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <!-- Pagination for Completed -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        <small>{{ $purchases->firstItem() ?? 0 }} sampai
                            {{ $purchases->lastItem() ?? 0 }} dari total {{ $purchasesCount }} pembelian</small>
                    </div>
                    <div>
                        {{ $purchases->links() }}
                    </div>
                </div>
            @else
                <p class="text-muted">Tidak ditemukan transaksi pembelian.</p>
            @endif
        </div>
    </div>

@endsection
