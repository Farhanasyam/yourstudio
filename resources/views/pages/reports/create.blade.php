@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
@include('layouts.navbars.auth.topnav', ['title' => 'Generate Report'])

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="row">
                        <div class="col-6 d-flex align-items-center">
                            <h6>Generate New Report</h6>
                        </div>
                        <div class="col-6 text-end">
                            <a href="{{ route('reports.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Back to Reports
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('reports.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form-control-label">Report Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', 'Report ' . date('Y-m-d H:i')) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="type" class="form-control-label">Report Type</label>
                                    <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                                        @foreach($reportTypes as $typeKey => $typeName)
                                            <option value="{{ $typeKey }}" {{ $type == $typeKey ? 'selected' : '' }}>
                                                {{ $typeName }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_date" class="form-control-label">Start Date</label>
                                    <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                           id="start_date" name="start_date" value="{{ old('start_date', date('Y-m-01')) }}" required>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_date" class="form-control-label">End Date</label>
                                    <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                           id="end_date" name="end_date" value="{{ old('end_date', date('Y-m-d')) }}" required>
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="format" class="form-control-label">Export Format (Optional)</label>
                                    <select class="form-control @error('format') is-invalid @enderror" id="format" name="format">
                                        <option value="">No Export</option>
                                        <option value="pdf" {{ old('format') == 'pdf' ? 'selected' : '' }}>PDF</option>
                                        <option value="excel" {{ old('format') == 'excel' ? 'selected' : '' }}>Excel/CSV</option>
                                        <option value="json" {{ old('format') == 'json' ? 'selected' : '' }}>JSON</option>
                                    </select>
                                    @error('format')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <h6 class="alert-heading">Report Information</h6>
                                    <div id="report-info">
                                        <p class="mb-0">Select a report type to see detailed information about what data will be included.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-chart-bar"></i> Generate Report
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const reportInfo = document.getElementById('report-info');
    
    const reportDescriptions = {
        'daily_sales': 'Shows daily sales data including transaction counts, total sales, taxes, and discounts. Useful for tracking daily performance trends.',
        'monthly_sales': 'Provides monthly sales summaries with aggregated data. Perfect for monthly performance analysis and comparisons.',
        'stock_report': 'Comprehensive inventory overview showing current stock levels, values, and item details across all categories.',
        'low_stock': 'Identifies items that are running low on stock or are out of stock. Essential for inventory management.',
        'item_trends': 'Analyzes which items are selling best during the selected period. Helps with product performance insights.',
        'cashier_performance': 'Evaluates cashier performance including transaction counts, sales totals, and average transaction values.',
        'sales_by_category': 'Breaks down sales performance by product categories. Useful for category analysis and planning.',
        'profit_analysis': 'Detailed profit analysis showing revenue, costs, and profit margins for each item sold.'
    };
    
    function updateReportInfo() {
        const selectedType = typeSelect.value;
        const description = reportDescriptions[selectedType] || 'Select a report type to see detailed information.';
        
        reportInfo.innerHTML = `
            <p class="mb-0"><strong>${typeSelect.options[typeSelect.selectedIndex].text}</strong></p>
            <p class="mb-0">${description}</p>
        `;
    }
    
    typeSelect.addEventListener('change', updateReportInfo);
    updateReportInfo();
});
</script>
@endpush
@endsection

