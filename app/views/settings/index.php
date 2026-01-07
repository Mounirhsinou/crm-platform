<?php $pageTitle = 'Company Profile'; ?>
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row mb-4 align-items-center">
    <div class="col">
        <h1 class="h4 mb-1 fw-bold">General Settings</h1>
        <p class="text-muted small mb-0">Configure your business identity and branding across the CRM</p>
    </div>
</div>

<div class="row g-4">
    <!-- Sidebar Navigation -->
    <div class="col-lg-3">
        <div class="card border-0 shadow-sm overflow-hidden mb-4">
            <div class="list-group list-group-flush small">
                <a href="<?php echo APP_URL; ?>/settings"
                    class="list-group-item list-group-item-action py-3 px-4 d-flex align-items-center active shadow-sm">
                    <i class="bi bi-building me-3"></i> Company Profile
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
                    class="list-group-item list-group-item-action py-3 px-4 d-flex align-items-center text-secondary">
                    <i class="bi bi-shield-lock me-3 opacity-50"></i> Security Portal
                </a>
                <a href="<?php echo APP_URL; ?>/settings/integrations"
                    class="list-group-item list-group-item-action py-3 px-4 d-flex align-items-center text-secondary">
                    <i class="bi bi-plug me-3 opacity-50"></i> Integrations
                </a>
            </div>
        </div>

        <div class="p-4 bg-light rounded-3">
            <div class="d-flex align-items-center mb-3">
                <i class="bi bi-card-image text-primary fs-4 me-3"></i>
                <h6 class="fw-bolder mb-0">Brand Identity</h6>
            </div>
            <p class="small text-muted mb-0">Your logo and address will appear on generated invoices and public payment
                links.</p>
        </div>
    </div>

    <div class="col-lg-9">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-main mb-4">Company Details</h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-main">Company Name</label>
                            <input type="text" class="form-control border-0 bg-light" name="company_name"
                                value="<?php echo Security::escape($company['company_name'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-main">
                                Primary Account Email
                                <i class="bi bi-info-circle text-muted" data-bs-toggle="tooltip"
                                    title="This email controls account ownership and recovery. Only the account owner can change this."></i>
                            </label>
                            <input type="email" class="form-control border-0 bg-light" name="owner_email"
                                value="<?php echo Security::escape($company['owner_email'] ?? ''); ?>" required>
                            <div class="form-text small text-muted">
                                This email represents account ownership
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-main">Owner / Admin Name</label>
                            <input type="text" class="form-control border-0 bg-light" name="owner_name"
                                value="<?php echo Security::escape($company['owner_name'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-main">Business Email</label>
                            <input type="email" class="form-control border-0 bg-light" name="email"
                                value="<?php echo Security::escape($company['email'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-main">Phone Number</label>
                            <input type="text" class="form-control border-0 bg-light" name="phone"
                                value="<?php echo Security::escape($company['phone'] ?? ''); ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-main">Website URL</label>
                            <input type="url" class="form-control border-0 bg-light" name="website"
                                value="<?php echo Security::escape($company['website'] ?? ''); ?>"
                                placeholder="https://example.com">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-main">Business Address</label>
                            <textarea class="form-control border-0 bg-light" name="address"
                                rows="3"><?php echo Security::escape($company['address'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-main mb-4">Company Logo</h6>

                    <div class="d-flex align-items-center">
                        <div class="logo-preview-container bg-light rounded-3 d-flex align-items-center justify-content-center me-4"
                            style="width: 120px; height: 120px; overflow: hidden;">
                            <?php if (!empty($company['logo_path'])): ?>
                                <img src="<?php echo APP_URL . '/' . $company['logo_path']; ?>?v=<?php echo time(); ?>"
                                    class="logo-preview-img img-fluid" style="max-height: 100%;">
                            <?php else: ?>
                                <i class="bi bi-image text-secondary opacity-50 fs-1 logo-preview-img"></i>
                            <?php endif; ?>
                        </div>

                        <div>
                            <p class="small text-muted mb-3">Upload your company logo. Recommended size 400x400px. JPG,
                                PNG or SVG allowed.</p>
                            <label class="btn btn-light btn-sm px-3 mb-0">
                                <i class="bi bi-image me-2"></i>Choose Logo
                                <input type="file" name="logo" id="logo" class="d-none">
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-3 text-end">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-lg me-2"></i>Save Settings
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once APP_PATH . '/views/settings/logo_upload_script.php'; ?>
<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>