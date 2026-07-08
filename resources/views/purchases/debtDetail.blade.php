@extends('layouts.index')

@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">
            Detail Utang - Invoice #{{ $debtHistory->purchase->invoice_number }}
        </h3>
    </div>

    <!-- Debt Information Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <p><strong>Status Utang:</strong>
                        <span
                            class="badge bg-{{ $debtHistory->status == 'paid' ? 'success' : ($debtHistory->status == 'late' ? 'danger' : 'warning') }}">
                            {{ ucfirst($debtHistory->status) }}
                        </span>
                    </p>
                    <p><strong>Supplier:</strong> {{ $debtHistory->supplier->company_name }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Tanggal Tagihan:</strong>
                        {{ \Carbon\Carbon::parse($debtHistory->bill_date)->format('d M Y') }}</p>
                    <p><strong>Tanggal Jatuh Tempo:</strong>
                        <span @if ($debtHistory->due_date < now()) class="text-danger" @endif>
                            {{ \Carbon\Carbon::parse($debtHistory->due_date)->format('d M Y') }}
                        </span>
                    </p>
                </div>
                <div class="col-md-4">
                    <p><strong>Jumlah Utang:</strong> Rp {{ number_format($debtHistory->debt_nominal, 0, ',', '.') }}</p>
                    @if ($debtHistory->status == 'paid')
                        <p><strong>Tanggal Dibayar:</strong>
                            {{ \Carbon\Carbon::parse($debtHistory->updated_at)->format('d M Y') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase Information Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Informasi Pembelian</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <p><strong>Nomor Invoice:</strong> {{ $debtHistory->purchase->invoice_number }}</p>
                    <p><strong>Tanggal Pembelian:</strong>
                        {{ \Carbon\Carbon::parse($debtHistory->purchase->purchase_date)->format('d M Y') }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Total Harga:</strong> Rp
                        {{ number_format($debtHistory->purchase->total_price, 0, ',', '.') }}</p>
                    @if ($debtHistory->purchase->receive_method == 'RE-02')
                        <p><strong>Biaya Pengiriman:</strong> Rp
                            {{ number_format($debtHistory->purchase->delivery_cost, 0, ',', '.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase Details Table -->
    <div class="card">
        <div class="card-header">
            <h5>Barang yang Dibeli</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>Nama Produk</th>
                        <th>Jumlah</th>
                        <th>Harga Satuan</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($debtHistory->purchase->purchaseDetails as $detail)
                        <tr>
                            <td>{{ $detail->product->product_name }}</td>
                            <td>{{ $detail->quantity }}</td>
                            <td>Rp {{ number_format(($detail->subtotal/$detail->quantity), 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-right">Total Keseluruhan:</th>
                        <th>Rp {{ number_format($debtHistory->purchase->total_price, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="mt-4">
        @if ($debtHistory->status == 'pending')
            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Metode Pembayaran</label>
                    <br>
                    <select name="paymentMethod" id="payment_method" class="form-select" required>
                        <option value="Tunai">Tunai</option>
                        <option value="Transfer">Transfer Bank</option>
                    </select>
                </div>
                <br><br>
            </div>

            <button type="button" class="btn btn-success" onclick="markAsPaid({{ $debtHistory->id }})">
                <i class="fas fa-check-circle"></i> Tandai Lunas
            </button>
        @endif
        <br>
        <a href="{{ route('debts.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar
        </a>
    </div>
@endsection

@section('javascript')
    <script>
        function markAsPaid(debtId) {
            const paymentMethod = document.getElementById('payment_method').value;

            if (!paymentMethod) {
                alert('Silakan pilih metode pembayaran.');
                return;
            }

            fetch(`/debts/${debtId}/mark-paid`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        paymentMethod: paymentMethod
                    }),
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.href = data.redirect;
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(() => alert('Terjadi kesalahan yang tidak terduga.'));
        }
    </script>
@endsection
