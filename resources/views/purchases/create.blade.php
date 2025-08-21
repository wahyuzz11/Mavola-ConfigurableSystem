@extends('layouts.index')

@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">
            Create Purchase
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

        <h2>Purchase Order</h2>
        <form id="purchaseOrderForm" method="POST" action="{{ route('purchases.store') }}">
            @csrf
            <!-- Supplier and Order Details -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="orderDate" class="form-label">Order Date</label>
                    <input type="date" class="form-control" id="orderDate" required name="order_date">
                </div>
                <div class="col-md-4">
                    <label for="orderNumber" class="form-label">Order Number</label>
                    <input type="text" class="form-control" id="orderNumber" name="invoice_number"
                        placeholder="Order number" readonly value="{{ $invoiceNumber }}">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="supplierName" class="form-label">Supplier Name</label>
                    <select id="supplierName" class="form-select" required name="suppliers">
                        <option value="">Select a supplier</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="productSelect" class="form-label">Add Product</label>
                    <select id="productSelect" class="form-select" style="width: 100%;">
                        <option value="">Search and select a product</option>
                    </select>
                </div>
            </div>

            @if ($expiredSetting->status == 1)
                <!-- Expiration Date Information Alert -->
                <div class="alert alert-info" role="alert">
                    <h6 class="alert-heading">
                        <i class="fas fa-clock"></i> Expiration Date Tracking
                    </h6>
                    <p class="mb-0">
                        Enter the number of days after purchase date for each product to automatically calculate expiration
                        dates and track product freshness.
                    </p>
                </div>
            @endif


            <!-- Items Table for Selected Products -->
            <table class="table table-bordered mt-3" id="itemsTable">
                <thead class="table-light">
                    <tr>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        @if ($expiredSetting->status == 1)
                            <th>Days to Expire</th>
                            <th>Expiration Date</th>
                        @endif
                        {{-- <th>Status</th> --}}
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Products added from dropdown will appear here -->
                </tbody>
            </table>
            <input type="hidden" id="purchasedProductsData" name="purchased_products">

            <!-- Expiration Status Legend -->
            {{-- <div class="card mb-3">
                <div class="card-body">
                    <h6 class="card-title">Expiration Status Legend:</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <span class="badge bg-success">● Fresh (30+ days)</span>
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-warning">● Caution (8-30 days)</span>
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-danger">● Critical (1-7 days)</span>
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-dark">● Expired</span>
                        </div>
                    </div>
                </div>
            </div> --}}

            <!-- Grand Total -->
            <div class="row mb-3">
                <div class="col-md-4 offset-md-8">
                    <label for="grandTotal" class="form-label">Grand Total</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" class="form-control" id="grandTotal" name="grand_total" readonly>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="form-group">
                    <label>Receive Method</label><br>
                    ~ <div class="d-flex">
                        @foreach ($receiveMethods as $index => $method)
                            @if ($activeMethods->count() == 1 && $method->status != 1)
                                @continue
                            @endif
                            <div class="form-check me-3">
                                <input class="form-check-input receive-method-radio" type="radio" name="receive_method"
                                    id="receiveMethod{{ $method->id }}" value="{{ $method->code }}"
                                    {{ $method->status == 1 ? 'checked' : '' }} data-code="{{ $method->code }}">
                                <label class="form-check-label" for="receiveMethod{{ $method->id }}">
                                    {{ $method->name }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="row mb-3" id="deliveryCostContainer" style="display: none;">
                <div class="col-md-6">
                    <label for="delivery_cost" class="form-label">Delivery Cost</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" class="form-control" id="delivery_cost" name="delivery_cost" min="0">
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="form-group">
                    <label for="paymentMethod">Purchase Method</label>
                    <select class="form-select" id="paymentMethod" name="payment_method" required>
                        <option value="" disabled selected>Please select a method</option>
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
                    <label for="due_date">Due Date</label>
                    <input type="date" class="form-control" id="due_date" name="due_date">
                </div>
            </div>

            <button type="submit" class="btn btn-success">Submit Order</button>
        </form>
    </div>
@endsection

@section('javascript')
    <script>
        const expiredActive = {{ $expiredSetting->status }};

        document.addEventListener('DOMContentLoaded', function() {
            const radioButtons = document.querySelectorAll('.receive-method-radio');
            const deliveryCostContainer = document.getElementById('deliveryCostContainer');

            // Check initial state
            const checkedRadio = document.querySelector('.receive-method-radio:checked');
            if (checkedRadio && checkedRadio.dataset.code === 'RE-02') {
                deliveryCostContainer.style.display = 'block';
            }

            // Add event listeners
            radioButtons.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.dataset.code === 'RE-02') {
                        deliveryCostContainer.style.display = 'block';
                    } else {
                        deliveryCostContainer.style.display = 'none';
                        document.getElementById('delivery_cost').value = '';
                    }
                });
            });
        });

        // Initialize Select2 for product dropdown
        $('#productSelect').select2({
            placeholder: "Search and select a product",
            ajax: {
                url: '{{ route('purchases.query') }}',
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

        let purchasedProducts = [];

        // Expiration Date Functions
        function calculateExpirationDate(orderDate, expireDays) {
            if (!expireDays || expireDays <= 0) return 'N/A';

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
                    text: `Expired ${Math.abs(days)} days ago`
                };
            }
            if (days === 0) {
                return {
                    class: 'badge bg-danger',
                    text: 'Expires today'
                };
            }
            if (days <= 7) {
                return {
                    class: 'badge bg-danger',
                    text: `${days} days left`
                };
            }
            if (days <= 30) {
                return {
                    class: 'badge bg-warning',
                    text: `${days} days left`
                };
            }
            return {
                class: 'badge bg-success',
                text: `${days} days left`
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

        // Handle product selection
        $('#productSelect').on('select2:select', function(e) {
            const data = e.params.data;
            const existingRow = $('#itemsTable tbody').find(`tr[data-product-id="${data.id}"]`);

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
                            <input type="number" class="form-control unit-price" value="${data.price.toFixed(2)}" step="500">
                        </div>
                    </td>
                        ${expiredActive === 1 ? 
                        (data.expired === 1 ? `
                                    <td>
                                        <input type="number" class="form-control expire-days" min="0" value="${data.expired_date}" placeholder="Days">
                                    </td>
                                    <td class="expiration-date">N/A</td>
                                         ` : `
                                    <td>Product doesn't support expiration tracking</td>
                                    <td>N/A</td>
                                     `) 
                                 : `
                                    `
                                }
                    <td class="total-price">${data.price.toFixed(2)}</td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
                </tr>`;
                $('#itemsTable tbody').append(newRow);

                const defaultExpireDays = (expiredActive === 1 && data.expired === 1) ? 0 : null;

                purchasedProducts.push({
                    product_id: data.id,
                    quantity: 1,
                    purchase_price: data.price,
                    total_price: data.price,
                    expire_days: defaultExpireDays
                });
            }

            $('#productSelect').val(null).trigger('change');
            updateGrandTotal();
            updateExpirationInfo();
        });

        // Update purchased products array when any field changes
        $('#itemsTable').on('change', '.quantity, .unit-price, .expire-days', function() {
            const row = $(this).closest('tr');
            const productId = row.data('product-id');
            const quantity = parseInt(row.find('.quantity').val()) || 0;
            const unitPrice = parseFloat(row.find('.unit-price').val()) || 0;
            const expireDays = parseInt(row.find('.expire-days').val()) || 0;
            const totalPrice = (quantity * unitPrice).toFixed(2);

            row.find('.total-price').text(totalPrice);

            // Update the purchasedProducts array
            const productIndex = purchasedProducts.findIndex(p => p.product_id == productId);
            if (productIndex !== -1) {
                purchasedProducts[productIndex] = {
                    product_id: productId,
                    quantity: quantity,
                    purchase_price: unitPrice,
                    total_price: totalPrice,
                    expire_days: expireDays
                };
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

        // Form submission
        $('#purchaseOrderForm').on('submit', function(e) {
            // Validate at least one product is selected
            if (purchasedProducts.length === 0) {
                e.preventDefault();
                alert('Please add at least one product');
                return false;
            }

            // Create proper data structure
            const formData = {
                invoice_number: $('#orderNumber').val(),
                order_date: $('#orderDate').val(),
                suppliers: $('#supplierName').val(),
                grand_total: $('#grandTotal').val(),
                purchased_products: purchasedProducts,
                receive_method: $('input[name="receive_method"]:checked').val(),
                payment_method: $('#paymentMethod').val()
            };

            // Only include delivery_cost if RE-02 is selected
            if (formData.receive_method === 'RE-02') {
                formData.delivery_cost = $('#delivery_cost').val();
            }

            // Only include due_date if P-PAY-03 is selected
            if (formData.payment_method === 'P-PAY-03') {
                formData.due_date = $('#due_date').val();
            }

            // Add as hidden field
            $('<input>').attr({
                type: 'hidden',
                name: 'form_data',
                value: JSON.stringify(formData)
            }).appendTo(this);

            return true;
        });

        // Initialize supplier select
        $('#supplierName').select2({
            placeholder: "Search for a supplier",
            allowClear: true,
            ajax: {
                url: '{{ route('findSupplier') }}',
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

        function updateGrandTotal() {
            let grandTotal = 0;
            purchasedProducts.forEach(product => {
                grandTotal += parseFloat(product.total_price);
            });
            $('#grandTotal').val(grandTotal.toFixed(2));
        }

        document.getElementById('paymentMethod').onchange = function() {
            document.getElementById('dueDateContainer').style.display =
                (this.value === 'P-PAY-03') ? 'block' : 'none';
        };
    </script>
@endsection
