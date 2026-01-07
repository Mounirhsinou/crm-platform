/**
 * CRM Login Page JavaScript
 * Handles form validation, password toggle, and UX enhancements
 */

document.addEventListener('DOMContentLoaded', function () {
    // Elements - with null checks
    const loginForm = document.getElementById('loginForm');
    const loginButton = document.getElementById('loginButton');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');

    // Optional elements (may not exist in all templates)
    const googleLoginBtn = document.getElementById('googleLogin');

    if (!loginForm || !loginButton || !emailInput || !passwordInput) {
        console.error('Required form elements not found');
        return;
    }

    /**
     * Password Toggle Functionality
     */
    if (togglePassword) {
        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            // Toggle icon
            const eyeIcon = this.querySelector('.eye-icon');
            if (eyeIcon) {
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
        });
    }

    /**
     * Add input animation on focus
     */
    const inputs = document.querySelectorAll('.form-input');
    inputs.forEach(input => {
        input.addEventListener('focus', function () {
            if (this.parentElement) {
                this.parentElement.style.transform = 'scale(1.01)';
            }
        });

        input.addEventListener('blur', function () {
            if (this.parentElement) {
                this.parentElement.style.transform = 'scale(1)';
            }
        });
    });

    /**
     * Google Login Handler (if button exists)
     */
    if (googleLoginBtn) {
        googleLoginBtn.addEventListener('click', function (e) {
            // If it's a link, let it navigate naturally
            // If it's a button, show alert
            if (this.tagName === 'BUTTON') {
                e.preventDefault();
                alert('Google login integration coming soon!');
            }
        });
    }

    /**
     * Auto-focus email input on page load
     */
    if (!emailInput.value) {
        emailInput.focus();
    }

    /**
     * Form submission - let it submit normally to PHP
     * The PHP backend handles all validation and redirects
     */
    loginForm.addEventListener('submit', function (e) {
        // Basic client-side validation
        const email = emailInput.value.trim();
        const password = passwordInput.value;

        if (!email) {
            e.preventDefault();
            alert('Please enter your email address.');
            emailInput.focus();
            return false;
        }

        if (!password) {
            e.preventDefault();
            alert('Please enter your password.');
            passwordInput.focus();
            return false;
        }

        // Disable button to prevent double submission
        loginButton.disabled = true;
        loginButton.style.opacity = '0.7';

        // Let the form submit normally to PHP
        return true;
    });
});
