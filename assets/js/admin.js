document.addEventListener('DOMContentLoaded', function () {
    // 1. Sidebar toggle on mobile
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('adminOverlay');
    const toggleBtn = document.getElementById('adminSidebarToggle');

    if (toggleBtn && sidebar && overlay) {
        toggleBtn.addEventListener('click', function () {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        });

        overlay.addEventListener('click', function () {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        });
    }

    // 2. Live image preview for file input
    const fileInput = document.getElementById('imageFileInput');
    const previewImg = document.getElementById('imagePreview');
    const previewPlaceholder = document.getElementById('imagePreviewPlaceholder');

    if (fileInput && previewImg) {
        fileInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    previewImg.classList.remove('d-none');
                    if (previewPlaceholder) {
                        previewPlaceholder.classList.add('d-none');
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // 3. Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) {
                bsAlert.close();
            }
        }, 5000);
    });
});
