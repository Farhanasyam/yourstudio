{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteConfirmModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-trash-alt text-danger" style="font-size: 3rem;"></i>
                </div>
                <p class="text-center fs-5 mb-0">Apakah anda yakin ingin menghapus data ini?</p>
                <p class="text-center text-muted">Data yang sudah dihapus tidak dapat dikembalikan!</p>
            </div>
            <div class="modal-footer justify-content-center border-0">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Batal
                </button>
                <button type="button" class="btn btn-danger px-4" id="confirmDelete">
                    <i class="fas fa-trash-alt me-2"></i>Ya, Hapus!
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Create Success Modal --}}
<div class="modal fade" id="createSuccessModal" tabindex="-1" aria-labelledby="createSuccessModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="createSuccessModalLabel">
                    <i class="fas fa-check-circle me-2"></i>Berhasil
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                </div>
                <p class="text-center fs-5 mb-0">Data berhasil ditambahkan!</p>
                <p class="text-center text-muted">Data telah berhasil disimpan ke dalam sistem.</p>
            </div>
            <div class="modal-footer justify-content-center border-0">
                <button type="button" class="btn btn-success px-4" data-bs-dismiss="modal">
                    <i class="fas fa-check me-2"></i>OK
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Update Success Modal --}}
<div class="modal fade" id="updateSuccessModal" tabindex="-1" aria-labelledby="updateSuccessModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="updateSuccessModalLabel">
                    <i class="fas fa-check-circle me-2"></i>Berhasil
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-check-circle text-info" style="font-size: 3rem;"></i>
                </div>
                <p class="text-center fs-5 mb-0">Data berhasil diperbarui!</p>
                <p class="text-center text-muted">Perubahan telah berhasil disimpan ke dalam sistem.</p>
            </div>
            <div class="modal-footer justify-content-center border-0">
                <button type="button" class="btn btn-info text-white px-4" data-bs-dismiss="modal">
                    <i class="fas fa-check me-2"></i>OK
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Error Modal --}}
<div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="errorModalLabel">
                    <i class="fas fa-exclamation-circle me-2"></i>Error
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-times-circle text-danger" style="font-size: 3rem;"></i>
                </div>
                <p class="text-center fs-5 mb-0" id="errorMessage">Terjadi kesalahan!</p>
                <p class="text-center text-muted">Silakan coba lagi atau hubungi administrator.</p>
            </div>
            <div class="modal-footer justify-content-center border-0">
                <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>
