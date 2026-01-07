<?php $pageTitle = 'Payment Setup'; ?>
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row mb-4 align-items-center">
    <div class="col">
        <h1 class="h4 mb-1">Payment Gateways</h1>
        <p class="text-muted small mb-0">Manage how you collect money from clients via Invoices and Public Links</p>
    </div>
</div>

<div class="row g-4">
    <!-- Sidebar Navigation -->
    <div class="col-lg-3">
        <div class="card border-0 shadow-sm overflow-hidden mb-4">
            <div class="list-group list-group-flush small">
                <a href="<?php echo APP_URL; ?>/settings"
                    class="list-group-item list-group-item-action py-3 px-4 d-flex align-items-center text-secondary">
                    <i class="bi bi-building me-3 opacity-50"></i> Company Profile
                </a>
                <a href="<?php echo APP_URL; ?>/settings/users"
                    class="list-group-item list-group-item-action py-3 px-4 d-flex align-items-center text-secondary">
                    <i class="bi bi-people me-3 opacity-50"></i> Roles & Users
                </a>
                <a href="<?php echo APP_URL; ?>/settings/payments"
                    class="list-group-item list-group-item-action py-3 px-4 d-flex align-items-center active shadow-sm">
                    <i class="bi bi-credit-card me-3"></i> Payment Setup
                </a>
                <a href="<?php echo APP_URL; ?>/settings/dataCollection"
                    class="list-group-item list-group-item-action py-3 px-4 d-flex align-items-center text-secondary">
                    <i class="bi bi-database-down me-3 opacity-50"></i> Data Storage
                </a>
                <a href="<?php echo APP_URL; ?>/settings/security"
                    class="list-group-item list-group-item-action py-3 px-4 d-flex align-items-center text-secondary">
                    <i class="bi bi-shield-lock me-3 opacity-50"></i> Security Portal
                </a>
                <a href="<?php echo APP_URL; ?>/settings/integrations"
                    class="list-group-item list-group-item-action py-3 px-4 d-flex align-items-center text-secondary">
                    <i class="bi bi-plug me-3 opacity-50"></i> Integrations
                </a>
            </div>
        </div>

        <div class="p-4 bg-light rounded-4 text-center">
            <div class="avatar-circles bg-white border mx-auto mb-3" style="width: 50px; height: 50px;">
                <i class="bi bi-shield-check text-success fs-4"></i>
            </div>
            <h6 class="fw-bold small-title mb-2">Verified Transmission</h6>
            <p class="small text-muted mb-0">Transactions are encrypted and handled directly by provider APIs.</p>
        </div>
    </div>

    <div class="col-lg-9">
        <!-- PayPal Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-4">
                    <div class="stat-card-icon bg-primary bg-opacity-10 text-primary me-3"
                        style="width: 40px; height: 40px;">
                        <i class="bi bi-paypal small"></i>
                    </div>
                    <h6 class="fw-bold text-main mb-0">PayPal Commerce</h6>
                </div>

                <div class="alert badge-soft-primary border-0 mb-4 small py-3 px-4">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-info-circle opacity-50 fs-5"></i>
                        <div>
                            Connect your REST credentials to enable Express Checkout. Visit
                            <a href="https://developer.paypal.com" target="_blank"
                                class="fw-bold text-decoration-none border-bottom border-primary border-opacity-25">PayPal
                                Dashboard</a>.
                        </div>
                    </div>
                </div>

                <form method="POST">
                    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-main">Live Client ID</label>
                        <input type="text" class="form-control border-0 bg-light" name="paypal_client_id"
                            value="<?php echo Security::escape($company['paypal_client_id'] ?? ''); ?>"
                            placeholder="Enter PayPal Client ID">
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-main">Live Secret Key</label>
                        <div class="input-group">
                            <input type="password" class="form-control border-0 bg-light px-3" id="paypal_secret"
                                name="paypal_secret"
                                value="<?php echo Security::escape($company['paypal_secret'] ?? ''); ?>"
                                placeholder="••••••••••••••••">
                            <button class="btn btn-light border-0 bg-light px-3" type="button"
                                onclick="toggleSecret('paypal_secret', 'phi-1')">
                                <i class="bi bi-eye" id="phi-1"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm px-4 py-2">Update PayPal Config</button>
                </form>
            </div>
        </div>

        <!-- Stripe Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-4">
                    <div class="stat-card-icon bg-indigo bg-opacity-10 text-indigo me-3"
                        style="width: 40px; height: 40px; background-color: rgba(99, 102, 241, 0.1);">
                        <i class="bi bi-stripe small" style="color: #6366f1;"></i>
                    </div>
                    <h6 class="fw-bold text-main mb-0">Stripe Global</h6>
                </div>

                <div class="alert border-0 mb-4 small py-3 px-4"
                    style="background-color: rgba(99, 102, 241, 0.05); color: #6366f1;">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-info-circle opacity-50 fs-5"></i>
                        <div>
                            Accept credit cards via <a href="https://dashboard.stripe.com/apikeys" target="_blank"
                                class="fw-bold text-decoration-none border-bottom border-indigo border-opacity-25"
                                style="color: #6366f1;">Stripe API Keys</a>.
                        </div>
                    </div>
                </div>

                <form method="POST">
                    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">

                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold text-main">Publishable Key</label>
                            <input type="text" class="form-control border-0 bg-light" name="stripe_publishable_key"
                                value="<?php echo Security::escape($company['stripe_publishable_key'] ?? ''); ?>"
                                placeholder="pk_live_...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-main">Environment</label>
                            <select class="form-select border-0 bg-light fw-medium" name="stripe_mode">
                                <option value="test" <?php echo ($company['stripe_mode'] ?? 'test') === 'test' ? 'selected' : ''; ?>>Testing (Sandbox)</option>
                                <option value="live" <?php echo ($company['stripe_mode'] ?? '') === 'live' ? 'selected' : ''; ?>>Production (Live)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-main">Secret Key</label>
                        <div class="input-group">
                            <input type="password" class="form-control border-0 bg-light px-3" id="stripe_secret_key"
                                name="stripe_secret_key"
                                value="<?php echo Security::escape($company['stripe_secret_key'] ?? ''); ?>"
                                placeholder="sk_live_...">
                            <button class="btn btn-light border-0 bg-light px-3" type="button"
                                onclick="toggleSecret('stripe_secret_key', 'phi-2')">
                                <i class="bi bi-eye" id="phi-2"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-main">Webhook Signing Secret</label>
                        <input type="password" class="form-control border-0 bg-light" name="stripe_webhook_secret"
                            value="<?php echo Security::escape($company['stripe_webhook_secret'] ?? ''); ?>"
                            placeholder="whsec_...">
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm px-4 py-2"
                        style="background-color: #6366f1; border: none;">Connect Stripe API</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleSecret(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input.type === "password") {
            input.type = "text";
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            input.type = "password";
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    }
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>