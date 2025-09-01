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
                                 <button type="button" class="btn btn-danger btn-sm" id="deleteAllItems" title="Delete all items on this page">
                                     <i class="fas fa-trash-alt"></i> Delete All Items
                                 </button>
                                                                 

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
    const deleteAllBtn = document.getElementById('deleteAllItems');
    
    if (deleteAllBtn) {
        deleteAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Get total items count from the table
            const itemRows = document.querySelectorAll('tbody tr');
            const totalItems = itemRows.length;
            
            if (totalItems === 0) {
                alert('No items to delete');
                return;
            }
            
            if (confirm(`Are you sure you want to delete ALL ${totalItems} items? This action cannot be undone!`)) {
                // Show loading state
                deleteAllBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
                deleteAllBtn.disabled = true;
                
                // Get all item IDs from the table
                const itemIds = [];
                itemRows.forEach(row => {
                    const deleteForm = row.querySelector('form[action*="/items/"]');
                    if (deleteForm) {
                        const action = deleteForm.getAttribute('action');
                        const itemId = action.split('/').pop();
                        if (itemId && !isNaN(itemId)) {
                            itemIds.push(itemId);
                        }
                    }
                });
                
                console.log('Items to delete:', itemIds);
                
                // Send bulk delete request
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                fetch('{{ route("items.bulk-delete") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        item_ids: itemIds
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert(data.message || `Successfully deleted ${data.deleted_count} item(s).`);
                        window.location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to delete items'));
                        resetButton();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting items: ' + error.message);
                    resetButton();
                });
            }
        });
    }
    
    function resetButton() {
        deleteAllBtn.innerHTML = '<i class="fas fa-trash-alt"></i> Delete All Items';
        deleteAllBtn.disabled = false;
    }
});
</script>
@endsection


