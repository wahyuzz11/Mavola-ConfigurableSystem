@extends('layouts.index')

@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">
            Debt Histories
        </h3>
        <div class="d-flex">
            <span class="badge bg-warning me-2">Pending: {{ $pendingCount }}</span>
            <span class="badge bg-success me-2">Paid: {{ $paidCount }}</span>
            <span class="badge bg-danger">Late: {{ $lateCount }}</span>
        </div>
    </div>

    <!-- Pending Debts Card -->
    <div class="card mb-4">
        <div class="card-header bg-warning">
            <h5>Pending Debts</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Supplier</th>
                        <th>Bill Date</th>
                        <th>Due Date</th>
                        <th>Amount</th>
                        <th>Purchase Ref</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pendingDebts as $debt)
                        <tr>
                            <td>{{ $debt->supplier->company_name }}</td>
                            <td>{{ \Carbon\Carbon::parse($debt->bill_date)->format('d M Y') }}</td>
                            <td @if ($debt->due_date < now()) class="text-danger" @endif>
                                {{ \Carbon\Carbon::parse($debt->due_date)->format('d M Y') }}
                            </td>
                            <td>Rp {{ number_format($debt->debt_nominal, 0, ',', '.') }}</td>
                            <td>{{ $debt->purchase->invoice_number ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('debts.show', $debt->id) }}" class="btn btn-info btn-sm">View</a>
                                @if ($debt->status == 'pending')
                                    <form action="{{ route('debts.mark-paid', $debt->id) }}" method="POST"
                                        style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">Mark Paid</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No pending debts found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $pendingDebts->links() }}
        </div>
    </div>

    <!-- Paid Debts Card -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5>Paid Debts</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Supplier</th>
                        <th>Bill Date</th>
                        <th>Paid Date</th>
                        <th>Amount</th>
                        <th>Purchase Ref</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($paidDebts as $debt)
                        <tr>
                            <td>{{ $debt->supplier->company_name }}</td>
                            <td>{{ \Carbon\Carbon::parse($debt->bill_date)->format('d M Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($debt->updated_at)->format('d M Y') }}</td>
                            <td>Rp {{ number_format($debt->debt_nominal, 0, ',', '.') }}</td>
                            <td>{{ $debt->purchase->invoice_number ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('debts.show', $debt->id) }}" class="btn btn-info btn-sm">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No paid debts found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $paidDebts->links() }}
        </div>
    </div>

    <!-- Late Debts Card -->
    <div class="card">
        <div class="card-header bg-danger text-white">
            <h5>Late Debts</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Supplier</th>
                        <th>Bill Date</th>
                        <th>Due Date</th>
                        <th>Days Late</th>
                        <th>Amount</th>
                        <th>Purchase Ref</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lateDebts as $debt)
                        @php
                            $daysLate = \Carbon\Carbon::parse($debt->due_date)->diffInDays(now());
                        @endphp
                        <tr>
                            <td>{{ $debt->supplier->company_name }}</td>
                            <td>{{ \Carbon\Carbon::parse($debt->bill_date)->format('d M Y') }}</td>
                            <td class="text-danger">{{ \Carbon\Carbon::parse($debt->due_date)->format('d M Y') }}</td>
                            <td>{{ $daysLate }} days</td>
                            <td>Rp {{ number_format($debt->debt_nominal, 0, ',', '.') }}</td>
                            <td>{{ $debt->purchase->invoice_number ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('debts.show', $debt->id) }}" class="btn btn-info btn-sm">View</a>
                                <form action="{{ route('debts.mark-paid', $debt->id) }}" method="POST"
                                    style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">Mark Paid</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No late debts found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $lateDebts->links() }}
        </div>
    </div>
@endsection
