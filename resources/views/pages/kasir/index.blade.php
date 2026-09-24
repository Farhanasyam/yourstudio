@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Kasir'])
    
    <div class="container-fluid py-4">
        <!-- Barcode Scanner Section -->
        <div class="row">
            <div class="col-12">
                <div class="card mb-3">
                    <div class="card-body p-3">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                                    <i class="fas fa-barcode text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                            <div class="col">
                                <input type="text"
                                       id="barcodeInput"
                                       class="form-control form-control-lg"
                                       placeholder="Scan barcode produk..."
                                       autocomplete="off"
                                       autofocus>
                            </div>
                        </div>
                        {{-- Connection / offline queue status (filled by the script below) --}}
                        <div class="d-flex flex-wrap align-items-center gap-2 mt-2 text-xs" id="kasirStatusBar">
                            <span class="badge bg-gradient-success" id="connectionBadge">
                                <i class="fas fa-wifi me-1"></i><span id="connectionText">Online</span>
                            </span>
                            <span class="badge bg-gradient-warning d-none" id="pendingBadge">
                                <i class="fas fa-clock me-1"></i><span id="pendingText">0 transaksi menunggu sinkron</span>
                            </span>
                            <span class="badge bg-gradient-danger d-none" id="failedBadge" role="button" onclick="showFailedSales()">
                                <i class="fas fa-exclamation-triangle me-1"></i><span id="failedText">0 gagal sinkron</span>
                            </span>
                            <span class="text-secondary" id="catalogText">Memuat data barang...</span>
                            <button type="button" class="btn btn-link btn-sm text-primary p-0 mb-0 ms-auto" id="syncButton" onclick="manualSync()">
                                <i class="fas fa-sync-alt me-1"></i>Sinkron
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Shopping Cart Section -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex align-items-center">
                            <h6 class="mb-0">Keranjang Belanja</h6>
                            <div class="ms-auto">
                                <span class="badge bg-gradient-success" id="cartItemCount">0 Item</span>
                                <button class="btn btn-link text-danger px-3 mb-0" onclick="clearCart()">
                                    <i class="fas fa-trash me-2"></i>Kosongkan
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0" style="max-height: calc(100vh - 400px); overflow-y: auto;">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Produk</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Harga</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Qty</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Subtotal</th>
                                        <th class="text-secondary opacity-7"></th>
                                    </tr>
                                </thead>
                                <tbody id="cartItems">
                                    <!-- Cart items will be populated here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Section -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-4">
                            <h6 class="mb-0">Total Pembayaran</h6>
                            <h3 class="text-success mb-0" id="totalAmount">Rp 0</h3>
                        </div>

                        <!-- Payment Method -->
                        <div class="form-group mb-3">
                            <label class="form-control-label">Metode Pembayaran</label>
                            <select class="form-select" id="paymentMethod">
                                <option value="cash">Tunai</option>
                                <option value="card">Kartu Debit/Kredit</option>
                                <option value="qris">QRIS</option>
                            </select>
                        </div>

                        <!-- Cash Payment Section -->
                        <div id="cashPaymentSection">
                            <div class="form-group mb-3">
                                <label class="form-control-label d-flex justify-content-between">
                                    <span>Jumlah Bayar</span>
                                    <a href="#" onclick="setExactAmount()" class="text-sm">Uang Pas</a>
                                </label>
                                <input type="number" 
                                       class="form-control form-control-lg" 
                                       id="paidAmount" 
                                       placeholder="0"
                                       min="0"
                                       step="1000">
                            </div>

                            <!-- Quick Amount Buttons -->
                            <div class="row g-2 mb-3">
                                <div class="col-4">
                                    <button class="btn btn-outline-primary w-100" onclick="setQuickAmount(10000)">10K</button>
                                </div>
                                <div class="col-4">
                                    <button class="btn btn-outline-primary w-100" onclick="setQuickAmount(20000)">20K</button>
                                </div>
                                <div class="col-4">
                                    <button class="btn btn-outline-primary w-100" onclick="setQuickAmount(50000)">50K</button>
                                </div>
                                <div class="col-4">
                                    <button class="btn btn-outline-primary w-100" onclick="setQuickAmount(100000)">100K</button>
                                </div>
                                <div class="col-4">
                                    <button class="btn btn-outline-primary w-100" onclick="setQuickAmount(200000)">200K</button>
                                </div>
                                <div class="col-4">
                                    <button class="btn btn-outline-primary w-100" onclick="setQuickAmount(500000)">500K</button>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label class="form-control-label">Kembalian</label>
                                <input type="text" 
                                       class="form-control form-control-lg bg-light" 
                                       id="changeAmount" 
                                       readonly
                                       value="Rp 0">
                            </div>
                        </div>

                        <!-- Non-Cash Payment Section -->
                        <div id="nonCashPaymentSection" style="display: none;">
                            <div class="alert alert-info mb-4">
                                <div class="d-flex">
                                    <div class="text-white">
                                        <i class="fas fa-info-circle"></i>
                                    </div>
                                    <div class="ms-3">
                                        <span class="text-sm">Silakan ikuti instruksi pembayaran pada mesin EDC atau scan QRIS yang tersedia</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <button class="btn btn-success btn-lg w-100 mb-2" 
                                id="processPaymentBtn" 
                                onclick="processPayment()">
                            <i class="fas fa-check-circle me-2"></i>Proses Pembayaran
                        </button>
                        <button class="btn btn-outline-secondary w-100" onclick="cancelTransaction()">
                            <i class="fas fa-times me-2"></i>Batalkan Transaksi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-check-circle me-2"></i>Transaksi Berhasil
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <h2 class="text-gradient text-success mb-4" id="modalTotalAmount">Rp 0</h2>
                    <div class="row justify-content-center">
                        <div class="col-8">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tr>
                                        <td class="text-sm text-start">No. Transaksi</td>
                                        <td class="text-sm text-end" id="modalTransactionCode">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-sm text-start">Bayar</td>
                                        <td class="text-sm text-end" id="modalPaidAmount">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-sm text-start">Kembalian</td>
                                        <td class="text-sm text-end" id="modalChangeAmount">-</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="printReceipt()">
                        <i class="fas fa-print me-2"></i>Cetak Struk
                    </button>
                    <button type="button" class="btn btn-success" onclick="newTransaction()">
                        <i class="fas fa-plus me-2"></i>Transaksi Baru
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
// Cart management: keyed by item id, so one item scanned via different barcodes stays one line
let cart = {};

