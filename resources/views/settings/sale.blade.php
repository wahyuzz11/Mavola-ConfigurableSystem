@extends('layouts.index')

@section('content')
    <div class="page-header mb-4">
        <h3 class="fw-bold mb-3">
            Konfigurasi Penjualan
        </h3>
    </div>

    @if (session('success'))
        <div style="background: green; color: white; padding: 10px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div style="background: red; color: white; padding: 10px; margin-bottom: 20px;">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('configuration.updateSale') }}">
        @csrf

        <!-- Payment Methods -->
        <div class="form-group mb-5">
            <label class="fw-semibold mb-3">Konfigurasi pembayaran penjualan</label>
            <div class="d-flex flex-wrap gap-3">
                @foreach ($paymentMethods as $method)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="payment_method[]"
                            id="payment_method_{{ $method->id }}" value="{{ $method->id }}" @checked($method->status == 1)
                            @disabled($method->types == 'mandatory')>
                        <label class="form-check-label" for="payment_method_{{ $method->id }}">
                            {{ $method->name }}
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Discount Configuration -->
        <div class="form-group mb-5">
            <label class="fw-semibold mb-3 d-block">Konfigurasi Diskon</label>

            <div class="form-check form-switch mb-4">
                <input class="form-check-input" type="checkbox" role="switch" id="discount_status"
                    name="discount_status" value="1" @checked($discStatus == 1)>
                <label class="form-check-label" for="discount_status">
                    Hidupkan Diskon
                </label>
            </div>

            <div class="text-muted mb-2" style="font-size: 13px;">
                Pilih salah satu jenis diskon yang ingin diaktifkan.
            </div>

            <div class="list-group" id="discount_method_list">
                @foreach ($discountMethods as $method)
                    <label for="discount_method_{{ $method->id }}"
                        class="list-group-item d-flex align-items-center gap-3 py-3 {{ !$discStatus ? 'disabled bg-light text-muted' : '' }}">
                        <input class="form-check-input mt-0 discount-method-radio" type="radio"
                            name="discount_method" id="discount_method_{{ $method->id }}"
                            value="{{ $method->id }}" @checked($method->status == 1) @disabled(!$discStatus)>
                        <div>
                            <div class="fw-semibold">{{ $method->name }}</div>
                            @if (in_array($method->code, ['DISC-02', 'DISC-03']))
                                <div class="text-muted" style="font-size: 12px;">
                                </div>
                            @elseif($method->code == 'DISC-01')
                                <div class="text-muted" style="font-size: 12px;">
                                </div>
                            @endif
                        </div>
                    </label>
                @endforeach
            </div>
            <br>


             <!-- Discount Information -->
            <div class="alert alert-info mb-4" style="font-size: 14px;">
                <strong>Informasi Diskon:</strong><br>
                • <strong>Diskon Produk Tertentu:</strong> Memungkinkan pengaturan persentase diskon untuk masing-masing
                produk tertentu<br>
                • <strong>Diskon Global:</strong> Menerapkan persentase diskon untuk total belanjaan pelanggan<br>
                • <strong>Cashback:</strong> Memberikan  cashback kepada pelanggan setelah melakukan pembelian<br>
                <br>
                <strong>Catatan:</strong> Hanya <span class="text-danger"><strong>satu diskon </strong></span> yang bisa dihidupkan dalam 1 waktu 
            </div> 
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="sale_shipping_expense" name="sale_shipping_expense"
                value="1" @checked($activeSaleShippingConfig->status == 1)>
            <label class="form-check-label" for="sale_shipping_expense">
                Aktifkan ongkir untuk penjualan
            </label>
        </div>

        <button type="submit" class="btn btn-primary mt-4">Save Configuration</button>
    </form>
@endsection

@section('javascript')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const discountStatusCheckbox = document.getElementById('discount_status');
            const discountMethodRadios = document.querySelectorAll('.discount-method-radio');

            // Toggle semua opsi diskon on/off berdasarkan status utama
            discountStatusCheckbox.addEventListener('change', function() {
                const isEnabled = this.checked;

                discountMethodRadios.forEach(radio => {
                    radio.disabled = !isEnabled;
                    if (!isEnabled) {
                        radio.checked = false;
                    }

                    const listItem = radio.closest('.list-group-item');
                    listItem.classList.toggle('disabled', !isEnabled);
                    listItem.classList.toggle('bg-light', !isEnabled);
                    listItem.classList.toggle('text-muted', !isEnabled);
                });
            });
        });
    </script>
@endsection