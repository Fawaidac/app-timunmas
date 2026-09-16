<script>
    // Konfirmasi aksi (hapus/approve) berbasis SweetAlert2.
    // Pemakaian: onsubmit="return confirmAction(event, this, 'Judul', 'Deskripsi')"
    function confirmAction(event, form, title, text, confirmColor) {
        event.preventDefault();
        Swal.fire({
            title: title || 'Konfirmasi',
            text: text || 'Tindakan ini tidak dapat dibatalkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: confirmColor || '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Lanjutkan',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then(function (result) {
            if (result.isConfirmed) {
                form.submit();
            }
        });
        return false;
    }
</script>
