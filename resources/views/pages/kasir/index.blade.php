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
// Cart management
let cart = {};

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
                <p class="text-sm font-weight-bold mb-0">${item.name}</p>
                <span class="text-xs text-secondary">${barcode}</span>
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
    
    const barcode = product.barcode;
    
    if (cart[barcode]) {
        cart[barcode].quantity++;
        showAlert(`${product.name} +1 (${cart[barcode].quantity})`, 'success');
    } else {
        cart[barcode] = {
            id: product.id,
            name: product.name,
            price: parseFloat(product.selling_price),
            quantity: 1
        };
        showAlert(`${product.name} ditambahkan ke keranjang`, 'success');
    }
    
    updateCart();
}

// Increase quantity
function increaseQuantity(barcode) {
    if (cart[barcode]) {
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

// Scan barcode function
function scanBarcode(barcode) {
    if (!barcode || barcode.length === 0) {
        showAlert('Barcode tidak boleh kosong', 'error');
        return;
    }

    // Get CSRF token
    const token = document.querySelector('meta[name="csrf-token"]');
    if (!token) {
        console.error('CSRF token not found');
        showAlert('CSRF token tidak ditemukan', 'error');
        return;
    }
    
    // Send request to server
    fetch('/kasir/search-barcode', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token.content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ barcode: barcode })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success && data.data) {
            addToCart(data.data);
        } else {
            showAlert(data.message || 'Produk tidak ditemukan', 'error');
        }
    })
    .catch(error => {
        console.error('Request failed:', error);
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
        
        // Auto-scan when barcode is complete (13 digits for EAN13)
        if (barcode.length === 13) {
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

    // Keep focus on barcode input
    document.addEventListener('click', function(e) {
        if (e.target !== barcodeInput && !e.target.closest('.modal')) {
            barcodeInput.focus();
        }
    });
    
    // Initialize cart display
    updateCart();
    
    // Setup payment handlers
    setupPaymentHandlers();
});

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
    const paid = parseInt(paidInput.value) || 0;
    
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

// Process payment transaction
function processPaymentTransaction(total, paid, change) {
    console.log('Processing transaction:', { total, paid, change });
    
    // Get cart items for transaction
    const items = [];
    for (let barcode in cart) {
        const item = cart[barcode];
        items.push({
            id: item.id,
            name: item.name,
            price: item.price,
            quantity: item.quantity,
            subtotal: item.price * item.quantity
        });
    }
    
    // Get payment method
    const paymentMethod = document.querySelector('select[name="payment_method"]')?.value || 'Tunai';
    
    // Prepare transaction data
    const transactionData = {
        items: items,
        total_amount: total,
        paid_amount: paid,
        change_amount: change,
        payment_method: paymentMethod,
        _token: document.querySelector('meta[name="csrf-token"]').content
    };
    
    console.log('Sending transaction data:', transactionData);
    
    // Send transaction to server
    fetch('/kasir/transaction', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify(transactionData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            console.log('Transaction successful:', data);
            
            // Show success message
            Swal.fire({
                title: 'Pembayaran Berhasil!',
                text: 'Transaksi telah diproses. Mencetak struk...',
                icon: 'success',
                showConfirmButton: false,
                timer: 2000
            }).then(() => {
                // Redirect to receipt page
                window.location.href = `/kasir/receipt/${data.transaction_id}`;
            });
        } else {
            showAlert(data.message || 'Gagal memproses transaksi', 'error');
        }
    })
    .catch(error => {
        console.error('Transaction failed:', error);
        showAlert('Gagal memproses transaksi: ' + error.message, 'error');
    });
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