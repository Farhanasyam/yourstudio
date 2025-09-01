@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Edit Barcode'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6>Edit Barcode</h6>
                            <a href="{{ route('barcodes.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Back to Barcodes
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form role="form" method="POST" action="{{ route('barcodes.update', $barcode) }}">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="item_id" class="form-control-label">Item *</label>
                                        <select class="form-control @error('item_id') is-invalid @enderror" 
                                                id="item_id" 
                                                name="item_id" 
                                                required>
                                            <option value="">Select an item</option>
                                            @foreach($items as $item)
                                                <option value="{{ $item->id }}" 
                                                        {{ old('item_id', $barcode->item_id) == $item->id ? 'selected' : '' }}>
                                                    {{ $item->name }} @if($item->sku)({{ $item->sku }})@endif
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('item_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="barcode_number" class="form-control-label">Barcode Number *</label>
                                        <div class="input-group">
                                            <input class="form-control @error('barcode_number') is-invalid @enderror" 
                                                   type="text" 
                                                   id="barcode_number" 
                                                   name="barcode_number" 
                                                   value="{{ old('barcode_number', $barcode->barcode_value) }}" 
                                                   placeholder="Enter barcode number"
                                                   required>
                                            <button type="button" class="btn btn-outline-info" onclick="generateBarcode()">
                                                <i class="fas fa-sync-alt"></i> Generate
                                            </button>
                                        </div>
                                        @error('barcode_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small id="barcodeHelp" class="form-text text-muted">
                                            Current barcode type determines the format
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="barcode_type" class="form-control-label">Barcode Type *</label>
                                        <select class="form-control @error('barcode_type') is-invalid @enderror" 
                                                id="barcode_type" 
                                                name="barcode_type" 
                                                required>
                                            <option value="">Select barcode type</option>
                                            <option value="CODE128" {{ old('barcode_type', $barcode->barcode_type) == 'CODE128' ? 'selected' : '' }}>CODE128 (Alphanumeric)</option>
                                            <option value="CODE39" {{ old('barcode_type', $barcode->barcode_type) == 'CODE39' ? 'selected' : '' }}>CODE39 (Alphanumeric)</option>
                                            <option value="EAN13" {{ old('barcode_type', $barcode->barcode_type) == 'EAN13' ? 'selected' : '' }}>EAN13 (13 digits only)</option>
                                            <option value="QR" {{ old('barcode_type', $barcode->barcode_type) == 'QR' ? 'selected' : '' }}>QR Code</option>
                                        </select>
                                        @error('barcode_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-control-label">Status</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                                   {{ old('is_active', $barcode->is_active) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">
                                                Active
                                            </label>
                                        </div>
                                        <small class="form-text text-muted">Only one barcode can be active per item</small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-control-label">Preview</label>
                                        <div id="barcodePreview" class="border p-3 text-center bg-light" style="min-height: 120px;">
                                            <small class="text-muted">Loading preview...</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Barcode
                                </button>
                                <a href="{{ route('barcodes.print', $barcode) }}" class="btn btn-warning ms-2" target="_blank">
                                    <i class="fas fa-print"></i> Print Barcode
                                </a>
                                <a href="{{ route('barcodes.index') }}" class="btn btn-secondary ms-2">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @include('layouts.footers.auth.footer')
    </div>

    <!-- Include required libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsbarcode/3.11.5/JsBarcode.all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js"></script>

    <!-- JavaScript for Barcode Generation -->
<script>
    'use strict';

    // Configuration constants
    const BARCODE_CONFIG = {
        EAN13: {
            help: 'EAN13 requires exactly 13 digits (0-9 only)',
            pattern: '[0-9]{13}',
            maxlength: '13',
            validation: /^\d{13}$/
        },
        QR: {
            help: 'QR Code can contain any text, URL, or data',
            pattern: null,
            maxlength: null,
            validation: null
        },
        CODE39: {
            help: 'CODE39 supports uppercase letters, digits, and some symbols',
            pattern: null,
            maxlength: '20',
            validation: null
        },
        CODE128: {
            help: 'CODE128 supports all ASCII characters',
            pattern: null,
            maxlength: '50',
            validation: null
        }
    };

    /**
     * Update barcode help text and input constraints based on selected type
     */
    function updateBarcodeHelp() {
        const barcodeType = document.getElementById('barcode_type').value;
        const helpText = document.getElementById('barcodeHelp');
        const barcodeInput = document.getElementById('barcode_number');
        
        if (!barcodeType || !BARCODE_CONFIG[barcodeType]) {
            helpText.textContent = 'Select barcode type first, then generate or enter manually';
            helpText.className = 'form-text text-muted';
            barcodeInput.removeAttribute('pattern');
            barcodeInput.removeAttribute('maxlength');
            updatePreview();
            return;
        }

        const config = BARCODE_CONFIG[barcodeType];
        
        // Update help text
        helpText.textContent = config.help;
        helpText.className = 'form-text text-info';
        
        // Update input constraints
        if (config.pattern) {
            barcodeInput.setAttribute('pattern', config.pattern);
        } else {
            barcodeInput.removeAttribute('pattern');
        }
        
        if (config.maxlength) {
            barcodeInput.setAttribute('maxlength', config.maxlength);
        } else {
            barcodeInput.removeAttribute('maxlength');
        }
        
        updatePreview();
    }

    /**
     * Generate barcode value based on selected type
     */
    function generateBarcode() {
        const itemSelect = document.getElementById('item_id');
        const barcodeInput = document.getElementById('barcode_number');
        const barcodeType = document.getElementById('barcode_type').value;
        
        // Validation
        if (!itemSelect.value) {
            alert('Please select an item first');
            return;
        }
        
        if (!barcodeType) {
            alert('Please select barcode type first');
            return;
        }

        // Generate code based on type
        const generatedCode = generateBarcodeValue(barcodeType);
        barcodeInput.value = generatedCode;
        updatePreview();
    }

    /**
     * Generate barcode value for specific type
     * @param {string} type - Barcode type
     * @returns {string} Generated barcode value
     */
    function generateBarcodeValue(type) {
        switch (type) {
            case 'EAN13':
                return generateEAN13();
            case 'QR':
                return generateQRCode();
            case 'CODE39':
                return generateCODE39();
            case 'CODE128':
            default:
                return generateCODE128();
        }
    }

    /**
     * Generate EAN13 barcode with check digit
     * @returns {string} EAN13 code
     */
    function generateEAN13() {
        const countryCode = '899'; // Indonesia country code
        const manufacturerCode = String(Math.floor(Math.random() * 9000) + 1000);
        const productCode = String(Math.floor(Math.random() * 90000) + 10000);
        const baseCode = countryCode + manufacturerCode + productCode;
        
        // Calculate check digit
        let sum = 0;
        for (let i = 0; i < 12; i++) {
            const digit = parseInt(baseCode[i]);
            sum += (i % 2 === 0) ? digit : digit * 3;
        }
        const checkDigit = sum % 10 === 0 ? 0 : 10 - (sum % 10);
        
        return baseCode + checkDigit.toString();
    }

    /**
     * Generate QR code value
     * @returns {string} QR code value
     */
    function generateQRCode() {
        const timestamp = Date.now().toString();
        const random = Math.random().toString(36).substring(2, 8).toUpperCase();
        return `QR-${timestamp}-${random}`;
    }

    /**
     * Generate CODE39 value
     * @returns {string} CODE39 value
     */
    function generateCODE39() {
        return Math.random().toString(36).substring(2, 12).toUpperCase();
    }

    /**
     * Generate CODE128 value
     * @returns {string} CODE128 value
     */
    function generateCODE128() {
        const timestamp = Date.now().toString().slice(-8);
        const random = Math.random().toString(36).substring(2, 6).toUpperCase();
        return `BC${timestamp}${random}`;
    }

    /**
     * Update barcode preview
     */
    function updatePreview() {
        const barcodeNumber = document.getElementById('barcode_number').value;
        const barcodeType = document.getElementById('barcode_type').value;
        const preview = document.getElementById('barcodePreview');
        
        // Clear preview if inputs are empty
        if (!barcodeNumber || !barcodeType) {
            preview.innerHTML = '<small class="text-muted">Enter barcode number and select type to see preview</small>';
            return;
        }
        
        // Validate input for specific types
        if (!validateBarcodeInput(barcodeNumber, barcodeType)) {
            return;
        }
        
        // Set loading state
        preview.innerHTML = `
            <div id="barcodeContainer"></div>
            <div class="mt-2"><small class="text-muted">${barcodeNumber} (${barcodeType})</small></div>
        `;
        
        // Generate preview
        try {
            if (barcodeType === 'QR') {
                generateQRPreview(barcodeNumber);
            } else {
                generateBarcodePreview(barcodeNumber, barcodeType);
            }
        } catch (error) {
            console.error('Preview generation error:', error);
            showFallbackPreview(barcodeNumber, barcodeType);
        }
    }

    /**
     * Validate barcode input based on type
     * @param {string} value - Barcode value
     * @param {string} type - Barcode type
     * @returns {boolean} Is valid
     */
    function validateBarcodeInput(value, type) {
        const preview = document.getElementById('barcodePreview');
        const config = BARCODE_CONFIG[type];
        
        if (config && config.validation && !config.validation.test(value)) {
            preview.innerHTML = `<small class="text-danger">${config.help}</small>`;
            return false;
        }
        
        return true;
    }

    /**
     * Generate QR code preview
     * @param {string} value - QR code value
     */
    function generateQRPreview(value) {
        if (typeof QRCode === 'undefined') {
            showFallbackPreview(value, 'QR');
            return;
        }
        
        const canvas = document.createElement('canvas');
        canvas.id = 'qrCanvas';
        document.getElementById('barcodeContainer').appendChild(canvas);
        
        QRCode.toCanvas(canvas, value, {
            width: 200,
            height: 200,
            margin: 2
        }, function (error) {
            if (error) {
                console.error('QR generation error:', error);
                showFallbackPreview(value, 'QR');
            }
        });
    }

    /**
     * Generate regular barcode preview
     * @param {string} value - Barcode value
     * @param {string} type - Barcode type
     */
    function generateBarcodePreview(value, type) {
        if (typeof JsBarcode === 'undefined') {
            showFallbackPreview(value, type);
            return;
        }
        
        const canvas = document.createElement('canvas');
        canvas.id = 'previewCanvas';
        document.getElementById('barcodeContainer').appendChild(canvas);
        
        JsBarcode('#previewCanvas', value, {
            format: type,
            width: 2,
            height: 50,
            displayValue: false
        });
    }

    /**
     * Show fallback preview when libraries are not available
     * @param {string} value - Barcode value
     * @param {string} type - Barcode type
     */
    function showFallbackPreview(value, type) {
        const preview = document.getElementById('barcodePreview');
        const alertClass = type === 'QR' ? 'alert-info' : 'alert-warning';
        
        preview.innerHTML = `
            <div class="alert ${alertClass}">
                <strong>${type === 'QR' ? 'QR Code' : 'Barcode'} Preview</strong><br>
                Type: ${type}<br>
                Value: ${value}<br>
                <small>Code will be generated when saved</small>
            </div>
        `;
    }

    /**
     * Check if required libraries are loaded
     */
    function checkLibraries() {
        const jsbarcodeMissing = typeof JsBarcode === 'undefined';
        const qrcodeMissing = typeof QRCode === 'undefined';
        
        if (jsbarcodeMissing || qrcodeMissing) {
            console.warn('Some barcode libraries are missing. Preview will show fallback content.');
            
            if (jsbarcodeMissing) console.warn('JsBarcode library not loaded');
            if (qrcodeMissing) console.warn('QRCode library not loaded');
        }
    }

    /**
     * Initialize the barcode form
     */
    function initializeBarcodeForm() {
        // Check library availability
        checkLibraries();
        
        // Get DOM elements
        const barcodeInput = document.getElementById('barcode_number');
        const barcodeTypeSelect = document.getElementById('barcode_type');
        
        if (!barcodeInput || !barcodeTypeSelect) {
            console.error('Required form elements not found');
            return;
        }
        
        // Attach event listeners
        barcodeInput.addEventListener('input', updatePreview);
        barcodeTypeSelect.addEventListener('change', updateBarcodeHelp);
        
        // Initialize form state
        updateBarcodeHelp();
    }

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', initializeBarcodeForm);
</script>
@endsection