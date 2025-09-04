<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran - {{ $transaction->transaction_code }}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 13px;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .receipt-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            position: relative;
        }

        .receipt-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb);
        }
        
        .receipt {
            max-width: 320px;
            margin: 0 auto;
            background: white;
            padding: 25px;
            position: relative;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            position: relative;
        }

        .header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 2px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 2px;
        }
        
        .logo {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            margin: 0 auto 15px;
            display: block;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            border: none;
            background: transparent;
            padding: 0;
        }

        .logo img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .store-name {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #2d3748;
            letter-spacing: 1px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .copy-indicator {
            position: absolute;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
            transform: rotate(15deg);
            z-index: 10;
        }
        
        .copy-indicator::before {
            content: '';
            position: absolute;
            top: -5px;
            left: -5px;
            right: -5px;
            bottom: -5px;
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            border-radius: 25px;
            z-index: -1;
            opacity: 0.3;
        }
        
        .store-info {
            font-size: 11px;
            line-height: 1.4;
            margin-bottom: 3px;
            color: #718096;
            font-weight: 400;
        }

        .social-info {
            display: inline-flex;
            align-items: center;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 500;
            margin-top: 4px;
        }

        .social-info i {
            margin-right: 5px;
        }
        
        .separator {
            border: none;
            height: 1px;
            background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
            margin: 10px 0;
            position: relative;
        }

        .separator.dotted {
            background: none;
            border-top: 2px dotted #e2e8f0;
        }

        .separator::before {
            content: '';
            position: absolute;
            left: -25px;
            top: 50%;
            transform: translateY(-50%);
            width: 15px;
            height: 15px;
            background: white;
            border-radius: 50%;
            border: 2px solid #e2e8f0;
        }

        .separator::after {
            content: '';
            position: absolute;
            right: -25px;
            top: 50%;
            transform: translateY(-50%);
            width: 15px;
            height: 15px;
            background: white;
            border-radius: 50%;
            border: 2px solid #e2e8f0;
        }
        
        .transaction-info {
            background: #f8fafc;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 15px;
            border: 1px solid #e2e8f0;
        }
        
        .transaction-info .info-line {
            display: flex;
            margin-bottom: 8px;
            align-items: center;
        }

        .transaction-info .info-line:last-child {
            margin-bottom: 0;
        }
        
        .transaction-info .info-label {
            width: 80px;
            flex-shrink: 0;
            font-weight: 500;
            color: #4a5568;
            font-size: 11px;
        }
        
        .transaction-info .info-colon {
            width: 15px;
            flex-shrink: 0;
            color: #718096;
            font-weight: 500;
        }
        
        .transaction-info .info-value {
            flex: 1;
            font-weight: 600;
            color: #2d3748;
            font-size: 11px;
        }

        .info-icon {
            width: 16px;
            height: 16px;
            margin-right: 8px;
            color: #667eea;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .items-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 12px 15px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .items-header div {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .items-header i {
            margin-right: 8px;
        }
        
        .items {
            margin-bottom: 20px;
        }
        
        .item {
            margin-bottom: 12px;
            padding: 12px;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
        }

        .item:hover {
            background: #edf2f7;
            border-color: #cbd5e0;
        }
        
        .item-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .item-name {
            flex: 1;
            padding-right: 15px;
            font-weight: 500;
            color: #2d3748;
            font-size: 12px;
        }
        
        .item-price {
            min-width: 80px;
            text-align: right;
            font-weight: 600;
            color: #667eea;
            font-size: 12px;
        }
        
        .totals {
            background: linear-gradient(135deg, #48bb78, #38a169);
            color: white;
            padding: 20px 25px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(72, 187, 120, 0.3);
        }
        
        .total-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
            font-weight: 600;
        }
        
        .total-label {
            flex: 1;
            display: flex;
            align-items: center;
            padding-right: 15px;
        }

        .total-label i {
            margin-right: 8px;
            font-size: 12px;
        }
        
        .total-amount {
            min-width: 100px;
            text-align: right;
            font-size: 15px;
            font-weight: 700;
            padding-left: 10px;
        }
        
        .payment-info {
            background: #edf2f7;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            border: 2px solid #e2e8f0;
        }

        .payment-method {
            display: inline-flex;
            align-items: center;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
        }

        .payment-method i {
            margin-right: 6px;
        }
        
        .slogan {
            text-align: center;
            font-style: italic;
            margin: 20px 0;
            font-size: 13px;
            color: #667eea;
            font-weight: 500;
            position: relative;
            padding: 15px 0;
        }

        .slogan::before,
        .slogan::after {
            content: '✨';
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
        }

        .slogan::before {
            left: 20px;
        }

        .slogan::after {
            right: 20px;
        }
        
        .footer {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 2px solid #e2e8f0;
        }
        
        .thank-you {
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 600;
            font-size: 14px;
            line-height: 1.4;
        }

        .date-time {
            margin-top: 15px;
            font-size: 10px;
            color: #718096;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .date-time i {
            margin-right: 5px;
            color: #667eea;
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        
        .btn-print, .btn-finish {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-print {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-finish {
            background: linear-gradient(135deg, #48bb78, #38a169);
            color: white;
            box-shadow: 0 4px 15px rgba(72, 187, 120, 0.3);
        }
        
        .btn-finish:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(72, 187, 120, 0.4);
        }
        
        @media print {
            body {
                padding: 0;
                background: white;
            }
            
            .receipt-container {
                box-shadow: none;
                border-radius: 0;
            }

            .receipt {
                max-width: none;
                margin: 0;
                padding: 15px;
            }
            
            .logo {
                width: 180px !important;
                height: 180px !important;
                margin: 0 auto 25px !important;
                border: none !important;
                background: transparent !important;
            }
            
            .store-name {
                font-size: 28px !important;
                margin-bottom: 15px !important;
                font-weight: 800 !important;
            }
            
            .store-info {
                font-size: 13px !important;
                margin-bottom: 5px !important;
            }
            
            .social-info {
                font-size: 12px !important;
                padding: 8px 16px !important;
            }
            
            .no-print {
                display: none !important;
            }

                    .separator::before,
        .separator::after {
            display: none;
        }
        
        .action-buttons {
            display: none;
        }
        }
        
        .print-button {
            position: fixed;
            top: 30px;
            right: 30px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 15px 25px;
            border-radius: 50px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .print-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(102, 126, 234, 0.4);
        }

        .print-button i {
            margin-right: 8px;
        }

        /* Animation for receipt entrance */
        .receipt-container {
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Cetak Struk
    </button>

    <div class="receipt-container">
        @if(isset($isCopy) && $isCopy)
            <div class="copy-indicator">COPY</div>
        @endif
        <div class="receipt">
            <!-- Header -->
            <div class="header">
                <div class="logo">
                    <img src="{{ asset('img/strukyourstudio.png') }}" alt="YOUR STUDIO">
                </div>
                <div class="store-name">YOUR STUDIO</div>
                <div class="store-info">
                    Jl. Raya Sawojajar Ruko WOW Paris<br>
                    Kav PA-1 12, Malang
                </div>
                <div class="social-info">
                    <i class="fab fa-instagram"></i> @your__studio
                </div>
            </div>

            <div class="separator"></div>

            <!-- Transaction Info -->
            <div class="transaction-info">
                <div class="info-line">
                    <div class="info-icon"><i class="far fa-calendar"></i></div>
                    <span class="info-label">Tanggal</span>
                    <span class="info-colon">:</span>
                    <span class="info-value">{{ $transaction->transaction_date->format('d-m-Y H:i') }}</span>
                </div>
                <div class="info-line">
                    <div class="info-icon"><i class="fas fa-receipt"></i></div>
                    <span class="info-label">No. Struk</span>
                    <span class="info-colon">:</span>
                    <span class="info-value">{{ $transaction->transaction_code }}</span>
                </div>
                <div class="info-line">
                    <div class="info-icon"><i class="fas fa-user"></i></div>
                    <span class="info-label">Kasir</span>
                    <span class="info-colon">:</span>
                    <span class="info-value">{{ $transaction->cashier->name }}</span>
                </div>
            </div>

            <!-- Items Header -->
            <div class="items-header">
                <div>
                    <span><i class="fas fa-shopping-bag"></i>Barang</span>
                    <span>Harga</span>
                </div>
            </div>

            <!-- Items -->
            <div class="items">
                @foreach($transaction->transactionItems as $item)
                <div class="item">
                    <div class="item-line">
                        <span class="item-name">{{ $item->item_name }}</span>
                        <span class="item-price">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="separator dotted"></div>

            <!-- Total -->
            <div class="totals">
                <div class="total-line">
                    <span class="total-label"><i class="fas fa-calculator"></i>TOTAL PEMBAYARAN</span>
                    <span class="total-amount">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Payment Info -->
            <div class="payment-info">
                <div class="payment-method">
                    <i class="fas fa-credit-card"></i>
                    {{ strtoupper($transaction->payment_method) }}
                </div>
            </div>

            <div class="separator"></div>

            <!-- Slogan -->
            <div class="slogan">
                "Create your own studio"
            </div>

            <!-- Footer -->
            <div class="footer">
                <div class="thank-you">
                    ✨ Terima kasih telah berbelanja ✨<br>
                    di YOUR STUDIO<br>
                    Sampai jumpa lagi! 😊
                </div>
                <div class="date-time">
                    <i class="far fa-clock"></i>
                    Dicetak pada {{ now()->format('d-m-Y H:i:s') }}
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="action-buttons">
                <button class="btn-print" onclick="printReceipt()">
                    <i class="fas fa-print"></i>
                    Print Struk
                </button>
                <button class="btn-finish" onclick="finishTransaction()">
                    <i class="fas fa-check-circle"></i>
                    Selesai
                </button>
            </div>
        </div>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            // Auto print after 1 second
            setTimeout(() => {
                window.print();
            }, 1000);
        }

        // Print receipt function
        function printReceipt() {
            window.print();
        }

        // Finish transaction function
        function finishTransaction() {
            // Show success notification
            Swal.fire({
                title: 'Transaksi Selesai!',
                text: 'Struk telah dicetak dan transaksi masuk ke histori',
                icon: 'success',
                confirmButtonText: 'OK',
                allowOutsideClick: false
            }).then((result) => {
                // Redirect to transaction history
                window.location.href = '/transaction-history';
            });
        }

        // Add some interactivity
        document.addEventListener('DOMContentLoaded', function() {
            // Add hover effect to items
            const items = document.querySelectorAll('.item');
            items.forEach(item => {
                item.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateX(5px)';
                });
                item.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateX(0)';
                });
            });
        });
    </script>
</body>
</html>
