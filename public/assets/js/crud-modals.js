// Initialize CRUD Modals
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all modals
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    const createSuccessModal = new bootstrap.Modal(document.getElementById('createSuccessModal'));
    const updateSuccessModal = new bootstrap.Modal(document.getElementById('updateSuccessModal'));
    const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
    
    let formToDelete = null;

    // Handle delete button clicks
    document.querySelectorAll('[data-action="delete"]').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            formToDelete = this.closest('form');
            deleteModal.show();
        });
    });

    // Handle confirm delete
    document.getElementById('confirmDelete')?.addEventListener('click', function() {
        if (formToDelete) {
            // Show loading state
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Menghapus...';
            this.disabled = true;
            
            // Submit the form
            formToDelete.submit();
        }
    });

    // Reset delete modal state when hidden
    document.getElementById('deleteConfirmModal')?.addEventListener('hidden.bs.modal', function() {
        const confirmBtn = document.getElementById('confirmDelete');
        if (confirmBtn) {
            confirmBtn.innerHTML = '<i class="fas fa-trash-alt me-2"></i>Ya, Hapus!';
            confirmBtn.disabled = false;
        }
        formToDelete = null;
    });

    // Handle form submissions
    document.querySelectorAll('form[data-type]').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const formType = this.dataset.type;
            
            // Add loading state to submit button
            const submitBtn = this.querySelector('[type="submit"]');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...';
                submitBtn.disabled = true;

                // Reset button state after submission
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 1000);
            }

            // Show success modal after successful submission
            if (formType === 'create') {
                setTimeout(() => createSuccessModal.show(), 1000);
            } else if (formType === 'update') {
                setTimeout(() => updateSuccessModal.show(), 1000);
            }
        });
    });

    // Function to show error modal
    window.showErrorModal = function(message) {
        const errorMessageEl = document.getElementById('errorMessage');
        if (errorMessageEl) {
            errorMessageEl.textContent = message || 'Terjadi kesalahan!';
        }
        errorModal.show();
    };

    // Handle AJAX errors
    document.addEventListener('ajax:error', function() {
        showErrorModal();
    });
});

// Helper function to show alerts
function showAlert(type, message) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'info-circle'} flex-shrink-0 me-2"></i>
            <div>${message}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    const alertContainer = document.getElementById('alert');
    if (alertContainer) {
        alertContainer.innerHTML = alertHtml;
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            const alert = alertContainer.querySelector('.alert');
            if (alert) {
                alert.classList.remove('show');
                setTimeout(() => alertContainer.innerHTML = '', 150);
            }
        }, 5000);
    }
}
