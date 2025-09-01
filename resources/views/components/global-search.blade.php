<div class="global-search-container">
    <div class="input-group">
        <span class="input-group-text">
            <i class="fas fa-search"></i>
        </span>
        <input type="text" 
               class="form-control" 
               id="globalSearchInput" 
               placeholder="Search items, categories, suppliers, transactions..."
               autocomplete="off">
        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="fas fa-filter"></i>
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#" data-type="all">All</a></li>
            <li><a class="dropdown-item" href="#" data-type="items">Items</a></li>
            <li><a class="dropdown-item" href="#" data-type="categories">Categories</a></li>
            <li><a class="dropdown-item" href="#" data-type="suppliers">Suppliers</a></li>
            <li><a class="dropdown-item" href="#" data-type="transactions">Transactions</a></li>
            <li><a class="dropdown-item" href="#" data-type="barcodes">Barcodes</a></li>
        </ul>
    </div>
    
    <!-- Search Results Dropdown -->
    <div class="search-results-dropdown" id="searchResults" style="display: none;">
        <div class="search-results-header">
            <h6 class="mb-0">Search Results</h6>
            <button type="button" class="btn-close" id="closeSearchResults"></button>
        </div>
        <div class="search-results-body" id="searchResultsBody">
            <!-- Results will be populated here -->
        </div>
    </div>
</div>

<style>
.global-search-container {
    position: relative;
    width: 300px;
}

.search-results-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    z-index: 1050;
    max-height: 400px;
    overflow-y: auto;
}

.search-results-header {
    display: flex;
    justify-content: between;
    align-items: center;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #dee2e6;
    background-color: #f8f9fa;
}

.search-results-body {
    padding: 0;
}

.search-result-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f1f3f4;
    cursor: pointer;
    transition: background-color 0.15s ease-in-out;
}

.search-result-item:hover {
    background-color: #f8f9fa;
}

.search-result-item:last-child {
    border-bottom: none;
}

.search-result-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.75rem;
    color: white;
}

.search-result-content {
    flex: 1;
}

.search-result-title {
    font-weight: 600;
    margin-bottom: 0.25rem;
    color: #344767;
}

.search-result-subtitle {
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
}

.search-result-description {
    font-size: 0.75rem;
    color: #adb5bd;
}

.search-result-type {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    background-color: #e9ecef;
    color: #6c757d;
    text-transform: uppercase;
    font-weight: 600;
}

.no-results {
    padding: 2rem 1rem;
    text-align: center;
    color: #6c757d;
}

.loading {
    padding: 1rem;
    text-align: center;
    color: #6c757d;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('globalSearchInput');
    const searchResults = document.getElementById('searchResults');
    const searchResultsBody = document.getElementById('searchResultsBody');
    const closeButton = document.getElementById('closeSearchResults');
    let searchTimeout;
    let currentSearchType = 'all';
    
    // Set search type from dropdown
    document.querySelectorAll('.dropdown-item[data-type]').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            currentSearchType = this.dataset.type;
            this.closest('.dropdown-menu').previousElementSibling.textContent = 
                this.textContent === 'All' ? '<i class="fas fa-filter"></i>' : this.textContent;
        });
    });
    
    // Search input handler
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        clearTimeout(searchTimeout);
        
        if (query.length < 2) {
            hideSearchResults();
            return;
        }
        
        searchTimeout = setTimeout(() => {
            performSearch(query);
        }, 300);
    });
    
    // Close search results
    closeButton.addEventListener('click', hideSearchResults);
    
    // Hide results when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.closest('.global-search-container').contains(e.target)) {
            hideSearchResults();
        }
    });
    
    function performSearch(query) {
        showLoading();
        
        fetch(`/api/search/global?q=${encodeURIComponent(query)}&type=${currentSearchType}`)
            .then(response => response.json())
            .then(data => {
                displayResults(data);
            })
            .catch(error => {
                console.error('Search error:', error);
                showError();
            });
    }
    
    function showLoading() {
        searchResultsBody.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Searching...</div>';
        showSearchResults();
    }
    
    function showError() {
        searchResultsBody.innerHTML = '<div class="no-results">Error occurred while searching</div>';
        showSearchResults();
    }
    
    function displayResults(results) {
        if (results.length === 0) {
            searchResultsBody.innerHTML = '<div class="no-results">No results found</div>';
            showSearchResults();
            return;
        }
        
        const html = results.map(result => `
            <div class="search-result-item" onclick="window.location.href='${result.url}'">
                <div class="search-result-icon ${result.color}">
                    <i class="${result.icon}"></i>
                </div>
                <div class="search-result-content">
                    <div class="search-result-title">${result.title}</div>
                    <div class="search-result-subtitle">${result.subtitle}</div>
                    <div class="search-result-description">${result.description}</div>
                </div>
                <div class="search-result-type">${result.type}</div>
            </div>
        `).join('');
        
        searchResultsBody.innerHTML = html;
        showSearchResults();
    }
    
    function showSearchResults() {
        searchResults.style.display = 'block';
    }
    
    function hideSearchResults() {
        searchResults.style.display = 'none';
    }
});
</script>
