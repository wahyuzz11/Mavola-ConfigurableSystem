@extends('layouts.index')

@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">
            Purchase Settings
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


    <form method="POST" action="{{ route('configuration.updatePurchase') }}">
        @csrf

        <div class="form-group">
            <label>Purchase payment Configuration</label><br>
            <div class="d-flex">
                @foreach ($paymentMethods as $method)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="payment_method[]"
                            id="payment_method_{{ $method->id }}" value="{{ $method->id }}" @checked($method->status == 1)
                            @disabled($method->types == 'mandatory') data-method-code="{{ $method->code }}">
                        <label class="form-check-label" for="payment_method_{{ $method->id }}">
                            {{ $method->name }}
                        </label>
                    </div>
                @endforeach
            </div>
        </div>


        <div class="form-group">
            <label>Shipping method</label><br>
            <div class="d-flex">
                @foreach ($receivingMethods as $method)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="receiving_method[]"
                            id="receiving_method_{{ $method->id }}" value="{{ $method->id }}"
                            @checked($method->status == 1) @disabled($method->types == 'mandatory')>
                        <label class="form-check-label" for="receiving_method_{{ $method->id }}">
                            {{ $method->name }}
                        </label>
                    </div>
                @endforeach


            </div>


        </div>

        <button type="submit" class="btn btn-primary mt-3">Save Configuration</button>

    </form>



    <!-- Debt Warning Modal -->
    <div class="modal fade" id="debtWarningModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Pending Debts Found</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>You cannot deactivate P-PAY-03 payment method while there are pending or late debts.</p>
                    <p>Please settle all debts first before changing this configuration.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="{{ route('debts.index') }}" class="btn btn-primary">
                        Go to Debt Management
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Configuration Confirmation Modal -->
    <div class="modal fade" id="configConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Confirm Configuration Changes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to update these payment and shipping configurations?</p>
                    <p class="text-muted">This change will affect all future purchases.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmConfigSubmit">Confirm Changes</button>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('javascript')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Configuration form submission handler
            const configForm = document.querySelector(
                'form[action="{{ route('configuration.updatePurchase') }}"]');

            configForm.addEventListener('submit', function(e) {
                // Check if P-PAY-03 is being deactivated
                const ppay03Checkbox = document.getElementById(
                    'payment_method_{{ $paymentMethods->firstWhere('code', 'P-PAY-03')->id }}');
                const isDeactivatingPPay03 = ppay03Checkbox && !ppay03Checkbox.checked;

                if (isDeactivatingPPay03) {
                    e.preventDefault();

                    // Check for pending debts via AJAX
                    fetch('{{ route('debts.check-pending') }}')
                        .then(response => response.json())
                        .then(data => {
                            if (data.has_pending_debts) {
                                // Show debt warning modal
                                $('#debtWarningModal').modal('show');
                            } else {
                                // No debts, proceed with confirmation
                                $('#configConfirmModal').modal('show');
                            }
                        });
                } else {
                    // For other changes, show regular confirmation
                    e.preventDefault();
                    $('#configConfirmModal').modal('show');
                }
            });

            // Confirm submission when modal button clicked
            document.getElementById('confirmConfigSubmit').addEventListener('click', function() {
                configForm.submit();
            });
        });
    </script>
@endsection
