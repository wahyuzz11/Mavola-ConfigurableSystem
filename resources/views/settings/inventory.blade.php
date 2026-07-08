@extends('layouts.index')

@section('content')
    <div class="page-header mb-4">
        <h3 class="fw-bold mb-3">
            Pengaturan Persediaan
        </h3>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa fa-exclamation-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa fa-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Inventory Method Section --}}
    <form method="POST" action="{{ route('configuration.updateInventory') }}">
        @csrf
        <div class="form-group mb-5">
            <label class="fw-semibold mb-3">Konfigurasi HPP</label>

            <div class="d-flex flex-column mb-4">
                @foreach ($cogsMethods as $method)
                    <div class="form-check mb-3">
                        <div class="d-flex align-items-center">
                            <input class="form-check-input" type="radio" name="cogs_method"
                                id="cogs_method_{{ $method->id }}" value="{{ $method->id }}"
                                @checked($method->status == 1) @disabled(!$activeCogsConfig->status)
                                data-method-id="{{ $method->id }}">

                            <label class="form-check-label ms-2" for="cogs_method_{{ $method->id }}">
                                {{ $method->name }}
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="expired_status" name="expired_status" value="1"
                    @checked($activeExpireConfig->status == 1)>
                <label class="form-check-label" for="expired_status">
                    Aktifkan pengaturan tanggal kedaluwarsa untuk produk
                </label>
            </div>

            <br>
            <br>

            <small class="text-muted d-block mb-3">
                <strong>FIFO</strong> (First In First Out): Batch pembelian yang paling lama akan digunakan terlebih
                dahulu.<br>
                <strong>Average</strong>: Harga pokok dihitung ulang setiap kali berdasarkan rata-rata tertimbang dari
                pembelian.<br>
            </small>


            {{-- PENJELASAN KONFIGURSASI --}}
            {{-- PENJELASAN KONFIGURASI --}}
            <div class="alert alert-warning mt-3" style="font-size: 13.5px;">
                <div class="d-flex align-items-start">
                    <i class="fa fa-info-circle me-2 mt-1"></i>
                    <div>
                        <strong>Cara Kerja Pengurutan Batch Produk</strong>
                        <p class="mb-2 mt-2">
                            Sistem menggunakan metode <strong>FIFO (First In First Out)</strong> atau
                            <strong>Average</strong> untuk menentukan urutan pengambilan batch produk saat terjadi
                            transaksi penjualan. Aturan pengurutannya sebagai berikut:
                        </p>

                        <ul class="mb-2 ps-3">
                            <li class="mb-2">
                                <strong>Metode FIFO:</strong>
                                Batch produk akan selalu diurutkan berdasarkan
                                <u>tanggal pembelian</u> (dari yang paling lama ke yang terbaru),
                                <strong>terlepas dari apakah pengaturan tanggal kadaluarsa aktif atau tidak</strong>.
                                
                            </li>
                            <li class="mb-2">
                                <strong>Metode Average:</strong>
                                <ul class="mt-1">
                                    <li>Jika tanggal kadaluarsa <strong>aktif</strong>, batch akan diurutkan berdasarkan
                                        <u>tanggal kadaluarsa paling awal</u> (batch yang akan kadaluarsa lebih dulu
                                        akan digunakan terlebih dahulu).
                                    </li>
                                    <li>Jika tanggal kadaluarsa <strong>tidak aktif</strong>, batch akan diurutkan
                                        berdasarkan <u>tanggal pembelian</u> seperti pada metode FIFO.
                                    </li>
                                    <li>
                                        <strong>Batch produk yang melewati tanggal kadaluarsa akan dianggap otomatis kosong oleh sistem</strong>
                                    </li>
                                </ul>
                            </li>
                        </ul>

                        <hr class="my-2">

                        <p class="mb-0">
                            <i class="fa fa-triangle-exclamation text-danger me-1"></i>
                            <strong>Perhatian:</strong> Pastikan <strong>stok produk dalam kondisi kosong (0)</strong>
                            sebelum mengaktifkan atau menonaktifkan pengaturan tanggal kadaluarsa. Mengubah pengaturan
                            ini saat stok masih tersedia dapat menyebabkan <strong>ketidaksesuaian data batch</strong>
                            pada transaksi berikutnya.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary mt-4">Simpan Konfigurasi</button>
    </form>
@endsection
