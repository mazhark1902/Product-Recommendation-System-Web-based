<script>
    window.addEventListener('swal:success', event => {
        Swal.fire({
            icon: 'success',
            title: event.detail.title,
            text: event.detail.text,
            timer: 3000,
            showConfirmButton: false
        });
    });
</script>
