@extends('layouts.index')

@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">
            Products List
        </h3>
    </div>

    @if ($inventoryTracking == 'periodic')
        <div class="page-header">
            <div class="ms-md-auto py-2 py-md-0">
                <a href="{{ route('purchases.create') }}" class="btn btn-primary btn-round">Recalculate Stock</a>
            </div>
        </div>
    @endif



    <h4 class="card-title mt-3">Search Place holder</h4>
    <div class="row">
        @foreach ($products as $product)
            <div class="col-xl-4">
                <div class="card card-post card-round">
                    <img src="{{ asset('assets/img/product/' . ($product->image ?: 'no_image.jpg')) }}"
                        alt="{{ $product->product_name }}" style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h3 class="card-title">
                            <a href="#"> {{ $product->product_name }} </a>
                        </h3>
                        <p class="card-text">
                            {{ $product->description }}
                        </p>
                        <p class="card-text">
                            <strong>Category:</strong> {{ $product->category->name ?? 'N/A' }}<br>
                            <strong>Unit:</strong> {{ $product->unit_name ?? 'N/A' }}<br>
                            <strong>Stock:</strong> {{ $product->total_stock ?? 0 }}<br>
                            <strong>Minimum Stock:</strong> {{ $product->minimum_total_stock ?? 0 }}<br>
                            <strong>Price:</strong> Rp. {{ number_format($product->price, 0, ',', '.') }}
                        </p>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('products.show', $product->id) }}" class="btn btn-primary">Edit</a>
                            <a href="{{ route('batches.show', $product->id) }}" class="btn btn-secondary">Batch</a>
                            <form action="{{ route('products.destroy', $product->id) }}" method="POST"
                                onsubmit="return confirm('Are you sure you want to delete this product?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>

                        </div>

                    </div>
                </div>
            </div>
        @endforeach
    </div>


    </div>
@endsection
