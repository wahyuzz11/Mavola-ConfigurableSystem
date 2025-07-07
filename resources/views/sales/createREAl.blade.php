@extends('layouts.index')

@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">Create Sales Order</h3>
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

        <form id="salesOrderForm" method="POST" action="{{ route('sales.store') }}">
            @csrf

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Order Date</label>
                    <input type="date" name="order_date" id="orderDate" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Order Number</label>
                    <input type="text" name="invoice_number" id="orderNumber" class="form-control" readonly
                        value="{{ $invoiceNumber }}">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Customer</label>
                    <select name="customers" id="customerName" class="form-select" required>
                        <option value="">Select Customer</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Add Product</label>
                    <select id="productSelect" class="form-select">
                        <option value="">Select Product</option>
                    </select>
                </div>
            </div>

            <table class="table table-bordered" id="itemsTable">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        @if ($discountMethods->where('code', 'DISC-01')->where('status', 1)->isNotEmpty())
                            <th>Discount (%)</th>
                        @endif
                        <th>Total</th>
                        <th>Action</th>
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
                            <label class="form-check-label" for="enableGlobalDiscount">Apply Global Discount?</label>
                        </div>
                        <input type="number" id="globalDiscountInput" name="global_discount" class="form-control"
                            placeholder="Global Discount (%)" style="display:none;" min="0" max="100"
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
                            <label class="form-check-label" for="enableCashback">Apply Cashback?</label>
                        </div>
                        <input type="number" id="cashbackInput" name="cashback_input" class="form-control"
                            placeholder="Cashback (%)" style="display:none;" min="0" max="100"
                            value="{{ $cashbackDefault }}">
                    </div>
                </div>
            @endif

            <div class="row mb-3">
                <div class="col-md-4 offset-md-8">
                    <div class="mb-2">
                        <label class="form-label">Subtotal</label>
                        <input type="text" id="subtotal" class="form-control" readonly>
                    </div>
                    @if ($discountMethods->where('code', 'DISC-02')->where('status', 1)->isNotEmpty())
                        <div class="mb-2">
                            <label class="form-label">Global Discount</label>
                            <input type="text" id="globalDiscountAmount" class="form-control" readonly>
                        </div>
                    @endif
                    <div class="mb-2">
                        <label class="form-label">Grand Total</label>
                        <input type="text" name="grand_total" id="grandTotal" class="form-control" readonly>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Payment Method</label>
                    <select name="payment_method" id="paymentMethod" class="form-select" required>
                        @foreach ($paymentMethods as $method)
                            <option value="{{ $method->code }}">{{ $method->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Delivery Method</label>
                    <div class="d-flex">
                        @foreach ($shippingMethods as $index => $method)
                            <div class="form-check me-3">
                                <input type="radio" name="delivery_method" class="form-check-input"
                                    value="{{ $method->code }}" id="deliveryMethod{{ $method->id }}"
                                    {{ $index === 0 ? 'checked' : '' }}>
                                <label class="form-check-label"
                                    for="deliveryMethod{{ $method->id }}">{{ $method->name }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <input type="hidden" name="form_data" id="formDataField">
            <button type="submit" class="btn btn-success">Submit Order</button>
        </form>
    </div>
@endsection

@section('javascript')
    <script>
        let saleProducts = [];
        const discountMethods = @json($discountMethods);

        // Check which discount methods are active
        const hasProductDiscount = discountMethods.some(d => d.code === 'DISC-01' && d.status === 1);
        const hasGlobalDiscount = discountMethods.some(d => d.code === 'DISC-02' && d.status === 1);
        const hasCashback = discountMethods.some(d => d.code === 'DISC-03' && d.status === 1);

        // Initialize Select2 for Products - Load all products initially
        $('#productSelect').select2({
            placeholder: "Search and select a product",
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

        // Initialize Select2 for Customers - Load all customers initially
        $('#customerName').select2({
            placeholder: "Search and select customer",
            allowClear: true,
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
                                text: item.text || item.name || item.customer_name
                            };
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 0 // Allow showing all customers without typing
        });

        // Add product to table
        $('#productSelect').on('select2:select', function(e) {
            const data = e.params.data;

            // Check if product already exists
            if (saleProducts.find(p => p.product_id == data.id)) {
                alert('Product already added!');
                $(this).val(null).trigger('change');
                return;
            }

            // Check stock
            if (data.stock <= 0) {
                alert('Product out of stock!');
                $(this).val(null).trigger('change');
                return;
            }

            // Build table row with default discount value
            let defaultDiscount = 0; // You can set this from configuration if needed
            let rowHtml =
                `
                <tr data-product-id="${data.id}">
                    <td>${data.text}</td>
                    <td><input type="number" class="form-control quantity" value="1" min="1" max="${data.stock}"></td>
                    <td><input type="number" class="form-control unit-price" value="${data.price}" step="500" min="0"></td>`;

            if (hasProductDiscount) {
                rowHtml +=
                    `<td><input type="number" class="form-control product-discount" value="${defaultDiscount}" min="0" max="100" step="1"></td>`;
            }

            rowHtml += `
                    <td class="total-price">${data.price}</td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
                </tr>`;

            $('#itemsTable tbody').append(rowHtml);

            // Add to products array
            saleProducts.push({
                product_id: data.id,
                product_name: data.text,
                quantity: 1,
                unit_price: parseFloat(data.price),
                discount_percentage: defaultDiscount,
                total_price: parseFloat(data.price)
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
            const price = parseFloat(row.find('.unit-price').val()) || 0;
            const discountPercentage = hasProductDiscount ? (parseFloat(row.find('.product-discount').val()) || 0) :
                0;

            // Calculate total with discount
            const subtotal = qty * price;
            const discountAmount = subtotal * (discountPercentage / 100);
            const total = subtotal - discountAmount;

            row.find('.total-price').text(total.toFixed(2));

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

            updateTotals();
        });

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
                $('#cashbackInput').toggle(this.checked);
                if (!this.checked) {
                    $('#cashbackInput').val(0);
                }
            });
        }

        // Update totals function
        function updateTotals() {
            let subtotal = 0;

            // Calculate subtotal from all products
            saleProducts.forEach(function(product) {
                subtotal += parseFloat(product.total_price) || 0;
            });

            $('#subtotal').val(subtotal.toFixed(2));

            // Calculate global discount
            let globalDiscountAmount = 0;
            if (hasGlobalDiscount && $('#enableGlobalDiscount').is(':checked')) {
                const globalDiscountPercentage = parseFloat($('#globalDiscountInput').val()) || 0;
                globalDiscountAmount = subtotal * (globalDiscountPercentage / 100);
            }

            $('#globalDiscountAmount').val(globalDiscountAmount.toFixed(2));

            // Calculate grand total
            const grandTotal = subtotal - globalDiscountAmount;
            $('#grandTotal').val(grandTotal.toFixed(2));
        }

        // Form submission
        $('#salesOrderForm').on('submit', function(e) {
            if (saleProducts.length === 0) {
                e.preventDefault();
                alert('Please add at least one product');
                return false;
            }

            // Validate customer selection
            if (!$('#customerName').val()) {
                e.preventDefault();
                alert('Please select a customer');
                return false;
            }

            // Prepare form data
            const formData = {
                order_date: $('#orderDate').val(),
                invoice_number: $('#orderNumber').val(),
                customers: $('#customerName').val(),
                subtotal: parseFloat($('#subtotal').val()) || 0,
                global_discount_percentage: hasGlobalDiscount && $('#enableGlobalDiscount').is(':checked') ?
                    (parseFloat($('#globalDiscountInput').val()) || 0) : 0,
                global_discount_amount: hasGlobalDiscount ? (parseFloat($('#globalDiscountAmount').val()) ||
                    0) : 0,
                grand_total: parseFloat($('#grandTotal').val()) || 0,
                sale_products: saleProducts,
                payment_method: $('#paymentMethod').val(),
                delivery_method: $('input[name="delivery_method"]:checked').val(),
                cashback: hasCashback && $('#enableCashback').is(':checked') ?
                    (parseFloat($('#cashbackInput').val()) || 0) : 0,
                global_discount: hasGlobalDiscount ? (parseFloat($('#globalDiscountAmount').val()) || 0) :
                    0 // Changed from total_discount
            };

            $('#formDataField').val(JSON.stringify(formData));
            return true;
        });

        // Set today's date as default
        document.getElementById('orderDate').valueAsDate = new Date();
    </script>
    @endsection@extends('layouts.index')

@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">Create Sales Order</h3>
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

        <form id="salesOrderForm" method="POST" action="{{ route('sales.store') }}">
            @csrf

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Order Date</label>
                    <input type="date" name="order_date" id="orderDate" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Order Number</label>
                    <input type="text" name="invoice_number" id="orderNumber" class="form-control" readonly
                        value="{{ $invoiceNumber }}">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Customer</label>
                    <select name="customers" id="customerName" class="form-select" required>
                        <option value="">Select Customer</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Add Product</label>
                    <select id="productSelect" class="form-select">
                        <option value="">Select Product</option>
                    </select>
                </div>
            </div>

            <table class="table table-bordered" id="itemsTable">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        @if ($discountMethods->where('code', 'DISC-01')->where('status', 1)->isNotEmpty())
                            <th>Discount (%)</th>
                        @endif
                        <th>Total</th>
                        <th>Action</th>
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
                            <label class="form-check-label" for="enableGlobalDiscount">Apply Global Discount?</label>
                        </div>
                        <input type="number" id="globalDiscountInput" name="global_discount" class="form-control"
                            placeholder="Global Discount (%)" style="display:none;" min="0" max="100"
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
                            <label class="form-check-label" for="enableCashback">Apply Cashback?</label>
                        </div>
                        <input type="number" id="cashbackInput" name="cashback_input" class="form-control"
                            placeholder="Cashback (%)" style="display:none;" min="0" max="100"
                            value="{{ $cashbackDefault }}">
                    </div>
                </div>
            @endif

            <div class="row mb-3">
                <div class="col-md-4 offset-md-8">
                    <div class="mb-2">
                        <label class="form-label">Subtotal</label>
                        <input type="text" id="subtotal" class="form-control" readonly>
                    </div>
                    @if ($discountMethods->where('code', 'DISC-02')->where('status', 1)->isNotEmpty())
                        <div class="mb-2">
                            <label class="form-label">Global Discount</label>
                            <input type="text" id="globalDiscountAmount" class="form-control" readonly>
                        </div>
                    @endif
                    <div class="mb-2">
                        <label class="form-label">Grand Total</label>
                        <input type="text" name="grand_total" id="grandTotal" class="form-control" readonly>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Payment Method</label>
                    <select name="payment_method" id="paymentMethod" class="form-select" required>
                        @foreach ($paymentMethods as $method)
                            <option value="{{ $method->code }}">{{ $method->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Delivery Method</label>
                    <div class="d-flex">
                        @foreach ($shippingMethods as $index => $method)
                            <div class="form-check me-3">
                                <input type="radio" name="delivery_method" class="form-check-input"
                                    value="{{ $method->code }}" id="deliveryMethod{{ $method->id }}"
                                    {{ $index === 0 ? 'checked' : '' }}>
                                <label class="form-check-label"
                                    for="deliveryMethod{{ $method->id }}">{{ $method->name }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <input type="hidden" name="form_data" id="formDataField">
            <button type="submit" class="btn btn-success">Submit Order</button>
        </form>
    </div>
@endsection

@section('javascript')
    <script>
        let saleProducts = [];
        const discountMethods = @json($discountMethods);

        // Check which discount methods are active
        const hasProductDiscount = discountMethods.some(d => d.code === 'DISC-01' && d.status === 1);
        const hasGlobalDiscount = discountMethods.some(d => d.code === 'DISC-02' && d.status === 1);
        const hasCashback = discountMethods.some(d => d.code === 'DISC-03' && d.status === 1);

        // Initialize Select2 for Products - Load all products initially
        $('#productSelect').select2({
            placeholder: "Search and select a product",
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

        // Initialize Select2 for Customers - Load all customers initially
        $('#customerName').select2({
            placeholder: "Search and select customer",
            allowClear: true,
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
                                text: item.text || item.name || item.customer_name
                            };
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 0 // Allow showing all customers without typing
        });

        // Add product to table
        $('#productSelect').on('select2:select', function(e) {
            const data = e.params.data;

            // Check if product already exists
            if (saleProducts.find(p => p.product_id == data.id)) {
                alert('Product already added!');
                $(this).val(null).trigger('change');
                return;
            }

            // Check stock
            if (data.stock <= 0) {
                alert('Product out of stock!');
                $(this).val(null).trigger('change');
                return;
            }

            // Build table row with default discount value
            let defaultDiscount = 0; // You can set this from configuration if needed
            let rowHtml =
                `
                <tr data-product-id="${data.id}">
                    <td>${data.text}</td>
                    <td><input type="number" class="form-control quantity" value="1" min="1" max="${data.stock}"></td>
                    <td><input type="number" class="form-control unit-price" value="${data.price}" step="0.01" min="0"></td>`;

            if (hasProductDiscount) {
                rowHtml +=
                    `<td><input type="number" class="form-control product-discount" value="${defaultDiscount}" min="0" max="100" step="0.01"></td>`;
            }

            rowHtml += `
                    <td class="total-price">${data.price}</td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
                </tr>`;

            $('#itemsTable tbody').append(rowHtml);

            // Add to products array
            saleProducts.push({
                product_id: data.id,
                product_name: data.text,
                quantity: 1,
                unit_price: parseFloat(data.price),
                discount_percentage: defaultDiscount,
                total_price: parseFloat(data.price)
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
            const price = parseFloat(row.find('.unit-price').val()) || 0;
            const discountPercentage = hasProductDiscount ? (parseFloat(row.find('.product-discount').val()) || 0) :
                0;

            // Calculate total with discount
            const subtotal = qty * price;
            const discountAmount = subtotal * (discountPercentage / 100);
            const total = subtotal - discountAmount;

            row.find('.total-price').text(total.toFixed(2));

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

            updateTotals();
        });

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
                $('#cashbackInput').toggle(this.checked);
                if (!this.checked) {
                    $('#cashbackInput').val(0);
                }
            });
        }

        // Update totals function
        function updateTotals() {
            let subtotal = 0;

            // Calculate subtotal from all products
            saleProducts.forEach(function(product) {
                subtotal += parseFloat(product.total_price) || 0;
            });

            $('#subtotal').val(subtotal.toFixed(2));

            // Calculate global discount
            let globalDiscountAmount = 0;
            if (hasGlobalDiscount && $('#enableGlobalDiscount').is(':checked')) {
                const globalDiscountPercentage = parseFloat($('#globalDiscountInput').val()) || 0;
                globalDiscountAmount = subtotal * (globalDiscountPercentage / 100);
            }

            $('#globalDiscountAmount').val(globalDiscountAmount.toFixed(2));

            // Calculate grand total
            const grandTotal = subtotal - globalDiscountAmount;
            $('#grandTotal').val(grandTotal.toFixed(2));
        }

        // Form submission
        $('#salesOrderForm').on('submit', function(e) {
            if (saleProducts.length === 0) {
                e.preventDefault();
                alert('Please add at least one product');
                return false;
            }

            // Validate customer selection
            if (!$('#customerName').val()) {
                e.preventDefault();
                alert('Please select a customer');
                return false;
            }

            // Prepare form data
            const formData = {
                order_date: $('#orderDate').val(),
                invoice_number: $('#orderNumber').val(),
                customers: $('#customerName').val(),
                subtotal: parseFloat($('#subtotal').val()) || 0,
                global_discount_percentage: hasGlobalDiscount && $('#enableGlobalDiscount').is(':checked') ?
                    (parseFloat($('#globalDiscountInput').val()) || 0) : 0,
                global_discount_amount: hasGlobalDiscount ? (parseFloat($('#globalDiscountAmount').val()) ||
                    0) : 0,
                grand_total: parseFloat($('#grandTotal').val()) || 0,
                sale_products: saleProducts,
                payment_method: $('#paymentMethod').val(),
                delivery_method: $('input[name="delivery_method"]:checked').val(),
                cashback: hasCashback && $('#enableCashback').is(':checked') ?
                    (parseFloat($('#cashbackInput').val()) || 0) : 0,
                global_discount: hasGlobalDiscount ? (parseFloat($('#globalDiscountAmount').val()) || 0) :
                    0 // Changed from total_discount
            };

            $('#formDataField').val(JSON.stringify(formData));
            return true;
        });

        // Set today's date as default
        document.getElementById('orderDate').valueAsDate = new Date();
    </script>
@endsection
