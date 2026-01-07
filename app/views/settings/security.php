<?php $pageTitle = 'Security Settings'; ?>
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row mb-4 align-items-center">
    <div class="col">
        <h1 class="h4 mb-1">Security & Privacy</h1>
        <p class="text-muted small mb-0">Manage your password, two-factor authentication, and account protection</p>
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
                    class="list-group-item list-group-item-action py-3 px-4 d-flex align-items-center text-secondary">
                    <i class="bi bi-credit-card me-3 opacity-50"></i> Payment Setup
                </a>
                <a href="<?php echo APP_URL; ?>/settings/dataCollection"
                    class="list-group-item list-group-item-action py-3 px-4 d-flex align-items-center text-secondary">
                    <i class="bi bi-database-down me-3 opacity-50"></i> Data Storage
                </a>
                <a href="<?php echo APP_URL; ?>/settings/security"
                    class="list-group-item list-group-item-action py-3 px-4 d-flex align-items-center active shadow-sm">
                    <i class="bi bi-shield-lock me-3"></i> Security Portal
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
            <h6 class="fw-bold small-title mb-2">Account Shield</h6>
            <p class="small text-muted mb-0">Always use a unique password. Enable 2FA for maximum account protection.
            </p>
        </div>
    </div>

    <div class="col-lg-9">
        <div class="row g-4">
            <!-- Password Section -->
            <div class="col-md-7">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4 text-main">
                            <div class="stat-card-icon bg-primary bg-opacity-10 text-primary me-3"
                                style="width: 40px; height: 40px;">
                                <i class="bi bi-lock small"></i>
                            </div>
                            <h6 class="fw-bold mb-0">Update Password</h6>
                        </div>

                        <?php if (isset($errors['general'])): ?>
                            <div class="alert badge-soft-warning border-0 small py-3 px-4 mb-4">
                                <i class="bi bi-exclamation-triangle me-2"></i> <?php echo $errors['general']; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>"
                                value="<?php echo $csrf_token; ?>">

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-main">Current Password</label>
                                <input type="password"
                                    class="form-control border-0 bg-light <?php echo isset($errors['current_password']) ? 'is-invalid' : ''; ?>"
                                    name="current_password" required>
                                <?php if (isset($errors['current_password'])): ?>
                                    <div class="invalid-feedback small"><?php echo $errors['current_password']; ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-main">New Password</label>
                                <input type="password"
                                    class="form-control border-0 bg-light <?php echo isset($errors['new_password']) ? 'is-invalid' : ''; ?>"
                                    name="new_password" required minlength="8">
                                <div class="form-text small opacity-50">Must be at least 8 characters</div>
                                <?php if (isset($errors['new_password'])): ?>
                                    <div class="invalid-feedback small"><?php echo $errors['new_password']; ?></div>
                                <?php endif; ?>

                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-semibold text-main">Confirm New Password</label>
                                <input type="password"
                                    class="form-control border-0 bg-light <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>"
                                    name="confirm_password" required minlength="8">
                            </div>

                            <button type="submit" class="btn btn-primary px-4">
                                Update Password
                            </button>
                        </form>
                    </div>
                </div>

                <!-- 2FA Section -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4 text-main">
                            <div class="stat-card-icon bg-success bg-opacity-10 text-success me-3"
                                style="width: 40px; height: 40px;">
                                <i class="bi bi-shield-check small"></i>
                            </div>
                            <h6 class="fw-bold mb-0">Two-Factor Authentication</h6>
                        </div>

                        <?php
                        $userModel = new User();
                        $currentUser = $userModel->findOne(['id' => $_SESSION['user_id']]);
                        ?>

                        <?php if ($currentUser['two_factor_enabled']): ?>
                            <div class="alert badge-soft-success border-0 mb-4 py-3 px-4">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-check-circle-fill me-3 fs-5"></i>
                                    <div>
                                        <div class="fw-bold small">MFA Protection Active</div>
                                        <div class="small opacity-75">Your account is secured via authenticator app.</div>
                                    </div>
                                </div>
                            </div>

                            <?php
                            $backupCodeModel = new BackupCode();
                            $unusedCodes = $backupCodeModel->countUnused($_SESSION['user_id']);
                            ?>

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="small text-muted">
                                    <i class="bi bi-key me-2 opacity-50"></i>
                                    You have <strong><?php echo $unusedCodes; ?></strong> unused backup codes.
                                </div>
                                <a href="<?php echo APP_URL; ?>/settings/enableTwoFactor"
                                    class="btn btn-link btn-sm text-decoration-none p-0">View Codes</a>
                            </div>

                            <button class="btn btn-outline-danger btn-sm border-0 bg-danger bg-opacity-10"
                                data-bs-toggle="modal" data-bs-target="#disable2faModal">
                                Disable 2FA Protection
                            </button>
                        <?php else: ?>
                            <p class="text-muted small mb-4">Add an extra layer of security to your account by using an
                                authenticator app (Google Authenticator, Authy, etc).</p>
                            <a href="<?php echo APP_URL; ?>/settings/enableTwoFactor" class="btn btn-light border-0 px-4">
                                <i class="bi bi-plus-lg me-2"></i> Configure 2FA
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold small-title mb-3">Security Standards</h6>
                        <div class="space-y-3">
                            <div class="d-flex align-items-start gap-2 mb-3">
                                <div class="stat-card-icon bg-light text-primary" style="width: 24px; height: 24px;">
                                    <i class="bi bi-check2 small"></i>
                                </div>
                                <div class="small text-muted flex-grow-1">Avoid using common words or sequential
                                    numbers.</div>
                            </div>
                            <div class="d-flex align-items-start gap-2 mb-3">
                                <div class="stat-card-icon bg-light text-primary" style="width: 24px; height: 24px;">
                                    <i class="bi bi-check2 small"></i>
                                </div>
                                <div class="small text-muted flex-grow-1">Include a mix of symbols, numbers, and cases.
                                </div>
                            </div>
                            <div class="d-flex align-items-start gap-2">
                                <div class="stat-card-icon bg-light text-primary" style="width: 24px; height: 24px;">
                                    <i class="bi bi-check2 small"></i>
                                </div>
                                <div class="small text-muted flex-grow-1">Rotate your password every 90 days for safety.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert badge-soft-info border-0 p-4">
                    <h6 class="fw-bold small text-uppercase mb-2 ls-wide">Recovery Protocol</h6>
                    <p class="small mb-0 opacity-75">Generated backup codes are the only way to recover your account if
                        you lose access to your 2FA device. Keep them in a secure, offline location.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Refined -->
<div class="modal fade" id="disable2faModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg p-3" style="border-radius: 20px;">
            <form method="POST" action="<?php echo APP_URL; ?>/settings/disableTwoFactor">
                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>"
                    value="<?php echo Security::generateCsrfToken(); ?>">
                <div class="modal-body text-center p-4">
                    <div class="stat-card-icon bg-danger bg-opacity-10 text-danger mx-auto mb-4"
                        style="width: 60px; height: 60px;">
                        <i class="bi bi-shield-x fs-3"></i>
                    </div>
                    <h5 class="fw-bold text-main mb-2">Disable 2FA Protection?</h5>
                    <p class="text-muted small mb-4">This significantly lowers your account security. Please verify your
                        current password to confirm this action.</p>

                    <div class="text-start">
                        <label class="form-label small fw-semibold text-main">Confirm Password</label>
                        <input type="password" name="password" class="form-control border-0 bg-light" required
                            placeholder="Type your password...">
                    </div>
                </div>
                <div class="modal-footer border-0 gap-2">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Keep Secured</button>
                    <button type="submit" class="btn btn-danger px-4">Disable MFA</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>