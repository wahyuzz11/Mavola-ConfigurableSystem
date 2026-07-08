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
        {{-- ✅ Ubah ke link ke halaman create --}}
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
        <div class="card-header bg-white d-flex flex-wrap gap-2 align-items-center py-3">
            <div class="input-group input-group-sm" style="max-width: 280px;">
                <span class="input-group-text bg-white border-end-0 text-muted">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" class="form-control border-start-0 ps-0" placeholder="Cari deskripsi atau ref..."
                    id="searchInput" oninput="filterTable()">
            </div>
            <span class="text-muted ms-auto" style="font-size:12px" id="rowCount"></span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0" id="journalTable">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px">#</th>
                        <th style="width:130px">Tanggal</th>
                        <th style="width:120px">No. Ref</th>
                        <th>Deskripsi</th>
                        <th style="width:80px" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="journalBody">
                    @forelse ($journals as $j)
                        <tr data-search="{{ strtolower($j->description . ' ' . $j->ref_code) }}">
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
                                <button class="btn btn-sm btn-outline-secondary" onclick="openModal({{ $j->id }})"
                                    data-bs-toggle="modal" data-bs-target="#detailModal" title="Lihat detail">
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

    {{-- ── Detail Modal ─────────────────────────────────────────────────── --}}
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">

                {{-- Header modal --}}
                <div class="modal-header border-bottom pb-3">
                    <div>
                        <h6 class="modal-title fw-semibold mb-0" id="detailModalLabel">Detail Jurnal</h6>
                        <p class="text-muted mb-0" style="font-size:12px">Entri akun debit dan kredit</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                {{-- Meta info --}}
                <div class="px-4 py-3 border-bottom bg-light d-flex flex-wrap gap-4" style="font-size:13px">
                    <div>
                        <div class="text-muted mb-1" style="font-size:11px; text-transform:uppercase; letter-spacing:.5px">
                            Tanggal</div>
                        <div class="fw-medium" id="mDate">—</div>
                    </div>
                    <div>
                        <div class="text-muted mb-1" style="font-size:11px; text-transform:uppercase; letter-spacing:.5px">
                            No. Referensi</div>
                        <div id="mRef">—</div>
                    </div>
                    <div>
                        <div class="text-muted mb-1"
                            style="font-size:11px; text-transform:uppercase; letter-spacing:.5px">Total Debit</div>
                        <div class="fw-medium text-danger" id="mTotal">—</div>
                    </div>
                </div>

                {{-- Deskripsi --}}
                <div class="px-4 pt-3 pb-2" id="mDescWrap" style="display:none">
                    <p class="text-muted mb-0" style="font-size:13px" id="mDesc"></p>
                </div>

                {{-- Body: tabel akun --}}
                <div class="modal-body p-0">

                    {{-- Loading state --}}
                    <div id="modalLoading" class="text-center py-5">
                        <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
                        <p class="text-muted mt-2 mb-0" style="font-size:13px">Memuat data...</p>
                    </div>

                    {{-- Tabel detail --}}
                    <div id="modalContent" style="display:none">
                        <table class="table mb-0">
                            <thead class="table-light" style="font-size:12px">
                                <tr>
                                    <th>Nama Akun</th>
                                    <th style="width:110px">Tipe</th>
                                    <th style="width:130px" class="text-end">Debit (Rp)</th>
                                    <th style="width:130px" class="text-end">Kredit (Rp)</th>
                                </tr>
                            </thead>
                            <tbody id="mEntries"></tbody>
                            <tfoot id="mFoot" class="table-light fw-semibold" style="font-size:13px"></tfoot>
                        </table>
                    </div>

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
@endsection

@section('javascript')
    <script>
        // ── Filter pencarian ────────────────────────────────────────────────
        function filterTable() {
            const q = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('#journalBody tr[data-search]');
            let visible = 0;

            rows.forEach(row => {
                const match = row.dataset.search.includes(q);
                row.style.display = match ? '' : 'none';
                if (match) visible++;
            });

            document.getElementById('rowCount').textContent =
                q ? `${visible} hasil ditemukan` : '';
        }

        // ── Buka modal detail ───────────────────────────────────────────────
        function openModal(journal_id) {
            // Reset ke loading state
            document.getElementById('modalLoading').style.display = '';
            document.getElementById('modalContent').style.display = 'none';
            document.getElementById('detailModalLabel').textContent = 'Detail Jurnal';
            document.getElementById('mDate').textContent = '—';
            document.getElementById('mRef').innerHTML = '—';
            document.getElementById('mTotal').textContent = '—';
            document.getElementById('mDescWrap').style.display = 'none';
            document.getElementById('mEntries').innerHTML = '';
            document.getElementById('mFoot').innerHTML = '';

            $.ajax({
                type: 'GET',
                url: `/accounting/${journal_id}/journalDetail`,
                success: function(data) {
                    // Isi header
                    document.getElementById('detailModalLabel').textContent =
                        data.description || 'Jurnal #' + journal_id;

                    document.getElementById('mDate').textContent =
                        data.created_at ?? '—';

                    document.getElementById('mRef').innerHTML = data.ref_code ?
                        `<span class="badge bg-secondary fw-normal">${data.ref_code}</span>` :
                        '—';

                    // Hitung total dari details jika backend tidak kirim
                    let totalDebit = 0,
                        totalCredit = 0;
                    let rows = '';

                    data.details.forEach(e => {
                        const debit = parseFloat(e.debit) || 0;
                        const credit = parseFloat(e.credit) || 0;
                        totalDebit += debit;
                        totalCredit += credit;

                        const typeBadge = {
                            'Asset': 'bg-primary',
                            'Liability': 'bg-warning text-dark',
                            'Equity': 'bg-info text-dark',
                            'Revenue': 'bg-success',
                            'Expense': 'bg-danger',
                        } [e.account.type] ?? 'bg-secondary';

                        rows += `
                        <tr style="font-size:13px">
                            <td class="align-middle">${e.account.name}</td>
                            <td class="align-middle">
                                <span class="badge ${typeBadge} fw-normal" style="font-size:11px">
                                    ${e.account.type}
                                </span>
                            </td>
                            <td class="text-end align-middle">
                                ${debit > 0 ? 'Rp ' + debit.toLocaleString('id-ID') : '<span class="text-muted">—</span>'}
                            </td>
                            <td class="text-end align-middle">
                                ${credit > 0 ? 'Rp ' + credit.toLocaleString('id-ID') : '<span class="text-muted">—</span>'}
                            </td>
                        </tr>`;
                    });

                    document.getElementById('mTotal').textContent =
                        'Rp ' + totalDebit.toLocaleString('id-ID');

                    document.getElementById('mEntries').innerHTML = rows;

                    document.getElementById('mFoot').innerHTML = `
                    <tr>
                        <td colspan="2" class="text-end">Total</td>
                        <td class="text-end">Rp ${totalDebit.toLocaleString('id-ID')}</td>
                        <td class="text-end">Rp ${totalCredit.toLocaleString('id-ID')}</td>
                    </tr>`;

                    // Tampilkan tabel
                    document.getElementById('modalLoading').style.display = 'none';
                    document.getElementById('modalContent').style.display = '';

                    // Tombol edit
                    document.getElementById('btnEdit').href =
                        `/journals/${journal_id}/edit`;
                },

                error: function() {
                    document.getElementById('modalLoading').innerHTML =
                        '<p class="text-danger py-4 text-center">Gagal memuat data jurnal.</p>';
                }
            });
        }
    </script>
@endsection
