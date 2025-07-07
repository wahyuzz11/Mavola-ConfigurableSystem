@extends('layouts.index')

@section('content')
    <form method="POST" action="{{ route('products.update', $product->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-md-7 col-lg-6">
                <div class="form-group">
                    <label for="name">Product Name</label>
                    <input type="text" class="form-control" id="name" placeholder="Enter product name" name="name"
                        value="{{ $product->product_name ?? '' }}">
                </div>

                <div class="form-group">
                    <label for="description">Product Description</label>
                    <textarea class="form-control" id="description" rows="5" name="description"
                        placeholder="Enter product description">{{ $product->description }}</textarea>
                </div>

                <div class="form-group">
                    <label for="product_category">Products Category</label>
                    <select class="form-select form-control" id="product_category" name="categories_id">
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}"
                                ${{ $product->categories_id == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if ($inventoryTracking == 'periodic')
                    <div class="form-group">
                        <label for="minimum_stock">Stock</label>
                        <input type="number" class="form-control" id="minimum_stock" placeholder="Enter stock"
                            name="total_stock" min="1" value="{{ $product->total_stock ?? 0 }}">
                    </div>
                @endif



                <div class="form-group">
                    <label for="unit_name">Unit Name</label>
                    <input type="text" class="form-control" id="unit_name" placeholder="Enter unit name" name="unit_name"
                        value="{{ $product->unit_name ?? '' }}">
                </div>

                <div class="form-group">
                    <label for="minimum_stock">Minimum Stock</label>
                    <input type="number" class="form-control" id="minimum_stock" placeholder="Enter minimum stock"
                        name="minimum_total_stock" min="1" value="{{ $product->minimum_total_stock ?? 0 }}">
                </div>

                <div class="form-group mb-4">
                    <label for="file_image" class="form-label">Upload Image</label>
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
                <div class="form-group">
                    <label for="expired_day">Expiration date</label>
                    <input type="number" class="form-control" id="expired_date" name="expired_date_settings" min="1"
                        value="{{ $product->expired_date_settings ?? 0 }}">
                </div>

                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" step="1" class="form-control" id="price" placeholder="Enter price"
                        name="price" min="1" value="{{ $product->price ?? 0 }}">
                </div>

                @if ($cogsMethod == 'avarage' || $inventoryTracking == 'periodic')
                    <div class="form-group">
                        <label for="cost">Cost</label>
                        <input type="number" step="1" class="form-control" id="cost" placeholder="Enter cost"
                            name="cost" min="1" value="{{ $product->cost ?? 0 }}">
                    </div>
                @endif

            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Update</button>
        </div>
    </form>
@endsection
