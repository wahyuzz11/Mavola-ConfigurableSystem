@extends('layouts.index')

@section('content')
    <div class="page-header mb-4">
        <h3 class="fw-bold mb-3">
            Inventory Settings
        </h3>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa fa-exclamation-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa fa-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Inventory Method Section --}}
    <form method="POST" action="{{ route('configuration.updateInventory') }}">
        @csrf

        <div class="form-group mb-5">
            <label class="fw-semibold mb-3">Inventory Method</label>
            <div class="d-flex flex-wrap gap-3 mb-3">
                @foreach ($inventoryMethods as $method)
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="inventory_method"
                            id="inventory_method_{{ $method->id }}" value="{{ $method->id }}"
                            @if ($method->status == 1) checked @endif>
                        <label class="form-check-label" for="inventory_method_{{ $method->id }}">
                            {{ $method->name }}
                        </label>
                    </div>
                @endforeach
            </div>

            <small class="text-muted d-block">
                <strong>Perpetual</strong>: Stock is updated in real time for every purchase and sale. Product batches are
                recorded and tracked individually.<br>
                <strong>Periodic</strong>: Stock is calculated at the end of a period. Transactions are recorded but stock
                is only updated during stock reconciliation.
            </small>
        </div>

        {{-- COGS Method Section --}}
        @if ($activeInventoryTracking->code == 'INV-T-01')
            <div class="form-group mb-5">
                {{-- <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="enable_cogs" name="enable_cogs" value="1"
                        @checked($activeCogsConfig->status == 1)>
                    <label class="form-check-label" for="enable_cogs">
                        Enable COGS Calculation
                    </label>
                </div> --}}

                {{-- @if ($activeCogsConfig->status == 1) --}}
                <label class="fw-semibold mb-3">COGS Configuration</label>
                <div class="d-flex flex-column mb-4">
                    @foreach ($cogsMethods as $method)
                        <div class="form-check mb-3">
                            <div class="d-flex align-items-center">
                                <input class="form-check-input" type="radio" name="cogs_method"
                                    id="cogs_method_{{ $method->id }}" value="{{ $method->id }}"
                                    @checked($method->status == 1) @disabled(!$activeCogsConfig->status)
                                    data-method-id="{{ $method->id }}">

                                <label class="form-check-label ms-2" for="cogs_method_{{ $method->id }}">
                                    {{ $method->name }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>


                <div class="form-check">

                    <input class="form-check-input" type="checkbox" id="expired_status" name="expired_status" value="1"
                        @checked($activeExpireConfig->status == 1)>
                    <label class="form-check-label" for="expired_status">
                        Enable Expired date settings for products
                    </label>
                </div>

                <br>
                <br>

                <small class="text-muted d-block mb-3">
                    <strong>FIFO</strong> (First In First Out): The oldest purchase batch will be used first.<br>
                    <strong>LIFO</strong> (Last In First Out): The most recent purchase batch will be used first.<br>
                    <strong>Average</strong>: The cost is recalculated each time based on the weighted average of
                    purchases.<br>
                    <strong>Standard</strong>: The cost is manually inserted by user.<br>
                </small>

                <small class="text-warning d-block">
                    Note: When using FIFO or LIFO, the system will determine the batch order during sales based on
                    <u>purchase date</u>, not product cost. Cost values are informational only and not used to calculate
                    profit.
                </small>
                {{-- @endif --}}
            </div>
        @endif

        <button type="submit" class="btn btn-primary mt-4">Save Configuration</button>
    </form>
@endsection
