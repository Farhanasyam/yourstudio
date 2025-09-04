@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Import Items'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6>Import Items from Excel</h6>
                            <a href="{{ route('items.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Back to Items
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Import Instructions -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <h6 class="text-info font-weight-bolder">Import Instructions:</h6>
                                    <p class="mb-2">Please ensure your Excel file has the following columns in order:</p>
                                    <ol class="mb-2">
                                        <li><strong>Kategori Produk</strong> - Product Category Name</li>
                                        <li><strong>Nama Produk</strong> - Product Name (Required)</li>
                                        <li><strong>Nama Varian</strong> - Variant Name (Optional)</li>
                                        <li><strong>Harga Jual</strong> - Selling Price (Required) - Harga yang akan dijual ke customer. Format: 15.000, 1.500.000, 15000, 1500000</li>
                                        <li><strong>SKU Master</strong> - SKU Code (Optional - will auto-generate if empty)</li>
                                    </ol>
                                    <p class="mb-2">
                                        <strong>Note:</strong> Harga beli (purchase price) akan otomatis diset ke nilai default yang Anda tentukan untuk semua produk yang diimport.
                                    </p>
                                    <p class="mb-2">
                                        <strong>Format Harga:</strong> Sistem mendukung format Indonesia (15.000, 1.500.000) dan format internasional (15000, 1500000). Gunakan titik (.) sebagai pemisah ribuan.
                                    </p>
                                    <p class="mb-0">
                                        <strong>Supported formats:</strong> .xlsx, .xls, .csv (Maximum file size: 10MB)<br>
                                        <strong>Note:</strong> First row should contain headers and will be skipped during import.<br>
                                        <a href="{{ asset('sample_import.csv') }}" download class="btn btn-outline-info btn-sm mt-2">
                                            <i class="fas fa-download"></i> Download Sample CSV
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Sample Template -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header pb-0">
                                        <h6 class="text-primary">Sample Template</h6>
                                    </div>
                                    <div class="card-body pt-0">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm">
                                                <thead class="bg-gradient-primary text-white">
                                                    <tr>
                                                        <th>Kategori Produk</th>
                                                        <th>Nama Produk</th>
                                                        <th>Nama Varian</th>
                                                        <th>Harga Jual</th>
                                                        <th>SKU Master</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>Buku & Alat Tulis / Perlengkapan Menggambar & Melukis / Kuas Lukis</td>
                                                        <td>Kuas Multifungsi VTEC / Artist Brushes</td>
                                                        <td>VT-102 Halus</td>
                                                        <td>17.900</td>
                                                        <td>KUAS-VTEC-ISI6-HALUS</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Hobi & Koleksi / Model Kit / Tool & Kit</td>
                                                        <td>Alat Ukir Clay/ Sculpting Tools isi 7 pcs</td>
                                                        <td>Isi 10 (14cm)</td>
                                                        <td>10.500</td>
                                                        <td>AUKR-ISI10</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Hobi & Koleksi / Kerajinan Tangan / DIY</td>
                                                        <td>DIY Paint by Number (Melukis) Kanvas 20x20cm by Your Studio</td>
                                                        <td>White Rose</td>
                                                        <td>23.500</td>
                                                        <td>PBN-F-1</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Form -->
                        <div class="row">
                            <div class="col-12">
                                <form action="{{ route('items.import') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="excel_file" class="form-label">Select Excel File</label>
                                        <input type="file" 
                                               class="form-control @error('excel_file') is-invalid @enderror" 
                                               id="excel_file" 
                                               name="excel_file" 
                                               accept=".xlsx,.xls,.csv"
                                               required>
                                        @error('excel_file')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                        <div class="form-text">
                                            Accepted formats: .xlsx, .xls, .csv (Max: 10MB)
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="default_purchase_price" class="form-label">Default Harga Beli (Purchase Price)</label>
                                        <input type="number" 
                                               class="form-control @error('default_purchase_price') is-invalid @enderror" 
                                               id="default_purchase_price" 
                                               name="default_purchase_price" 
                                               value="10000"
                                               min="0"
                                               step="1000"
                                               required>
                                        @error('default_purchase_price')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                        <div class="form-text">
                                            Harga beli default yang akan digunakan untuk semua produk yang diimport
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary" id="importBtn">
                                                <i class="fas fa-upload"></i> Import Items
                                            </button>
                                            <a href="{{ route('items.index') }}" class="btn btn-secondary ms-2">
                                                <i class="fas fa-times"></i> Cancel
                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('layouts.footers.auth.footer')
    </div>
@endsection

@section('scripts')
<script>
document.getElementById('excel_file').addEventListener('change', function(e) {
    const fileInput = document.getElementById('excel_file');
    const importBtn = document.getElementById('importBtn');
    
    if (fileInput.files[0]) {
        if (fileInput.files[0].size > 10 * 1024 * 1024) {
            showWarningAlert('File size exceeds 10MB limit. Please choose a smaller file.');
            fileInput.value = '';
            return;
        }
        
        const allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel', 'text/csv'];
        if (!allowedTypes.includes(fileInput.files[0].type)) {
            showWarningAlert('Invalid file format. Please select an Excel file (.xlsx, .xls) or CSV file (.csv).');
            fileInput.value = '';
            return;
        }
        
        importBtn.innerHTML = '<i class="fas fa-upload"></i> Import ' + fileInput.files[0].name;
    } else {
        importBtn.innerHTML = '<i class="fas fa-upload"></i> Import Items';
    }
});

// Loading state on form submit
document.querySelector('form').addEventListener('submit', function() {
    const importBtn = document.getElementById('importBtn');
    importBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Importing...';
    importBtn.disabled = true;
});
</script>
@endsection
