@extends('layouts.index')

@section('content')

    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <h3 class="fw-bold mb-0">
            <i class="fas fa-file-invoice text-primary me-2"></i>
            Detail Pembelian <span class="text-muted">- Faktur #{{ $purchase->invoice_number }}</span>
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

    <!-- Kartu Informasi Pembelian -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-info-circle text-primary me-2"></i>Informasi Pembelian</h5>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-4">
                    <p class="mb-0"><strong>Nomor Faktur:</strong><br>{{ $purchase->invoice_number }}</p>
                </div>
                <div class="col-md-4">
                    <p class="mb-2"><strong>Pemasok:</strong><br>{{ $supplier->company_name }}</p>
                    <p class="mb-0"><strong>Tanggal Pembelian:</strong><br>
                        {{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d M Y H:i') }}</p>
                </div>
                <div class="col-md-4">
                    <p class="mb-0 fs-5"><strong>Total Harga:</strong><br>
                        <span class="text-success fw-bold">Rp
                            {{ number_format($purchase->total_price, 0, ',', '.') }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Detail Pembelian -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-boxes text-primary me-2"></i>Barang Dibeli</h5>
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
                        @forelse ($purchaseDetails as $detail)
                            <tr>
                                <td>{{ $detail->product->product_name }}</td>
                                <td class="text-center">{{ $detail->quantity }}</td>
                                <td class="text-end">Rp {{ number_format($detail->subtotal/$detail->quantity, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">
                                    Tidak ada barang untuk pembelian ini
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="table-success">
                            <th colspan="3" class="text-end">Total Keseluruhan:</th>
                            <th class="text-end">Rp {{ number_format($purchase->total_price, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mt-4">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <a href="{{ route('purchases.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Daftar Pembelian
                </a>
            </div>
        </div>
    </div>
@endsection
