// Sweet Alert Delete Confirmation
function deleteConfirmation(formId) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: "Apakah anda yakin ingin menghapus data ini?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(formId).submit();
        }
    });
}
