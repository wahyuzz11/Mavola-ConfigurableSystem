@extends('layouts.index')

@section('content')
    {{-- ── Header ──────────────────────────────────────────────────────── --}}
    <div class="mb-4">
        <h5 class="fw-semibold mb-0">
            <i class="bi bi-diagram-3 me-2 text-muted"></i>Chart of Accounts
        </h5>
        <p class="text-muted mb-0" style="font-size:13px">Daftar semua akun berdasarkan tipe</p>
    </div>

    {{-- ── Summary per tipe ────────────────────────────────────────────── --}}
    <div class="row g-3 mb-4">
        @foreach (['Asset', 'Liability', 'Equity', 'Revenue', 'Expense'] as $type)
            @php
                $count = $accounts->where('type', $type)->count();
                $active = $accounts->where('type', $type)->where('is_active', 1)->count();
                $colors = [
                    'Asset' => ['bg' => '#EAF3DE', 'text' => '#3B6D11', 'icon' => 'bi-building'],
                    'Liability' => ['bg' => '#FAEEDA', 'text' => '#854F0B', 'icon' => 'bi-arrow-down-circle'],
                    'Equity' => ['bg' => '#EEEDFE', 'text' => '#534AB7', 'icon' => 'bi-pie-chart'],
                    'Revenue' => ['bg' => '#E1F5EE', 'text' => '#0F6E56', 'icon' => 'bi-graph-up-arrow'],
                    'Expense' => ['bg' => '#FCEBEB', 'text' => '#A32D2D', 'icon' => 'bi-receipt'],
                ][$type];
            @endphp
            <div class="col">
                <div class="card border-0 h-100" style="background:{{ $colors['bg'] }}">
                    <div class="card-body py-3 px-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi {{ $colors['icon'] }}" style="color:{{ $colors['text'] }}; font-size:16px"></i>
                            <span
                                style="font-size:12px; font-weight:500; color:{{ $colors['text'] }}">{{ $type }}</span>
                        </div>
                        <div class="fw-semibold fs-5" style="color:{{ $colors['text'] }}">{{ $count }}</div>
                        <div style="font-size:11px; color:{{ $colors['text'] }}; opacity:.75">{{ $active }} aktif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ── Filter & tabel ───────────────────────────────────────────────── --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap gap-2 align-items-center py-3">
            <div class="input-group input-group-sm" style="max-width:260px">
                <span class="input-group-text bg-white border-end-0 text-muted">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" class="form-control border-start-0 ps-0" placeholder="Cari kode atau nama akun..."
                    id="searchInput" oninput="filterTable()">
            </div>

            <div class="d-flex gap-2 ms-auto">
                <select class="form-select form-select-sm" style="max-width:140px" id="typeFilter" onchange="filterTable()">
                    <option value="">Semua tipe</option>
                    <option>Asset</option>
                    <option>Liability</option>
                    <option>Equity</option>
                    <option>Revenue</option>
                    <option>Expense</option>
                </select>
                <select class="form-select form-select-sm" style="max-width:130px" id="statusFilter"
                    onchange="filterTable()">
                    <option value="">Semua status</option>
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>

            <span class="text-muted" style="font-size:12px" id="rowCount"></span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light" style="font-size:12px">
                    <tr>
                        <th style="width:110px">Kode</th>
                        <th>Nama Akun</th>
                        <th style="width:120px">Tipe</th>
                        <th style="width:90px" class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody id="accountBody">
                    @forelse($accounts as $acc)
                        <tr data-search="{{ strtolower($acc->code . ' ' . $acc->name) }}" data-type="{{ $acc->type }}"
                            data-status="{{ $acc->is_active }}">
                            <td class="align-middle font-monospace text-muted" style="font-size:13px">
                                {{ $acc->code }}
                            </td>
                            <td class="align-middle fw-medium" style="font-size:13px">
                                {{ $acc->name }}
                            </td>
                            <td class="align-middle">
                                <span class="badge fw-normal"
                                    style="font-size:11px; background-color: black; color: white;">
                                    {{ $acc->type }}
                                </span>
                            </td>
                            <td class="text-center align-middle">
                                @if ($acc->is_active)
                                    <span class="badge bg-success-subtle text-success fw-normal"
                                        style="font-size:11px">Aktif</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary fw-normal"
                                        style="font-size:11px">Nonaktif</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">
                                <i class="bi bi-diagram-3 fs-3 d-block mb-2"></i>
                                Belum ada akun tersedia.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('javascript')
    <script>
        function filterTable() {
            const q = document.getElementById('searchInput').value.toLowerCase();
            const type = document.getElementById('typeFilter').value;
            const status = document.getElementById('statusFilter').value;
            const rows = document.querySelectorAll('#accountBody tr[data-search]');
            let visible = 0;

            rows.forEach(row => {
                const show = row.dataset.search.includes(q) &&
                    (!type || row.dataset.type === type) &&
                    (status === '' || row.dataset.status === status);
                row.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            document.getElementById('rowCount').textContent =
                (q || type || status !== '') ? `${visible} akun ditemukan` : '';
        }
    </script>
@endsection
