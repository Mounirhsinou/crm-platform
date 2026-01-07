<?php $pageTitle = 'Security Recovery'; ?>
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row justify-content-center py-5">
    <div class="col-lg-7 col-xl-6">
        <div class="text-center mb-5">
            <div class="stat-card-icon bg-success bg-opacity-10 text-success mx-auto mb-3"
                style="width: 60px; height: 60px;">
                <i class="bi bi-check-circle-fill fs-3"></i>
            </div>
            <h2 class="h4 fw-bold text-main">Protection Activated</h2>
            <p class="text-muted small">Your account is now secured with Two-Factor Authentication</p>
        </div>

        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-body p-4 p-lg-5">
                <div class="alert badge-soft-warning border-0 mb-4 py-3 px-4 d-flex align-items-start">
                    <i class="bi bi-exclamation-triangle-fill me-3 mt-1 fs-5"></i>
                    <div class="small">
                        <strong class="d-block mb-1">Vital: Save Backup Codes</strong>
                        Each code can be used only once. These are the <strong>ONLY</strong> way to recover access if
                        you lose your device. Store them in a secure vault or print them.
                    </div>
                </div>

                <div class="mb-4">
                    <label
                        class="small fw-semibold text-main opacity-50 mb-3 d-block text-center text-uppercase ls-wide"
                        style="font-size: 0.65rem;">Your Recovery Keys</label>
                    <div class="row g-3">
                        <?php foreach ($backup_codes as $index => $code): ?>
                            <div class="col-6">
                                <div
                                    class="p-3 bg-light rounded-3 text-center d-flex align-items-center justify-content-between">
                                    <span
                                        class="text-muted small fw-medium opacity-50"><?php echo sprintf('%02d', $index + 1); ?></span>
                                    <code class="fw-bold text-main fs-6"
                                        style="letter-spacing: 1px;"><?php echo $code; ?></code>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="alert border-0 p-3 small text-indigo d-flex align-items-center mb-5"
                    style="background-color: rgba(99, 102, 241, 0.05); color: #6366f1;">
                    <i class="bi bi-printer-fill me-3 opacity-50 fs-5"></i>
                    <span class="fw-medium">Recommendation: Copy these keys to your password manager immediately.</span>
                </div>

                <div class="d-grid">
                    <a href="<?php echo APP_URL; ?>/settings/security" class="btn btn-primary py-2 fw-medium">
                        Go to Security Portal
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>