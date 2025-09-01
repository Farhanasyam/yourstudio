@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Kasir'])
    
    <div class="container-fluid py-4">
        <!-- User Info -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-info d-flex align-items-center" role="alert">
                    <i class="fas fa-user-circle me-2"></i>
                    <div>
                        <strong>Kasir:</strong> {{ Auth::user()->name }} 
                        <span class="badge bg-primary ms-2">{{ Auth::user()->role }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Scanner & Input Section -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <div class="d-flex align-items-center">
                            <h6 class="mb-0">Scanner Barcode</h6>
                            <button type="button" class="btn btn-primary btn-sm ms-auto" onclick="focusBarcodeInput()">
                                <i class="fas fa-barcode me-1"></i> Focus Scanner
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-control-label">Scan Barcode atau Input Manual</label>
                                    <div class="input-group">
                                        <input type="text" 
                                               id="barcodeInput" 
                                               class="form-control" 
                                               placeholder="Scan barcode atau ketik kode..."
                                               autocomplete="off"
                                               style="height: 38px; line-height: 1;">
                                        <button type="button" 
                                                class="btn btn-primary" 
                                                id="searchButton"
                                                style="height: 38px; line-height: 1;">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Posisi cursor harus di input ini untuk barcode scanner berfungsi
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Item Preview -->
                        <div id="itemPreview" class="mt-3" style="display: none;">
                            <div class="alert alert-info">
                                <div class="row align-items-center">
                                    <div class="col-md-2">
                                        <img id="previewImage" src="" alt="Item" class="img-fluid rounded" style="max-height: 80px;">
                                    </div>
                                    <div class="col-md-6">
                                        <h6 id="previewName" class="mb-1"></h6>
                                        <p class="mb-1"><small>SKU: <span id="previewSku"></span></small></p>
                                        <p class="mb-0"><small>Stok: <span id="previewStock"></span> <span id="previewUnit"></span></small></p>
                                    </div>
                                    <div class="col-md-2">
                                        <h5 class="text-success mb-0" id="previewPrice"></h5>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-success btn-sm w-100" onclick="addToCart()">
                                            <i class="fas fa-plus me-1"></i> Tambah
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shopping Cart -->
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex align-items-center">
                            <h6 class="mb-0">Keranjang Belanja</h6>
                            <button type="button" class="btn btn-info btn-sm me-2 ms-auto" data-bs-toggle="tooltip" data-bs-placement="top" title="Klik angka quantity untuk edit manual">
                                <i class="fas fa-info-circle"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="clearCart()">
                                <i class="fas fa-trash me-1"></i> Kosongkan
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0" id="cartTable">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Item</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Harga</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Qty</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Total</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="cartItems">
                                    <!-- Cart items will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Section -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header pb-0">
                        <h6 class="mb-0">Pembayaran</h6>
                    </div>
                    <div class="card-body">
                        <!-- Summary -->
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span id="subtotalAmount">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total Item:</span>
                                <span id="totalItems">0</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <strong>Total:</strong>
                                <strong id="totalAmount" class="text-success">Rp 0</strong>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="form-group">
                            <label class="form-control-label">Metode Pembayaran</label>
                            <select class="form-select" id="paymentMethod">
                                <option value="cash">Tunai</option>
                                <option value="card">Kartu</option>
                                <option value="transfer">Transfer</option>
                                <option value="qris">QRIS</option>
                            </select>
                        </div>

                        <!-- Cash Payment -->
                        <div id="cashPayment">
                            <div class="form-group">
                                <label class="form-control-label">Jumlah Bayar</label>
                                <input type="number" 
                                       class="form-control form-control-lg" 
                                       id="paidAmount" 
                                       placeholder="0"
                                       min="0"
                                       step="1000">
                            </div>
                            <div class="form-group">
                                <label class="form-control-label">Kembalian</label>
                                <input type="text" 
                                       class="form-control form-control-lg" 
                                       id="changeAmount" 
                                       readonly
                                       placeholder="Rp 0">
                            </div>

                            <!-- Quick Amount Buttons -->
                            <div class="row mb-3">
                                <div class="col-6"><button type="button" class="btn btn-outline-primary btn-sm w-100" onclick="setQuickAmount(50000)">50K</button></div>
                                <div class="col-6"><button type="button" class="btn btn-outline-primary btn-sm w-100" onclick="setQuickAmount(100000)">100K</button></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-6"><button type="button" class="btn btn-outline-primary btn-sm w-100" onclick="setQuickAmount(200000)">200K</button></div>
                                <div class="col-6"><button type="button" class="btn btn-outline-primary btn-sm w-100" onclick="setQuickAmount(500000)">500K</button></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-12"><button type="button" class="btn btn-outline-success btn-sm w-100" onclick="setExactAmount()">Uang Pas</button></div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2">
                            <button type="button" 
                                    class="btn btn-success btn-lg" 
                                    id="processPayment" 
                                    onclick="processPayment()"
                                    disabled>
                                <i class="fas fa-credit-card me-2"></i>
                                Proses Pembayaran
                            </button>
                            <button type="button" 
                                    class="btn btn-secondary" 
                                    onclick="clearCart()">
                                <i class="fas fa-times me-2"></i>
                                Batalkan Transaksi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Modal -->
    <div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2 mb-0">Memproses transaksi...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle me-2"></i>
                        Transaksi Berhasil
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                        <h5 id="successMessage">Transaksi berhasil diproses!</h5>
                        <div class="alert alert-info mt-3">
                            <div class="row">
                                <div class="col-6"><strong>Kode Transaksi:</strong></div>
                                <div class="col-6" id="transactionCode">-</div>
                            </div>
                            <div class="row">
                                <div class="col-6"><strong>Total:</strong></div>
                                <div class="col-6" id="modalTotal">-</div>
                            </div>
                            <div class="row">
                                <div class="col-6"><strong>Bayar:</strong></div>
                                <div class="col-6" id="modalPaid">-</div>
                            </div>
                            <div class="row">
                                <div class="col-6"><strong>Kembalian:</strong></div>
                                <div class="col-6" id="modalChange">-</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="printReceipt()">
                        <i class="fas fa-print me-1"></i> Cetak Struk
                    </button>
                    <button type="button" class="btn btn-success" onclick="newTransaction()" data-bs-dismiss="modal">
                        <i class="fas fa-plus me-1"></i> Transaksi Baru
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('js')
<script>
    // Global variables
    let cart = [];
    let currentPreviewItem = null;
    let lastTransactionId = null;

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize empty cart display
        updateCartDisplay();
        updatePaymentSummary();
        setupEventListeners();
        focusBarcodeInput();
    });

    function setupEventListeners() {
        // Barcode input event
        document.getElementById('barcodeInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchBarcode();
            }
        });

        // Search button
        document.getElementById('searchButton').addEventListener('click', searchBarcode);

        // Paid amount change
        document.getElementById('paidAmount').addEventListener('input', calculateChange);

        // Payment method change
        document.getElementById('paymentMethod').addEventListener('change', function() {
            const cashPayment = document.getElementById('cashPayment');
            if (this.value === 'cash') {
                cashPayment.style.display = 'block';
            } else {
                cashPayment.style.display = 'none';
                document.getElementById('paidAmount').value = document.getElementById('totalAmount').textContent.replace(/[^0-9]/g, '');
                calculateChange();
            }
        });

        // Auto focus barcode input when clicked anywhere (except when editing quantity)
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.modal') && 
                !e.target.closest('input[type="number"]') && 
                !e.target.closest('.quantity-controls')) {
                setTimeout(() => focusBarcodeInput(), 100);
            }
        });

        // Initialize tooltips
        if (typeof bootstrap !== 'undefined') {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    }

    function focusBarcodeInput() {
        document.getElementById('barcodeInput').focus();
    }

    function searchBarcode() {
        const barcode = document.getElementById('barcodeInput').value.trim();
        
        if (!barcode) {
            showAlert('warning', 'Masukkan kode barcode terlebih dahulu!');
            return;
        }

        // Show loading
        const searchButton = document.getElementById('searchButton');
        const originalText = searchButton.innerHTML;
        searchButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        searchButton.disabled = true;

        // AJAX request
        fetch('/kasir/search-barcode', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ barcode: barcode })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Auto add to cart if item found
                currentPreviewItem = data.data;
                addToCartDirectly();
            } else {
                showAlert('error', data.message || 'Barcode tidak ditemukan!');
                hideItemPreview();
                currentPreviewItem = null;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Terjadi kesalahan saat mencari barcode!');
            hideItemPreview();
            currentPreviewItem = null;
        })
        .finally(() => {
            searchButton.innerHTML = originalText;
            searchButton.disabled = false;
            document.getElementById('barcodeInput').value = '';
            focusBarcodeInput();
        });
    }

    function showItemPreview(item) {
        document.getElementById('previewImage').src = item.image || '/img/default-product.png';
        document.getElementById('previewName').textContent = item.name;
        document.getElementById('previewSku').textContent = item.sku;
        document.getElementById('previewStock').textContent = item.stock_quantity;
        document.getElementById('previewUnit').textContent = item.unit;
        document.getElementById('previewPrice').textContent = formatCurrency(item.selling_price);
        
        document.getElementById('itemPreview').style.display = 'block';
    }

    function hideItemPreview() {
        document.getElementById('itemPreview').style.display = 'none';
    }

    function addToCartDirectly() {
        if (!currentPreviewItem) {
            showAlert('warning', 'Tidak ada item yang dipilih!');
            return;
        }

        // Check if item already in cart
        const existingItemIndex = cart.findIndex(item => item.id === currentPreviewItem.id);
        
        if (existingItemIndex !== -1) {
            // Check stock
            if (cart[existingItemIndex].quantity >= currentPreviewItem.stock_quantity) {
                showAlert('warning', `Stok ${currentPreviewItem.name} tidak mencukupi! Stok tersedia: ${currentPreviewItem.stock_quantity}`);
                return;
            }
            // Increment quantity
            cart[existingItemIndex].quantity += 1;
            showAlert('success', `${currentPreviewItem.name} quantity ditambah menjadi ${cart[existingItemIndex].quantity}`);
        } else {
            // Add new item
            cart.push({
                id: currentPreviewItem.id,
                name: currentPreviewItem.name,
                sku: currentPreviewItem.sku,
                barcode: currentPreviewItem.barcode,
                unit_price: currentPreviewItem.selling_price,
                quantity: 1,
                stock_quantity: currentPreviewItem.stock_quantity,
                unit: currentPreviewItem.unit
            });
            showAlert('success', `${currentPreviewItem.name} berhasil ditambahkan ke keranjang!`);
        }

        updateCartDisplay();
        updatePaymentSummary();
        hideItemPreview();
        currentPreviewItem = null;
        focusBarcodeInput();
    }

    function addToCart() {
        if (!currentPreviewItem) {
            showAlert('warning', 'Tidak ada item yang dipilih!');
            return;
        }

        addToCartDirectly();
    }

    function updateCartDisplay() {
        const cartItems = document.getElementById('cartItems');
        const emptyCart = document.getElementById('emptyCart');
        
        if (cart.length === 0) {
            // Clear all cart items first
            cartItems.innerHTML = '';
            // Show empty cart message
            const emptyRow = `
                <tr id="emptyCart">
                    <td colspan="5" class="text-center text-muted">
                        <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                        <p>Keranjang masih kosong</p>
                    </td>
                </tr>
            `;
            cartItems.innerHTML = emptyRow;
            return;
        }
        
        // Build cart items HTML
        let cartHTML = '';
        cart.forEach((item, index) => {
            cartHTML += `
                <tr>
                    <td>
                        <div class="d-flex px-2 py-1">
                            <div class="d-flex flex-column justify-content-center">
                                <h6 class="mb-0 text-sm">${escapeHtml(item.name)}</h6>
                                <p class="text-xs text-secondary mb-0">SKU: ${escapeHtml(item.sku)}</p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <p class="text-xs font-weight-bold mb-0">${formatCurrency(item.unit_price)}</p>
                        <p class="text-xs text-secondary mb-0">per ${escapeHtml(item.unit)}</p>
                    </td>
                    <td class="align-middle text-center">
                        <div class="d-flex align-items-center justify-content-center quantity-controls">
                            <button type="button" class="btn btn-sm btn-outline-danger me-1" onclick="decrementQuantity(${index})" ${item.quantity <= 1 ? 'disabled' : ''}>
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" 
                                   class="form-control form-control-sm text-center mx-1" 
                                   style="width: 60px;" 
                                   value="${item.quantity}" 
                                   min="1" 
                                   max="${item.stock_quantity}"
                                   title="Enter: Simpan | Esc: Batal | ↑↓: +/-"
                                   onchange="updateQuantityManual(${index}, this.value)"
                                   onblur="validateQuantityInput(${index}, this)"
                                   onfocus="this.select()"
                                   onkeydown="handleQuantityKeydown(event, ${index})">
                            <button type="button" class="btn btn-sm btn-outline-success ms-1" onclick="incrementQuantity(${index})" ${item.quantity >= item.stock_quantity ? 'disabled' : ''}>
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <small class="text-muted">Max: ${item.stock_quantity}</small>
                    </td>
                    <td class="align-middle text-center">
                        <span class="text-secondary text-xs font-weight-bold">${formatCurrency(item.unit_price * item.quantity)}</span>
                    </td>
                    <td class="align-middle text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFromCart(${index})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        
        cartItems.innerHTML = cartHTML;
    }

    function incrementQuantity(index) {
        if (cart[index].quantity < cart[index].stock_quantity) {
            cart[index].quantity += 1;
            updateCartDisplay();
            updatePaymentSummary();
        }
    }

    function decrementQuantity(index) {
        if (cart[index].quantity > 1) {
            cart[index].quantity -= 1;
            updateCartDisplay();
            updatePaymentSummary();
        }
    }

    function removeFromCart(index) {
        cart.splice(index, 1);
        updateCartDisplay();
        updatePaymentSummary();
        showAlert('info', 'Item dihapus dari keranjang');
    }

    function updateQuantityManual(index, newQuantity) {
        const quantity = parseInt(newQuantity);
        const item = cart[index];
        
        if (isNaN(quantity) || quantity < 1) {
            showAlert('warning', 'Quantity minimal adalah 1!');
            updateCartDisplay(); // Reset display
            return;
        }
        
        if (quantity > item.stock_quantity) {
            showAlert('warning', `Quantity maksimal untuk ${item.name} adalah ${item.stock_quantity}!`);
            updateCartDisplay(); // Reset display
            return;
        }
        
        cart[index].quantity = quantity;
        updateCartDisplay();
        updatePaymentSummary();
        showAlert('success', `Quantity ${item.name} diubah menjadi ${quantity}`);
    }

    function validateQuantityInput(index, inputElement) {
        const item = cart[index];
        const currentValue = parseInt(inputElement.value);
        
        // Jika input kosong atau invalid, kembalikan ke nilai sebelumnya
        if (isNaN(currentValue) || currentValue < 1) {
            inputElement.value = item.quantity;
            showAlert('warning', 'Quantity tidak valid, dikembalikan ke nilai sebelumnya');
            return;
        }
        
        // Jika melebihi stok, set ke maksimal stok
        if (currentValue > item.stock_quantity) {
            inputElement.value = item.stock_quantity;
            cart[index].quantity = item.stock_quantity;
            updateCartDisplay();
            updatePaymentSummary();
            showAlert('warning', `Quantity maksimal untuk ${item.name} adalah ${item.stock_quantity}!`);
            return;
        }
        
        // Jika valid, update
        cart[index].quantity = currentValue;
        updateCartDisplay();
        updatePaymentSummary();
    }

    function handleQuantityKeydown(event, index) {
        const input = event.target;
        
        // Handle Enter key - apply changes and focus back to barcode input
        if (event.key === 'Enter') {
            event.preventDefault();
            validateQuantityInput(index, input);
            focusBarcodeInput();
            return;
        }
        
        // Handle Escape key - cancel changes and focus back to barcode input
        if (event.key === 'Escape') {
            event.preventDefault();
            input.value = cart[index].quantity; // Reset to original value
            focusBarcodeInput();
            return;
        }
        
        // Handle Arrow Up - increment
        if (event.key === 'ArrowUp') {
            event.preventDefault();
            if (cart[index].quantity < cart[index].stock_quantity) {
                incrementQuantity(index);
            }
            return;
        }
        
        // Handle Arrow Down - decrement
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            if (cart[index].quantity > 1) {
                decrementQuantity(index);
            }
            return;
        }
    }

    function clearCart() {
        if (cart.length === 0) {
            showAlert('info', 'Keranjang sudah kosong');
            return;
        }
        
        if (confirm('Yakin ingin mengosongkan keranjang?')) {
            cart = [];
            currentPreviewItem = null;
            updateCartDisplay();
            updatePaymentSummary();
            hideItemPreview();
            document.getElementById('paidAmount').value = '';
            document.getElementById('changeAmount').value = 'Rp 0';
            document.getElementById('barcodeInput').value = '';
            showAlert('info', 'Keranjang dikosongkan');
            focusBarcodeInput();
        }
    }

    function updatePaymentSummary() {
        const subtotal = cart.reduce((sum, item) => sum + (item.unit_price * item.quantity), 0);
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        
        document.getElementById('subtotalAmount').textContent = formatCurrency(subtotal);
        document.getElementById('totalAmount').textContent = formatCurrency(subtotal);
        document.getElementById('totalItems').textContent = totalItems;
        
        // Enable/disable payment button
        const processButton = document.getElementById('processPayment');
        processButton.disabled = cart.length === 0;
        
        calculateChange();
    }

    function calculateChange() {
        const totalAmount = parseFloat(document.getElementById('totalAmount').textContent.replace(/[^0-9]/g, '')) || 0;
        const paidAmount = parseFloat(document.getElementById('paidAmount').value) || 0;
        const change = Math.max(0, paidAmount - totalAmount);
        
        document.getElementById('changeAmount').value = formatCurrency(change);
        
        // Update process button state
        const processButton = document.getElementById('processPayment');
        const paymentMethod = document.getElementById('paymentMethod').value;
        
        if (paymentMethod === 'cash') {
            processButton.disabled = cart.length === 0 || paidAmount < totalAmount;
        } else {
            processButton.disabled = cart.length === 0;
            document.getElementById('paidAmount').value = totalAmount;
        }
    }

    function setQuickAmount(amount) {
        document.getElementById('paidAmount').value = amount;
        calculateChange();
    }

    function setExactAmount() {
        const totalAmount = parseFloat(document.getElementById('totalAmount').textContent.replace(/[^0-9]/g, '')) || 0;
        document.getElementById('paidAmount').value = totalAmount;
        calculateChange();
    }

    function processPayment() {
        if (cart.length === 0) {
            showAlert('warning', 'Keranjang masih kosong!');
            return;
        }

        const totalAmount = parseFloat(document.getElementById('totalAmount').textContent.replace(/[^0-9]/g, '')) || 0;
        const paidAmount = parseFloat(document.getElementById('paidAmount').value) || 0;
        const paymentMethod = document.getElementById('paymentMethod').value;

        if (paymentMethod === 'cash' && paidAmount < totalAmount) {
            showAlert('warning', 'Jumlah pembayaran kurang dari total!');
            return;
        }

        // Show loading modal
        const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
        loadingModal.show();

        // Set timeout for loading modal
        const loadingTimeout = setTimeout(() => {
            loadingModal.hide();
            showAlert('error', 'Request timeout. Silakan coba lagi atau periksa koneksi internet.');
        }, 30000); // 30 seconds timeout

        // Prepare transaction data
        const transactionData = {
            items: cart.map(item => ({
                item_id: item.id,
                quantity: item.quantity,
                unit_price: item.unit_price,
                barcode_scanned: item.barcode
            })),
            paid_amount: paymentMethod === 'cash' ? paidAmount : totalAmount,
            payment_method: paymentMethod
        };

        console.log('Sending transaction data:', transactionData);

        // Send transaction
        fetch('/kasir/transaction', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(transactionData)
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            clearTimeout(loadingTimeout);
            loadingModal.hide();
            
            if (data.success) {
                // Show success modal
                lastTransactionId = data.data.transaction_id;
                document.getElementById('transactionCode').textContent = data.data.transaction_code;
                document.getElementById('modalTotal').textContent = formatCurrency(data.data.total_amount);
                document.getElementById('modalPaid').textContent = formatCurrency(data.data.paid_amount);
                document.getElementById('modalChange').textContent = formatCurrency(data.data.change_amount);
                
                const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                successModal.show();
                
                // Clear cart and reset form
                cart = [];
                currentPreviewItem = null;
                updateCartDisplay();
                updatePaymentSummary();
                hideItemPreview();
                document.getElementById('paidAmount').value = '';
                document.getElementById('changeAmount').value = 'Rp 0';
                document.getElementById('barcodeInput').value = '';
                
            } else {
                showAlert('error', data.message || 'Terjadi kesalahan saat memproses transaksi!');
            }
        })
        .catch(error => {
            clearTimeout(loadingTimeout);
            loadingModal.hide();
            console.error('Error:', error);
            
            // Show detailed error message
            let errorMessage = 'Terjadi kesalahan saat memproses transaksi!';
            if (error.message.includes('404')) {
                errorMessage = 'Endpoint transaksi tidak ditemukan. Pastikan migration sudah dijalankan.';
            } else if (error.message.includes('500')) {
                errorMessage = 'Server error. Periksa log aplikasi untuk detail.';
            } else if (error.message.includes('403')) {
                errorMessage = 'Akses ditolak. Periksa permission user.';
            }
            
            showAlert('error', errorMessage + ' Detail: ' + error.message);
        });
    }

    function printReceipt() {
        if (lastTransactionId) {
            window.open(`/kasir/receipt/${lastTransactionId}`, '_blank');
        }
    }

    function newTransaction() {
        lastTransactionId = null;
        document.getElementById('paymentMethod').value = 'cash';
        document.getElementById('cashPayment').style.display = 'block';
        focusBarcodeInput();
    }

    function formatCurrency(amount) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    function showAlert(type, message) {
        // Create alert element
        const alertTypes = {
            success: 'alert-success',
            error: 'alert-danger', 
            warning: 'alert-warning',
            info: 'alert-info'
        };
        
        const alertHtml = `
            <div class="alert ${alertTypes[type]} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
                <span class="alert-text">${message}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', alertHtml);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            if (alerts.length > 0) {
                alerts[alerts.length - 1].remove();
            }
        }, 3000);
    }
</script>
@endpush
