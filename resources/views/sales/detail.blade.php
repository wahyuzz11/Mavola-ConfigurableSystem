@extends('layouts.index')
 
@section('content')
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <h3 class="fw-bold mb-0">
            <i class="fas fa-file-invoice text-primary me-2"></i>
            Detail Penjualan <span class="text-muted">- Faktur #{{ $sale->invoice_number }}</span>
        </h3>
    </div>
 
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    <script>
                        console.error("Laravel error: {{ $error }}");
                    </script>
                @endforeach
            </ul>
        </div>
    @endif
 
    <!-- Kartu Informasi Penjualan -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-info-circle text-primary me-2"></i>Informasi Penjualan</h5>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-4">
                    <p class="mb-2"><strong>Nomor Faktur:</strong><br>{{ $sale->invoice_number }}</p>
                    <p class="mb-2"><strong>Pelanggan:</strong><br>{{ $sale->customer->name ?? $sale->customer->customer_name }}</p>
                    <p class="mb-0"><strong>Metode Pembayaran:</strong><br>
                        @if ($sale->payment_method == 'S-PAY-01')
                            <span class="badge bg-success">Tunai</span>
                        @elseif ($sale->payment_method == 'S-PAY-02')
                            <span class="badge bg-primary">Transfer</span>
                        @elseif ($sale->payment_method == 'S-PAY-03')
                            <span class="badge bg-info text-dark">QRIS</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-4">
                    <p class="mb-0"><strong>Tanggal Penjualan:</strong><br>
                        {{ \Carbon\Carbon::parse($sale->sale_date)->format('d M Y H:i') }}</p>
                </div>
                <div class="col-md-4">
                    @if ($sale->global_discount > 0)
                        <p class="mb-2"><strong>Diskon Global:</strong><br>
                            Rp {{ number_format($sale->global_discount, 0, ',', '.') }}
                        </p>
                    @endif
                    @if ($sale->discount_cashback > 0)
                        <p class="mb-2"><strong>Cashback:</strong><br>
                            Rp {{ number_format($sale->discount_cashback, 0, ',', '.') }}
                        </p>
                    @endif
                    @if ($sale->total_discount > 0)
                        <p class="mb-2"><strong>Total Diskon:</strong><br>
                            Rp {{ number_format($sale->total_discount, 0, ',', '.') }}
                        </p>
                    @endif
                    <p class="mb-0 fs-5"><strong>Total Transaksi:</strong><br>
                        <span class="text-success fw-bold">Rp {{ number_format($sale->total_price, 0, ',', '.') }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
 
    <!-- Tabel Detail Penjualan -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-boxes text-primary me-2"></i>Barang Terjual</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Nama Produk</th>
                            <th class="text-center">Jumlah</th>
                            <th class="text-end">Harga Satuan</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sale->saleDetails as $detail)
                            <tr>
                                <td>{{ $detail->product->product_name }}</td>
                                <td class="text-center">{{ $detail->quantity }}</td>
                                <td class="text-end">
                                    Rp {{ number_format($detail->subtotal / $detail->quantity, 0, ',', '.') }}
                                </td>
                                <td class="text-end">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">
                                    Tidak ada barang untuk penjualan ini
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="table-success">
                            <th colspan="3" class="text-end">Total Keseluruhan:</th>
                            <th class="text-end">Rp {{ number_format($sale->total_price, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
 
    <!-- Tombol Aksi -->
    <div class="card shadow-sm mt-4">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <a href="{{ route('sales.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Daftar Penjualan
                </a>
            </div>
        </div>
    </div>
@endsection