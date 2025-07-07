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
            <input type="hidden" id="purchasedProductsData" name="purchased_products">

            <!-- Grand Total -->
            <div class="row mb-3">
                <div class="col-md-4 offset-md-8">
                    <label for="grandTotal" class="form-label">Grand Total</label>
                    <div class="input-group"> <span class="input-group-text">Rp</span>
                        <input type="text" class="form-control" id="grandTotal" name="grand_total" readonly>
                    </div>
                </div>
            </div>


            <div class="row mb-3">
                <div class="form-group">
                    <label>Receive Method</label><br>
                    <div class="d-flex">
                        @foreach ($receiveMethods as $index => $method)
                            @if ($activeMethods->count() == 1 && $method->status != 1)
                                @continue
                            @endif
                            <div class="form-check me-3">
                                <input class="form-check-input receive-method-radio" type="radio" name="receive_method"
                                    id="receiveMethod{{ $method->id }}" value="{{ $method->code }}"
                                    {{ $method->status == 1 ? 'checked' : '' }} data-code="{{ $method->code }}" <label
                                    class="form-check-label" for="receiveMethod{{ $method->id }}">
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
                    {{-- min="{{ now()->format('Y-m-d') }}" > --}}
                </div>
            </div>



            <button type="submit" class="btn btn-success">Submit Order</button>
        </form>
    </div>
@endsection

@section('javascript')
    <script>
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
                            stock: product.total_stock
                        }))
                    };
                },
                cache: true
            }
        });

        let purchasedProducts = [];

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
                    <input type="number" class="form-control unit-price" value="${data.price.toFixed(2)}" step="500"></td>
                </div>
                </td>
               <td class="total-price">${data.price.toFixed(2)}</td>
                <td><button type="button" class="btn btn-danger remove-row">Remove</button></td>
            </tr>`;
                $('#itemsTable tbody').append(newRow);

                purchasedProducts.push({
                    product_id: data.id,
                    quantity: 1,
                    purchase_price: data.price,
                    total_price: data.price
                });
            }

            $('#productSelect').val(null).trigger('change');
            updateGrandTotal();
        });

        function formatCurrency(amount) {
            return 'Rp ' + parseFloat(amount).toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function parseCurrency(currencyString) {
            return parseFloat(currencyString.replace(/[^0-9.]/g, ''));
        }

        function formatCurrencyInput(input) {
            input.value = formatCurrency(input.value);
        }

        function clearCurrencyFormat(input) {
            input.value = parseCurrency(input.value);
        }

        // Update purchased products array when quantity changes
        $('#itemsTable').on('change', '.quantity, .unit-price', function() {
            const row = $(this).closest('tr');
            const productId = row.data('product-id');
            const quantity = parseInt(row.find('.quantity').val()) || 0;
            const unitPrice = parseFloat(row.find('.unit-price').val()) || 0;
            const totalPrice = (quantity * unitPrice).toFixed(2);

            row.find('.total-price').text(totalPrice);

            // Update the purchasedProducts array
            const productIndex = purchasedProducts.findIndex(p => p.product_id == productId);
            if (productIndex !== -1) {
                purchasedProducts[productIndex] = {
                    product_id: productId,
                    quantity: quantity,
                    purchase_price: unitPrice,
                    total_price: totalPrice
                };
            }

            updateGrandTotal();
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
