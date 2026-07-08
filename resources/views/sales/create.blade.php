@extends('layouts.index')

@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">Buat Pesanan Penjualan</h3>
    </div>

    <div class="container mt-5">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($checkAccount == 'kosong')
            <div class="alert alert-warning">
                Asset masih kosong, input jurnal kas terlebih dahulu
            </div>
        @endif

        <form id="salesOrderForm" method="POST">
            @csrf

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Tanggal Pesanan</label>
                    <input type="date" name="order_date" id="orderDate" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nomor Pesanan</label>
                    <input type="text" name="invoice_number" id="orderNumber" class="form-control" readonly
                        value="{{ $invoiceNumber }}">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Pelanggan</label>
                    <select name="customers" id="customerName" class="form-select" required>
                        <option value="">Pilih Pelanggan</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tambah Produk</label>
                    <select id="productSelect" class="form-select">
                        <option value="">Pilih Produk</option>
                    </select>
                </div>
            </div>

            <table class="table table-bordered" id="itemsTable">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Jumlah</th>
                        <th>Harga Satuan</th>
                        @if ($discountMethods->where('code', 'DISC-01')->where('status', 1)->isNotEmpty())
                            <th>Diskon (%)</th>
                        @endif
                        <th>Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>


            <!-- Global Discount Section -->
            @if ($discountMethods->where('code', 'DISC-02')->where('status', 1)->isNotEmpty())
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="enableGlobalDiscount">
                            <label class="form-check-label" for="enableGlobalDiscount">Terapkan Diskon Global?</label>
                        </div>
                        <input type="number" id="globalDiscountInput" name="global_discount" class="form-control"
                            placeholder="Diskon Global (%)" style="display:none;" min="0" max="100"
                            value="0">
                    </div>
                </div>
            @endif

            <!-- Cashback Section -->
            @if ($discountMethods->where('code', 'DISC-03')->where('status', 1)->isNotEmpty())
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="enableCashback">
                            <label class="form-check-label" for="enableCashback">Terapkan Cashback?</label>
                        </div>
                        <div class="input-group" id="cashbackInputGroup" style="display:none;">
                            <span class="input-group-text">Rp</span>
                            <input type="text" inputmode="numeric" id="cashbackInput" name="cashback_input"
                                class="form-control" placeholder="0" value="">
                        </div>
                    </div>
                </div>
            @endif

            <div class="row mb-3">
                <div class="col-md-4 offset-md-8">
                    @if ($discountMethods->where('code', 'DISC-02')->where('status', 1)->isNotEmpty())
                        <div class="mb-2">
                            <label class="form-label">Diskon Global</label>
                            <input type="text" id="globalDiscountAmount" class="form-control" readonly>
                        </div>
                    @endif

                    @if ($discountMethods->where('code', 'DISC-01')->where('status', 1)->isNotEmpty())
                        <div class="mb-2">
                            <label class="form-label">Total Diskon</label>
                            <input type="text" id="totalDiscountAmount" class="form-control" readonly>
                        </div>
                    @endif
                    <div class="mb-2">
                        <label class="form-label">Total Keseluruhan</label>
                        <input type="text" name="grand_total" id="grandTotal" class="form-control" readonly>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Metode Pembayaran</label>
                    <select name="payment_method" id="paymentMethod" class="form-select" required>
                        @foreach ($paymentMethods as $method)
                            <option value="{{ $method->code }}">{{ $method->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Delivery Cost Container - Shows when DEL-02 is selected -->
            @if ($saleShippingConfig->status == 1)
                <div class="row mb-3" id="deliveryCostContainer">
                    <div class="col-md-6">
                        <label for="delivery_cost" class="form-label">Biaya Pengiriman</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" id="delivery_cost" name="delivery_cost"
                                min="0">
                        </div>
                    </div>
                </div>
            @endif

            <!-- Recipient Details Container - Shows when DEL-02 is selected -->
            <div id="recipientDetailsContainer" style="display: none;">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="recipient_name" class="form-label">Nama Penerima</label>
                        <input type="text" class="form-control" id="recipient_name" name="recipient_name"
                            placeholder="Masukkan nama penerima">
                    </div>
                    <div class="col-md-6">
                        <label for="customer_address" class="form-label">Alamat Pelanggan</label>
                        <textarea class="form-control" id="customer_address" name="customer_address" rows="3"
                            placeholder="Masukkan alamat pengiriman"></textarea>
                    </div>
                </div>
            </div>

            <input type="hidden" name="form_data" id="formDataField">
            <button type="submit" class="btn btn-success">Kirim Pesanan</button>
        </form>
    </div>
@endsection

@section('javascript')
    <script>
        let saleProducts = [];
        let selectedCustomerData = null;
        let totalDiscountAmount = 0;

        const discountMethods = @json($discountMethods);

        // Set today's date as default
        document.getElementById('orderDate').valueAsDate = new Date();

        // Check which discount methods are active

        const hasProductDiscount = discountMethods.some(d => d.code === 'DISC-01' && d.status == 1);
        const hasGlobalDiscount = discountMethods.some(d => d.code === 'DISC-02' && d.status == 1);
        const hasCashback = discountMethods.some(d => d.code === 'DISC-03' && d.status == 1);

        // Format angka menjadi mata uang Rupiah
        function formatRupiah(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR'
            }).format(amount || 0);
        }

        // Format angka dengan pemisah ribuan ala Indonesia (tanpa simbol mata uang), untuk dipakai di input teks
        function formatNumberInput(value) {
            const numericValue = String(value).replace(/\D/g, '');
            return numericValue ? new Intl.NumberFormat('id-ID').format(numericValue) : '';
        }

        // Ambil angka murni dari input yang sudah diformat dengan pemisah ribuan
        function parseFormattedNumber(value) {
            return parseInt(String(value).replace(/\D/g, '')) || 0;
        }

        // Initialize Select2 for Products - Load all products initially
        $(document).ready(function() {

            $('#productSelect').select2({
                placeholder: "Cari dan pilih produk",
                width: 'resolve',
                allowClear: true,
                ajax: {
                    url: '{{ route('sales.query') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            search: params.term || '', // Allow empty search to show all
                            page: params.page || 1
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(function(item) {
                                return {
                                    id: item.id,
                                    text: item.product_name,
                                    price: item.price,
                                    stock: item.total_stock
                                };
                            })
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0 // Allow showing all products without typing
            });
        });


        // Initialize Select2 for Customers - Load all customers initially
        $('#customerName').select2({
            placeholder: "Cari dan pilih pelanggan",
            allowClear: true,
            width: 'resolve',
            ajax: {
                url: '{{ route('findCustomer') }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term || '', // Allow empty search to show all
                        page: params.page || 1
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.map(function(item) {
                            return {
                                id: item.id,
                                text: item.text || item.name || item.customer_name,
                                customerData: item // Store full customer data
                            };
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 0 // Allow showing all customers without typing
        });

        // Handle customer selection
        $('#customerName').on('select2:select', function(e) {
            const data = e.params.data;
            selectedCustomerData = data.customerData;

            // Update recipient fields if DEL-02 is selected
            const isDeliverySelected = $('.delivery-method-radio:checked').data('code') === 'DEL-02';
            if (isDeliverySelected) {
                updateRecipientFields();
            }
        });

        // Handle delivery method change
        $(document).on('change', '.delivery-method-radio', function() {
            const deliveryCode = $(this).data('code');
            const deliveryCostContainer = $('#deliveryCostContainer');
            const recipientDetailsContainer = $('#recipientDetailsContainer');

            if (deliveryCode === 'DEL-02') {
                deliveryCostContainer.show();
                recipientDetailsContainer.show();
                updateRecipientFields();
            } else {
                deliveryCostContainer.hide();
                recipientDetailsContainer.hide();
                $('#delivery_cost').val('');
                $('#recipient_name').val('');
                $('#customer_address').val('');
            }
        });

        // Function to update recipient fields with customer data
        function updateRecipientFields() {
            if (selectedCustomerData) {
                $('#recipient_name').val(selectedCustomerData.name || '');
                $('#customer_address').val(selectedCustomerData.address || '');
            }
        }

        // Check initial state for delivery method
        $(document).ready(function() {
            const checkedDeliveryRadio = $('.delivery-method-radio:checked');
            if (checkedDeliveryRadio.length && checkedDeliveryRadio.data('code') === 'DEL-02') {
                $('#deliveryCostContainer').show();
                $('#recipientDetailsContainer').show();
            }
        });

        // Add product to table
        $('#productSelect').on('select2:select', function(e) {
            const data = e.params.data;

            // Check if product already exists
            if (saleProducts.find(p => p.product_id == data.id)) {
                alert('Produk sudah ditambahkan!');
                $(this).val(null).trigger('change');
                return;
            }

            // Check stock
            if (data.stock <= 0) {
                alert('Stok produk habis!');
                $(this).val(null).trigger('change');
                return;
            }

            // Build table row with default discount value
            let defaultDiscount = 0;
            let rowHtml =
                `
                <tr data-product-id="${data.id}">
                    <td>${data.text}</td>
                    <td><input type="number" class="form-control quantity" value="1" min="1" max="${data.stock}"></td>
                    <td>
                    <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" class="form-control unit-price" value="${data.price}" step="500">
                    </div>
                    </td>`;

            if (hasProductDiscount) {
                rowHtml +=
                    `<td><input type="number" class="form-control product-discount" value="${defaultDiscount}" min="0" max="100" step="0.01"></td>`;
            }

            rowHtml += `
                    <td class="total-price">${formatRupiah(data.price)}</td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-row">Hapus</button></td>
                </tr>`;

            $('#itemsTable tbody').append(rowHtml);

            // Add to products array
            saleProducts.push({
                product_id: data.id,
                product_name: data.text,
                quantity: 1,
                unit_price: parseInt(data.price),
                discount_percentage: defaultDiscount,
                total_price: parseInt(data.price)
            });

            // Clear selection
            $(this).val(null).trigger('change');
            updateTotals();
        });

        // Handle quantity, price, and discount changes
        $('#itemsTable').on('input', '.quantity, .unit-price, .product-discount', function() {
            const row = $(this).closest('tr');
            const productId = row.data('product-id');
            const qty = parseFloat(row.find('.quantity').val()) || 0;
            const price = parseInt(row.find('.unit-price').val()) || 0;
            const discountPercentage = hasProductDiscount ? (parseFloat(row.find('.product-discount').val()) || 0) :
                0;

            // Calculate total with discount
            const subtotal = qty * price;
            const discountAmount = subtotal * (discountPercentage / 100);
            const total = subtotal - discountAmount;
            totalDiscountAmount += discountAmount;
            $('#totalDiscountAmount').val(formatRupiah(totalDiscountAmount));

            row.find('.total-price').text(formatRupiah(total));

            // Update products array
            const index = saleProducts.findIndex(p => p.product_id == productId);
            if (index !== -1) {
                saleProducts[index] = {
                    ...saleProducts[index],
                    quantity: qty,
                    unit_price: price,
                    discount_percentage: discountPercentage,
                    total_price: total
                };
            }

            recalculateTotalDiscount(); // panggil fungsi baru
            updateTotals();
        });

        function recalculateTotalDiscount() {
            let total = 0;
            $('#itemsTable tbody tr').each(function() {
                const row = $(this);
                const qty = parseFloat(row.find('.quantity').val()) || 0;
                const price = parseInt(row.find('.unit-price').val()) || 0;
                const disc = hasProductDiscount ? (parseFloat(row.find('.product-discount').val()) || 0) : 0;
                total += (qty * price) * (disc / 100);
            });
            $('#totalDiscountAmount').val(formatRupiah(total));
        }

        // Remove product row
        $('#itemsTable').on('click', '.remove-row', function() {
            const row = $(this).closest('tr');
            const productId = row.data('product-id');

            // Remove from array
            saleProducts = saleProducts.filter(p => p.product_id != productId);

            // Remove row
            row.remove();
            updateTotals();
        });

        // Global discount toggle
        if (hasGlobalDiscount) {
            $('#enableGlobalDiscount').on('change', function() {
                const isChecked = this.checked;
                $('#globalDiscountInput').toggle(isChecked);
                if (!isChecked) {
                    $('#globalDiscountInput').val(0);
                }
                updateTotals();
            });

            $('#globalDiscountInput').on('input', function() {
                updateTotals();
            });
        }

        // Cashback toggle
        if (hasCashback) {
            $('#enableCashback').on('change', function() {
                $('#cashbackInputGroup').toggle(this.checked);
                if (!this.checked) {
                    $('#cashbackInput').val('');
                }
            });

            // Format input cashback dengan pemisah ribuan saat mengetik (nilai rupiah langsung)
            $('#cashbackInput').on('input', function() {
                $(this).val(formatNumberInput($(this).val()));
            });
        }

        // Update totals function
        function updateTotals() {
            let subtotal = 0;

            // Calculate subtotal from all products
            saleProducts.forEach(function(product) {
                subtotal += parseInt(product.total_price) || 0;
            });

            // Calculate global discount
            let globalDiscountAmount = 0;
            if (hasGlobalDiscount && $('#enableGlobalDiscount').is(':checked')) {
                const globalDiscountPercentage = parseFloat($('#globalDiscountInput').val()) || 0;
                globalDiscountAmount = subtotal * (globalDiscountPercentage / 100);
            }

            $('#globalDiscountAmount').val(formatRupiah(globalDiscountAmount));

            // Calculate grand total
            const grandTotal = subtotal - globalDiscountAmount;
            $('#grandTotal').val(formatRupiah(grandTotal));


        }


        $('#salesOrderForm').on('submit', async function(e) {
            e.preventDefault();

            if ($('#itemsTable tbody tr').length === 0) {
                alert('Tolong tambahkan minimal satu produk ke dalam pesanan');
                return;
            }


            const products = [];
            $('#itemsTable tbody tr').each(function() {
                const row = $(this);
                products.push({
                    product_id: row.data('product-id'),
                    quantity: parseFloat(row.find('.quantity').val()) || 0,
                    unit_price: parseInt(row.find('.unit-price').val()) || 0,
                    subtotal: Math.round(
                        (parseFloat(row.find('.quantity').val()) || 0) *
                        (parseFloat(row.find('.unit-price').val()) ||
                            0)
                    ),
                    discount_value: hasProductDiscount ? (parseFloat(row.find(
                        '.product-discount').val()) || 0) : 0
                });
            });

            const deliveryMethod = $('.delivery-method-radio:checked').data('code');
            const paymentMethod = $('#paymentMethod').val();

            // Hitung ulang subtotal dan grand total dari data mentah (bukan dari teks yang sudah diformat Rupiah)
            let rawSubtotal = 0;
            saleProducts.forEach(function(product) {
                rawSubtotal += parseInt(product.total_price) || 0;
            });
            let rawGlobalDiscount = 0;
            if (hasGlobalDiscount && $('#enableGlobalDiscount').is(':checked')) {
                const globalDiscountPercentage = parseFloat($('#globalDiscountInput').val()) || 0;
                rawGlobalDiscount = rawSubtotal * (globalDiscountPercentage / 100);
            }
            const rawGrandTotal = rawSubtotal - rawGlobalDiscount;

            const payload = {
                invoice_number: $('#orderNumber').val(),
                total_price: Math.round(rawGrandTotal) || 0,
                sale_date: $('#orderDate').val(),
                customers_id: $('#customerName').val(),
                delivery_method: deliveryMethod,
                payment_method: paymentMethod,
                status: 'completed',
                global_discount: hasGlobalDiscount ? Math.round(rawGlobalDiscount) : 0,
                total_discount: hasProductDiscount ? Math.round(totalDiscountAmount) : 0,
                discount_cashback: hasCashback && $('#enableCashback').is(':checked') ?
                    parseFormattedNumber($('#cashbackInput').val()) : 0,
                recipient_name: $('#recipient_name').val(),
                customer_address: $('#customer_address').val(),
                shipped_date: deliveryMethod === 'DEL-02' ? new Date().toISOString().split('T')[0] : null,
                delivery_cost: parseInt($('#delivery_cost').val()) || null,
                products,
            };

            try {
                const res = await fetch('{{ route('sales.storeAjax') }}', {
                    method: 'POST',
                    headers: {
                        'Content-type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });

                const text = await res.text();
                console.log('Raw response:', text); // ← check this in console

                try {
                    const data = JSON.parse(text);
                    if (!res.ok) throw new Error(data.error || 'Terjadi kesalahan');
                    window.location.href = data.redirect;
                } catch (err) {
                    alert('Error: ' + err.message);
                }
            } catch (err) {
                alert('Error: ' + err.message);
            };
        });
    </script>
@endsection
