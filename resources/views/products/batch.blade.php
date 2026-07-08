@extends('layouts.index')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <h3 class="fw-bold mb-0">
            <i class="fas fa-layer-group text-primary me-2"></i>
            Batch Produk <span class="text-muted">- {{ $product->product_name }}</span>
        </h3>
    </div>

    <!-- Kartu Informasi Produk -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-box text-primary me-2"></i>Informasi Produk</h5>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-4">
                    <p class="mb-2"><strong>Nama Produk:</strong><br>{{ $product->product_name }}</p>
                    <p class="mb-0"><strong>Total Stok:</strong><br>{{ $product->total_stock }} {{ $product->unit_name }}
                    </p>
                </div>
                @if ($expirySettings == 'tanggal kadaluarsa')
                    <div class="col-md-4">
                        <p class="mb-0"><strong>Status Pengaturan Tanggal </strong><br>
                            @if ($product->expired_date_active == 1)
                                Aktif
                            @else
                                Tidak Aktif
                            @endif
                        </p>
                    </div>
                    @if ($product->expired_date_active == 1)
                        <div class="col-md-4">
                            <p class="mb-0"><strong>Pengaturan Tanggal
                                    Kedaluwarsa:</strong><br>{{ $product->expired_date_setting }} hari</p>
                        </div>
                    @endif
                @endif

                <div class="col-md-4">
                    <p class="mb-0"><strong>Harga Jual:</strong><br>Rp
                        {{ number_format($product->price, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Batch -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-list text-primary me-2"></i>Detail Batch</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle w-100" id="batchesTable">
                    <thead class="table-dark">
                        <tr>
                            <th>Kode Batch</th>
                            <th class="text-center">Stok</th>
                            <th class="text-end">Modal per Batch</th>
                            <th>Tanggal Pembelian</th>
                            @if ($expirySettings == 'tanggal kadaluarsa')
                                <th>Tanggal Kedaluwarsa</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($batches as $batch)
                            @if ($batch->empty_status == 0)
                                <tr>
                                    <td>{{ $batch->serial_code }}</td>
                                    <td class="text-center">{{ $batch->stock }} {{ $product->unit_name }}</td>
                                    <td class="text-end">Rp {{ number_format($batch->cost_per_batch, 0, ',', '.') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($batch->purchase_date)->format('d M Y') }}</td>
                                    @if ($expirySettings == 'tanggal kadaluarsa')
                                        <td>
                                            @if ($product->expired_date_active == 1 && $batch->expired_date == null)
                                                <form action="{{ route('productBatches.updateExpiredDate', $batch->id) }}"
                                                    method="POST" class="d-flex gap-1">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="date" name="expired_date"
                                                        class="form-control form-control-sm"
                                                        min="{{ \Carbon\Carbon::parse($batch->purchase_date)->format('Y-m-d') }}"
                                                        required>
                                                    <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                                                </form>
                                            @elseif ($batch->expired_date)
                                                {{ \Carbon\Carbon::parse($batch->expired_date)->format('d M Y') }}
                                            @else
                                                Aktifkan tanggal kadaluarsa pada produk {{ $product->product_name }}
                                                jika ingin menambah tanggal kadaluarsa pada batch ini
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">
                                    Tidak ada batch untuk produk ini
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
