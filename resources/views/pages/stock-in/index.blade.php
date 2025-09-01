@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Stock In'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                @php
                    $filters = [
                        [
                            'name' => 'supplier_id',
                            'label' => 'Supplier',
                            'type' => 'select',
                            'options' => $suppliers->pluck('name', 'id')->toArray()
                        ],
                        [
                            'name' => 'start_date',
                            'label' => 'Start Date',
                            'type' => 'date'
                        ],
                        [
                            'name' => 'end_date',
                            'label' => 'End Date',
                            'type' => 'date'
                        ],
                        [
                            'name' => 'status',
                            'label' => 'Status',
                            'type' => 'select',
                            'options' => [
                                'completed' => 'Completed',
                                'pending' => 'Pending'
                            ]
                        ]
                    ];
                @endphp
                
                <x-search-form 
                    placeholder="Search stock in by notes, supplier, user, or items..." 
                    :filters="$filters" 
                    :showFilters="true" />
                
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6>Stock In Transactions</h6>
                            <a href="{{ route('stock-in.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Add Stock In
                            </a>
                        </div>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2" id="tableContainer">
                        @include('pages.stock-in.partials.table')
                    </div>
                    
                    @if($stockIns->hasPages())
                        <div class="card-footer">
                            <div class="d-flex justify-content-center">
                                {{ $stockIns->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
