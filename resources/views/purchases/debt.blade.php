@extends('layouts.index')

@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">
            Riwayat Utang
        </h3>
        <div class="d-flex">
            <span class="badge bg-warning me-2">Belum Lunas: {{ $pendingCount }}</span>
            <span class="badge bg-success me-2">Lunas: {{ $paidCount }}</span>
            <span class="badge bg-danger">Terlambat: {{ $lateCount }}</span>
        </div>
    </div>

    <!-- Pending Debts Card -->
    <div class="card mb-4">
        <div class="card-header bg-warning">
            <h5>Utang Belum Lunas</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Supplier</th>
                        <th>Tanggal Tagihan</th>
                        <th>Tanggal Jatuh Tempo</th>
                        <th>Jumlah</th>
                        <th>Ref. Pembelian</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pendingDebts as $debt)
                        <tr>
                            <td>{{ $debt->supplier->company_name }}</td>
                            <td>{{ \Carbon\Carbon::parse($debt->bill_date)->format('d M Y') }}</td>
                            <td @if ($debt->due_date < now()) class="text-danger" @endif>
                                {{ \Carbon\Carbon::parse($debt->due_date)->format('d M Y') }}
                            </td>
                            <td>Rp {{ number_format($debt->debt_nominal, 0, ',', '.') }}</td>
                            <td>{{ $debt->purchase->invoice_number ?? 'Tidak ada' }}</td>
                            <td>
                                <a href="{{ route('debts.show', $debt->id) }}" class="btn btn-info btn-sm">Lihat</a>
                                {{-- @if ($debt->status == 'pending')
                                    <form action="{{ route('debts.mark-paid', $debt->id) }}" method="POST"
                                        style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">Tandai Lunas</button>
                                    </form>
                                @endif --}}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada utang yang belum lunas</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $pendingDebts->links() }}
        </div>
    </div>

    <!-- Paid Debts Card -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5>Utang Lunas</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Supplier</th>
                        <th>Tanggal Tagihan</th>
                        <th>Tanggal Dibayar</th>
                        <th>Jumlah</th>
                        <th>Ref. Pembelian</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($paidDebts as $debt)
                        <tr>
                            <td>{{ $debt->supplier->company_name }}</td>
                            <td>{{ \Carbon\Carbon::parse($debt->bill_date)->format('d M Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($debt->updated_at)->format('d M Y') }}</td>
                            <td>Rp {{ number_format($debt->debt_nominal, 0, ',', '.') }}</td>
                            <td>{{ $debt->purchase->invoice_number ?? 'Tidak ada' }}</td>
                            <td>
                                <a href="{{ route('debts.show', $debt->id) }}" class="btn btn-info btn-sm">Lihat</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada utang yang sudah lunas</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $paidDebts->links() }}
        </div>
    </div>

    <!-- Late Debts Card -->
    <div class="card">
        <div class="card-header bg-danger text-white">
            <h5>Utang Terlambat</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Supplier</th>
                        <th>Tanggal Tagihan</th>
                        <th>Tanggal Jatuh Tempo</th>
                        <th>Hari Keterlambatan</th>
                        <th>Jumlah</th>
                        <th>Ref. Pembelian</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lateDebts as $debt)
                        @php
                            $daysLate = \Carbon\Carbon::parse($debt->due_date)->diffInDays(now());
                        @endphp
                        <tr>
                            <td>{{ $debt->supplier->company_name }}</td>
                            <td>{{ \Carbon\Carbon::parse($debt->bill_date)->format('d M Y') }}</td>
                            <td class="text-danger">{{ \Carbon\Carbon::parse($debt->due_date)->format('d M Y') }}</td>
                            <td>{{ $daysLate }} hari</td>
                            <td>Rp {{ number_format($debt->debt_nominal, 0, ',', '.') }}</td>
                            <td>{{ $debt->purchase->invoice_number ?? 'Tidak ada' }}</td>
                            <td>
                                <a href="{{ route('debts.show', $debt->id) }}" class="btn btn-info btn-sm">Lihat</a>
                                {{-- <form action="{{ route('debts.mark-paid', $debt->id) }}" method="POST"
                                    style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">Tandai Lunas</button>
                                </form> --}}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada utang yang terlambat</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $lateDebts->links() }}
        </div>
    </div>
@endsection
