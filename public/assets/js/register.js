/**
 * CRM Register Page JavaScript
 * Handles form validation, password strength, and UI interactions
 */

document.addEventListener('DOMContentLoaded', function () {
    // Form elements
    const registerForm = document.getElementById('registerForm');
    const registerButton = document.getElementById('registerButton');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const togglePasswordBtn = document.getElementById('togglePassword');
    const toggleConfirmPasswordBtn = document.getElementById('toggleConfirmPassword');
    const strengthBars = document.querySelectorAll('.strength-bar');
    const strengthText = document.getElementById('strengthText');
    const errorMessage = document.getElementById('errorMessage');
    const errorText = document.getElementById('errorText');
    const successMessage = document.getElementById('successMessage');

    // Password toggle functionality
    if (togglePasswordBtn) {
        togglePasswordBtn.addEventListener('click', function () {
            togglePasswordVisibility(passwordInput, this);
        });
    }

    if (toggleConfirmPasswordBtn) {
        toggleConfirmPasswordBtn.addEventListener('click', function () {
            togglePasswordVisibility(confirmPasswordInput, this);
        });
    }

    /**
     * Toggle password visibility
     */
    function togglePasswordVisibility(input, button) {
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);

        // Update icon
        const eyeIcon = button.querySelector('.eye-icon');
        if (type === 'text') {
            eyeIcon.innerHTML = `
                <path d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            `;
        } else {
            eyeIcon.innerHTML = `
                <path d="M2.5 10C2.5 10 5 4.16667 10 4.16667C15 4.16667 17.5 10 17.5 10C17.5 10 15 15.8333 10 15.8333C5 15.8333 2.5 10 2.5 10Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M10 12.5C11.3807 12.5 12.5 11.3807 12.5 10C12.5 8.61929 11.3807 7.5 10 7.5C8.61929 7.5 7.5 8.61929 7.5 10C7.5 11.3807 8.61929 12.5 10 12.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            `;
        }
    }

    /**
     * Password strength checker
     */
    if (passwordInput) {
        passwordInput.addEventListener('input', function () {
            const password = this.value;
            const strength = calculatePasswordStrength(password);
            updatePasswordStrengthUI(strength);

            // Clear password mismatch error when typing
            if (confirmPasswordInput.value) {
                validatePasswordMatch();
            }
        });
    }

    /**
     * Confirm password validation
     */
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', function () {
            validatePasswordMatch();
        });
    }

    /**
     * Validate password match
     */
    function validatePasswordMatch() {
        if (confirmPasswordInput.value && passwordInput.value !== confirmPasswordInput.value) {
            confirmPasswordInput.classList.add('input-error');
        } else {
            confirmPasswordInput.classList.remove('input-error');
        }
    }

    /**
     * Calculate password strength
     */
    function calculatePasswordStrength(password) {
        let strength = 0;

        if (password.length === 0) {
            return 0;
        }

        // Length check
        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;

        // Character variety checks
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++; // Mixed case
        if (/\d/.test(password)) strength++; // Numbers
        if (/[^a-zA-Z0-9]/.test(password)) strength++; // Special characters

        // Normalize to 0-4 scale
        if (strength <= 2) return 1; // Weak
        if (strength <= 3) return 2; // Medium
        if (strength === 4) return 3; // Good
        return 4; // Strong
    }

    /**
     * Update password strength UI
     */
    function updatePasswordStrengthUI(strength) {
        // Reset all bars
        strengthBars.forEach(bar => {
            bar.classList.remove('active', 'medium', 'strong');
        });

        // Update strength text
        strengthText.classList.remove('weak', 'medium', 'strong');

        if (strength === 0) {
            strengthText.textContent = '';
            return;
        }

        // Activate bars based on strength
        for (let i = 0; i < strength; i++) {
            if (strengthBars[i]) {
                strengthBars[i].classList.add('active');

                if (strength === 2) {
                    strengthBars[i].classList.add('medium');
                } else if (strength >= 3) {
                    strengthBars[i].classList.add('strong');
                }
            }
        }

        // Update text
        if (strength === 1) {
            strengthText.textContent = 'Weak password';
            strengthText.classList.add('weak');
        } else if (strength === 2) {
            strengthText.textContent = 'Medium password';
            strengthText.classList.add('medium');
        } else if (strength >= 3) {
            strengthText.textContent = 'Strong password';
            strengthText.classList.add('strong');
        }
    }

    /**
     * Show error message
     */
    function showError(message) {
        if (errorMessage && errorText) {
            errorText.textContent = message;
            errorMessage.style.display = 'flex';

            // Scroll to top to show error
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    /**
     * Hide error message
     */
    function hideError() {
        if (errorMessage) {
            errorMessage.style.display = 'none';
        }
    }

    /**
     * Show success message
     */
    function showSuccess(message) {
        if (successMessage) {
            const successText = successMessage.querySelector('span');
            if (successText) {
                successText.textContent = message;
            }
            successMessage.style.display = 'flex';

            // Scroll to top to show success
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    /**
     * Form validation
     */
    if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
            e.preventDefault();
            let isValid = true;
            let errorMsg = '';

            // Hide previous errors
            hideError();

            // Clear previous field errors
            document.querySelectorAll('.form-input').forEach(input => {
                input.classList.remove('input-error');
            });

            // Validate company name
            const companyName = document.getElementById('company_name');
            if (companyName && companyName.value.trim().length < 2) {
                isValid = false;
                errorMsg = 'Company name must be at least 2 characters';
                companyName.classList.add('input-error');
            }

            // Validate full name
            const fullName = document.getElementById('full_name');
            if (fullName && fullName.value.trim().length < 2) {
                isValid = false;
                errorMsg = 'Full name must be at least 2 characters';
                fullName.classList.add('input-error');
            }

            // Validate email
            const email = document.getElementById('email');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email && !emailRegex.test(email.value)) {
                isValid = false;
                errorMsg = 'Please enter a valid email address';
                email.classList.add('input-error');
            }

            // Validate password strength (minimum 6 characters)
            if (passwordInput.value.length < 6) {
                isValid = false;
                errorMsg = 'Password must be at least 6 characters';
                passwordInput.classList.add('input-error');
            }

            // Validate password match
            if (passwordInput.value !== confirmPasswordInput.value) {
                isValid = false;
                errorMsg = 'Passwords do not match';
                confirmPasswordInput.classList.add('input-error');
            }

            if (!isValid) {
                showError(errorMsg);
                return false;
            }

            // Show loading state
            const btnText = registerButton.querySelector('.btn-text');
            const btnLoader = registerButton.querySelector('.btn-loader');

            if (btnText && btnLoader) {
                btnText.style.display = 'none';
                btnLoader.style.display = 'flex';
            }

            registerButton.disabled = true;

            // Let the form submit to the PHP controller
            registerForm.submit();
        });
    }

    /**
     * Input focus animations
     */
    const inputs = document.querySelectorAll('.form-input');
    inputs.forEach(input => {
        input.addEventListener('focus', function () {
            this.parentElement.classList.add('focused');
        });

        input.addEventListener('blur', function () {
            this.parentElement.classList.remove('focused');
        });

        // Clear error state on input
        input.addEventListener('input', function () {
            this.classList.remove('input-error');
            hideError();
        });
    });

    /**
     * Auto-dismiss flash messages
     */
    const flashMessages = document.querySelectorAll('.success-message, .error-message');
    flashMessages.forEach(message => {
        // Only auto-dismiss success messages, not errors
        if (message.classList.contains('success-message')) {
            setTimeout(() => {
                message.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
                message.style.opacity = '0';
                message.style.transform = 'translateY(-10px)';

                setTimeout(() => {
                    message.remove();
                }, 300);
            }, 5000);
        }
    });
});
