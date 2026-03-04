<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Barcode - {{ $barcode->item->name ?? 'N/A' }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 0; padding: 16px; background: #e9ecef; }
        .no-print { }
        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none !important; }
            .label-sheet { box-shadow: none; border: none; page-break-after: always; }
            .label-sheet:last-child { page-break-after: avoid; }
        }
        .controls {
            position: fixed;
            top: 16px;
            right: 16px;
            background: #fff;
            padding: 16px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
        }
        .controls label { display: block; margin-bottom: 8px; font-weight: 600; }
        .controls input[type="number"] { width: 80px; padding: 8px; margin-left: 8px; }
        .btn { padding: 10px 16px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; margin: 4px 4px 4px 0; }
        .btn-primary { background: #5e72e4; color: #fff; }
        .btn-success { background: #2dce89; color: #fff; }
        .btn-secondary { background: #6c757d; color: #fff; }
        .preview-wrap { max-width: 400px; margin: 20px auto; }
        .label-sheet {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .label-sheet .product-name { font-size: 14px; font-weight: 700; text-align: center; margin-bottom: 12px; line-height: 1.3; }
        .label-sheet .barcode-wrap {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            margin: 12px 0;
            min-height: 80px;
            text-align: center;
        }
        .label-sheet .barcode-wrap canvas,
        .label-sheet .barcode-wrap img { max-width: 100%; height: auto; }
        .label-sheet .barcode-wrap table { margin: 0 auto !important; }
        .label-sheet .barcode-value { font-size: 11px; text-align: center; font-family: 'Courier New', monospace; margin-top: 8px; }
        .label-sheet .price { font-size: 13px; font-weight: 700; text-align: center; color: #e91e63; margin-top: 8px; }
    </style>
</head>
<body>
    <div class="controls no-print">
        <label>Jumlah label: <input type="number" id="qty" value="1" min="1" max="50"></label>
        <button type="button" class="btn btn-primary" onclick="renderLabels()">Generate</button>
        <button type="button" class="btn btn-success" onclick="window.print()">Print</button>
        <button type="button" class="btn btn-secondary" onclick="window.close()">Tutup</button>
    </div>

    <div id="labels" class="preview-wrap"></div>

    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        var barcodeData = {
            value: @json($barcode->barcode_value),
            type: @json(strtoupper($barcode->barcode_type)),
            productName: @json($barcode->item->name ?? 'N/A'),
            sellingPrice: {{ (float) ($barcode->item->selling_price ?? 0) }}
        };

        function formatPrice(p) {
            if (!p || p <= 0) return '-';
            return 'Rp ' + Math.round(p).toLocaleString('id-ID');
        }

        function makeLabel(index) {
            var wrap = document.createElement('div');
            wrap.className = 'label-sheet';
            wrap.innerHTML =
                '<div class="product-name">' + (barcodeData.productName || 'Produk') + '</div>' +
                '<div class="barcode-wrap" id="bc-wrap-' + index + '"></div>' +
                '<div class="barcode-value">' + barcodeData.value + '</div>' +
                '<div class="price">' + formatPrice(barcodeData.sellingPrice) + '</div>';

            var bcWrap = wrap.querySelector('.barcode-wrap');
            if (barcodeData.type === 'QR') {
                try {
                    new QRCode(bcWrap, { text: barcodeData.value, width: 160, height: 160 });
                } catch (e) {
                    bcWrap.innerHTML = '<span style="color:red">QR error</span>';
                }
            } else {
                var canvas = document.createElement('canvas');
                bcWrap.appendChild(canvas);
                try {
                    JsBarcode(canvas, barcodeData.value, {
                        format: barcodeData.type,
                        width: 2,
                        height: 70,
                        displayValue: false
                    });
                } catch (e) {
                    bcWrap.innerHTML = '<span style="color:red">Barcode error</span>';
                }
            }
            return wrap;
        }

        function renderLabels() {
            var n = parseInt(document.getElementById('qty').value, 10) || 1;
            n = Math.min(50, Math.max(1, n));
            var container = document.getElementById('labels');
            container.innerHTML = '';
            for (var i = 0; i < n; i++) {
                container.appendChild(makeLabel(i));
            }
        }

        window.onload = function() { renderLabels(); };
    </script>
</body>
</html>
