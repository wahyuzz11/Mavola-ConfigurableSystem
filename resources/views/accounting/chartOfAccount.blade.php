@extends('layouts.index')

@section('content')
    {{-- ── Header ──────────────────────────────────────────────────────── --}}
    <div class="mb-4">
        <h5 class="fw-semibold mb-0">
            <i class="bi bi-diagram-3 me-2 text-muted"></i>Chart of Accounts
        </h5>
        <p class="text-muted mb-0" style="font-size:13px">Daftar semua akun berdasarkan tipe</p>
    </div>

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


    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light" style="font-size:12px">
                    <tr>
                        <th style="width:110px">Kode</th>
                        <th>Nama Akun</th>
                        <th style="width:120px">Tipe</th>
                        
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
                            {{-- <td class="text-center align-middle">
                                @if ($acc->is_active)
                                    <span class="badge bg-success-subtle text-success fw-normal"
                                        style="font-size:11px">Aktif</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary fw-normal"
                                        style="font-size:11px">Nonaktif</span>
                                @endif
                            </td> --}}
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


