<!DOCTYPE html>
<html lang="<?php echo Lang::current(); ?>" <?php echo Lang::isRtl() ? 'dir="rtl"' : ''; ?>>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - <?php echo APP_NAME; ?></title>
    <meta name="description" content="Sign in to your CRM dashboard to manage clients, deals, and grow your business.">
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
                    <h1 class="brand-name"><?php echo APP_NAME; ?></h1>
                </div>

                <div class="hero-content">
                    <h2 class="hero-title">Manage your business relationships with ease</h2>
                    <p class="hero-description">Track clients, close deals, and grow your revenue with our powerful CRM
                        platform trusted by thousands of businesses worldwide.</p>

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
                            <span>360° Client Management</span>
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
                            <span>Advanced Deal Pipeline</span>
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
                            <span>Real-time Analytics</span>
                        </div>
                    </div>
                </div>

                <div class="testimonial">
                    <p class="testimonial-text">"This CRM transformed how we manage our client relationships. Revenue
                        increased by 40% in just 6 months."</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">JD</div>
                        <div class="author-info">
                            <div class="author-name">John Davis</div>
                            <div class="author-role">CEO, TechVentures Inc.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="login-right">
            <div class="login-form-container">
                <div class="form-header">
                    <h2 class="form-title"><?php echo _t('welcome_back'); ?></h2>
                    <p class="form-subtitle"><?php echo _t('sign_in_to_dashboard'); ?></p>
                </div>

                <form id="loginForm" class="login-form" method="POST" action="<?php echo APP_URL; ?>/auth/login">
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
                                required autofocus autocomplete="email">
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
                                placeholder="Enter your password" required autocomplete="current-password">
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
                        <?php if (isset($errors['password'])): ?>
                            <div class="field-error"><?php echo $errors['password']; ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember" id="remember">
                            <span class="checkbox-custom"></span>
                            <span class="checkbox-text"><?php echo _t('remember_me'); ?></span>
                        </label>
                        <a href="<?php echo APP_URL; ?>/auth/forgotPassword"
                            class="forgot-link"><?php echo _t('forgot_password'); ?></a>
                    </div>

                    <!-- Login Button -->
                    <button type="submit" class="btn-primary" id="loginButton">
                        <span class="btn-text"><?php echo _t('sign_in'); ?></span>
                    </button>

                    <!-- Google OAuth -->
                    <?php
                    $oauth = new GoogleOAuth();
                    if ($oauth->isConfigured()):
                        ?>
                        <!-- Divider -->
                        <div class="divider">
                            <span>Or continue with</span>
                        </div>

                        <!-- Google Login -->
                        <a href="<?php echo APP_URL; ?>/auth/google" class="btn-google">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M18.1713 8.36791H17.5001V8.33325H10.0001V11.6666H14.7096C14.0225 13.6069 12.1763 14.9999 10.0001 14.9999C7.23882 14.9999 5.00007 12.7612 5.00007 9.99992C5.00007 7.23867 7.23882 4.99992 10.0001 4.99992C11.2746 4.99992 12.4342 5.48009 13.3171 6.26625L15.6742 3.90909C14.1859 2.52217 12.1951 1.66659 10.0001 1.66659C5.39799 1.66659 1.66675 5.39784 1.66675 9.99992C1.66675 14.602 5.39799 18.3333 10.0001 18.3333C14.6022 18.3333 18.3334 14.602 18.3334 9.99992C18.3334 9.44117 18.2759 8.89575 18.1713 8.36791Z"
                                    fill="#FFC107" />
                                <path
                                    d="M2.62756 6.12117L5.36548 8.12909C6.10631 6.29492 7.90048 4.99992 10.0001 4.99992C11.2746 4.99992 12.4342 5.48009 13.3171 6.26625L15.6742 3.90909C14.1859 2.52217 12.1951 1.66659 10.0001 1.66659C6.79923 1.66659 4.02339 3.47367 2.62756 6.12117Z"
                                    fill="#FF3D00" />
                                <path
                                    d="M10.0001 18.3333C12.1526 18.3333 14.1101 17.5095 15.5876 16.1699L13.0084 13.9874C12.1434 14.6449 11.0801 15.0008 10.0001 14.9999C7.83258 14.9999 5.99175 13.6178 5.29842 11.6891L2.58008 13.7832C3.96091 16.4816 6.76091 18.3333 10.0001 18.3333Z"
                                    fill="#4CAF50" />
                                <path
                                    d="M18.1713 8.36791H17.5001V8.33325H10.0001V11.6666H14.7096C14.3809 12.5902 13.7889 13.3972 13.0071 13.9879L13.0084 13.9871L15.5876 16.1696C15.4051 16.3354 18.3334 14.1666 18.3334 9.99992C18.3334 9.44117 18.2759 8.89575 18.1713 8.36791Z"
                                    fill="#1976D2" />
                            </svg>
                            Sign in with Google
                        </a>
                    <?php endif; ?>
                </form>

                <!-- Footer -->
                <div class="form-footer">
                    <p><?php echo _t('dont_have_account'); ?> <a href="<?php echo APP_URL; ?>/auth/register"
                            class="signup-link"><?php echo _t('sign_up'); ?></a></p>
                </div>

                <!-- Demo Account Info -->
                <div class="demo-account">
                    <strong>Demo Account:</strong><br>
                    Email: demo@crm.com<br>
                    Password: demo123
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo APP_URL; ?>/assets/js/login.js"></script>
</body>

</html>