@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Categories'])
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
                        ]
                    ];
                @endphp
                
                <x-search-form 
                    placeholder="Search categories by name, description, or code..." 
                    :filters="$filters" 
                    :showFilters="true" />
                
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6>Categories</h6>
                            <a href="{{ route('categories.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Add Category
                            </a>
                        </div>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2" id="tableContainer">
                        @include('pages.categories.partials.table')
                    </div>
                    
                    @if($categories->hasPages())
                        <div class="card-footer">
                            <div class="d-flex justify-content-center">
                                {{ $categories->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @include('layouts.footers.auth.footer')
    </div>
@endsection
