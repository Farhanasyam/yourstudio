<div class="table-responsive p-0">
    <table class="table align-items-center mb-0">
        <thead>
            <tr>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                    Transaction Code</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                    Supplier</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                    Date</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                    Total Amount</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                    Status</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                    Created By</th>
                <th class="text-secondary opacity-7"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($stockIns as $stockIn)
                <tr>
                    <td>
                        <div class="d-flex px-2 py-1">
                            <div class="icon icon-shape icon-sm me-3 bg-gradient-success shadow text-center">
                                <i class="ni ni-cart text-white opacity-10"></i>
                            </div>
                            <div class="d-flex flex-column justify-content-center">
                                <h6 class="mb-0 text-sm">{{ $stockIn->transaction_code }}</h6>
                                <p class="text-xs text-secondary mb-0">{{ $stockIn->notes ?? 'No notes' }}</p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <p class="text-xs font-weight-bold mb-0">{{ $stockIn->supplier->name ?? 'N/A' }}</p>
                        <p class="text-xs text-secondary mb-0">{{ $stockIn->supplier->phone ?? '' }}</p>
                    </td>
                    <td class="align-middle text-center text-sm">
                        <span class="text-secondary text-xs font-weight-bold">
                            {{ $stockIn->transaction_date->format('d M Y') }}
                        </span>
                    </td>
                    <td class="align-middle text-center text-sm">
                        <span class="text-secondary text-xs font-weight-bold">
                            Rp {{ number_format($stockIn->total_amount, 0, ',', '.') }}
                        </span>
                    </td>
                    <td class="align-middle text-center text-sm">
                        <span class="badge badge-sm bg-gradient-{{ $stockIn->status === 'completed' ? 'success' : 'warning' }}">
                            {{ ucfirst($stockIn->status) }}
                        </span>
                    </td>
                    <td class="align-middle text-center text-sm">
                        <span class="text-secondary text-xs font-weight-bold">
                            {{ $stockIn->user->name ?? 'N/A' }}
                        </span>
                    </td>
                    <td class="align-middle">
                        {{-- Inline icon actions: a dropdown here widened the table with its hidden menu --}}
                        <div class="d-flex align-items-center gap-1">
                            <a href="{{ route('stock-in.show', $stockIn) }}" class="btn btn-link text-info p-1 mb-0" title="View">
                                <i class="fas fa-eye text-sm"></i>
                            </a>
                            <a href="{{ route('stock-in.edit', $stockIn) }}" class="btn btn-link text-secondary p-1 mb-0" title="Edit">
                                <i class="fas fa-pencil-alt text-sm"></i>
                            </a>
                            <form id="delete-stock-in-{{ $stockIn->id }}" action="{{ route('stock-in.destroy', $stockIn) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-link text-danger p-1 mb-0" title="Delete"
                                        onclick="deleteConfirmation('delete-stock-in-{{ $stockIn->id }}')">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <div class="d-flex flex-column align-items-center">
                            <i class="ni ni-cart text-secondary opacity-10" style="font-size: 3rem;"></i>
                            <p class="text-secondary mt-2">No stock in transactions found</p>
                            @if(!request('search') && !request()->except(['page', 'search']))
                                <a href="{{ route('stock-in.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Create First Stock In
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
