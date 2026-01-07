<script>
    // Logo upload handler with validation and feedback
    document.addEventListener('DOMContentLoaded', function () {
        const logoInput = document.getElementById('logo');
        const submitBtn = document.querySelector('button[type="submit"]');

        if (logoInput) {
            logoInput.addEventListener('change', function (e) {
                const file = e.target.files[0];

                if (!file) return;

                // Validate file size (2MB max)
                if (file.size > 2 * 1024 * 1024) {
                    alert('Logo file size must be less than 2MB');
                    e.target.value = '';
                    return;
                }

                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/svg+xml'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Only JPG, PNG, and SVG files are allowed');
                    e.target.value = '';
                    return;
                }

                // Update submit button to show file is ready
                if (submitBtn) {
                    submitBtn.innerHTML = '<i class="bi bi-upload me-2"></i>Upload ' + file.name;
                    submitBtn.classList.add('btn-success');
                    submitBtn.classList.remove('btn-primary');
                }

                // Show preview
                const reader = new FileReader();
                reader.onload = function (event) {
                    const logoPreview = document.querySelector('.logo-preview-img');
                    if (logoPreview) {
                        logoPreview.src = event.target.result;
                    }
                };
                reader.readAsDataURL(file);
            });
        }
    });
</script>