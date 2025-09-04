@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Items'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                @php
                    $filters = [
                        [
                            'name' => 'category_id',
                            'label' => 'Category',
                            'type' => 'select',
                            'options' => $categories->pluck('name', 'id')->toArray()
                        ],
                        [
                            'name' => 'supplier_id',
                            'label' => 'Supplier',
                            'type' => 'select',
                            'options' => $suppliers->pluck('name', 'id')->toArray()
                        ],
                        [
                            'name' => 'stock_status',
                            'label' => 'Stock Status',
                            'type' => 'select',
                            'options' => [
                                'in_stock' => 'In Stock',
                                'out_of_stock' => 'Out of Stock',
                                'low_stock' => 'Low Stock'
                            ]
                        ],
                        [
                            'name' => 'is_active',
                            'label' => 'Status',
                            'type' => 'select',
                            'options' => [
                                '1' => 'Active',
                                '0' => 'Inactive'
                            ]
                        ],
                        [
                            'name' => 'price_min',
                            'label' => 'Min Price',
                            'type' => 'number',
                            'placeholder' => 'Min price'
                        ],
                        [
                            'name' => 'price_max',
                            'label' => 'Max Price',
                            'type' => 'number',
                            'placeholder' => 'Max price'
                        ]
                    ];
                @endphp
                
                <x-search-form 
                    placeholder="Search items by name, SKU, barcode, category, or supplier..." 
                    :filters="$filters" 
                    :showFilters="true" />
                
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6>Items</h6>
                            <div class="btn-group" role="group">
                                <a href="{{ route('items.import.show') }}" class="btn btn-success btn-sm">
                                    <i class="fas fa-file-excel"></i> Import Excel
                                </a>
                                <a href="{{ route('items.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Add Item
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body px-0 pt-0 pb-2" id="tableContainer">
                        @include('pages.items.partials.table')
                    </div>
                    
                    @if($items->hasPages())
                        <div class="card-footer">
                            <div class="d-flex justify-content-center">
                                {{ $items->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @include('layouts.footers.auth.footer')
    </div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show success messages using SweetAlert
    @if(session('success'))
        showSuccessAlert('{{ session('success') }}');
    @endif
    
    // Show error messages using SweetAlert
    @if(session('error'))
        showErrorAlert('{{ session('error') }}');
    @endif
    
    // Show warning messages using SweetAlert
    @if(session('warning'))
        showWarningAlert('{{ session('warning') }}');
    @endif
    
    // Show info messages using SweetAlert
    @if(session('info'))
        showInfoAlert('{{ session('info') }}');
    @endif
});
</script>
@endsection