// Local item catalog (from /kasir/catalog, kept in the browser by kasir-offline.js):
// scanning looks items up here, so it is instant and works without a connection.
let catalogByCode = {};
let catalogSettings = {};
let cashierName = @json(auth()->user()->name);

function indexCatalog(catalog) {
    catalogByCode = {};
    if (!catalog || !catalog.items) return;
    catalog.items.forEach(function (item) {
        (item.codes || []).forEach(function (code) { catalogByCode[String(code)] = item; });
    });
    catalogSettings = catalog.settings || {};
    if (catalog.cashier && catalog.cashier.name) cashierName = catalog.cashier.name;
}

function escapeHtml(text) {
    return String(text).replace(/[&<>"']/g, function (c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
}

function formatRp(value) {
    return 'Rp ' + Math.round(value).toLocaleString('id-ID');
}

// Update cart display
function updateCart() {
    const cartBody = document.getElementById('cartItems');
    const totalElement = document.getElementById('totalAmount');
    const countElement = document.getElementById('cartItemCount');
    const processBtn = document.getElementById('processPaymentBtn');
    
    if (!cartBody || !totalElement || !countElement) {
        console.error('Cart elements not found');
        return;
    }
    
    // Clear table
    cartBody.innerHTML = '';
    
    let total = 0;
    let count = 0;
    
    // Add items to table
    for (let barcode in cart) {
        const item = cart[barcode];
        const subtotal = item.price * item.quantity;
        total += subtotal;
        count += item.quantity;
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="ps-3">
                <p class="text-sm font-weight-bold mb-0">${escapeHtml(item.name)}</p>
                <span class="text-xs text-secondary">${escapeHtml(item.code || '')}</span>
            </td>
            <td class="text-sm text-end pe-2">Rp ${item.price.toLocaleString()}</td>
            <td class="text-center">
                <div class="d-flex align-items-center justify-content-center">
                    <button class="btn btn-sm btn-outline-secondary me-2" onclick="decreaseQuantity('${barcode}')">
                        <i class="fas fa-minus"></i>
                    </button>
                    <input type="number" 
                           class="form-control form-control-sm text-center" 
                           style="width: 60px;" 
                           value="${item.quantity}" 
                           min="1" 
                           max="999"
                           onchange="updateQuantity('${barcode}', this.value)"
                           onkeyup="if(event.key === 'Enter') this.blur()">
                    <button class="btn btn-sm btn-outline-secondary ms-2" onclick="increaseQuantity('${barcode}')">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </td>
            <td class="text-sm text-end pe-2">Rp ${subtotal.toLocaleString()}</td>
            <td class="text-center">
                <button class="btn btn-link text-danger mb-0" onclick="removeItem('${barcode}')">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        `;
        cartBody.appendChild(row);
    }
    
    // Update totals
    totalElement.textContent = `Rp ${total.toLocaleString()}`;
    countElement.textContent = `${count} Item`;
    
    // Update button states
    if (processBtn) {
        if (count > 0) {
            processBtn.disabled = false;
            processBtn.classList.remove('btn-secondary');
            processBtn.classList.add('btn-success');
        } else {
            processBtn.disabled = true;
            processBtn.classList.remove('btn-success');
            processBtn.classList.add('btn-secondary');
        }
    }
    
    // Update paid amount if cart is empty
    const paidInput = document.getElementById('paidAmount');
    if (paidInput && count === 0) {
        paidInput.value = '0';
    }
    
    // Recalculate change after cart update
    setTimeout(() => {
        calculateChange();
    }, 100);
}

// Add item to cart
function addToCart(product) {
    if (!product || !product.barcode) {
        console.error('Invalid product data');
        return;
    }
    
    const barcode = 'i' + product.id; // cart key

    if (cart[barcode]) {
        cart[barcode].stock = product.stock_quantity;
        if (!hasStockFor(barcode, cart[barcode].quantity + 1)) return;
        cart[barcode].quantity++;
        showAlert(`${product.name} +1 (${cart[barcode].quantity})`, 'success');
    } else {
        cart[barcode] = {
            id: product.id,
            name: product.name,
            code: product.barcode,
            price: parseFloat(product.selling_price),
            stock: product.stock_quantity,
            quantity: 1
        };
        if (!hasStockFor(barcode, 1)) { delete cart[barcode]; return; }
        showAlert(`${product.name} ditambahkan ke keranjang`, 'success');
    }
    
    updateCart();
}

// Check requested quantity against the stock returned by the scan
function hasStockFor(barcode, quantity) {
    const item = cart[barcode];
    if (item && typeof item.stock === 'number' && quantity > item.stock) {
        showAlert(`Stok ${item.name} hanya ${item.stock}`, 'error');
        return false;
    }
    return true;
}

// Increase quantity
function increaseQuantity(barcode) {
    if (cart[barcode]) {
        if (!hasStockFor(barcode, cart[barcode].quantity + 1)) return;
        cart[barcode].quantity++;
        updateCart();
        showAlert(`${cart[barcode].name} +1 (${cart[barcode].quantity})`, 'info');
    }
}

// Decrease quantity
function decreaseQuantity(barcode) {
    if (cart[barcode] && cart[barcode].quantity > 1) {
        cart[barcode].quantity--;
        updateCart();
        showAlert(`${cart[barcode].name} -1 (${cart[barcode].quantity})`, 'info');
    } else if (cart[barcode] && cart[barcode].quantity === 1) {
        removeItem(barcode);
    }
}

// Update quantity manually
function updateQuantity(barcode, newQuantity) {
    const quantity = parseInt(newQuantity);
    
    if (isNaN(quantity) || quantity < 1) {
        updateCart(); // Reset to previous value
        return;
    }
    
    if (cart[barcode]) {
        if (!hasStockFor(barcode, quantity)) {
            updateCart(); // Reset to previous value
            return;
        }
        cart[barcode].quantity = quantity;
        updateCart();
        showAlert(`${cart[barcode].name} quantity diubah ke ${quantity}`, 'info');
    }
}

// Remove item from cart
function removeItem(barcode) {
    if (cart[barcode]) {
        const itemName = cart[barcode].name;
        delete cart[barcode];
        updateCart();
        showAlert(`${itemName} dihapus dari keranjang`, 'warning');
    }
}

// Clear cart
function clearCart() {
    cart = {};
    updateCart();
    showAlert('Keranjang dikosongkan', 'info');
}

// Show alert with SweetAlert2
function showAlert(message, type = 'success') {
    Swal.fire({
        text: message,
        icon: type,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true
    });
}

// Scan: look the barcode up in the local catalog (instant, works offline).
// Only a code that is not in the catalog yet (e.g. a barcode added a moment ago) asks the server.
function scanBarcode(barcode) {
    barcode = String(barcode || '').trim();
    if (!barcode) {
        showAlert('Barcode tidak boleh kosong', 'error');
        return;
    }
    const item = catalogByCode[barcode];
    if (item) {
        if (item.price <= 0) {
            showAlert(`Harga jual ${item.name} belum diatur. Atur harga di menu Items.`, 'error');
            return;
        }
        if (item.stock <= 0) {
            showAlert(`Stok ${item.name} habis`, 'error');
            return;
        }
        addToCart({ id: item.id, name: item.name, barcode: barcode, selling_price: item.price, stock_quantity: item.stock });
        return;
    }
    if (!navigator.onLine) {
        showAlert('Produk tidak ditemukan di data barang offline', 'error');
        return;
    }
    fetch('/kasir/search-barcode', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ barcode: barcode })
    })
    .then(response => {
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return response.json();
    })
    .then(data => {
        if (data.success && data.data) {
            addToCart(data.data);
            loadCatalog(); // the local catalog was missing this code
        } else {
            showAlert(data.message || 'Produk tidak ditemukan', 'error');
        }
    })
    .catch(error => {
        showAlert('Gagal memproses barcode: ' + error.message, 'error');
    });
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    const barcodeInput = document.getElementById('barcodeInput');
    
    if (!barcodeInput) {
        console.error('Barcode input not found');
        return;
    }
    
    // Barcode input handler
    barcodeInput.addEventListener('input', function() {
        const barcode = this.value.trim();
        
        // Auto-scan only for complete EAN13 (13 digits). Other types (CODE128/QR are 15 chars)
        // are submitted by the scanner's Enter key, otherwise they'd be cut off at 13 chars.
        if (/^\d{13}$/.test(barcode)) {
            scanBarcode(barcode);
            this.value = ''; // Clear input
        }
    });
    
    // Enter key handler
    barcodeInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const barcode = this.value.trim();
            if (barcode) {
                scanBarcode(barcode);
            this.value = '';
            }
        }
    });

    // Keep focus on the barcode input, except when the cashier clicks another field
    // (payment amount, method, qty) or a dialog
    document.addEventListener('click', function(e) {
        if (e.target.closest('input, select, textarea, .modal, .swal2-container')) return;
        barcodeInput.focus();
    });

    // Initialize cart display
    updateCart();

    // Setup payment handlers
    setupPaymentHandlers();

    // Local catalog: use the saved copy immediately, then refresh from the server
    if (window.KasirOffline) {
        indexCatalog(KasirOffline.getCatalog());
        loadCatalog();
        setInterval(loadCatalog, 5 * 60 * 1000);
        document.addEventListener('kasir-offline:changed', renderStatus);
        document.addEventListener('kasir-offline:catalog', function (e) { indexCatalog(e.detail); renderStatus(); });
        renderStatus();
    }

    // Keep this page available offline
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').catch(function (e) {
            console.warn('Service worker tidak aktif (butuh HTTPS):', e.message);
        });
    }
});

function loadCatalog() {
    if (!window.KasirOffline || !navigator.onLine) { renderStatus(); return Promise.resolve(); }
    return KasirOffline.refreshCatalog().catch(function () { renderStatus(); });
}

// Connection, pending queue and catalog age shown above the cart
function renderStatus() {
    if (!window.KasirOffline) return;
    const s = KasirOffline.status();
    const badge = document.getElementById('connectionBadge');
    badge.className = 'badge ' + (s.online ? 'bg-gradient-success' : 'bg-gradient-secondary');
    document.getElementById('connectionText').textContent = s.online ? 'Online' : 'Offline - transaksi disimpan di perangkat ini';

    const pending = document.getElementById('pendingBadge');
    pending.classList.toggle('d-none', s.pending === 0);
    document.getElementById('pendingText').textContent = (s.syncing ? 'Mengirim ' : '') + s.pending + ' transaksi menunggu sinkron';

    const failed = document.getElementById('failedBadge');
    failed.classList.toggle('d-none', s.failed.length === 0);
    document.getElementById('failedText').textContent = s.failed.length + ' gagal sinkron (klik)';

    const itemCount = Object.keys(catalogByCode).length ? (KasirOffline.getCatalog() || { items: [] }).items.length : 0;
    document.getElementById('catalogText').textContent = s.catalogAt
        ? `Data barang: ${itemCount} item, diperbarui ${new Date(s.catalogAt).toLocaleString('id-ID', { dateStyle: 'short', timeStyle: 'short' })}`
        : 'Data barang belum tersedia offline - buka halaman ini saat online';
}

function manualSync() {
    if (!navigator.onLine) {
        showAlert('Masih offline. Transaksi akan dikirim otomatis saat online.', 'warning');
        return;
    }
    Promise.all([KasirOffline.syncQueue(), loadCatalog()]).then(function () {
        const s = KasirOffline.status();
        showAlert(s.pending === 0 && s.failed.length === 0 ? 'Semua transaksi sudah tersinkron' : `${s.pending} transaksi masih menunggu`, s.pending === 0 ? 'success' : 'warning');
    });
}

// Sales the server refused when syncing (e.g. an item was deleted meanwhile): show and allow retry
function showFailedSales() {
    const failed = KasirOffline.status().failed;
    if (!failed.length) return;
    const list = failed.map(s => `<li><b>${escapeHtml(s.transaction_code)}</b> (${formatRp(s.total_amount)}): ${escapeHtml(s.error || '')}</li>`).join('');
    Swal.fire({
        title: 'Transaksi gagal sinkron',
        html: `<ul class="text-start text-sm">${list}</ul><p class="text-sm">Laporkan ke admin. Transaksi tetap tersimpan di perangkat ini.</p>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Coba kirim ulang',
        cancelButtonText: 'Tutup'
    }).then(function (r) { if (r.isConfirmed) KasirOffline.retryFailed(); });
}

// Setup payment handlers
function setupPaymentHandlers() {
    console.log('Setting up payment handlers...');
    
    // Manual input handler
    const paidInput = document.getElementById('paidAmount');
    if (paidInput) {
        console.log('Paid input found, setting up event listeners');
        paidInput.addEventListener('input', function() {
            console.log('Paid input changed to:', this.value);
            calculateChange();
        });
        
        paidInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                console.log('Enter pressed in paid input');
                processPayment();
            }
        });
    } else {
        console.error('Paid input not found');
    }

    // Card / QRIS: hide cash inputs and treat the payment as exact
    const methodSelect = document.getElementById('paymentMethod');
    if (methodSelect) {
        methodSelect.addEventListener('change', function() {
            const isCash = this.value === 'cash';
            document.getElementById('cashPaymentSection').style.display = isCash ? '' : 'none';
            document.getElementById('nonCashPaymentSection').style.display = isCash ? 'none' : '';
            if (!isCash) setExactAmount();
        });
    }

    // Initial calculation
    setTimeout(() => {
        calculateChange();
    }, 100);
}

