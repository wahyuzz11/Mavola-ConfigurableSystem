@extends('layouts.index')

@section('content')
    <div class="page-header mb-4">
        <h3 class="fw-bold mb-3">
            Sale Settings
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
            <label class="fw-semibold mb-3">Payment Configuration</label>
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
            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" id="discount_status" name="discount_status" value="1"
                    @checked($discStatus == 1)>
                <label class="form-check-label" for="discount_status">
                    Enable Discount System
                </label>
            </div>

            <!-- Discount Information -->
            <div class="alert alert-info mb-4" style="font-size: 14px;">
                <strong>Discount Information:</strong><br>
                • <strong>Diskon Produk Tertentu:</strong> Allows setting individual discount percentages for specific
                products. This is configured per product in the product management section.<br>
                • <strong>Global Discount:</strong> Applies a percentage discount to all products in the store. Enter
                percentage value (e.g., 10 for 10% discount).<br>
                • <strong>Cashback:</strong> Provides cashback percentage to customers after purchase. Enter percentage
                value (e.g., 5 for 5% cashback).<br>
                <br>
                <strong>Note:</strong> <span class="text-danger">Diskon Produk Tertentu and Global Discount cannot be
                    activated simultaneously.</span> Only one discount type can be active at a time.
            </div>

            <div class="d-flex flex-column">
                @foreach ($discountMethods as $method)
                    <div class="form-check mb-3">
                        <div class="d-flex align-items-center">
                            <input class="form-check-input discount-method-checkbox" type="checkbox"
                                name="discount_method[]" id="discount_method_{{ $method->id }}"
                                value="{{ $method->id }}" @checked($method->status == 1) @disabled(!$discStatus)
                                data-method-id="{{ $method->id }}" data-method-code="{{ $method->code }}">

                            <label class="form-check-label ms-2" for="discount_method_{{ $method->id }}">
                                {{ $method->name }}
                            </label>

                            @if ($method->status == 1 && $discStatus && in_array($method->code, ['DISC-02', 'DISC-03']))
                                <div class="input-group ms-3" style="width: 150px;">
                                    <input type="number" class="form-control discount-value"
                                        name="discount_value[{{ $method->id }}]" id="discount_value_{{ $method->id }}"
                                        placeholder="Percentage" min="0" max="100" step="0.01"
                                        @disabled(!$discStatus) value="{{ $method->value ?? 0 }}">
                                    <span class="input-group-text">%</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="form-group mb-5">
            <label class="fw-semibold mb-3">Shipping method</label>
            <div class="d-flex flex-wrap gap-3">
                @foreach ($shippingMethods as $method)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="shipping_method[]"
                            id="shipping_method_{{ $method->id }}" value="{{ $method->id }}"
                            @checked($method->status == 1) @disabled($method->types == 'mandatory')>
                        <label class="form-check-label" for="shipping_method_{{ $method->id }}">
                            {{ $method->name }}
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        <button type="submit" class="btn btn-primary mt-4">Save Configuration</button>
    </form>
@endsection

@section('javascript')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const discountStatusCheckbox = document.getElementById('discount_status');
            const discountMethodCheckboxes = document.querySelectorAll('.discount-method-checkbox');

            // Function to handle mutual exclusivity between global and product-specific discounts
            function handleDiscountMutualExclusivity() {
                discountMethodCheckboxes.forEach(checkbox => {
                    const methodCode = checkbox.dataset.methodCode;

                    checkbox.addEventListener('change', function() {
                        if (this.checked) {
                            // If global discount is selected, uncheck product-specific discount
                            if (methodCode === 'DISC-02') {
                                const productDiscountCheckbox = document.querySelector(
                                    '[data-method-code="DISC-01"]');
                                if (productDiscountCheckbox && productDiscountCheckbox.checked) {
                                    productDiscountCheckbox.checked = false;
                                }
                            }

                            // If product-specific discount is selected, uncheck global discount
                            if (methodCode === 'DISC-01') {
                                const globalDiscountCheckbox = document.querySelector(
                                    '[data-method-code="DISC-02"]');
                                if (globalDiscountCheckbox && globalDiscountCheckbox.checked) {
                                    globalDiscountCheckbox.checked = false;
                                    // Also disable and clear its value input
                                    const globalValueInput = document.getElementById(
                                        `discount_value_${globalDiscountCheckbox.dataset.methodId}`
                                        );
                                    if (globalValueInput) {
                                        globalValueInput.disabled = true;
                                        globalValueInput.value = '';
                                    }
                                }
                            }
                        }
                    });
                });
            }

            // Function to handle discount main status toggle
            function handleDiscountStatusToggle() {
                discountStatusCheckbox.addEventListener('change', function() {
                    const isEnabled = this.checked;

                    discountMethodCheckboxes.forEach(checkbox => {
                        checkbox.disabled = !isEnabled;
                        if (!isEnabled) {
                            checkbox.checked = false;
                        }

                        // Handle value inputs only for global and cashback discounts
                        const methodId = checkbox.dataset.methodId;
                        const methodCode = checkbox.dataset.methodCode;
                        if (['DISC-02', 'DISC-03'].includes(methodCode)) {
                            const valueInput = document.getElementById(
                            `discount_value_${methodId}`);
                            if (valueInput) {
                                valueInput.disabled = !isEnabled || !checkbox.checked;
                                if (!isEnabled) {
                                    valueInput.value = '';
                                }
                            }
                        }
                    });
                });
            }

            // Enable/disable discount value inputs based on checkbox state (only for global and cashback)
            function handleDiscountValueInputs() {
                discountMethodCheckboxes.forEach(checkbox => {
                    const methodId = checkbox.dataset.methodId;
                    const methodCode = checkbox.dataset.methodCode;

                    // Only handle value inputs for global and cashback discounts
                    if (['DISC-02', 'DISC-03'].includes(methodCode)) {
                        const valueInput = document.getElementById(`discount_value_${methodId}`);

                        if (valueInput) {
                            // Set initial state
                            valueInput.disabled = !checkbox.checked || checkbox.disabled;

                            // Add change listener
                            checkbox.addEventListener('change', function() {
                                valueInput.disabled = !this.checked || this.disabled;
                                if (!this.checked) {
                                    valueInput.value = '';
                                }
                            });
                        }
                    }
                });
            }

            // Initialize all functions
            handleDiscountMutualExclusivity();
            handleDiscountStatusToggle();
            handleDiscountValueInputs();
        });
    </script>
@endsection