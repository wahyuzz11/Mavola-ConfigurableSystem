@extends('layouts.index')

@section('content')

    <div class="container py-4">
        <h2 class="mb-4">Ringkasan Dashboard</h2>

        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-white shadow p-4 rounded">
                <h4 class="text-gray-700">Total Penjualan</h4>
                <p class="text-xl font-semibold text-green-600">Rp {{ number_format($totalSales, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white shadow p-4 rounded">
                <h4 class="text-gray-700">Total Pembelian</h4>
                <p class="text-xl font-semibold text-red-600">Rp {{ number_format($totalPurchases, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white shadow p-4 rounded">
                <h4 class="text-gray-700">Jumlah Transaksi Penjualan</h4>
                <p class="text-xl font-semibold">{{ $totalSalesTransactions }} transaksi</p>
            </div>
            <div class="bg-white shadow p-4 rounded">
                <h4 class="text-gray-700">Jumlah Transaksi Pembelian</h4>
                <p class="text-xl font-semibold">{{ $totalPurchaseTransactions }} transaksi</p>
            </div>
        </div>

        @if ($inventoryTracking !== 'periodic')
            <div class="bg-yellow-100 p-4 rounded mb-6">
                <h4 class="font-semibold mb-2">Pengingat Expired Batch</h4>
                <p>{{ $expiringBatchCount }} batch mendekati tanggal expired (dalam 7 hari ke depan)</p>
                <ul class="mt-2 list-disc list-inside">
                    @foreach ($expiringSoonBatch as $batch)
                        <li>Batch #{{ $batch->serial_code }} - Expired:
                            {{ \Carbon\Carbon::parse($batch->expired_date)->format('d M Y') }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($inventoryTracking === 'periodic')
            <div class="bg-blue-100 p-4 rounded">
                <h4 class="font-semibold mb-2">Perhitungan Laba (Metode Periodik)</h4>
                <p class="text-lg">Laba Kotor: <span class="text-green-600 font-bold">Rp
                        {{ number_format($profit, 0, ',', '.') }}</span></p>
            </div>
        @endif

    </div>

@endsection
