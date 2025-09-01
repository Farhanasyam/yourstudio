<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Barcode - {{ $barcode->item->name ?? 'N/A' }}</title>
    <style>
        @page {
            size: 33mm 15mm; /* Ukuran label individual */
            margin: 0;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
                width: 33mm;
                height: 15mm;
                background: white;
            }
            .no-print {
                display: none !important;
            }
            .print-header {
                display: none !important;
            }
            .item-info {
                display: none !important;
            }
            .label-row {
                width: 33mm;
                height: 15mm;
                page-break-after: always;
                page-break-inside: avoid;
                margin: 0;
                padding: 0;
            }
            .label-row:last-child {
                page-break-after: avoid;
            }
            .barcode-label {
                width: 33mm;
                height: 15mm;
                margin: 0;
                padding: 0.5mm;
                border: none;
                background: white;
            }
            .product-name {
                font-size: 6px;
                height: 2mm;
                margin: 0;
                line-height: 1;
            }
            .barcode-image {
                width: 30mm;
                height: 7mm;
                margin: 0.5mm 0;
            }
            .barcode-number {
                font-size: 4px;
                height: 1.5mm;
                margin: 0;
                line-height: 1;
            }
            .price-display {
                font-size: 5px;
                height: 2mm;
                margin: 0.2mm 0 0 0;
                padding: 0.3mm;
                line-height: 1;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 10px;
            background: #f5f5f5;
        }
        
        .print-header {
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .print-header h1 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 18px;
        }
        
        .item-info {
            background: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .item-info h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            padding: 3px 0;
            border-bottom: 1px solid #eee;
        }
        
        .info-label {
            font-weight: bold;
            color: #666;
        }
        
        .preview-container {
            display: flex;
            flex-direction: column;
            gap: 5px;
            justify-content: center;
            margin-top: 20px;
        }
        
        .label-row {
            display: flex;
            width: 33mm;
            height: 15mm;
            background: white;
            border: 1px solid #ddd;
            margin-bottom: 5px;
        }
        
        .barcode-label {
            width: 33mm;
            height: 15mm;
            background: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0.5mm;
            box-sizing: border-box;
            position: relative;
        }
        
        .product-name {
            font-size: 6px;
            font-weight: bold;
            text-align: center;
            margin: 0;
            line-height: 1;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            height: 2mm;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .barcode-image {
            width: 30mm;
            height: 7mm;
            object-fit: contain;
            margin: 0.5mm 0;
        }
        
        .barcode-number {
            font-size: 4px;
            text-align: center;
            margin: 0;
            font-family: 'Courier New', monospace;
            line-height: 1;
            height: 1.5mm;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .price-display {
            font-size: 5px;
            font-weight: bold;
            text-align: center;
            color: #d9534f;
            background: #f8f9fa;
            border-radius: 1px;
            padding: 0.3mm;
            margin: 0.2mm 0 0 0;
            line-height: 1;
            height: 2mm;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 0.3px solid #ddd;
        }
        
        .print-controls {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
        }
        
        .print-controls label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: #333;
        }
        
        .quantity-input {
            width: 80px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-left: 10px;
        }
        
        .btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px 5px 5px 0;
            font-size: 14px;
            transition: background 0.2s;
        }
        
        .btn:hover {
            background: #0056b3;
        }
        
        .btn-success {
            background: #28a745;
        }
        
        .btn-success:hover {
            background: #1e7e34;
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
    </style>
</head>
<body>
    <div class="print-controls no-print">
        <label>Jumlah Label:
            <input type="number" id="quantity" class="quantity-input" value="10" min="1" max="100">
        </label>
        <div style="margin-top: 10px;">
            <label style="font-size: 12px; color: #666;">
                <input type="checkbox" id="singleLabel" checked> 1 Label per Baris (33mm x 15mm)
            </label>
        </div>
        <button onclick="generateLabels()" class="btn btn-success">Generate</button>
        <button onclick="window.print()" class="btn">Print</button>
        <button onclick="window.close()" class="btn btn-secondary">Close</button>
    </div>
    
    <div class="print-header">
        <h1>Label Barcode Thermal - {{ $barcode->item->name ?? 'N/A' }}</h1>
        <p><strong>Size:</strong> <span id="pageSize">33mm x 15mm (1 label per row)</span> | <strong>Type:</strong> {{ $barcode->barcode_type }}</p>
        <p><em>Untuk Printer Thermal - Pastikan setting printer sesuai ukuran di atas, margin 0</em></p>
    </div>
    
    <div class="item-info no-print">
        <h3>Informasi Produk</h3>
        <div class="info-row">
            <span class="info-label">Nama Produk:</span>
            <span>{{ $barcode->item->name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Kode Barcode:</span>
            <span>{{ $barcode->item->barcode_value ?? $barcode->barcode_value }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Tipe Barcode:</span>
            <span>{{ $barcode->barcode_type }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Harga Jual:</span>
            <span>{{ $barcode->item->selling_price ? 'Rp ' . number_format($barcode->item->selling_price, 0, ',', '.') : 'Tidak ada harga' }}</span>
        </div>
        @if(isset($barcode->item->purchase_price))
        <div class="info-row">
            <span class="info-label">Harga Beli:</span>
            <span>Rp {{ number_format($barcode->item->purchase_price, 0, ',', '.') }}</span>
        </div>
        @endif
    </div>
    
    <div id="barcodeContainer" class="preview-container">
        <!-- Barcode labels akan muncul di sini -->
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script>
        // Data dari server dengan fallback values untuk testing
        const barcodeData = {
            value: '{{ $barcode->item->barcode_value ?? $barcode->barcode_value }}' || '8991821514697',
            type: '{{ $barcode->barcode_type }}' || 'EAN13',
            productName: '{{ $barcode->item->name ?? "N/A" }}' || 'Kanvas',
            sellingPrice: '{{ $barcode->item->selling_price ?? "" }}' || '20000',
            purchasePrice: '{{ $barcode->item->purchase_price ?? "" }}' || '10000'
        };
        
        console.log('Barcode data initialized:', barcodeData);
        
        function formatPrice(price) {
            if (!price || price === '' || price === '0') {
                return 'No Price';
            }
            
            const numPrice = parseFloat(price);
            if (isNaN(numPrice)) {
                return 'No Price';
            }
            
            return 'Rp ' + numPrice.toLocaleString('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        }
        
        function createBarcodeLabel(index) {
            console.log('Creating barcode label', index);
            
            const labelDiv = document.createElement('div');
            labelDiv.className = 'barcode-label';
            
            // Product name
            const productNameDiv = document.createElement('div');
            productNameDiv.className = 'product-name';
            productNameDiv.textContent = barcodeData.productName;
            
            // Barcode canvas
            const barcodeCanvas = document.createElement('canvas');
            barcodeCanvas.className = 'barcode-image';
            barcodeCanvas.id = 'barcode-canvas-' + index;
            barcodeCanvas.width = 300; // Increased canvas size for better quality
            barcodeCanvas.height = 80;
            
            // Barcode number
            const barcodeNumberDiv = document.createElement('div');
            barcodeNumberDiv.className = 'barcode-number';
            barcodeNumberDiv.textContent = barcodeData.value;
            
            // Price display
            const priceDiv = document.createElement('div');
            priceDiv.className = 'price-display';
            priceDiv.textContent = formatPrice(barcodeData.sellingPrice);
            
            labelDiv.appendChild(productNameDiv);
            labelDiv.appendChild(barcodeCanvas);
            labelDiv.appendChild(barcodeNumberDiv);
            labelDiv.appendChild(priceDiv);
            
            // Generate barcode immediately after canvas is added to DOM
            setTimeout(() => {
                console.log('Generating barcode for canvas:', 'barcode-canvas-' + index);
                try {
                    if (typeof JsBarcode !== 'undefined') {
                        JsBarcode('#barcode-canvas-' + index, barcodeData.value, {
                            format: barcodeData.type,
                            width: 2,
                            height: 60,
                            displayValue: false,
                            margin: 8,
                            fontSize: 12,
                            background: '#ffffff',
                            lineColor: '#000000'
                        });
                        console.log('Barcode generated successfully for', index);
                    } else {
                        console.error('JsBarcode library not loaded');
                        // Fallback
                        const ctx = barcodeCanvas.getContext('2d');
                        ctx.fillStyle = '#000';
                        ctx.font = '12px Arial';
                        ctx.textAlign = 'center';
                        ctx.fillText(barcodeData.value, barcodeCanvas.width/2, barcodeCanvas.height/2);
                    }
                } catch (error) {
                    console.error('Error generating barcode:', error);
                    // Fallback jika barcode gagal
                    const ctx = barcodeCanvas.getContext('2d');
                    ctx.fillStyle = '#000';
                    ctx.font = '12px Arial';
                    ctx.textAlign = 'center';
                    ctx.fillText('Error: ' + barcodeData.value, barcodeCanvas.width/2, barcodeCanvas.height/2);
                }
            }, 100);
            
            return labelDiv;
        }
        
        function generateLabels() {
            const container = document.getElementById('barcodeContainer');
            const quantity = parseInt(document.getElementById('quantity').value) || 1;
            const singleLabel = document.getElementById('singleLabel').checked;
            
            console.log('Generating labels:', quantity, 'Single label:', singleLabel);
            
            container.innerHTML = '';
            
            // Update page size info
            const pageSizeSpan = document.getElementById('pageSize');
            
            if (singleLabel) {
                // 1 label per row (33mm x 15mm)
                pageSizeSpan.textContent = '33mm x 15mm (1 label per row)';
                
                for (let i = 0; i < quantity; i++) {
                    const rowDiv = document.createElement('div');
                    rowDiv.className = 'label-row';
                    
                    const label = createBarcodeLabel(i);
                    rowDiv.appendChild(label);
                    container.appendChild(rowDiv);
                }
            } else {
                // 2 labels per row (70mm x 15mm) - for compatibility
                pageSizeSpan.textContent = '70mm x 15mm (2 labels per row)';
                
                for (let i = 0; i < quantity; i += 2) {
                    const rowDiv = document.createElement('div');
                    rowDiv.className = 'label-row';
                    rowDiv.style.width = '70mm';
                    
                    // First label
                    const label1 = createBarcodeLabel(i);
                    label1.style.width = '33mm';
                    rowDiv.appendChild(label1);
                    
                    // Second label (if exists)
                    if (i + 1 < quantity) {
                        const label2 = createBarcodeLabel(i + 1);
                        label2.style.width = '33mm';
                        rowDiv.appendChild(label2);
                    } else {
                        // Add empty space if odd number
                        const emptyDiv = document.createElement('div');
                        emptyDiv.className = 'barcode-label';
                        emptyDiv.style.width = '33mm';
                        emptyDiv.style.visibility = 'hidden';
                        rowDiv.appendChild(emptyDiv);
                    }
                    
                    container.appendChild(rowDiv);
                }
            }
            
            console.log('Labels generated, container children:', container.children.length);
        }
        
        // Auto generate saat halaman dimuat
        window.onload = function() {
            console.log('Page loaded, barcodeData:', barcodeData);
            
            // Check if JsBarcode is loaded
            if (typeof JsBarcode === 'undefined') {
                console.error('JsBarcode library not loaded!');
                setTimeout(() => {
                    generateLabels();
                }, 1000); // Wait 1 second for library to load
            } else {
                console.log('JsBarcode library loaded successfully');
                generateLabels();
            }
        };
        
        // Also try to generate after a delay to ensure library is loaded
        setTimeout(() => {
            if (document.getElementById('barcodeContainer').children.length === 0) {
                console.log('Retrying label generation...');
                generateLabels();
            }
        }, 500);
        
        // Regenerate saat quantity atau mode berubah
        document.getElementById('quantity').addEventListener('input', generateLabels);
        document.getElementById('singleLabel').addEventListener('change', generateLabels);
    </script>
</body>
</html>