<div class="table-responsive p-0">
    <table class="table align-items-center mb-0">
        <thead>
            <tr>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                    Category</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                    Description</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                    Items Count</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                    Created</th>
                <th class="text-secondary opacity-7"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($categories as $category)
                <tr>
                    <td>
                        <div class="d-flex px-2 py-1">
                            <div class="icon icon-shape icon-sm me-3 bg-gradient-primary shadow text-center">
                                <i class="ni ni-tag text-white opacity-10"></i>
                            </div>
                            <div class="d-flex flex-column justify-content-center">
                                <h6 class="mb-0 text-sm">{{ $category->name }}</h6>
                                <p class="text-xs text-secondary mb-0">{{ $category->code }}</p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <p class="text-xs font-weight-bold mb-0">{{ Str::limit($category->description, 50) }}</p>
                    </td>
                    <td class="align-middle text-center text-sm">
                        <span class="badge badge-sm bg-gradient-info">{{ $category->items_count }}</span>
                    </td>
                    <td class="align-middle text-center">
                        <span class="text-secondary text-xs font-weight-bold">{{ $category->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y') }}</span>
                    </td>
                    <td class="align-middle">
                        <div class="btn-group" role="group">
                            <a href="{{ route('categories.edit', $category) }}" class="btn btn-link text-secondary font-weight-bold text-xs" data-toggle="tooltip" data-original-title="Edit category">
                                <i class="fas fa-pencil-alt text-xs me-1"></i>Edit
                            </a>
                            <form id="delete-form-category-{{ $category->id }}" action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-link text-danger font-weight-bold text-xs" 
                                        onclick="deleteConfirmation('delete-form-category-{{ $category->id }}')"
                                        data-toggle="tooltip" data-original-title="Delete category">
                                    <i class="fas fa-trash text-xs me-1"></i>Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-4">
                        <div class="d-flex flex-column align-items-center">
                            <i class="ni ni-tag text-secondary opacity-10" style="font-size: 3rem;"></i>
                            <p class="text-secondary mt-2">No categories found</p>
                            @if(!request('search') && !request()->except(['page', 'search']))
                                <a href="{{ route('categories.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Create First Category
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
