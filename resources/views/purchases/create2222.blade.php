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
                    <input type="text" class="form-control" id="grandTotal" name="grand_total" readonly>
                </div>
            </div>


            <div class="row mb-3">
                <div class="form-group">
                    <label>Receive Method</label><br>
                    <div class="d-flex">
                        @foreach ($receiveMethods as $index => $method)
                            <div class="form-check me-3">
                                <input class="form-check-input" type="radio" name="receive_method"
                                    id="receiveMethod{{ $method->id }}" value="{{ $method->code }}"
                                    {{ $index === 0 ? 'checked' : '' }}>
                                <label class="form-check-label" for="receiveMethod{{ $method->id }}">
                                    {{ $method->name }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="form-group">
                    <label for="paymentMethod">Payment Method</label>
                    <select class="form-select" id="paymentMethod" name="payment_method" required>
                        @foreach ($purchaseMethods as $method)
                            <option value="{{ $method->code }}">{{ $method->name }}</option>
                        @endforeach
                    </select>
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

        let purchasedProducts = []; // 2D array to store all purchased products

        // Handle product selection
        $('#productSelect').on('select2:select', function(e) {
            const data = e.params.data;
            const existingRow = $('#itemsTable tbody').find(`tr:has(input[value="${data.text}"])`);

            if (existingRow.length > 0) {
                // If the product is already selected, increase the quantity
                const quantityInput = existingRow.find('.quantity');
                const newQuantity = parseInt(quantityInput.val()) + 1; // Increase quantity by 1
                quantityInput.val(newQuantity);
                existingRow.find('.total').val((newQuantity * parseFloat(existingRow.find('.unit-price').val()))
                    .toFixed(2));

                // Update the 2D array with the new quantity
                updatePurchasedProducts();
            } else {
                // Add a new row if the product is not already in the table
                const newRow = `
                    <tr>
                        <input type="hidden" name="productId[]" value="${data.id}"> <!-- Add product ID here -->
                        <td><input type="text" class="form-control" name="productName[]" value="${data.text}" readonly></td>
                        <td><input type="number" class="form-control quantity" name="quantity[]" min="1" value="1" required></td>
                        <td><input type="text" class="form-control unit-price" name="unitPrice[]" value="${data.price.toFixed(2)}"></td>
                        <td><input type="text" class="form-control total" name="total[]" readonly></td>
                        <td><button type="button" class="btn btn-danger remove-row">Remove</button></td>
                    </tr>`;
                $('#itemsTable tbody').append(newRow);

                // Add the new product to the 2D array
                purchasedProducts.push([{
                    productId: data.id,
                    productName: data.text,
                    quantity: 1,
                    unitPrice: data.price.toFixed(2),
                    total: (data.price * 1).toFixed(2)
                }]);

                updatePurchasedProducts(); // Update the 2D array
            }

            $('#productSelect').val(null).trigger('change'); // Clear selection
            updateGrandTotal(); // Update grand total
        });

        // Calculate totals
        function updateGrandTotal() {
            let grandTotal = 0;
            $('#itemsTable tbody .total').each(function() {
                grandTotal += parseFloat($(this).val()) || 0;
            });
            $('#grandTotal').val(grandTotal.toFixed(2));
        }

        // Handle quantity change and total calculation
        $('#itemsTable').on('change', '.quantity, .unit-price', function() {
            const row = $(this).closest('tr');
            const productId = row.data('product-id');
            const quantity = parseInt(row.find('.quantity').val()) || 0;
            const unitPrice = parseCurrency(row.find('.unit-price').val()) || 0;
            const totalPrice = (quantity * unitPrice);

            row.find('.total-price').text(formatCurrency(totalPrice));

            // Update purchased products array
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

        // Remove row and update grand total
        $('#itemsTable').on('click', '.remove-row', function() {
            const row = $(this).closest('tr');
            const productName = row.find('input[name="productName[]"]').val();
            $(this).closest('tr').remove();

            // Remove the product from the 2D array
            purchasedProducts = purchasedProducts.filter(product => product[0].productName !== productName);

            updateGrandTotal();
        });

        // Update the 2D array with current table data
        function updatePurchasedProducts() {
            purchasedProducts = [];
            $('#itemsTable tbody tr').each(function() {
                const row = $(this);
                const product = {
                    productId: row.find('input[name="productId[]"]').val(),
                    productName: row.find('input[name="productName[]"]').val(),
                    quantity: row.find('.quantity').val(),
                    unitPrice: row.find('.unit-price').val(),
                    total: row.find('.total').val()
                };

                purchasedProducts.push([product]);
            });
        }

        // Initialize Select2 for supplier dropdown
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


        // Serialize the purchased products array and send it to the Laravel controller
        // $('#purchaseOrderForm').submit(function(e) {
        //     // Prevent the default form submission
        //     // e.preventDefault();
        //     // Serialize the purchasedProducts array as JSON and set it in the hidden input field
        //     $('#purchasedProductsData').val(JSON.stringify(purchasedProducts));

        //     // Now submit the form normally
        //     this.submit();
        // });
    </script>
@endsection
