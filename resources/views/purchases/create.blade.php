@extends('layouts.index')

@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">
            Buat Pembelian
        </h3>
    </div>

    <div class="container mt-5">

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        <script>
                            console.error("Laravel error: {{ $error }}");
                        </script>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($checkAccount == 'kosong')
            <div class="alert alert-warning">

                Asset masih kosong, input jurnal kas terlebih dahulu

            </div>
        @endif

        <h2>Pesanan Pembelian</h2>
        <form id="purchaseOrderForm">
            @csrf
            <!-- Supplier and Order Details -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="orderDate" class="form-label">Tanggal Pesanan</label>
                    <input type="date" class="form-control" id="orderDate" required name="order_date">
                </div>
                <div class="col-md-4">
                    <label for="orderNumber" class="form-label">Nomor Pesanan</label>
                    <input type="text" class="form-control" id="orderNumber" name="invoice_number"
                        placeholder="Nomor pesanan" readonly value="{{ $invoiceNumber }}">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="supplierName" class="form-label">Nama Supplier</label>
                    <select id="supplierName" class="form-select" required name="suppliers">
                        <option value="">Pilih supplier</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="productSelect" class="form-label">Tambah Produk</label>
                    <select id="productSelect" class="form-select" style="width: 100%;">
                        <option value="">Cari dan pilih produk</option>
                    </select>
                </div>
            </div>
            @if ($purchaseShippingConfig->status == 1)
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

            <!-- Items Table for Selected Products -->
            <table class="table table-bordered mt-3" id="itemsTable">
                <thead class="table-light">
                    <tr>
                        <th>Nama Produk</th>
                        <th>Jumlah</th>
                        <th>Harga Satuan</th>
                        @if ($expiredSetting->status == 1)
                            <th>Hari Menuju Kadaluarsa</th>
                            <th>Tanggal Kadaluarsa</th>
                        @endif
                        {{-- <th>Status</th> --}}
                        <th>Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Products added from dropdown will appear here -->
                </tbody>
            </table>
            {{-- <input type="hidden" id="purchasedProductsData" name="purchased_products"> --}}

            <!-- Grand Total -->
            <div class="row mb-3">
                <div class="col-md-4 offset-md-8">
                    <label for="grandTotal" class="form-label">Total Keseluruhan</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" class="form-control" id="grandTotal" name="grand_total" readonly>
                    </div>
                </div>
            </div>

            @if ($purchaseShippingConfig->status == 1)
                <div class="row mb-3" id="deliveryCostContainer" style="display: none;">
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


            <div class="row mb-3">
                <div class="form-group">
                    <label for="paymentMethod">Metode Pembelian</label>
                    <select class="form-select" id="paymentMethod" name="payment_method" required>
                        <option value="" disabled selected>Silakan pilih metode</option>
                        @foreach ($purchaseMethods as $method)
                            <option value="{{ $method->code }}">
                                {{ $method->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Due Date Picker - Initially hidden -->
            <div class="row mb-3" id="dueDateContainer" style="display: none;">
                <div class="form-group">
                    <label for="due_date">Tanggal Jatuh Tempo</label>
                    <input type="date" class="form-control" id="due_date" name="due_date">
                </div>
            </div>

            <button type="submit" class="btn btn-success">Kirim Pesanan</button>
        </form>
    </div>
@endsection

@section('javascript')
    <script>
        const expiredActive = {{ $expiredSetting->status }};
        let grandTotal = 0;

        $('#paymentMethod').on('change', function() {
            if ($(this).val() === 'P-PAY-03') {
                $('#dueDateContainer').show();
            } else {
                $('#dueDateContainer').hide();
                $('#due_date').val(''); // Opsional: kosongkan tanggal jatuh tempo
            }
        });

        // Initialize Select2 for product dropdown
        $('#productSelect').select2({
            placeholder: "Cari dan pilih produk",
            ajax: {
                url: '{{ route('purchases.query') }}', // double quotes inside
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.map(product => ({
                            id: product.id,
                            text: product.product_name,
                            price: product.price,
                            stock: product.total_stock,
                            expired: product.expired,
                            expired_date: product.expired_date
                        }))
                    };
                },
                cache: true
            }
        });

        $('#supplierName').select2({
            placeholder: "Cari supplier",
            allowClear: true,
            ajax: {
                url: '{{ route('findSupplier') }}', // double quotes inside
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });

        let purchasedProducts = [];

        // Expiration Date Functions
        function calculateExpirationDate(orderDate, expireDays) {
            if (!expireDays || expireDays <= 0) return 'Tidak ada';

            const orderDateObj = new Date(orderDate);
            const expirationDate = new Date(orderDateObj);
            expirationDate.setDate(orderDateObj.getDate() + parseInt(expireDays));

            return expirationDate.toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        }



        function getDaysUntilExpiration(orderDate, expireDays) {
            if (!expireDays || expireDays <= 0) return null;

            const orderDateObj = new Date(orderDate);
            const expirationDate = new Date(orderDateObj);
            expirationDate.setDate(orderDateObj.getDate() + parseInt(expireDays));

            const today = new Date();
            const timeDiff = expirationDate - today;
            const daysDiff = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));

            return daysDiff;
        }

        function getExpirationStatus(orderDate, expireDays) {
            const days = getDaysUntilExpiration(orderDate, expireDays);
            if (days === null) return {
                class: '',
                text: ''
            };

            if (days < 0) {
                return {
                    class: 'badge bg-dark',
                    text: `Kadaluarsa ${Math.abs(days)} hari yang lalu`
                };
            }
            if (days === 0) {
                return {
                    class: 'badge bg-danger',
                    text: 'Kadaluarsa hari ini'
                };
            }
            if (days <= 7) {
                return {
                    class: 'badge bg-danger',
                    text: `${days} hari lagi`
                };
            }
            if (days <= 30) {
                return {
                    class: 'badge bg-warning',
                    text: `${days} hari lagi`
                };
            }
            return {
                class: 'badge bg-success',
                text: `${days} hari lagi`
            };
        }

        function updateExpirationInfo() {
            const orderDate = $('#orderDate').val();
            if (!orderDate) return;

            $('#itemsTable tbody tr').each(function() {
                const row = $(this);
                const expireDays = parseInt(row.find('.expire-days').val()) || 0;
                const expirationDate = calculateExpirationDate(orderDate, expireDays);
                const status = getExpirationStatus(orderDate, expireDays);

                row.find('.expiration-date').text(expirationDate);
                row.find('.expiration-status').attr('class', status.class).text(status.text);
            });
        }


        function updateGrandTotal() {
            grandTotal = 0; // reset dulu sebelum hitung ulang
            purchasedProducts.forEach(product => {
                grandTotal += parseInt(product.total_price) || 0;
            });
            $('#grandTotal').val(formatRupiah(grandTotal));
        }



        function formatRupiah(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR'
            }).format(amount);
        }



        $('#productSelect').on('select2:select', function(e) {
            const data = e.params.data;
            const price = Math.round(parseFloat(data.price)) || 0; // ⬅️ bulatkan di sini

            const existingRow = $('#itemsTable tbody').
            find(`tr[data-product-id="${data.id}"]`);

            if (existingRow.length > 0) {
                const quantityInput = existingRow.find('.quantity');
                const newQuantity = parseInt(quantityInput.val()) + 1;
                quantityInput.val(newQuantity).trigger('change');
            } else {
                const newRow = `
        <tr data-product-id="${data.id}">
            <td>${data.text}</td>
            <td><input type="number" class="form-control quantity" min="1" value="1" required></td>
            <td>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" class="form-control unit-price" value="${price}" step="500">
                </div>
            </td>
                ${expiredActive === 1 ? 
                (data.expired === 1 ? `
                                    <td>
                                        <input type="number" class="form-control expire-days" min="0" value="${data.expired_date}" placeholder="Hari">
                                    </td>
                                    <td class="expiration-date">Tidak ada</td>
                                     ` : `
                                    <td>Produk ini tidak mendukung pelacakan kadaluarsa</td>
                                    <td>Tidak ada</td>
                                     `) 
             : ``
        }
            <td class="total-price">${formatRupiah(price)}</td>
            <td><button type="button" class="btn btn-danger btn-sm remove-row">Hapus</button></td>
        </tr>`;
                $('#itemsTable tbody').append(newRow);

                const defaultExpireDays = (expiredActive === 1 && data.expired === 1) ? 0 : null;

                purchasedProducts.push({
                    product_id: data.id,
                    quantity: 1,
                    purchase_price: price,
                    total_price: price,
                    expire_days: defaultExpireDays
                });
            }

            $('#productSelect').val(null).trigger('change');
            updateGrandTotal();
            updateExpirationInfo();
        });


        $('#itemsTable').on('change', '.quantity, .unit-price, .expire-days', function() {
            const row = $(this).closest('tr');
            const productId = String(row.data('product-id')); // ⬅️ paksa jadi string
            const quantity = parseFloat(row.find('.quantity').val()) || 0;
            const unitPrice = parseFloat(row.find('.unit-price').val()) || 0;
            const expireDays = parseInt(row.find('.expire-days').val()) || 0;
            const totalPrice = quantity * unitPrice;

            row.find('.total-price').text(formatRupiah(totalPrice));

            const product = purchasedProducts.find(p => String(p.product_id) ===
                productId);

            if (product) {
                product.quantity = quantity;
                product.purchase_price = unitPrice;
                product.total_price = totalPrice;
                product.expire_days = expireDays;
            } else {
                // fallback kalau entah kenapa belum tersinkron di array
                purchasedProducts.push({
                    product_id: row.data('product-id'),
                    quantity,
                    purchase_price: unitPrice,
                    total_price: totalPrice,
                    expire_days: expireDays
                });
            }

            updateGrandTotal();
            updateExpirationInfo();
        });
        // Update expiration info when order date changes
        $('#orderDate').on('change', function() {
            updateExpirationInfo();
        });

        // Remove row
        $('#itemsTable').on('click', '.remove-row', function() {
            const row = $(this).closest('tr');
            const productId = row.data('product-id');

            // Remove from purchasedProducts array
            purchasedProducts = purchasedProducts.filter(p => p.product_id != productId);

            row.remove();
            updateGrandTotal();
        });


        // From submission dengan AJAX
        $('#purchaseOrderForm').on('submit', async function(e) {
            e.preventDefault();

            const products = [];
            let grandTotal = 0;

            if ($('#itemsTable tbody tr').length === 0) {
                alert('Tolong tambahkan setidaknya satu produk ke dalam pesanan');
                return;
            }

            $('#itemsTable tbody tr').each(function() {
                const row = $(this);
                const quantity = parseFloat(row.find('.quantity').val()) || 0;
                const purchase_price = parseInt(row.find('.unit-price').val()) || 0;
                const total_price = quantity * purchase_price;

                grandTotal += total_price;

                products.push({
                    product_id: row.data('product-id'),
                    quantity,
                    purchase_price,
                    expire_days: parseInt(row.find('.expire-days').val()) || 0,
                    total_price,
                });
            });

            const receiveMethod = $('input[name="receive_method"]:checked').val();
            const paymentMethod = $('#paymentMethod').val()


            const payload = {
                invoice_number: $('#orderNumber').val(),
                order_date: $('#orderDate').val(),
                suppliers: $('#supplierName').val(),
                grand_total: grandTotal || 0,
                receive_method: receiveMethod,
                payment_method: paymentMethod,
                delivery_cost: parseInt($('#delivery_cost').val()) || null,
                due_date: paymentMethod === 'P-PAY-03' ? $('#due_date').val() : null,
                products,
            };

            try {
                const res = await fetch('{{ route('purchases.storeAjax') }}', {
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

                // const data = await res.json();

                // if (!res.ok) throw new Error(data.error || 'Ada error terjadi ');

                // window.location.href = data.redirect;
            } catch (err) {
                alert('Error: ' + err.message);
            }

        });
    </script>
@endsection
