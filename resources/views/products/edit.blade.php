@extends('layouts.index')

@section('content')
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


    <form method="POST" action="{{ route('products.update', $product->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-md-7 col-lg-6">
                <div class="form-group">
                    <label for="name">Nama Produk</label>
                    <input type="text" class="form-control" id="name" placeholder="Masukkan nama produk"
                        name="name" value="{{ $product->product_name ?? '' }}">
                </div>

                <div class="form-group">
                    <label for="description">Deskripsi Produk</label>
                    <textarea class="form-control" id="description" rows="5" name="description"
                        placeholder="Masukkan deskripsi produk">{{ $product->description }}</textarea>
                </div>

                <div class="form-group">
                    <label for="product_category">Kategori Produk</label>
                    <select class="form-select form-control" id="product_category" name="categories_id">
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}"
                                ${{ $product->categories_id == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
               

                <div class="form-group">
                    <label for="unit_name">Nama Satuan</label>
                    <input type="text" class="form-control" id="unit_name" placeholder="Masukkan nama satuan" name="unit_name"
                        value="{{ $product->unit_name ?? '' }}">
                </div>

                <div class="form-group">
                    <label for="minimum_stock">Stok Minimum</label>
                    <input type="number" class="form-control" id="minimum_stock" placeholder="Masukkan stok minimum"
                        name="minimum_total_stock" min="1" value="{{ $product->minimum_total_stock ?? 0 }}">
                </div>

                <div class="form-group mb-4">
                    <label for="file_image" class="form-label">Unggah Gambar</label>
                    <input type="file" id="file_image" name="file_image"
                        class="form-control @error('file_image') is-invalid @enderror">
                    @error('file_image')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

            </div>
            <div class="col-md-7 col-lg-6">

                @if ($expiredDateSetting != null)
                    <div class="form-group">

                        <h5 class="card-title">Pengaturan Kedaluwarsa Produk</h5>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" @if ($product->expired_date_active == 1) checked @endif
                                value="1" id="expired_active" name="expired_active_setting">
                            <label class="form-check-label" for="expired_active">
                                Aktifkan tanggal kedaluwarsa untuk produk ini
                            </label>
                        </div>


                        <div id="expiration_settings" @if ($product->expired_date_active != 1) style="display: none;" @endif>
                            <label for="expired_date" class="form-label">Pengaturan tanggal kedaluwarsa (hari)</label>
                            <input type="number" class="form-control" id="expired_date" name="expired_date_setting"
                                min="1" placeholder="Masukkan jumlah hari"
                                value="{{ $product->expired_date_setting ?? 0 }}">
                            <div class="form-text">Jumlah hari setelah tanggal pembelian saat produk kedaluwarsa</div>
                        </div>
                    </div>
                @endif

                <div class="form-group">
                    <label for="price">Harga</label>
                    <input type="number" step="1" class="form-control" id="price" placeholder="Masukkan harga"
                        name="price" min="1" value="{{ $product->price ?? 0 }}">
                </div>


            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Perbarui</button>
        </div>
    </form>
@endsection


@section('javascript')
    <script>
        const expiredActive = document.getElementById("expired_active");
        const expiredDateDiv = document.getElementById("expiration_settings");


        expiredActive.addEventListener('change', function() {
            if (this.checked) {
                expiredDateDiv.style.display = 'block';
            } else {
                expiredDateDiv.style.display = 'none';

                document.getElementById('expired_date').value = '';
            }
        });
    </script>
@endsection