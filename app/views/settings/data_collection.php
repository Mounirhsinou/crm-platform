<?php $pageTitle = 'Data Storage'; ?>
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row mb-4 align-items-center">
    <div class="col">
        <h1 class="h4 mb-1">Data Retention & Collection</h1>
        <p class="text-muted small mb-0">Define how client information is captured and managed in your CRM</p>
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
                    class="list-group-item list-group-item-action py-3 px-4 d-flex align-items-center active shadow-sm">
                    <i class="bi bi-database-down me-3"></i> Data Storage
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
                <i class="bi bi-shield-lock text-primary fs-4"></i>
            </div>
            <h6 class="fw-bold small-title mb-2">Privacy & Compliance</h6>
            <p class="small text-muted mb-0">Only capture data you have a legal right to store. Ensure local compliance
                (GDPR/CCPA).</p>
        </div>
    </div>

    <div class="col-lg-9">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-4 text-main">
                    <div class="stat-card-icon bg-primary bg-opacity-10 text-primary me-3"
                        style="width: 40px; height: 40px;">
                        <i class="bi bi-database-fill-gear small"></i>
                    </div>
                    <h6 class="fw-bold mb-0">Capture Governance</h6>
                </div>

                <form method="POST">
                    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">

                    <div class="mb-5">
                        <label class="fw-bold small text-uppercase mb-3 opacity-50 text-main ls-wide d-block">Attribute
                            Mapping</label>

                        <div class="list-group list-group-flush border-top">
                            <div
                                class="list-group-item py-4 px-0 border-light d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1 small fw-bold text-main">Capture Full Name</h6>
                                    <p class="text-muted small mb-0">Extract personal identity from checkout forms.</p>
                                </div>
                                <div class="form-check form-switch custom-switch">
                                    <input class="form-check-input" type="checkbox" name="lead_collect_name" value="1"
                                        <?php echo ($settings['lead_collect_name'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                </div>
                            </div>

                            <div
                                class="list-group-item py-4 px-0 border-light d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1 small fw-bold text-main">Capture Email Address</h6>
                                    <p class="text-muted small mb-0">Primary identifier for marketing and billing.</p>
                                </div>
                                <div class="form-check form-switch custom-switch">
                                    <input class="form-check-input" type="checkbox" name="lead_collect_email" value="1"
                                        <?php echo ($settings['lead_collect_email'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                </div>
                            </div>

                            <div
                                class="list-group-item py-4 px-0 border-light d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1 small fw-bold text-main">Capture Phone Metadata</h6>
                                    <p class="text-muted small mb-0">Required for SMS and WhatsApp automations.</p>
                                </div>
                                <div class="form-check form-switch custom-switch">
                                    <input class="form-check-input" type="checkbox" name="lead_collect_phone" value="1"
                                        <?php echo ($settings['lead_collect_phone'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                </div>
                            </div>

                            <div
                                class="list-group-item py-4 px-0 border-light d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1 small fw-bold text-main">Capture Geographic Data</h6>
                                    <p class="text-muted small mb-0">Street, City, and Country details for logistics.
                                    </p>
                                </div>
                                <div class="form-check form-switch custom-switch">
                                    <input class="form-check-input" type="checkbox" name="lead_collect_address"
                                        value="1" <?php echo ($settings['lead_collect_address'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="fw-bold small text-uppercase mb-3 opacity-50 text-main ls-wide d-block">System
                            Intelligence</label>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="p-4 border-0 bg-light rounded-4 h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0 small fw-bold text-main">Deduplication Engine</h6>
                                        <div class="form-check form-switch custom-switch">
                                            <input class="form-check-input" type="checkbox" name="lead_deduplication"
                                                value="1" <?php echo ($settings['lead_deduplication'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-0">Merged existing records by matching Email/Phone to
                                        keep your CRM clean.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-4 border-0 bg-light rounded-4 h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0 small fw-bold text-main">Export Capability</h6>
                                        <div class="form-check form-switch custom-switch">
                                            <input class="form-check-input" type="checkbox" name="lead_allow_export"
                                                value="1" <?php echo ($settings['lead_allow_export'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-0">Grants users permission to download lead data in
                                        CSV format.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-top">
                        <button type="submit" class="btn btn-primary px-4 py-2">Save Collection Rules</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>