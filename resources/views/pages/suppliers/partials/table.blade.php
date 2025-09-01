@if($suppliers->count() > 0)
    <div class="table-responsive p-0">
        <table class="table align-items-center mb-0">
            <thead>
                <tr>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Supplier</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Contact Info</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Address</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Items Count</th>
                    <th class="text-secondary opacity-7"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($suppliers as $supplier)
                    <tr>
                        <td>
                            <div class="d-flex px-2 py-1">
                                <div class="icon icon-shape icon-sm me-3 bg-gradient-info shadow text-center">
                                    <i class="fas fa-truck text-white opacity-10"></i>
                                </div>
                                <div class="d-flex flex-column justify-content-center">
                                    <h6 class="mb-0 text-sm">{{ $supplier->name }}</h6>
                                    <p class="text-xs text-secondary mb-0">{{ $supplier->contact_person ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <p class="text-xs font-weight-bold mb-0">{{ $supplier->email ?? 'N/A' }}</p>
                            <p class="text-xs text-secondary mb-0">{{ $supplier->phone ?? 'N/A' }}</p>
                        </td>
                        <td>
                            <p class="text-xs font-weight-bold mb-0">{{ Str::limit($supplier->address, 50) ?? 'N/A' }}</p>
                        </td>
                        <td>
                            <span class="badge badge-sm bg-gradient-success">{{ $supplier->items_count ?? 0 }} items</span>
                        </td>
                        <td class="align-middle">
                            <div class="btn-group" role="group">
                                <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-link text-info font-weight-bold text-xs" data-toggle="tooltip" data-original-title="View supplier">
                                    <i class="fas fa-eye text-xs me-1"></i>View
                                </a>
                                <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-link text-secondary font-weight-bold text-xs" data-toggle="tooltip" data-original-title="Edit supplier">
                                    <i class="fas fa-pencil-alt text-xs me-1"></i>Edit
                                </a>
                                <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-link text-danger font-weight-bold text-xs" 
                                            onclick="return confirm('Are you sure you want to delete this supplier?')" 
                                            data-toggle="tooltip" data-original-title="Delete supplier">
                                        <i class="fas fa-trash text-xs me-1"></i>Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="text-center py-4">
        <div class="icon icon-shape icon-lg bg-gradient-primary shadow text-center border-radius-lg">
            <i class="fas fa-truck opacity-10"></i>
        </div>
        <h6 class="mt-4">No Suppliers Found</h6>
        <p class="text-sm text-secondary">
            @if(request('search') || request()->except(['page', 'search']))
                No suppliers match your search criteria.
            @else
                Get started by adding your first supplier.
            @endif
        </p>
        @if(!request('search') && !request()->except(['page', 'search']))
            <a href="{{ route('suppliers.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add First Supplier
            </a>
        @endif
    </div>
@endif
