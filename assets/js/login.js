/**
 * CRM Login Page JavaScript
 * Handles form validation, password toggle, loading states, and error display
 */

document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const loginForm = document.getElementById('loginForm');
    const loginButton = document.getElementById('loginButton');
    const btnText = loginButton.querySelector('.btn-text');
    const btnLoader = loginButton.querySelector('.btn-loader');
    const errorMessage = document.getElementById('errorMessage');
    const errorText = document.getElementById('errorText');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');
    const googleLoginBtn = document.getElementById('googleLogin');

    /**
     * Password Toggle Functionality
     */
    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Toggle icon
        const eyeIcon = this.querySelector('.eye-icon');
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
    });

    /**
     * Hide error message when user starts typing
     */
    emailInput.addEventListener('input', hideError);
    passwordInput.addEventListener('input', hideError);

    function hideError() {
        if (errorMessage.style.display !== 'none') {
            errorMessage.style.display = 'none';
        }
    }

    /**
     * Show error message
     */
    function showError(message) {
        errorText.textContent = message;
        errorMessage.style.display = 'flex';
        
        // Scroll to error message
        errorMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    /**
     * Set loading state
     */
    function setLoading(isLoading) {
        if (isLoading) {
            loginButton.disabled = true;
            btnText.style.display = 'none';
            btnLoader.style.display = 'flex';
        } else {
            loginButton.disabled = false;
            btnText.style.display = 'block';
            btnLoader.style.display = 'none';
        }
    }

    /**
     * Validate email format
     */
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    /**
     * Form submission handler
     */
    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Hide any existing errors
        hideError();
        
        // Get form values
        const email = emailInput.value.trim();
        const password = passwordInput.value;
        
        // Client-side validation
        if (!email) {
            showError('Please enter your email address.');
            emailInput.focus();
            return;
        }
        
        if (!isValidEmail(email)) {
            showError('Please enter a valid email address.');
            emailInput.focus();
            return;
        }
        
        if (!password) {
            showError('Please enter your password.');
            passwordInput.focus();
            return;
        }
        
        if (password.length < 6) {
            showError('Password must be at least 6 characters long.');
            passwordInput.focus();
            return;
        }
        
        // Set loading state
        setLoading(true);
        
        try {
            // Submit form via AJAX
            const formData = new FormData(loginForm);
            
            const response = await fetch(loginForm.action, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Success - redirect to dashboard
                window.location.href = result.redirect || 'views/dashboard.php';
            } else {
                // Show error message
                showError(result.message || 'Invalid email or password. Please try again.');
                setLoading(false);
            }
        } catch (error) {
            console.error('Login error:', error);
            showError('An error occurred. Please try again later.');
            setLoading(false);
        }
    });

    /**
     * Google Login Handler (Placeholder)
     */
    googleLoginBtn.addEventListener('click', function() {
        // TODO: Implement Google OAuth login
        alert('Google login integration coming soon!');
    });

    /**
     * Add input animation on focus
     */
    const inputs = document.querySelectorAll('.form-input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.01)';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
    });

    /**
     * Check for URL parameters (e.g., error messages from server)
     */
    const urlParams = new URLSearchParams(window.location.search);
    const errorParam = urlParams.get('error');
    
    if (errorParam) {
        const errorMessages = {
            'invalid': 'Invalid email or password. Please try again.',
            'expired': 'Your session has expired. Please log in again.',
            'unauthorized': 'You must be logged in to access that page.',
            'locked': 'Your account has been locked. Please contact support.',
            'inactive': 'Your account is inactive. Please contact support.'
        };
        
        showError(errorMessages[errorParam] || 'An error occurred. Please try again.');
    }

    /**
     * Auto-focus email input on page load
     */
    if (!emailInput.value) {
        emailInput.focus();
    }
});
