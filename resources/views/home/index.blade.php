@extends('layouts.index')

@section('content')
    <div class="container-fluid py-4 px-4">

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="fw-bold text-dark mb-0">Dashboard</h2>
                <p class="text-muted small mb-0">Ringkasan aktivitas bisnis Anda</p>
            </div>
            <div class="text-muted small d-none d-md-block">
                <i class="bi bi-calendar3 me-1"></i>{{ now()->translatedFormat('l, d F Y') }}
            </div>
        </div>

        {{-- ===================== SUMMARY CARDS ===================== --}}
        <div class="row g-3 mb-4">

            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success"
                            style="width:48px;height:48px;">
                            <i class="bi bi-graph-up-arrow fs-4"></i>
                        </div>
                        <div>
                            <p class="text-uppercase text-muted small fw-semibold mb-1"
                                style="font-size:.7rem;letter-spacing:.03em;">
                                Total Penjualan
                            </p>
                            <h5 class="fw-bold text-dark mb-0">Rp {{ number_format($totalSales, 0, ',', '.') }}</h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger"
                            style="width:48px;height:48px;">
                            <i class="bi bi-cart-dash fs-4"></i>
                        </div>
                        <div>
                            <p class="text-uppercase text-muted small fw-semibold mb-1"
                                style="font-size:.7rem;letter-spacing:.03em;">
                                Total Pembelian
                            </p>
                            <h5 class="fw-bold text-dark mb-0">Rp {{ number_format($totalPurchases, 0, ',', '.') }}</h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary"
                            style="width:48px;height:48px;">
                            <i class="bi bi-receipt fs-4"></i>
                        </div>
                        <div>
                            <p class="text-uppercase text-muted small fw-semibold mb-1"
                                style="font-size:.7rem;letter-spacing:.03em;">
                                Transaksi Penjualan
                            </p>
                            <h5 class="fw-bold text-dark mb-0">{{ $totalSalesTransactions }}</h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center"
                            style="width:48px;height:48px;background-color:rgba(111,66,193,.1);color:#6f42c1;">
                            <i class="bi bi-bag-check fs-4"></i>
                        </div>
                        <div>
                            <p class="text-uppercase text-muted small fw-semibold mb-1"
                                style="font-size:.7rem;letter-spacing:.03em;">
                                Transaksi Pembelian
                            </p>
                            <h5 class="fw-bold text-dark mb-0">{{ $totalPurchaseTransactions }}</h5>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        @if ($expirySettings == 'tanggal kadaluarsa')
            <div class="card border-0 shadow-sm mb-4">
                <div
                    class="card-header bg-warning bg-opacity-10 border-bottom border-warning-subtle d-flex align-items-center justify-content-between py-3">
                    <h6 class="fw-semibold text-warning-emphasis mb-0">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Pengingat Batch Kadaluarsa
                    </h6>
                    <span class="badge rounded-pill text-bg-warning">{{ $expiringBatchCount }} batch</span>
                </div>

                <div class="card-body">
                    @if ($expiringBatchCount > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr class="text-muted small text-uppercase">
                                        <th style="font-size:.72rem;letter-spacing:.03em;">Kode Batch</th>
                                        <th style="font-size:.72rem;letter-spacing:.03em;">Tanggal Kadaluarsa</th>
                                        <th style="font-size:.72rem;letter-spacing:.03em;">Sisa Hari</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($expiringSoonBatch as $batch)
                                        @php
                                            $expiredDate = \Carbon\Carbon::parse($batch->expired_date);
                                            $daysLeft = now()->copy()->startOfDay()->diffInDays($expiredDate, false);
                                        @endphp
                                        <tr>
                                            <td class="fw-semibold text-dark">{{ $batch->serial_code }}</td>
                                            <td class="text-muted">{{ $expiredDate->format('d M Y') }}</td>
                                            <td>
                                                <span
                                                    class="badge rounded-pill text-dark {{ $daysLeft <= 2 ? 'bg-danger' : 'bg-warning' }}">
                                                    {{ $daysLeft }} hari lagi
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-check-circle fs-3 d-block mb-2 text-success"></i>
                            Tidak ada batch yang mendekati tanggal kadaluarsa.
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- ===================== PERHITUNGAN LABA ===================== --}}
        <div class="card border-0 shadow-sm">
            <div
                class="card-header bg-white d-flex flex-column flex-sm-row align-items-sm-center justify-content-sm-between gap-3 py-3">
                <h6 class="fw-semibold text-dark mb-0">
                    <i class="bi bi-cash-coin me-2 text-primary"></i>Perhitungan Laba
                </h6>

                <form method="GET" action="{{ route('home.index') }}" class="d-flex flex-wrap align-items-center gap-2">
                    <select name="period" onchange="this.form.submit()" class="form-select form-select-sm">
                        <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Hari Ini</option>
                        <option value="week" {{ $period === 'week' ? 'selected' : '' }}>Minggu Ini (Min–Sab)</option>
                        <option value="month" {{ $period === 'month' ? 'selected' : '' }}>Bulan Ini</option>
                        <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Custom</option>
                    </select>

                    <div id="customDateFields"
                        class="d-flex align-items-center gap-2 {{ $period === 'custom' ? '' : 'd-none' }}">
                        <input type="date" name="start_date" value="{{ request('start_date', $start->toDateString()) }}"
                            class="form-control form-control-sm">
                        <span class="text-muted small">s/d</span>
                        <input type="date" name="end_date" value="{{ request('end_date', $end->toDateString()) }}"
                            class="form-control form-control-sm">
                        <button type="submit" class="btn btn-primary btn-sm">
                            Terapkan
                        </button>
                    </div>
                </form>
            </div>

            <div class="px-3 pt-3">
                <p class="text-muted small mb-0">
                    <i class="bi bi-calendar-range me-1"></i>
                    Periode: {{ $start->format('d M Y') }} &ndash; {{ $end->format('d M Y') }}
                </p>
            </div>

            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-sm-4">
                        <div class="bg-light rounded-3 p-3 border-start border-4 border-secondary h-100">
                            <p class="text-uppercase text-muted small fw-semibold mb-1"
                                style="font-size:.7rem;letter-spacing:.03em;">
                                Revenue
                            </p>
                            <h5 class="fw-bold text-dark mb-0">Rp {{ number_format($revenue, 0, ',', '.') }}</h5>
                        </div>
                    </div>
                    <div class="col-12 col-sm-4">
                        <div class="bg-light rounded-3 p-3 border-start border-4 border-secondary h-100">
                            <p class="text-uppercase text-muted small fw-semibold mb-1"
                                style="font-size:.7rem;letter-spacing:.03em;">
                                HPP
                            </p>
                            <h5 class="fw-bold mb-0"> Rp {{ number_format($cogs, 0, ',', '.') }}</h5>
                        </div>
                    </div>
                    <div class="col-12 col-sm-4">
                        <div class="bg-success bg-opacity-10 rounded-3 p-3 border-start border-4 border-success h-100">
                            <p class="text-uppercase fw-semibold mb-1"
                                style="font-size:.7rem;letter-spacing:.03em;color:#000;">
                                Laba Kotor
                            </p>
                            <h5 class="fw-bold mb-0" style="color:#000;">
                                Rp {{ number_format($grossProfit, 0, ',', '.') }}
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        document.querySelector('select[name="period"]').addEventListener('change', function() {
            const customFields = document.getElementById('customDateFields');
            customFields.classList.toggle('d-none', this.value !== 'custom');
        });
    </script>
@endsection
