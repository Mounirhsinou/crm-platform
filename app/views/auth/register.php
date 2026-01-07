<!DOCTYPE html>
<html lang="<?php echo Lang::current(); ?>" <?php echo Lang::isRtl() ? 'dir="rtl"' : ''; ?>>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - <?php echo APP_NAME; ?></title>
    <meta name="description"
        content="Create your CRM account and start managing clients, deals, and growing your business.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/register.css?v=1.1">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo APP_URL; ?>/assets/img/favicon.png">
</head>

<body>
    <div class="register-container">
        <!-- Left Side - Illustration/Branding -->
        <div class="register-left">
            <div class="animation-background">
                <div class="bg-orb orb-1"></div>
                <div class="bg-orb orb-2"></div>
                <div class="floating-elements">
                    <div class="float-icon icon-1">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path>
                            <path d="M22 12A10 10 0 0 0 12 2v10z"></path>
                        </svg>
                    </div>
                    <div class="float-icon icon-2">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <div class="float-icon icon-3">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="branding-content">
                <div class="logo-section">
                    <div class="logo-icon">
                        <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="48" height="48" rx="12" fill="url(#gradient)" />
                            <path
                                d="M24 14C18.48 14 14 18.48 14 24C14 29.52 18.48 34 24 34C29.52 34 34 29.52 34 24C34 18.48 29.52 14 24 14ZM24 20C25.66 20 27 21.34 27 23C27 24.66 25.66 26 24 26C22.34 26 21 24.66 21 23C21 21.34 22.34 20 24 20ZM24 31.2C21.5 31.2 19.29 29.92 18 28C18.03 26 22 24.9 24 24.9C25.99 24.9 29.97 26 30 28C28.71 29.92 26.5 31.2 24 31.2Z"
                                fill="white" />
                            <defs>
                                <linearGradient id="gradient" x1="0" y1="0" x2="48" y2="48"
                                    gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#2563EB" />
                                    <stop offset="1" stop-color="#1E40AF" />
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>
                    <h1 class="brand-name"><?php echo APP_NAME; ?></h1>
                </div>

                <div class="hero-content">
                    <h2 class="hero-title">Start growing your business today</h2>
                    <p class="hero-description">Join thousands of businesses using our CRM platform to manage clients,
                        close deals, and increase revenue.</p>

                    <div class="features-list">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M7.5 10L9.16667 11.6667L12.5 8.33333M17.5 10C17.5 14.1421 14.1421 17.5 10 17.5C5.85786 17.5 2.5 14.1421 2.5 10C2.5 5.85786 5.85786 2.5 10 2.5C14.1421 2.5 17.5 5.85786 17.5 10Z"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </div>
                            <span>Free 14-day trial, no credit card required</span>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M7.5 10L9.16667 11.6667L12.5 8.33333M17.5 10C17.5 14.1421 14.1421 17.5 10 17.5C5.85786 17.5 2.5 14.1421 2.5 10C2.5 5.85786 5.85786 2.5 10 2.5C14.1421 2.5 17.5 5.85786 17.5 10Z"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </div>
                            <span>Setup in minutes, not hours</span>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M7.5 10L9.16667 11.6667L12.5 8.33333M17.5 10C17.5 14.1421 14.1421 17.5 10 17.5C5.85786 17.5 2.5 14.1421 2.5 10C2.5 5.85786 5.85786 2.5 10 2.5C14.1421 2.5 17.5 5.85786 17.5 10Z"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </div>
                            <span>24/7 customer support</span>
                        </div>
                    </div>
                </div>

                <div class="benefits-card">
                    <h3 class="benefits-title">What you'll get:</h3>
                    <div class="benefits-list">
                        <div class="benefit-item">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M13.3334 4L6.00002 11.3333L2.66669 8" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span>Unlimited client contacts</span>
                        </div>
                        <div class="benefit-item">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M13.3334 4L6.00002 11.3333L2.66669 8" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span>Advanced deal pipeline</span>
                        </div>
                        <div class="benefit-item">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M13.3334 4L6.00002 11.3333L2.66669 8" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span>Invoice generation & tracking</span>
                        </div>
                        <div class="benefit-item">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M13.3334 4L6.00002 11.3333L2.66669 8" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span>Real-time analytics dashboard</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Register Form -->
        <div class="register-right">
            <div class="register-form-container">
                <div class="form-header">
                    <h2 class="form-title">Create your account</h2>
                    <p class="form-subtitle">Start your free trial today</p>
                </div>

                <form id="registerForm" class="register-form" method="POST"
                    action="<?php echo APP_URL; ?>/auth/register">
                    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">

                    <!-- Success Message -->
                    <?php if ($flash = Session::getFlash('success')): ?>
                        <div class="success-message">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M7.5 10L9.16667 11.6667L12.5 8.33333M17.5 10C17.5 14.1421 14.1421 17.5 10 17.5C5.85786 17.5 2.5 14.1421 2.5 10C2.5 5.85786 5.85786 2.5 10 2.5C14.1421 2.5 17.5 5.85786 17.5 10Z"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span><?php echo Security::escape($flash); ?></span>
                        </div>
                    <?php endif; ?>

                    <!-- Error Message -->
                    <?php if ($flash = Session::getFlash('error')): ?>
                        <div class="error-message">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M10 6V10M10 14H10.01M19 10C19 14.9706 14.9706 19 10 19C5.02944 19 1 14.9706 1 10C1 5.02944 5.02944 1 10 1C14.9706 1 19 5.02944 19 10Z"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span><?php echo Security::escape($flash); ?></span>
                        </div>
                    <?php endif; ?>

                    <!-- Company Name Input -->
                    <div class="form-group">
                        <label for="company_name" class="form-label"><?php echo _t('company_name'); ?></label>
                        <div class="input-wrapper">
                            <div class="input-icon">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M2.5 7.5L10 2.5L17.5 7.5M3.33333 8.33333V15.8333C3.33333 16.7538 4.07953 17.5 5 17.5H15C15.9205 17.5 16.6667 16.7538 16.6667 15.8333V8.33333M7.5 17.5V11.6667C7.5 10.7462 8.24619 10 9.16667 10H10.8333C11.7538 10 12.5 10.7462 12.5 11.6667V17.5"
                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </div>
                            <input type="text" id="company_name" name="company_name"
                                class="form-input <?php echo isset($errors['company_name']) ? 'input-error' : ''; ?>"
                                placeholder="Your Company Inc."
                                value="<?php echo Security::escape($company_name ?? ''); ?>" required autofocus>
                        </div>
                        <?php if (isset($errors['company_name'])): ?>
                            <div class="field-error"><?php echo $errors['company_name']; ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Full Name Input -->
                    <div class="form-group">
                        <label for="full_name" class="form-label"><?php echo _t('full_name'); ?></label>
                        <div class="input-wrapper">
                            <div class="input-icon">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M10 10C12.0711 10 13.75 8.32107 13.75 6.25C13.75 4.17893 12.0711 2.5 10 2.5C7.92893 2.5 6.25 4.17893 6.25 6.25C6.25 8.32107 7.92893 10 10 10ZM10 10C6.54822 10 3.75 12.0147 3.75 14.5V17.5H16.25V14.5C16.25 12.0147 13.4518 10 10 10Z"
                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </div>
                            <input type="text" id="full_name" name="full_name"
                                class="form-input <?php echo isset($errors['full_name']) ? 'input-error' : ''; ?>"
                                placeholder="John Smith" value="<?php echo Security::escape($full_name ?? ''); ?>"
                                required>
                        </div>
                        <?php if (isset($errors['full_name'])): ?>
                            <div class="field-error"><?php echo $errors['full_name']; ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Email Input -->
                    <div class="form-group">
                        <label for="email" class="form-label"><?php echo _t('email_address'); ?></label>
                        <div class="input-wrapper">
                            <div class="input-icon">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M2.5 6.66667L9.0755 11.0504C9.63533 11.4236 10.3647 11.4236 10.9245 11.0504L17.5 6.66667M4.16667 15.8333H15.8333C16.7538 15.8333 17.5 15.0871 17.5 14.1667V5.83333C17.5 4.91286 16.7538 4.16667 15.8333 4.16667H4.16667C3.24619 4.16667 2.5 4.91286 2.5 5.83333V14.1667C2.5 15.0871 3.24619 15.8333 4.16667 15.8333Z"
                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </div>
                            <input type="email" id="email" name="email"
                                class="form-input <?php echo isset($errors['email']) ? 'input-error' : ''; ?>"
                                placeholder="you@company.com" value="<?php echo Security::escape($email ?? ''); ?>"
                                required autocomplete="email">
                        </div>
                        <?php if (isset($errors['email'])): ?>
                            <div class="field-error"><?php echo $errors['email']; ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Password Input -->
                    <div class="form-group">
                        <label for="password" class="form-label"><?php echo _t('password'); ?></label>
                        <div class="input-wrapper">
                            <div class="input-icon">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M5.83333 9.16667V6.66667C5.83333 4.36548 7.69881 2.5 10 2.5C12.3012 2.5 14.1667 4.36548 14.1667 6.66667V9.16667M10 12.0833V13.75M6.5 17.5H13.5C14.4205 17.5 15.1667 16.7538 15.1667 15.8333V10.8333C15.1667 9.91286 14.4205 9.16667 13.5 9.16667H6.5C5.57953 9.16667 4.83333 9.91286 4.83333 10.8333V15.8333C4.83333 16.7538 5.57953 17.5 6.5 17.5Z"
                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </div>
                            <input type="password" id="password" name="password"
                                class="form-input <?php echo isset($errors['password']) ? 'input-error' : ''; ?>"
                                placeholder="Create a strong password" required autocomplete="new-password">
                            <button type="button" class="toggle-password" id="togglePassword"
                                aria-label="Toggle password visibility">
                                <svg class="eye-icon" width="20" height="20" viewBox="0 0 20 20" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M2.5 10C2.5 10 5 4.16667 10 4.16667C15 4.16667 17.5 10 17.5 10C17.5 10 15 15.8333 10 15.8333C5 15.8333 2.5 10 2.5 10Z"
                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path
                                        d="M10 12.5C11.3807 12.5 12.5 11.3807 12.5 10C12.5 8.61929 11.3807 7.5 10 7.5C8.61929 7.5 7.5 8.61929 7.5 10C7.5 11.3807 8.61929 12.5 10 12.5Z"
                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>
                        <div class="password-strength" id="passwordStrength">
                            <div class="strength-bar"></div>
                            <div class="strength-bar"></div>
                            <div class="strength-bar"></div>
                            <div class="strength-bar"></div>
                        </div>
                        <div class="strength-text" id="strengthText"></div>
                        <?php if (isset($errors['password'])): ?>
                            <div class="field-error"><?php echo $errors['password']; ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Confirm Password Input -->
                    <div class="form-group">
                        <label for="confirm_password" class="form-label"><?php echo _t('confirm_password'); ?></label>
                        <div class="input-wrapper">
                            <div class="input-icon">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M5.83333 9.16667V6.66667C5.83333 4.36548 7.69881 2.5 10 2.5C12.3012 2.5 14.1667 4.36548 14.1667 6.66667V9.16667M10 12.0833V13.75M6.5 17.5H13.5C14.4205 17.5 15.1667 16.7538 15.1667 15.8333V10.8333C15.1667 9.91286 14.4205 9.16667 13.5 9.16667H6.5C5.57953 9.16667 4.83333 9.91286 4.83333 10.8333V15.8333C4.83333 16.7538 5.57953 17.5 6.5 17.5Z"
                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </div>
                            <input type="password" id="confirm_password" name="confirm_password"
                                class="form-input <?php echo isset($errors['confirm_password']) ? 'input-error' : ''; ?>"
                                placeholder="Re-enter your password" required autocomplete="new-password">
                            <button type="button" class="toggle-password" id="toggleConfirmPassword"
                                aria-label="Toggle confirm password visibility">
                                <svg class="eye-icon" width="20" height="20" viewBox="0 0 20 20" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M2.5 10C2.5 10 5 4.16667 10 4.16667C15 4.16667 17.5 10 17.5 10C17.5 10 15 15.8333 10 15.8333C5 15.8333 2.5 10 2.5 10Z"
                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path
                                        d="M10 12.5C11.3807 12.5 12.5 11.3807 12.5 10C12.5 8.61929 11.3807 7.5 10 7.5C8.61929 7.5 7.5 8.61929 7.5 10C7.5 11.3807 8.61929 12.5 10 12.5Z"
                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>
                        <?php if (isset($errors['confirm_password'])): ?>
                            <div class="field-error"><?php echo $errors['confirm_password']; ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Register Button -->
                    <button type="submit" class="btn-primary" id="registerButton">
                        <span class="btn-text"><?php echo _t('sign_up'); ?></span>
                        <div class="btn-loader" style="display: none;">
                            <svg class="spinner" width="20" height="20" viewBox="0 0 20 20" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M10 2.5C5.85786 2.5 2.5 5.85786 2.5 10C2.5 14.1421 5.85786 17.5 10 17.5C14.1421 17.5 17.5 14.1421 17.5 10"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                            </svg>
                            <span>Creating account...</span>
                        </div>
                    </button>

                    <!-- Terms & Privacy -->
                    <p class="terms-text">
                        By creating an account, you agree to our
                        <a href="#">Terms of Service</a> and
                        <a href="#">Privacy Policy</a>
                    </p>
                </form>

                <!-- Footer -->
                <div class="form-footer">
                    <p>Already have an account? <a href="<?php echo APP_URL; ?>/auth/login" class="signin-link">Sign
                            in</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo APP_URL; ?>/assets/js/register.js"></script>
</body>

</html>