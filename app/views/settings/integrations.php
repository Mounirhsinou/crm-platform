<?php $pageTitle = 'Integrations'; ?>
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row mb-4 align-items-center">
    <div class="col">
        <h1 class="h4 mb-1">Integrations</h1>
        <p class="text-muted small mb-0">Manage external messaging providers for your CRM</p>
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
                    class="list-group-item list-group-item-action py-3 px-4 d-flex align-items-center text-secondary">
                    <i class="bi bi-shield-lock me-3 opacity-50"></i> Security Portal
                </a>
                <a href="<?php echo APP_URL; ?>/settings/integrations"
                    class="list-group-item list-group-item-action py-3 px-4 d-flex align-items-center active shadow-sm">
                    <i class="bi bi-plug me-3"></i> Integrations
                </a>
            </div>
        </div>

        <div class="p-4 bg-light rounded-4 text-center">
            <div class="avatar-circles bg-white border mx-auto mb-3" style="width: 50px; height: 50px;">
                <i class="bi bi-send-check text-success fs-4"></i>
            </div>
            <h6 class="fw-bold small-title mb-2">Connectivity</h6>
            <p class="small text-muted mb-0">Configure your SMS and WhatsApp providers to enable direct client
                communication.</p>
        </div>
    </div>

    <div class="col-lg-9">
        <div class="row g-4">
            <!-- SMS Provider Settings -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4 text-main">
                            <div class="stat-card-icon bg-primary bg-opacity-10 text-primary me-3"
                                style="width: 40px; height: 40px;">
                                <i class="bi bi-chat-left-dots small"></i>
                            </div>
                            <h6 class="fw-bold mb-0">SMS Gateway</h6>
                        </div>

                        <form action="<?php echo APP_URL; ?>/messaging/settings" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <input type="hidden" name="type" value="sms">

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-main">Core Provider</label>
                                <select name="provider" class="form-select border-0 bg-light fw-medium">
                                    <option value="twilio" <?php echo ($sms && $sms['provider'] === 'twilio') ? 'selected' : ''; ?>>Twilio Cloud</option>
                                    <option value="vonage" <?php echo ($sms && $sms['provider'] === 'vonage') ? 'selected' : ''; ?>>Vonage (Nexmo)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-main">API Key / Access SID</label>
                                <input type="text" name="api_key" class="form-control border-0 bg-light"
                                    value="<?php echo $sms ? Security::escape($sms['api_key']) : ''; ?>"
                                    placeholder="ACxxxxxxxxxxxx">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-main">API Secret / Auth Token</label>
                                <input type="password" name="api_secret" class="form-control border-0 bg-light"
                                    value="<?php echo $sms ? Security::escape($sms['api_secret']) : ''; ?>"
                                    placeholder="••••••••••••••••">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-main">Sender Identity
                                    (Number/Alpha)</label>
                                <input type="text" name="sender_id" class="form-control border-0 bg-light"
                                    value="<?php echo $sms ? Security::escape($sms['sender_id']) : ''; ?>"
                                    placeholder="+1234567890">
                            </div>

                            <div class="mb-4 p-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
                                <label class="form-check-label small fw-semibold text-main" for="smsEnabled">Activation
                                    Status</label>
                                <div class="form-check form-switch custom-switch mb-0">
                                    <input class="form-check-input" type="checkbox" name="is_enabled" id="smsEnabled"
                                        <?php echo ($sms && $sms['is_enabled']) ? 'checked' : ''; ?>>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-sm py-2">Update SMS Config</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- WhatsApp Provider Settings -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4 text-main">
                            <div class="stat-card-icon bg-success bg-opacity-10 text-success me-3"
                                style="width: 40px; height: 40px;">
                                <i class="bi bi-whatsapp small"></i>
                            </div>
                            <h6 class="fw-bold mb-0">WhatsApp Business</h6>
                        </div>

                        <form action="<?php echo APP_URL; ?>/messaging/settings" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <input type="hidden" name="type" value="whatsapp">

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-main">Business API Provider</label>
                                <select name="provider" class="form-select border-0 bg-light fw-medium">
                                    <option value="meta" <?php echo ($whatsapp && $whatsapp['provider'] === 'meta') ? 'selected' : ''; ?>>Meta Business (Direct)</option>
                                    <option value="twilio" <?php echo ($whatsapp && $whatsapp['provider'] === 'twilio') ? 'selected' : ''; ?>>Twilio for WhatsApp</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-main">Permanent Access Token</label>
                                <input type="text" name="api_key" class="form-control border-0 bg-light"
                                    value="<?php echo $whatsapp ? Security::escape($whatsapp['api_key']) : ''; ?>"
                                    placeholder="EAAGxxxxxxxx">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-main">Phone Number ID</label>
                                <input type="text" name="api_secret" class="form-control border-0 bg-light"
                                    value="<?php echo $whatsapp ? Security::escape($whatsapp['api_secret']) : ''; ?>"
                                    placeholder="105xxxxxxxx">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-main">Verified Sender Number</label>
                                <input type="text" name="sender_id" class="form-control border-0 bg-light"
                                    value="<?php echo $whatsapp ? Security::escape($whatsapp['sender_id']) : ''; ?>"
                                    placeholder="+1234567890">
                            </div>

                            <div class="mb-4 p-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
                                <label class="form-check-label small fw-semibold text-main"
                                    for="whatsappEnabled">Activation Status</label>
                                <div class="form-check form-switch custom-switch mb-0">
                                    <input class="form-check-input" type="checkbox" name="is_enabled"
                                        id="whatsappEnabled" <?php echo ($whatsapp && $whatsapp['is_enabled']) ? 'checked' : ''; ?>>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-success btn-sm py-2">Update WhatsApp
                                    Config</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>