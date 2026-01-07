<?php $pageTitle = 'Setup Protection'; ?>
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row justify-content-center py-5">
    <div class="col-lg-8 col-xl-7">
        <div class="text-center mb-5">
            <div class="stat-card-icon bg-primary bg-opacity-10 text-primary mx-auto mb-3"
                style="width: 60px; height: 60px;">
                <i class="bi bi-shield-lock-fill fs-3"></i>
            </div>
            <h2 class="h4 fw-bold text-main">Two-Factor Security</h2>
            <p class="text-muted small">Protect your account with an additional cryptographic layer</p>
        </div>

        <div class="card border-0 shadow-sm overflow-hidden mb-4">
            <div class="card-body p-0">
                <div class="row g-0">
                    <div class="col-md-6 border-end p-4 p-lg-5">
                        <div class="d-flex align-items-center mb-4">
                            <span class="badge badge-soft-primary rounded-pill me-2 px-3">Step 1</span>
                            <h6 class="fw-bold mb-0 text-main small-title">Scan QR Code</h6>
                        </div>
                        <p class="small text-muted mb-4">Use an authenticator app (Google, Authy, or Microsoft) to scan
                            this code.</p>

                        <div class="bg-light rounded-4 p-4 text-center border mb-4">
                            <img src="<?php echo $qr_url; ?>" alt="QR Code"
                                class="img-fluid rounded-3 shadow-sm bg-white p-2" style="max-width: 160px;"
                                onerror="this.parentElement.innerHTML='<div class=\'alert badge-soft-warning small\'>QR Code failed to load.</div>'">
                        </div>

                        <div class="small text-muted opacity-75">
                            <ul class="ps-3 mb-0 space-y-2">
                                <li>Open your authenticator</li>
                                <li>Select <strong>Add Account</strong></li>
                                <li>Scan the <strong>QR Code</strong></li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-md-6 p-4 p-lg-5 bg-light bg-opacity-50">
                        <div class="d-flex align-items-center mb-4">
                            <span class="badge badge-soft-success rounded-pill me-2 px-3">Step 2</span>
                            <h6 class="fw-bold mb-0 text-main small-title">Manual Key</h6>
                        </div>
                        <p class="small text-muted mb-4">If Scanning is unavailable, enter this master key into your app
                            manually.</p>

                        <div class="mb-4">
                            <label class="small fw-semibold text-main opacity-50 mb-2 ls-wide d-block text-uppercase"
                                style="font-size: 0.65rem;">Secret Configuration Key</label>
                            <div class="input-group">
                                <input type="text" class="form-control border-0 bg-white fw-bold text-center small"
                                    value="<?php echo $secret; ?>" id="secretKey" readonly style="letter-spacing: 2px;">
                                <button class="btn btn-white border-0 bg-white px-3" type="button"
                                    onclick="copySecret(this)">
                                    <i class="bi bi-clipboard text-muted"></i>
                                </button>
                            </div>
                        </div>

                        <div class="small text-muted border-top border-light pt-4">
                            <div class="fw-bold mb-1">Configuration Data:</div>
                            <div class="opacity-75">
                                Account: <code class="bg-white px-1">CRM Admin</code><br>
                                Type: <code class="bg-white px-1">TOTP (Time-based)</code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Verification Form -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-lg-5 text-center">
                <div class="d-flex align-items-center justify-content-center mb-4">
                    <span class="badge badge-soft-info rounded-pill me-2 px-3">Step 3</span>
                    <h6 class="fw-bold mb-0 text-main small-title">Final Verification</h6>
                </div>

                <p class="text-muted small mb-4">Enter the 6-digit code from your app to finalize the setup.</p>

                <form method="POST" action="<?php echo APP_URL; ?>/settings/verifyTwoFactor" class="mx-auto"
                    style="max-width: 320px;">
                    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">
                    <div class="mb-4">
                        <input type="text" name="code"
                            class="form-control form-control-lg text-center border-0 bg-light fw-bold" maxlength="6"
                            pattern="[0-9]{6}" placeholder="000 000" required autofocus
                            style="font-size: 24px; letter-spacing: 10px; height: 60px; border-radius: 12px;">
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary py-2 fw-medium">
                            Enable Security Activation
                        </button>
                        <a href="<?php echo APP_URL; ?>/settings/security"
                            class="btn btn-link text-muted text-decoration-none small">
                            Cancel Setup
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function copySecret(btn) {
        const secretInput = document.getElementById('secretKey');
        secretInput.select();
        document.execCommand('copy');

        const icon = btn.querySelector('i');
        const originalClass = icon.className;
        icon.className = 'bi bi-check-lg text-success';

        setTimeout(() => {
            icon.className = originalClass;
        }, 2000);
    }
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>