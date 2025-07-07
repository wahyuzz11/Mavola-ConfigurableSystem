@extends('layouts.index')

@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">
            Create Sales Order
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


        <h2>Sales Order</h2>
        <form id="salesOrderForm" method="POST" action="{{ route('sales.store') }}">
            @csrf
            <!-- Customer and Order Details -->
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
                    <label for="customerName" class="form-label">Customer Name</label>
                    <select id="customerName" class="form-select" required name="customers">
                        <option value="">Select a customer</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="productSelect" class="form-label">Add Product</label>
                    <select id="productSelect" class="form-select" style="width: 100%;">
                        <option value="">Search and select a product</option>
                    </select>
                </div>
            </div>


            <!-- Items Table for Selected Products -->
            <table class="table table-bordered mt-3" id="itemsTable">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Products added from dropdown will appear here -->
                </tbody>
            </table>
            <input type="hidden" id="saleProductsData" name="sale_products">

            <!-- Grand Total -->
            <div class="row mb-3">
                <div class="col-md-4 offset-md-8">
                    <label for="grandTotal" class="form-label">Grand Total</label>
                    <input type="text" class="form-control" id="grandTotal" name="grand_total" readonly>
                </div>
            </div>

            @if ($discStatus == 1)
                <div class="row mb-3">
                    <div class="form-group">
                        <label>Cashback Percentage</label>
                        <div class="form-check-group">
                            @foreach ($discountMethods as $method)
                                @if ($method->status == 1)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="discount_method"
                                            id="discountMethod{{ $method->code }}" value="{{ $method->code }}"
                                            @if ($loop->first) checked @endif>
                                        <label class="form-check-label" for="discountMethod{{ $method->code }}">
                                            {{ $method->name }}
                                        </label>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            
            <div class="row mb-3">
                <div class="form-group">
                    <label for="paymentMethod">Payment Method</label>
                    <select class="form-select" id="paymentMethod" name="payment_method" required>
                        @foreach ($paymentMethods as $method)
                            <option value="{{ $method->code }}">{{ $method->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="form-group">
                    <label>Shipping Method</label><br>
                    <div class="d-flex">
                        @foreach ($shippingMethods as $index => $method)
                            <div class="form-check me-3">
                                <input class="form-check-input" type="radio" name="delivery_method"
                                    id="shippingMethod{{ $method->id }}" value="{{ $method->code }}"
                                    {{ $index === 0 ? 'checked' : '' }}>
                                <label class="form-check-label" for="shippingMethod{{ $method->id }}">
                                    {{ $method->name }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-success">Submit Order</button>
        </form>
    </div>
@endsection


@section('javascript')
    <script>
        // Initialize Select2 for product dropdown
        $('#productSelect').select2({
            placeholder: "Search and select a product",
            ajax: {
                url: '{{ route('sales.query') }}',
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
                            stock: product.total_stock
                        }))
                    };
                },
                cache: true
            }
        });

        let saleProducts = [];

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
                <td><input type="number" class="form-control unit-price" value="${data.price.toFixed(2)}" step="0.01"></td>
                <td class="total-price">${data.price.toFixed(2)}</td>
                <td><button type="button" class="btn btn-danger remove-row">Remove</button></td>
            </tr>`;
                $('#itemsTable tbody').append(newRow);

                saleProducts.push({
                    product_id: data.id,
                    quantity: 1,
                    unit_price: data.price,
                    total_price: data.price
                });
            }

            $('#productSelect').val(null).trigger('change');
            updateGrandTotal();
        });

        // Update sale products array when quantity changes
        $('#itemsTable').on('change', '.quantity, .unit-price', function() {
            const row = $(this).closest('tr');
            const productId = row.data('product-id');
            const quantity = parseInt(row.find('.quantity').val()) || 0;
            const unitPrice = parseFloat(row.find('.unit-price').val()) || 0;
            const totalPrice = (quantity * unitPrice).toFixed(2);

            row.find('.total-price').text(totalPrice);

            // Update the saleProducts array
            const productIndex = saleProducts.findIndex(p => p.product_id == productId);
            if (productIndex !== -1) {
                saleProducts[productIndex] = {
                    product_id: productId,
                    quantity: quantity,
                    unit_price: unitPrice,
                    total_price: totalPrice
                };
            }

            updateGrandTotal();
        });

        // Remove row
        $('#itemsTable').on('click', '.remove-row', function() {
            const row = $(this).closest('tr');
            const productId = row.data('product-id');

            // Remove from saleProducts array
            saleProducts = saleProducts.filter(p => p.product_id != productId);

            row.remove();
            updateGrandTotal();
        });

        // Form submission
        $('#salesOrderForm').on('submit', function(e) {
            // Validate at least one product is selected
            if (saleProducts.length === 0) {
                e.preventDefault();
                alert('Please add at least one product');
                return false;
            }

            // Create proper data structure
            const formData = {
                order_date: $('#orderDate').val(),
                invoice_number: $('#orderNumber').val(),
                customers: $('#customerName').val(),
                grand_total: $('#grandTotal').val(),
                sale_products: saleProducts,
                discount_method: $('#discountMethod').val(),
                payment_method: $('#paymentMethod').val(),
                delivery_method: $('input[name="delivery_method"]:checked').val()
            };

            // Add as hidden field
            $('<input>').attr({
                type: 'hidden',
                name: 'form_data',
                value: JSON.stringify(formData)
            }).appendTo(this);

            return true;
        });

        // Initialize customer select
        $('#customerName').select2({
            placeholder: "Search for a customer",
            allowClear: true,
            ajax: {
                url: '{{ route('findCustomer') }}',
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
            saleProducts.forEach(product => {
                grandTotal += parseFloat(product.total_price);
            });
            $('#grandTotal').val(grandTotal.toFixed(2));
        }
    </script>
@endsection
