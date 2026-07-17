@extends('layouts.index')

@section('content')
    {{-- ── Header ──────────────────────────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-semibold mb-0">
                <i class="bi bi-journal-text me-2 text-muted"></i>Jurnal Akuntansi
            </h5>
            <p class="text-muted mb-0" style="font-size:13px">Daftar semua entri jurnal</p>
        </div>
        <a href="{{ route('accounting.create') }}" class="btn btn-dark btn-sm d-inline-flex align-items-center gap-2">
            <i class="bi bi-plus-lg"></i> Buat Jurnal
        </a>
    </div>

    {{-- ── Metric cards ─────────────────────────────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 bg-light h-100">
                <div class="card-body py-3">
                    <div class="text-muted mb-1" style="font-size:12px">
                        <i class="bi bi-journal-text me-1"></i> Total Jurnal
                    </div>
                    <div class="fw-semibold fs-5">{{ $journals->total() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 bg-light h-100">
                <div class="card-body py-3">
                    <div class="text-muted mb-1" style="font-size:12px">
                        <i class="bi bi-arrow-up-circle me-1"></i> Total Debit
                    </div>
                    <div class="fw-semibold fs-5 text-danger">
                        Rp
                        {{ number_format($journals->sum(fn($j) => $j->journalEntriesDetails->sum(fn($d) => $d->debit ?? 0)), 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 bg-light h-100">
                <div class="card-body py-3">
                    <div class="text-muted mb-1" style="font-size:12px">
                        <i class="bi bi-cash-stack me-1"></i> Total Kredit
                    </div>
                    <div class="fw-semibold fs-5 text-success">
                        Rp
                        {{ number_format($journals->sum(fn($j) => $j->journalEntriesDetails->sum(fn($d) => $d->credit ?? 0)), 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Tabel jurnal ─────────────────────────────────────────────────── --}}
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px">#</th>
                        <th style="width:130px">Tanggal</th>
                        <th style="width:120px">No. Ref</th>
                        <th>Deskripsi</th>
                        <th style="width:80px" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($journals as $j)
                        <tr>
                            <td class="text-muted small align-middle">{{ $loop->iteration }}</td>
                            <td class="align-middle" style="font-size:13px">
                                {{ $j->created_at ? $j->created_at->format('d M Y') : '—' }}
                            </td>
                            <td class="align-middle">
                                @if ($j->ref_code)
                                    <span class="badge bg-secondary fw-normal">{{ $j->ref_code }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="align-middle" style="font-size:13px">
                                {{ Str::limit($j->description, 80) }}
                            </td>
                            <td class="text-center align-middle">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="modal"
                                    data-bs-target="#detailModal{{ $j->id }}" title="Lihat detail">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="bi bi-journal-x fs-3 d-block mb-2"></i>
                                Belum ada jurnal. <a href="{{ route('accounting.create') }}">Buat jurnal pertama</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($journals->hasPages())
            <div class="card-footer bg-white d-flex justify-content-end py-2">
                {{ $journals->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    {{-- ── Detail Modal (dirender langsung per baris, tanpa AJAX/JS) ─────── --}}
    @foreach ($journals as $j)
        @php
            $totalDebit = $j->journalEntriesDetails->sum(fn($d) => $d->debit ?? 0);
            $totalCredit = $j->journalEntriesDetails->sum(fn($d) => $d->credit ?? 0);
            $typeBadges = [
                'Asset' => 'bg-primary',
                'Liability' => 'bg-warning text-dark',
                'Equity' => 'bg-info text-dark',
                'Revenue' => 'bg-success',
                'Expense' => 'bg-danger',
            ];
        @endphp
        <div class="modal fade" id="detailModal{{ $j->id }}" tabindex="-1"
            aria-labelledby="detailModalLabel{{ $j->id }}" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">

                    {{-- Header modal --}}
                    <div class="modal-header border-bottom pb-3">
                        <div>
                            <h6 class="modal-title fw-semibold mb-0" id="detailModalLabel{{ $j->id }}">
                                {{ $j->description ?: 'Jurnal #' . $j->id }}
                            </h6>
                            <p class="text-muted mb-0" style="font-size:12px">Entri akun debit dan kredit</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    {{-- Meta info --}}
                    <div class="px-4 py-3 border-bottom bg-light d-flex flex-wrap gap-4" style="font-size:13px">
                        <div>
                            <div class="text-muted mb-1"
                                style="font-size:11px; text-transform:uppercase; letter-spacing:.5px">Tanggal</div>
                            <div class="fw-medium">
                                {{ $j->created_at ? $j->created_at->format('d M Y') : '—' }}
                            </div>
                        </div>
                        <div>
                            <div class="text-muted mb-1"
                                style="font-size:11px; text-transform:uppercase; letter-spacing:.5px">No. Referensi
                            </div>
                            <div>
                                @if ($j->ref_code)
                                    <span class="badge bg-secondary fw-normal">{{ $j->ref_code }}</span>
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                        <div>
                            <div class="text-muted mb-1"
                                style="font-size:11px; text-transform:uppercase; letter-spacing:.5px">Total Debit</div>
                            <div class="fw-medium text-danger">Rp {{ number_format($totalDebit, 0, ',', '.') }}</div>
                        </div>
                    </div>

                    {{-- Body: tabel akun --}}
                    <div class="modal-body p-0">
                        <table class="table mb-0">
                            <thead class="table-light" style="font-size:12px">
                                <tr>
                                    <th>Nama Akun</th>
                                    <th style="width:110px">Tipe</th>
                                    <th style="width:130px" class="text-end">Debit (Rp)</th>
                                    <th style="width:130px" class="text-end">Kredit (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($j->journalEntriesDetails as $e)
                                    <tr style="font-size:13px">
                                        <td class="align-middle">{{ $e->account->name ?? '—' }}</td>
                                        <td class="align-middle">
                                            <span
                                                class="badge {{ $typeBadges[$e->account->type ?? ''] ?? 'bg-secondary' }} fw-normal"
                                                style="font-size:11px">
                                                {{ $e->account->type ?? '—' }}
                                            </span>
                                        </td>
                                        <td class="text-end align-middle">
                                            @if (($e->debit ?? 0) > 0)
                                                Rp {{ number_format($e->debit, 0, ',', '.') }}
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-end align-middle">
                                            @if (($e->credit ?? 0) > 0)
                                                Rp {{ number_format($e->credit, 0, ',', '.') }}
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">Tidak ada entri.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light fw-semibold" style="font-size:13px">
                                <tr>
                                    <td colspan="2" class="text-end">Total</td>
                                    <td class="text-end">Rp {{ number_format($totalDebit, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($totalCredit, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Footer modal --}}
                    <div class="modal-footer border-top py-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg me-1"></i>Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection
