<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password -
        <?php echo APP_NAME; ?>
    </title>
    <meta name="description" content="Reset your password to regain access to your CRM dashboard.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/login.css?v=1.1">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo APP_URL; ?>/assets/img/favicon.png">
</head>

<body>
    <div class="login-container">
        <!-- Left Side - Illustration/Branding -->
        <div class="login-left">
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
                    <h1 class="brand-name">
                        <?php echo APP_NAME; ?>
                    </h1>
                </div>

                <div class="hero-content">
                    <h2 class="hero-title">Reset your password</h2>
                    <p class="hero-description">Don't worry, it happens. Enter your email address and we'll send you
                        instructions to reset your password.</p>
                </div>
            </div>
        </div>

        <!-- Right Side - Forgot Password Form -->
        <div class="login-right">
            <div class="login-form-container">
                <div class="form-header">
                    <h2 class="form-title">Forgot Password</h2>
                    <p class="form-subtitle">Enter your email to request a reset link</p>
                </div>

                <form id="forgotPasswordForm" class="login-form" method="POST"
                    action="<?php echo APP_URL; ?>/auth/forgotPassword">
                    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">

                    <!-- Email Input -->
                    <div class="form-group">
                        <label for="email" class="form-label">Email address</label>
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
                                required autofocus autocomplete="email">
                        </div>
                        <?php if (isset($errors['email'])): ?>
                            <div class="field-error">
                                <?php echo $errors['email']; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-primary" id="resetButton">
                        <span class="btn-text">Send reset link</span>
                    </button>
                </form>

                <!-- Footer -->
                <div class="form-footer">
                    <p>Remember your password? <a href="<?php echo APP_URL; ?>/auth/login" class="signup-link">Back to
                            login</a></p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>