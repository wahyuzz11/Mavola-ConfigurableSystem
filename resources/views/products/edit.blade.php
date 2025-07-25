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

                @if ($expiredDateSetting != null && $inventoryTracking == 'perpetual')
                    <div class="form-group">

                        <h5 class="card-title">Product Expiration Settings</h5>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" @if ($product->expired_date_active == 1) checked @endif
                                value="1" id="expired_active" name="expired_active_setting">
                            <label class="form-check-label" for="expired_active">
                                Activate expiration date for this product
                            </label>
                        </div>

                        
                        <div id="expiration_settings" @if ($product->expired_date_active != 1) style="display: none;" @endif >
                            <label for="expired_date" class="form-label">Expiration date settings (days)</label>
                            <input type="number" class="form-control" id="expired_date" name="expired_date_setting"
                                min="1" placeholder="Enter number of days"
                                value="{{ $product->expired_date_setting ?? 0 }}">
                            <div class="form-text">Number of days after purchase date when the product expires</div>
                        </div>
                    </div>
                @endif

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


    {{-- <script>
        // Get references to the checkbox and the expiration settings div
        const expiredActiveCheckbox = document.getElementById('expired_active');
        const expirationSettingsDiv = document.getElementById('expiration_settings');

        // Add event listener to the checkbox
        expiredActiveCheckbox.addEventListener('change', function() {
            if (this.checked) {
                // Show the expiration settings div
                expirationSettingsDiv.style.display = 'block';
            } else {
                // Hide the expiration settings div
                expirationSettingsDiv.style.display = 'none';
                // Optional: Clear the input value when hiding
                document.getElementById('expired_date').value = '';
            }
        });
    </script> --}}
@endsection
