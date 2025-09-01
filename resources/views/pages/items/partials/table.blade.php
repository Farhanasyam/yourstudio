<div class="table-responsive p-0">
    <table class="table align-items-center mb-0">
        <thead>
            <tr>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                    Item</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                    Category</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                    Stock</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                    Purchase Price</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                    Selling Price</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                    Status</th>
                <th class="text-secondary opacity-7"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    <td>
                        <div class="d-flex px-2 py-1">
                            <div class="icon icon-shape icon-sm me-3 bg-gradient-warning shadow text-center">
                                <i class="ni ni-box-2 text-white opacity-10"></i>
                            </div>
                            <div class="d-flex flex-column justify-content-center">
                                <h6 class="mb-0 text-sm">{{ $item->name }}</h6>
                                <p class="text-xs text-secondary mb-0">{{ $item->sku }}</p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <p class="text-xs font-weight-bold mb-0">{{ $item->category->name ?? 'N/A' }}</p>
                    </td>
                    <td class="align-middle text-center text-sm">
                        @if($item->stock_quantity <= $item->minimum_stock)
                            <span class="badge badge-sm bg-gradient-danger">{{ $item->stock_quantity }} {{ $item->unit }}</span>
                        @elseif($item->stock_quantity == 0)
                            <span class="badge badge-sm bg-gradient-secondary">{{ $item->stock_quantity }} {{ $item->unit }}</span>
                        @else
                            <span class="badge badge-sm bg-gradient-success">{{ $item->stock_quantity }} {{ $item->unit }}</span>
                        @endif
                    </td>
                    <td class="align-middle text-center">
                        <span class="text-secondary text-xs font-weight-bold">Rp {{ number_format($item->purchase_price, 0, ',', '.') }}</span>
                    </td>
                    <td class="align-middle text-center">
                        <span class="text-secondary text-xs font-weight-bold">Rp {{ number_format($item->selling_price, 0, ',', '.') }}</span>
                    </td>
                    <td class="align-middle text-center text-sm">
                        @if($item->is_active)
                            <span class="badge badge-sm bg-gradient-success">Active</span>
                        @else
                            <span class="badge badge-sm bg-gradient-secondary">Inactive</span>
                        @endif
                    </td>
                    <td class="align-middle">
                        <div class="btn-group" role="group">
                            <a href="{{ route('items.show', $item) }}" class="btn btn-link text-info font-weight-bold text-xs" data-toggle="tooltip" data-original-title="View item">
                                <i class="fas fa-eye text-xs me-1"></i>View
                            </a>
                            <a href="{{ route('items.edit', $item) }}" class="btn btn-link text-secondary font-weight-bold text-xs" data-toggle="tooltip" data-original-title="Edit item">
                                <i class="fas fa-pencil-alt text-xs me-1"></i>Edit
                            </a>
                            <form action="{{ route('items.destroy', $item) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-link text-danger font-weight-bold text-xs" 
                                        onclick="return confirm('Are you sure you want to delete this item?')" 
                                        data-toggle="tooltip" data-original-title="Delete item">
                                    <i class="fas fa-trash text-xs me-1"></i>Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <div class="d-flex flex-column align-items-center">
                            <i class="ni ni-box-2 text-secondary opacity-10" style="font-size: 3rem;"></i>
                            <p class="text-secondary mt-2">No items found</p>
                            @if(!request('search') && !request()->except(['page', 'search']))
                                <a href="{{ route('items.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Create First Item
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
