/**
 * SweetAlert2 helpers untuk konfirmasi dan notifikasi di seluruh aplikasi.
 * Pastikan SweetAlert2 sudah dimuat sebelum script ini.
 */
(function() {
    'use strict';

    /**
     * Konfirmasi sebelum submit form (untuk aksi krusial: hapus, bulk generate, dll.)
     * @param {HTMLFormElement} form - Element form
     * @param {Object} options - { title, html, icon, confirmButtonText, cancelButtonText }
     * @returns {Promise<boolean>} - true jika user konfirmasi
     */
    window.confirmSubmit = function(form, options) {
        var opts = Object.assign({
            title: 'Konfirmasi',
            html: 'Apakah Anda yakin?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Lanjutkan',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }, options || {});

        return Swal.fire(opts).then(function(result) {
            if (result.isConfirmed && form) {
                form.submit();
            }
            return result.isConfirmed;
        });
    };

    /**
     * Notifikasi sukses/error/info (menggantikan alert)
     * @param {string} message - Pesan
     * @param {string} type - 'success' | 'error' | 'warning' | 'info'
     * @param {string} title - Judul opsional
     */
    window.swalNotify = function(message, type, title) {
        type = type || 'info';
        title = title || (type === 'success' ? 'Berhasil' : type === 'error' ? 'Error' : type === 'warning' ? 'Perhatian' : 'Info');
        Swal.fire({
            title: title,
            text: message,
            icon: type,
            confirmButtonColor: '#3085d6'
        });
    };

    /**
     * Konfirmasi dengan input teks (mis. ketik "HAPUS")
     * @param {Object} options - title, html, inputValue, inputValidator, confirmButtonText
     * @returns {Promise<string|null>} - nilai input jika dikonfirmasi, null jika batal
     */
    window.confirmWithInput = function(options) {
        var opts = Object.assign({
            title: 'Konfirmasi',
            input: 'text',
            inputPlaceholder: '',
            inputValidator: function() { return null; },
            showCancelButton: true,
            confirmButtonText: 'Konfirmasi',
            cancelButtonText: 'Batal'
        }, options || {});
        return Swal.fire(opts).then(function(result) {
            return result.isConfirmed ? result.value : null;
        });
    };
})();