// Set quick amount (called by HTML onclick)
function setQuickAmount(amount) {
    console.log('setQuickAmount called with:', amount);
    const paidInput = document.getElementById('paidAmount');
    if (paidInput) {
        paidInput.value = amount;
        console.log('Set paid amount to:', amount);
        calculateChange();
        paidInput.focus();
    } else {
        console.error('Paid input not found in setQuickAmount');
    }
}

// Set exact amount (total payment)
function setExactAmount() {
    console.log('setExactAmount called');
    const totalElement = document.getElementById('totalAmount');
    if (totalElement) {
        const totalText = totalElement.textContent;
        const total = parseInt(totalText.replace(/[^\d]/g, ''));
        console.log('Total from element:', totalText, 'Parsed total:', total);
        if (!isNaN(total)) {
            setQuickAmount(total);
        }
    } else {
        console.error('Total element not found');
    }
}

// Calculate change
function calculateChange() {
    console.log('calculateChange called');
    const totalElement = document.getElementById('totalAmount');
    const paidInput = document.getElementById('paidAmount');
    const changeElement = document.getElementById('changeAmount');
    
    console.log('Elements found:', {
        totalElement: !!totalElement,
        paidInput: !!paidInput,
        changeElement: !!changeElement
    });
    
    if (!totalElement || !paidInput || !changeElement) {
        console.error('One or more payment elements not found');
            return;
    }
    
    const totalText = totalElement.textContent;
    const total = parseInt(totalText.replace(/[^\d]/g, ''));
    const paid = parseInt(paidInput.value) || 0;
    
    console.log('Values:', {
        totalText: totalText,
        total: total,
        paid: paid
    });
    
    if (!isNaN(total) && !isNaN(paid)) {
        const change = paid - total;
        console.log('Calculated change:', change);
        
        // Use .value because changeAmount is an input readonly
        changeElement.value = `Rp ${change.toLocaleString()}`;
        
        // Change color based on change amount
        if (change < 0) {
            changeElement.style.color = '#dc3545'; // Red for negative
        } else if (change === 0) {
            changeElement.style.color = '#28a745'; // Green for exact
        } else {
            changeElement.style.color = '#17a2b8'; // Blue for positive
        }
        
        console.log('Change element updated:', changeElement.value);
    } else {
        console.error('Invalid total or paid amount');
    }
}

