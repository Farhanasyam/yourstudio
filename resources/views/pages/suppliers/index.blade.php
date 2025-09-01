@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Suppliers'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                @php
                    $filters = [
                        [
                            'name' => 'items_count',
                            'label' => 'Items Count',
                            'type' => 'select',
                            'options' => [
                                'has_items' => 'Has Items',
                                'no_items' => 'No Items'
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
                        ]
                    ];
                @endphp
                
                <x-search-form 
                    placeholder="Search suppliers by name, contact person, phone, email, or address..." 
                    :filters="$filters" 
                    :showFilters="true" />
                
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6>Suppliers</h6>
                            <a href="{{ route('suppliers.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Add Supplier
                            </a>
                        </div>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2" id="tableContainer">
                        @include('pages.suppliers.partials.table')
                    </div>
                    
                    @if($suppliers->hasPages())
                        <div class="card-footer">
                            <div class="d-flex justify-content-center">
                                {{ $suppliers->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @include('layouts.footers.auth.footer')
    </div>
@endsection
