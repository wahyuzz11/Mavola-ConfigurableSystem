@extends('layouts.index')

@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">Laporan Penjualan</h3>
        <div class="ms-md-auto py-2 py-md-0">
            <a href="{{ route('sales.create') }}" class="btn btn-primary btn-round">Buat Pesanan Penjualan</a>
        </div>
    </div>
    <br>
    <br>
    <!-- Completed Sales -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Penjualan Selesai</h4>
            <div class="text-muted">
                <small>Total: {{ $salesCount }} pesanan | Rp
                    {{ number_format($totalSalesTransaction, 0, ',', '.') }}</small>
            </div>
        </div>
        <div class="card-body">
            @if ($sales->count() > 0)
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Tanggal Penjualan</th>
                            <th>Pelanggan</th>
                            <th>Nominal Transaksi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sales as $sale)
                            <tr>
                                <td>{{ $sale->invoice_number }}</td>
                                <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d M Y') }}</td>
                                <td>{{ $sale->customer->name ?? 'Tidak ada' }}</td>
                                <td>Rp {{ number_format($sale->total_price, 0, ',', '.') }}</td>
                                {{-- <td><span class="badge bg-success">{{ $sale->status }}</span></td> --}}
                                {{-- <th>
                                    Rp {{ number_format($sale->sale_details_sum_cogs_sale, 0, ',', '.') }}
                                </th>                              --}}
                                <td>
                                    <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-info btn-sm">Lihat</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <!-- Pagination for Completed -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        <small>Menampilkan {{ $sales->firstItem() ?? 0 }} sampai {{ $sales->lastItem() ?? 0 }}
                            dari {{ $salesCount }} hasil</small>
                    </div>
                    <div>
                        {{ $sales->links() }}
                    </div>
                </div>
            @else
                <p class="text-muted">Tidak ada transaksi penjualan</p>
            @endif
        </div>
    </div>

@endsection