// Process payment
function processPayment() {
    console.log('processPayment called');
    const totalElement = document.getElementById('totalAmount');
    const paidInput = document.getElementById('paidAmount');
    const changeElement = document.getElementById('changeAmount');
    
    if (!totalElement || !paidInput || !changeElement) {
        showAlert('Element pembayaran tidak ditemukan', 'error');
        return;
    }
    
    const totalText = totalElement.textContent;
    const total = parseInt(totalText.replace(/[^\d]/g, ''));
    const methodSelect = document.getElementById('paymentMethod');
    const isCash = !methodSelect || methodSelect.value === 'cash';
    // Non-cash payments are always for the exact amount (cart may have changed after selecting the method)
    const paid = isCash ? (parseInt(paidInput.value) || 0) : total;

    console.log('Processing payment:', { total, paid });
    
    if (isNaN(total) || total <= 0) {
        showAlert('Keranjang belanja kosong', 'error');
        return;
    }
    
    if (isNaN(paid) || paid <= 0) {
        showAlert('Masukkan jumlah pembayaran', 'error');
        return;
    }
    
    if (paid < total) {
        showAlert('Pembayaran kurang dari total belanja', 'error');
        return;
    }
    
    // Show confirmation
    Swal.fire({
        title: 'Konfirmasi Pembayaran',
        html: `
            <div class="text-left">
                <p><strong>Total Belanja:</strong> Rp ${total.toLocaleString()}</p>
                <p><strong>Jumlah Bayar:</strong> Rp ${paid.toLocaleString()}</p>
                <p><strong>Kembalian:</strong> Rp ${(paid - total).toLocaleString()}</p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Proses Pembayaran',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Process the payment
            processPaymentTransaction(total, paid, paid - total);
        }
    });
}

// Prevent double submit
let paymentInProgress = false;

function resetPaymentButton() {
    paymentInProgress = false;
    const btn = document.getElementById('processPaymentBtn');
    if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Proses Pembayaran'; }
}

function clearAfterSale() {
    cart = {};
    updateCart();
    const paidInput = document.getElementById('paidAmount');
    const changeElement = document.getElementById('changeAmount');
    if (paidInput) paidInput.value = '0';
    if (changeElement) changeElement.value = 'Rp 0';
    resetPaymentButton();
}

// Process payment transaction.
// Every sale gets a client_uuid. If the server can't be reached (offline, timeout, server down,
// expired session) the sale goes into the offline queue with that same id, gets an offline
// receipt, and is sent automatically later. The server ignores a second copy of the same id,
// so a sale that did reach the server before the connection dropped is never counted twice.
function processPaymentTransaction(total, paid, change) {
    if (paymentInProgress) return;
    paymentInProgress = true;
    const btn = document.getElementById('processPaymentBtn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
    }

    const items = [];
    for (let key in cart) {
        const item = cart[key];
        items.push({ id: item.id, name: item.name, price: item.price, quantity: item.quantity });
    }

    const paymentMethodSelect = document.getElementById('paymentMethod');
    const now = new Date();
    const sale = {
        client_uuid: KasirOffline.uuid(),
        transaction_code: KasirOffline.offlineCode(now), // only used if it ends up as an offline sale
        created_at: now.toISOString(),
        items: items,
        total_amount: total,
        paid_amount: paid,
        change_amount: change,
        payment_method: (paymentMethodSelect && paymentMethodSelect.value) ? paymentMethodSelect.value : 'cash',
        cashier_name: cashierName,
    };

    if (!navigator.onLine) {
        saveOfflineSale(sale, 'Perangkat sedang offline');
        return;
    }

    KasirOffline.postSale(sale)
        .then(function (res) {
            if (res.status === 419 || res.status === 401 || res.redirected) {
                return saveOfflineSale(sale, 'Sesi login berakhir, login ulang agar transaksi terkirim');
            }
            if (res.status >= 500) {
                return saveOfflineSale(sale, 'Server sedang bermasalah');
            }
            return res.json().then(function (data) {
                if (res.ok && data.success) {
                    KasirOffline.adjustCatalogStock(items);
                    Swal.fire({
                        title: 'Pembayaran Berhasil!',
                        text: 'Transaksi telah diproses. Mencetak struk...',
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        window.location.href = `/kasir/receipt/${data.transaction_id}`;
                    });
                } else {
                    // The server checked the sale and refused it (stock, price...): nothing to retry
                    resetPaymentButton();
                    showAlert(data.message || 'Gagal memproses transaksi', 'error');
                }
            });
        })
        .catch(function () {
            // No answer (connection dropped or timed out): the sale may or may not have been saved,
            // the queue's client_uuid makes the retry safe either way
            saveOfflineSale(sale, 'Koneksi terputus');
        });
}

function saveOfflineSale(sale, reason) {
    const queued = Object.assign({}, sale, { offline: true, queued_at: new Date().toISOString() });
    if (!KasirOffline.enqueue(queued)) {
        resetPaymentButton();
        Swal.fire('Gagal menyimpan offline', 'Penyimpanan browser penuh atau diblokir. Jangan serahkan barang; coba lagi saat online.', 'error');
        return;
    }
    KasirOffline.adjustCatalogStock(sale.items);
    indexCatalog(KasirOffline.getCatalog());
    clearAfterSale();

    const autoPrint = ['1', 'true', 'on'].includes(String(catalogSettings.auto_print_receipt ?? '1'));
    if (autoPrint) printOfflineReceipt(queued);
    Swal.fire({
        title: 'Transaksi disimpan offline',
        html: `${escapeHtml(reason)}.<br>Struk <b>${escapeHtml(sale.transaction_code)}</b> tersimpan di perangkat ini dan akan dikirim otomatis saat online.`,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: autoPrint ? 'Cetak ulang struk' : 'Cetak struk',
        cancelButtonText: 'Transaksi baru'
    }).then(function (r) {
        if (r.isConfirmed) printOfflineReceipt(queued);
    });
    renderStatus();
}

// Receipt printed from the browser for offline sales (same store details as the server receipt)
function printOfflineReceipt(sale) {
    const s = catalogSettings;
    const lines = sale.items.map(i => `
        <tr><td colspan="2">${escapeHtml(i.name)}</td></tr>
        <tr><td>${i.quantity} x ${formatRp(i.price)}</td><td class="r">${formatRp(i.price * i.quantity)}</td></tr>`).join('');
    const when = new Date(sale.created_at).toLocaleString('id-ID', { dateStyle: 'short', timeStyle: 'short' });
    const html = `<!DOCTYPE html><html><head><meta charset="utf-8"><title>${escapeHtml(sale.transaction_code)}</title>
<style>
  @page { size: 58mm auto; margin: 2mm; }
  body { font-family: 'Courier New', monospace; font-size: 11px; width: 54mm; margin: 0 auto; color: #000; }
  .c { text-align: center; } .r { text-align: right; } .b { font-weight: bold; }
  table { width: 100%; border-collapse: collapse; } td { vertical-align: top; padding: 1px 0; }
  hr { border: 0; border-top: 1px dashed #000; margin: 4px 0; }
</style></head><body>
  <div class="c b" style="font-size:13px">${escapeHtml(s.store_name || 'YOUR STUDIO')}</div>
  ${s.store_address ? `<div class="c">${escapeHtml(s.store_address)}</div>` : ''}
  ${s.store_phone ? `<div class="c">Telp. ${escapeHtml(s.store_phone)}</div>` : ''}
  ${s.store_instagram ? `<div class="c">IG ${escapeHtml(s.store_instagram)}</div>` : ''}
  <hr>
  <div>No   : ${escapeHtml(sale.transaction_code)}</div>
  <div>Tgl  : ${escapeHtml(when)}</div>
  <div>Kasir: ${escapeHtml(sale.cashier_name || '')}</div>
  <hr>
  <table>${lines}</table>
  <hr>
  <table>
    <tr class="b"><td>TOTAL</td><td class="r">${formatRp(sale.total_amount)}</td></tr>
    <tr><td>Bayar (${escapeHtml(String(sale.payment_method).toUpperCase())})</td><td class="r">${formatRp(sale.paid_amount)}</td></tr>
    <tr><td>Kembali</td><td class="r">${formatRp(sale.change_amount)}</td></tr>
  </table>
  <hr>
  ${s.receipt_header ? `<div class="c">"${escapeHtml(s.receipt_header)}"</div>` : ''}
  ${s.receipt_footer ? `<div class="c">${escapeHtml(s.receipt_footer)}</div>` : ''}
  <div class="c" style="margin-top:4px">(transaksi offline)</div>
</body></html>`;

    let frame = document.getElementById('offlineReceiptFrame');
    if (!frame) {
        frame = document.createElement('iframe');
        frame.id = 'offlineReceiptFrame';
        frame.style.cssText = 'position:fixed;width:0;height:0;border:0;right:0;bottom:0;';
        document.body.appendChild(frame);
    }
    const doc = frame.contentWindow.document;
    doc.open();
    doc.write(html);
    doc.close();
    setTimeout(function () { frame.contentWindow.focus(); frame.contentWindow.print(); }, 300);
}

// Cancel transaction
function cancelTransaction() {
    Swal.fire({
        title: 'Batalkan Transaksi?',
        text: 'Semua item di keranjang akan dihapus',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Batalkan',
        cancelButtonText: 'Tidak',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            clearCart();
            const paidInput = document.getElementById('paidAmount');
            const changeElement = document.getElementById('changeAmount');
            
            if (paidInput) paidInput.value = '0';
            if (changeElement) changeElement.value = 'Rp 0';
            
            showAlert('Transaksi dibatalkan', 'info');
        }
    });
}
</script>
@endpush