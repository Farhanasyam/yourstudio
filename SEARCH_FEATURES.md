# Search Features Documentation

## Overview
This document describes the comprehensive search functionality implemented across the YourStudio inventory management system. The search features include both individual page searches and a global search system.

## Features Implemented

### 1. Individual Page Search Features

#### Categories Search
- **Search Fields**: Name, description, code
- **Filters**: Items count (has items, no items)
- **Location**: `/categories`
- **Controller**: `CategoryController@index`

#### Items Search
- **Search Fields**: Name, description, SKU, barcode, category name, supplier name
- **Filters**: 
  - Category
  - Supplier
  - Stock status (in stock, out of stock, low stock)
  - Active status
  - Price range (min/max)
- **Location**: `/items`
- **Controller**: `ItemController@index`

#### Suppliers Search
- **Search Fields**: Name, contact person, phone, email, address
- **Filters**: 
  - Items count (has items, no items)
  - Active status
- **Location**: `/suppliers`
- **Controller**: `SupplierController@index`

#### Sales/Transactions Search
- **Search Fields**: Transaction number, customer name, phone, cashier name, item names/SKUs
- **Filters**: 
  - Date range (start date, end date)
- **Location**: `/sales`
- **Controller**: `SaleController@index`

#### Stock In Search
- **Search Fields**: Notes, supplier name, user name, item names/SKUs
- **Filters**: 
  - Supplier
  - Date range
  - Status
- **Location**: `/stock-in`
- **Controller**: `StockInController@index`

#### Barcodes Search
- **Search Fields**: Barcode value, barcode type, item name/SKU, creator name
- **Filters**: 
  - Barcode type
  - Active status
  - Print status
  - Item
- **Location**: `/barcodes`
- **Controller**: `BarcodeController@index`

#### User Management Search
- **Search Fields**: Name, email
- **Filters**: 
  - Role
  - Approval status
  - Active status
- **Location**: `/user-management`
- **Controller**: `UserManagementController@index`

### 2. Global Search System

#### Global Search Controller
- **Controller**: `SearchController`
- **Methods**:
  - `globalSearch()` - Search across all entities
  - `quickItemSearch()` - Quick search for items (used in kasir)

#### Global Search Features
- **Searchable Entities**: Items, Categories, Suppliers, Transactions, Barcodes
- **Search Types**: All, Items, Categories, Suppliers, Transactions, Barcodes
- **Results**: Clickable results with icons and descriptions
- **API Endpoint**: `/api/search/global`

#### Quick Item Search
- **Purpose**: Fast item lookup for kasir operations
- **Search Fields**: Name, SKU, barcode
- **Filters**: Active items only
- **API Endpoint**: `/api/search/items`

### 3. Search Components

#### Search Form Component
- **File**: `resources/views/components/search-form.blade.php`
- **Features**:
  - Text search input
  - Collapsible filters section
  - Auto-submit on filter change
  - Clear search functionality
  - Responsive design

#### Global Search Component
- **File**: `resources/views/components/global-search.blade.php`
- **Features**:
  - Real-time search with debouncing
  - Dropdown results with icons
  - Type filtering
  - Clickable results
  - Loading states

### 4. Technical Implementation

#### Database Queries
- Uses Laravel's Eloquent ORM with `where` and `whereHas` clauses
- Implements pagination for performance
- Supports relationship searches (e.g., searching items by category name)

#### AJAX Support
- All search forms support AJAX requests
- Partial table views for dynamic updates
- JSON responses for API endpoints

#### Pagination
- All search results include pagination
- Maintains search parameters across pages
- Uses Laravel's built-in pagination

### 5. User Interface Features

#### Search Forms
- Consistent design across all pages
- Filter dropdowns with relevant options
- Clear and reset functionality
- Responsive layout

#### Search Results
- Paginated results with navigation
- Empty state handling
- Loading states
- Error handling

#### Global Search
- Dropdown interface
- Real-time results
- Type filtering
- Keyboard navigation support

### 6. Usage Examples

#### Basic Search
```php
// In controller
$query = Item::with(['category', 'supplier']);

if ($request->filled('search')) {
    $search = $request->search;
    $query->where(function($q) use ($search) {
        $q->where('name', 'like', "%{$search}%")
          ->orWhere('sku', 'like', "%{$search}%");
    });
}

$items = $query->paginate(15);
```

#### Using Search Form Component
```blade
@php
$filters = [
    [
        'name' => 'category_id',
        'label' => 'Category',
        'type' => 'select',
        'options' => $categories->pluck('name', 'id')->toArray()
    ]
];
@endphp

<x-search-form 
    placeholder="Search items..." 
    :filters="$filters" 
    :showFilters="true" />
```

#### Global Search API Call
```javascript
fetch(`/api/search/global?q=${query}&type=items`)
    .then(response => response.json())
    .then(data => {
        // Handle results
    });
```

### 7. Performance Considerations

#### Database Optimization
- Uses indexes on searchable columns
- Implements pagination to limit result sets
- Uses eager loading to prevent N+1 queries

#### Frontend Optimization
- Debounced search input (300ms delay)
- AJAX requests to avoid full page reloads
- Cached filter options

### 8. Security Features

#### Input Validation
- All search inputs are validated
- SQL injection prevention through Eloquent ORM
- XSS protection through Blade templating

#### Access Control
- Search features respect user roles and permissions
- API endpoints protected by authentication middleware

### 9. Future Enhancements

#### Potential Improvements
- Full-text search using database engines (MySQL FULLTEXT, PostgreSQL)
- Elasticsearch integration for advanced search
- Search result highlighting
- Search history and suggestions
- Advanced filters (date ranges, numeric ranges)
- Export search results
- Search analytics and reporting

#### Additional Search Types
- Fuzzy search for typos
- Phonetic search for similar names
- Search within PDF documents
- Image search for product photos

## Conclusion

The search functionality provides a comprehensive and user-friendly way to find information across the entire inventory management system. The implementation is scalable, performant, and follows Laravel best practices. Users can quickly locate items, categories, suppliers, transactions, and other entities using both individual page searches and the global search system.
