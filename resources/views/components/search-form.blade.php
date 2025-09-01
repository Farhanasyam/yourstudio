@props(['placeholder' => 'Search...', 'filters' => [], 'showFilters' => false])

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ request()->url() }}" id="searchForm">
            <div class="row">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text" style="height: 38px; line-height: 1;"><i class="fas fa-search"></i></span>
                        <input type="text" 
                               class="form-control" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="{{ $placeholder }}"
                               autocomplete="off"
                               style="height: 38px; line-height: 1;">
                        <button class="btn btn-primary" type="submit" style="height: 38px; line-height: 1;">
                            <i class="fas fa-search"></i> Search
                        </button>
                        @if(request('search') || request()->except(['page', 'search']))
                            <a href="{{ request()->url() }}" class="btn btn-outline-secondary" style="height: 38px; line-height: 1;">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        @endif
                    </div>
                </div>
                
                @if($showFilters && !empty($filters))
                    <div class="col-md-6">
                        <button class="btn btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" style="height: 38px; line-height: 1;">
                            <i class="fas fa-filter"></i> Filters
                        </button>
                    </div>
                @endif
            </div>
            
            @if($showFilters && !empty($filters))
                <div class="collapse mt-3" id="filterCollapse">
                    <div class="card card-body">
                        <div class="row">
                            @foreach($filters as $filter)
                                <div class="col-md-{{ $filter['width'] ?? '3' }}">
                                    <label for="{{ $filter['name'] }}" class="form-label">{{ $filter['label'] }}</label>
                                    @if($filter['type'] === 'select')
                                        <select class="form-select" name="{{ $filter['name'] }}" id="{{ $filter['name'] }}" style="height: 38px; line-height: 1;">
                                            <option value="">All {{ $filter['label'] }}</option>
                                            @foreach($filter['options'] as $value => $label)
                                                <option value="{{ $value }}" {{ request($filter['name']) == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @elseif($filter['type'] === 'date')
                                        <input type="date" 
                                               class="form-control" 
                                               name="{{ $filter['name'] }}" 
                                               id="{{ $filter['name'] }}"
                                               value="{{ request($filter['name']) }}"
                                               style="height: 38px; line-height: 1;">
                                    @elseif($filter['type'] === 'number')
                                        <input type="number" 
                                               class="form-control" 
                                               name="{{ $filter['name'] }}" 
                                               id="{{ $filter['name'] }}"
                                               placeholder="{{ $filter['placeholder'] ?? '' }}"
                                               value="{{ request($filter['name']) }}"
                                               style="height: 38px; line-height: 1;">
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary" style="height: 38px; line-height: 1;">
                                    <i class="fas fa-filter"></i> Apply Filters
                                </button>
                                <a href="{{ request()->url() }}" class="btn btn-outline-secondary" style="height: 38px; line-height: 1;">
                                    <i class="fas fa-times"></i> Clear All
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('searchForm');
    const tableContainer = document.getElementById('tableContainer');
    const paginationContainer = document.querySelector('.card-footer');
    
    // Auto-submit form when filters change
    const filterInputs = document.querySelectorAll('#searchForm select, #searchForm input[type="date"], #searchForm input[type="number"]');
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            submitFormAjax();
        });
    });
    
    // Handle form submission
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        submitFormAjax();
    });
    
    function submitFormAjax() {
        const formData = new FormData(searchForm);
        const queryString = new URLSearchParams(formData).toString();
        const url = searchForm.action + '?' + queryString;
        
        // Show loading state
        if (tableContainer) {
            tableContainer.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><p class="mt-2">Loading...</p></div>';
        }
        
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            // Create a temporary div to parse the HTML
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            
            // Extract table content
            const newTableContainer = tempDiv.querySelector('#tableContainer');
            if (newTableContainer && tableContainer) {
                tableContainer.innerHTML = newTableContainer.innerHTML;
            }
            
            // Extract pagination
            const newPagination = tempDiv.querySelector('.card-footer');
            if (newPagination && paginationContainer) {
                paginationContainer.innerHTML = newPagination.innerHTML;
            } else if (newPagination && !paginationContainer) {
                // If pagination container doesn't exist, create it
                const card = tableContainer.closest('.card');
                if (card) {
                    const footer = document.createElement('div');
                    footer.className = 'card-footer';
                    footer.innerHTML = newPagination.innerHTML;
                    card.appendChild(footer);
                }
            }
            
            // Update URL without page reload
            window.history.pushState({}, '', url);
        })
        .catch(error => {
            console.error('Error:', error);
            if (tableContainer) {
                tableContainer.innerHTML = '<div class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle fa-2x"></i><p class="mt-2">Error loading data</p></div>';
            }
        });
    }
    
    // Handle pagination clicks
    document.addEventListener('click', function(e) {
        if (e.target.matches('.pagination a')) {
            e.preventDefault();
            const url = e.target.href;
            
            // Show loading state
            if (tableContainer) {
                tableContainer.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><p class="mt-2">Loading...</p></div>';
            }
            
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                
                const newTableContainer = tempDiv.querySelector('#tableContainer');
                if (newTableContainer && tableContainer) {
                    tableContainer.innerHTML = newTableContainer.innerHTML;
                }
                
                const newPagination = tempDiv.querySelector('.card-footer');
                if (newPagination && paginationContainer) {
                    paginationContainer.innerHTML = newPagination.innerHTML;
                }
                
                window.history.pushState({}, '', url);
            })
            .catch(error => {
                console.error('Error:', error);
                if (tableContainer) {
                    tableContainer.innerHTML = '<div class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle fa-2x"></i><p class="mt-2">Error loading data</p></div>';
                }
            });
        }
    });
});
</script>
