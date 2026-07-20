@extends('layouts.index')

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <h3 class="fw-bold mb-0">
            <i class="fas fa-boxes-stacked text-primary me-2"></i>
            Daftar Produk
        </h3>
        <a href="{{ route('products.create') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> Tambah Produk
        </a>
    </div>

    <!-- Kotak Pencarian -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                <input type="text" id="productSearch" class="form-control"
                    placeholder="Cari nama produk atau kategori...">
            </div>
        </div>
    </div>

    <!-- Daftar Produk -->
    <div class="row g-4" id="productGrid">
        @forelse ($products as $product)
            <div class="col-xl-4 col-md-6 product-item" data-name="{{ strtolower($product->product_name) }}"
                data-category="{{ strtolower($product->category->name ?? '') }}">
                <div class="card card-post card-round h-100 shadow-sm">
                    <img src="{{ asset('assets/img/product/' . ($product->image ?: 'no_image.jpg')) }}"
                        alt="{{ $product->product_name }}" class="card-img-top" style="height: 200px; object-fit: cover;">
                    <div class="card-body d-flex flex-column">
                        <h3 class="card-title fs-5 mb-1">
                            {{ $product->product_name }}
                        </h3>
                        <p class="card-text text-muted small mb-3">
                            {{ $product->description ?: 'Tidak ada deskripsi' }}
                        </p>

                        <ul class="list-unstyled small mb-3">
                            <li class="mb-1">
                                <strong>Kategori:</strong> {{ $product->category->name ?? 'Tidak ada' }}
                            </li>
                            <li class="mb-1">
                                <strong>Satuan:</strong> {{ $product->unit_name ?? 'Tidak ada' }}
                            </li>
                            <li class="mb-1">
                                <strong>Total Stok:</strong>
                                @php $stock = $product->total_stock ?? 0; @endphp
                                <span class="badge {{ $stock > $product->minimum_total_stock ? 'bg-success' : 'bg-danger' }}">
                                    {{ $stock }} {{ $product->unit_name }}
                                </span>
                            </li>
                            <li>
                                <strong>Harga:</strong>
                                <span class="text-success fw-bold">
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </span>
                            </li>
                        </ul>

                        <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top">
                            <a href="{{ route('products.show', $product->id) }}" class="btn btn-primary btn-sm"
                                title="Ubah Produk">
                                <i class="fas fa-pen"></i> Ubah
                            </a>
                            <a href="{{ route('batches.show', $product->id) }}" class="btn btn-secondary btn-sm"
                                title="Lihat Batch">
                                <i class="fas fa-layer-group"></i> Batch
                            </a>
                            <form action="{{ route('products.destroy', $product->id) }}" method="POST"
                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="Hapus Produk">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body text-center text-muted py-5">
                        <i class="fas fa-box-open fa-2x mb-3 d-block"></i>
                        Belum ada produk yang tersedia
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pesan Jika Hasil Pencarian Kosong -->
    <div class="col-12 d-none" id="noResultMessage">
        <div class="card shadow-sm">
            <div class="card-body text-center text-muted py-5">
                <i class="fas fa-search fa-2x mb-3 d-block"></i>
                Produk tidak ditemukan
            </div>
        </div>
    </div>
@endsection

@section('javascript')
    <script>
        document.getElementById('productSearch').addEventListener('input', function() {
            const keyword = this.value.toLowerCase().trim();
            const items = document.querySelectorAll('.product-item');
            let visibleCount = 0;

            items.forEach(function(item) {
                const name = item.dataset.name || '';
                const category = item.dataset.category || '';
                const match = name.includes(keyword) || category.includes(keyword);

                item.style.display = match ? '' : 'none';
                if (match) visibleCount++;
            });

            document.getElementById('noResultMessage').classList.toggle('d-none', visibleCount !== 0);
        });
    </script>
@endsection
