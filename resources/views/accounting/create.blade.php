@extends('layouts.index')

@section('content')
    <div class="page-header mb-4">
        <h3 class="fw-bold mb-1">Buat Jurnal Baru</h3>
        <p class="text-muted mb-0">Tambahkan entri jurnal akuntansi dengan akun debit dan kredit.</p>
    </div>

    {{-- Alert --}}
    <div id="alertBox" class="alert d-none" role="alert"></div>

    <form id="journalForm">
        @csrf

        {{-- Header Jurnal --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Data Jurnal</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="journalDate" class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="journalDate" name="order_date" required>
                    </div>
                    <div class="col-md-3">
                        <label for="refCode" class="form-label">No. Referensi</label>
                        <input type="text" class="form-control" id="refCode" name="ref_code" placeholder="Contoh: JRN-001">
                    </div>
                    <div class="col-md-6">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="description" name="description" rows="2" placeholder="Deskripsi transaksi..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabel Akun --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Entri Akun</span>
                <div class="d-flex gap-2 align-items-center" style="min-width: 300px;">
                    <select id="accountSelect" class="form-select" style="width: 100%;"></select>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0" id="accountsTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;">Kode Akun</th>
                            <th>Nama Akun</th>
                            <th style="width: 120px;">Tipe</th>
                            <th style="width: 200px;">Debit (Rp)</th>
                            <th style="width: 200px;">Kredit (Rp)</th>
                            <th style="width: 80px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="accountsBody">
                        <tr id="emptyRow">
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-journal-text fs-4 d-block mb-1"></i>
                                Belum ada akun ditambahkan. Pilih akun dari dropdown di atas.
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="table-light fw-semibold">
                        <tr>
                            <td colspan="3" class="text-end">Total</td>
                            <td id="totalDebit" class="text-end">Rp 0</td>
                            <td id="totalCredit" class="text-end">Rp 0</td>
                            <td></td>
                        </tr>
                        <tr id="balanceRow" class="d-none">
                            <td colspan="3" class="text-end text-danger">Selisih</td>
                            <td colspan="2" id="balanceDiff" class="text-end text-danger"></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Tombol Aksi --}}
        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('accounting.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary" id="submitBtn">
                <span id="submitSpinner" class="spinner-border spinner-border-sm d-none me-1"></span>
                Simpan Jurnal
            </button>
        </div>
    </form>
@endsection

@section('javascript')
<script>

    let rowCounter = 0;
    const addedAccounts = {}; // code => true, mencegah duplikat

    $('#accountSelect').select2({
        placeholder: "Cari dan pilih akun...",
        allowClear: true,
        ajax: {
            url: '{{ route('accounting.query') }}',
            dataType: 'json',
            delay: 250,
            data: params => ({ search: params.term }),
            processResults: data => ({
                results: data.map(a => ({
                    id: a.id,
                    text: `${a.code} – ${a.name}`,
                    code: a.code,
                    name: a.name,
                    type: a.type
                }))
            }),
            cache: true
        }
    });

    $('#accountSelect').on('select2:select', function (e) {
        const data = e.params.data;

        if (addedAccounts[data.code]) {
            showAlert('warning', `Akun "${data.name}" sudah ada dalam daftar.`);
            $(this).val(null).trigger('change');
            return;
        }

        addedAccounts[data.code] = true;
        rowCounter++;

        $('#emptyRow').hide();

        // Tentukan default: apakah baris ini debit atau kredit?
        // Aturan sederhana: baris pertama selalu Debit, sisanya bisa diisi bebas.
        const isFirstEntry = Object.keys(addedAccounts).length === 1;

        const row = `
            <tr data-account-code="${data.code}" data-row="${rowCounter}">
                <td class="text-center text-muted small align-middle">${data.code}</td>
                <td class="align-middle fw-medium">${data.name}</td>
                <td class="align-middle">
                    <span class="badge bg-secondary">${data.type}</span>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Rp</span>
                        <input type="number"
                               class="form-control debit-input"
                               min="0"
                               step="1"
                               value="${isFirstEntry ? '' : '0'}"
                               placeholder="0"
                               data-code="${data.code}"
                               ${isFirstEntry ? 'autofocus' : ''}>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Rp</span>
                        <input type="number"
                               class="form-control credit-input"
                               min="0"
                               step="1"
                               value="0"
                               placeholder="0"
                               data-code="${data.code}">
                    </div>
                </td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-row" title="Hapus baris">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>`;

        $('#accountsBody').append(row);
        $(this).val(null).trigger('change'); // Reset dropdown
        recalculate();
    });

    // ─── Hapus baris ───────────────────────────────────────
    $('#accountsBody').on('click', '.remove-row', function () {
        const row = $(this).closest('tr');
        const code = row.data('account-code');
        delete addedAccounts[code];
        row.remove();

        if ($('#accountsBody tr:visible').length === 0) {
            $('#emptyRow').show();
        }
        recalculate();
    });

    // ─── Hitung ulang total saat nilai berubah ─────────────
    $('#accountsBody').on('input', '.debit-input, .credit-input', function () {
        recalculate();
    });

    function recalculate() {
        let totalDebit = 0;
        let totalCredit = 0;

        $('#accountsBody tr:not(#emptyRow)').each(function () {
            const debit  = parseFloat($(this).find('.debit-input').val())  || 0;
            const credit = parseFloat($(this).find('.credit-input').val()) || 0;
            totalDebit  += debit;
            totalCredit += credit;
        });

        $('#totalDebit').text('Rp ' + formatNumber(totalDebit));
        $('#totalCredit').text('Rp ' + formatNumber(totalCredit));

        const diff = Math.abs(totalDebit - totalCredit);
        if (diff > 0 && (totalDebit > 0 || totalCredit > 0)) {
            $('#balanceRow').removeClass('d-none');
            $('#balanceDiff').text('Rp ' + formatNumber(diff));
        } else {
            $('#balanceRow').addClass('d-none');
        }
    }

    function formatNumber(n) {
        return n.toLocaleString('id-ID');
    }

    // ─── Submit ────────────────────────────────────────────
    $('#journalForm').on('submit', async function (e) {
        e.preventDefault();

        // Kumpulkan data akun
        const accounts = [];
        let totalDebit = 0;
        let totalCredit = 0;
        let hasEntry = false;

        $('#accountsBody tr:not(#emptyRow)').each(function () {
            const code = String($(this).data('account-code'));
            const debit  = parseFloat($(this).find('.debit-input').val())  || 0;
            const credit = parseFloat($(this).find('.credit-input').val()) || 0;

            accounts.push({ account_code: code, debit, credit });
            totalDebit  += debit;
            totalCredit += credit;
            hasEntry = true;
        });

        // Validasi: minimal ada satu akun
        if (!hasEntry) {
            showAlert('danger', 'Tambahkan minimal satu akun sebelum menyimpan.');
            return;
        }

        // Validasi: debit harus sama dengan kredit
        if (Math.abs(totalDebit - totalCredit) > 0.01) {
            showAlert('danger', `Total Debit (Rp ${formatNumber(totalDebit)}) dan Kredit (Rp ${formatNumber(totalCredit)}) harus seimbang.`);
            return;
        }

        // Validasi: tanggal wajib diisi
        if (!$('#journalDate').val()) {
            showAlert('danger', 'Tanggal jurnal wajib diisi.');
            return;
        }

        const payload = {
            order_date:  $('#journalDate').val(),
            ref_code:    $('#refCode').val(),
            description: $('#description').val(),
            accounts:    accounts,
        };

        // Kirim ke backend
        setLoading(true);
        try {
            const response = await fetch('{{ route('accounting.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload)
            });

            const result = await response.json();

            if (response.ok) {
                showAlert('success', 'Jurnal berhasil disimpan! Mengalihkan...');
                setTimeout(() => {
                    window.location.href = '{{ route('accounting.index') }}';
                }, 1200);
            } else {
                const errors = result.errors
                    ? Object.values(result.errors).flat().join('<br>')
                    : result.message ?? 'Terjadi kesalahan.';
                showAlert('danger', errors);
            }
        } catch (err) {
            showAlert('danger', 'Gagal terhubung ke server. Coba lagi.');
        } finally {
            setLoading(false);
        }
    });

    // ─── Helper UI ─────────────────────────────────────────
    function showAlert(type, message) {
        const box = $('#alertBox');
        box.removeClass('d-none alert-success alert-danger alert-warning')
           .addClass(`alert-${type}`)
           .html(message);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function setLoading(loading) {
        $('#submitBtn').prop('disabled', loading);
        $('#submitSpinner').toggleClass('d-none', !loading);
    }
</script>
@endsection