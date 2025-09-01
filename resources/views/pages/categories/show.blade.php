@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Category Details'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6>Category Details</h6>
                            <div>
                                <a href="{{ route('categories.edit', $category) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="{{ route('categories.index') }}" class="btn btn-secondary btn-sm ms-2">
                                    <i class="fas fa-arrow-left"></i> Back to Categories
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-uppercase text-body text-xs font-weight-bolder">Basic Information</h6>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="form-control-label">Name</label>
                                            <p class="form-control-static">{{ $category->name }}</p>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="form-control-label">Code</label>
                                            <p class="form-control-static">{{ $category->code ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="form-control-label">Description</label>
                                            <p class="form-control-static">{{ $category->description ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-uppercase text-body text-xs font-weight-bolder">Statistics</h6>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="form-control-label">Total Items</label>
                                            <p class="form-control-static">
                                                <span class="badge badge-sm bg-gradient-success">{{ $category->items->count() }} items</span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="form-control-label">Created At</label>
                                            <p class="form-control-static">{{ $category->created_at->format('d M Y, H:i') }}</p>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="form-control-label">Last Updated</label>
                                            <p class="form-control-static">{{ $category->updated_at->format('d M Y, H:i') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        @if($category->items->count() > 0)
                            <hr class="horizontal dark">
                            <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Items in this Category</h6>
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Item</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">SKU</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Stock</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($category->items as $item)
                                            <tr>
                                                <td>
                                                    <div class="d-flex px-2 py-1">
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm">{{ $item->name }}</h6>
                                                            <p class="text-xs text-secondary mb-0">{{ Str::limit($item->description, 50) }}</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">{{ $item->sku }}</p>
                                                </td>
                                                <td class="align-middle text-center text-sm">
                                                    @if($item->stock_quantity <= $item->minimum_stock && $item->stock_quantity > 0)
                                                        <span class="badge badge-sm bg-gradient-warning">{{ $item->stock_quantity }} {{ $item->unit }}</span>
                                                    @elseif($item->stock_quantity <= 0)
                                                        <span class="badge badge-sm bg-gradient-danger">Out of Stock</span>
                                                    @else
                                                        <span class="badge badge-sm bg-gradient-success">{{ $item->stock_quantity }} {{ $item->unit }}</span>
                                                    @endif
                                                </td>
                                                <td class="align-middle text-center">
                                                    <span class="text-secondary text-xs font-weight-bold">Rp {{ number_format($item->selling_price, 0, ',', '.') }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @include('layouts.footers.auth.footer')
    </div>
@endsection
