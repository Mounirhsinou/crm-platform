<?php $pageTitle = 'SMS Broadcast'; ?>
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row mb-4 align-items-center">
    <div class="col">
        <h1 class="h4 mb-1">SMS Broadcast</h1>
        <p class="text-muted small mb-0">Send mass SMS messages to your client database</p>
    </div>
</div>

<?php if (!$settings || !$settings['is_enabled']): ?>
    <div class="alert badge-soft-warning border-0 shadow-sm d-flex align-items-center mb-4">
        <i class="bi bi-exclamation-triangle me-3 fs-4"></i>
        <div>
            <strong>Configuration Required:</strong> SMS provider is not enabled.
            <a href="<?php echo APP_URL; ?>/settings/integrations" class="alert-link ms-1">Configure Settings</a>
        </div>
    </div>
<?php else: ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form id="smsForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                        <div class="mb-4">
                            <label class="form-label fw-semibold text-main">Recipients</label>
                            <textarea name="recipients" id="recipients" class="form-control border-0 bg-light px-3 py-3"
                                rows="6" placeholder="+1234567890&#10;+0987654321" style="border-radius: 12px;"></textarea>
                            <div class="form-text mt-2 d-flex justify-content-between small">
                                <span>Paste numbers (one per line)</span>
                                <span id="recipientCount" class="fw-medium text-primary">0 recipients</span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-semibold text-main mb-0">Message Content</label>
                                <span id="charCounter" class="small text-muted fw-medium">0 / 160 characters</span>
                            </div>
                            <textarea name="message" id="message" class="form-control border-0 bg-light px-3 py-3" rows="5"
                                placeholder="Type your SMS message here..." style="border-radius: 12px;"></textarea>
                            <div class="form-text mt-2 small">
                                <i class="bi bi-info-circle me-1 opacity-50"></i> Standard SMS is limited to 160 characters.
                            </div>
                        </div>

                        <div id="statusAlert" class="alert d-none border-0"></div>

                        <button type="submit" id="sendBtn" class="btn btn-primary px-4">
                            <i class="bi bi-send me-2"></i> Launch Broadcast
                        </button>
                    </form>

                    <!-- Progress Section -->
                    <div id="progressSection" class="mt-4 d-none">
                        <div class="p-3 bg-light rounded-3">
                            <h6 class="fw-bold mb-3 small-title text-main">Broadcast Progress</h6>
                            <div class="progress mb-3" style="height: 8px; background-color: #e2e8f0;">
                                <div id="progressBar"
                                    class="progress-bar bg-primary progress-bar-striped progress-bar-animated"
                                    role="progressbar" style="width: 0%"></div>
                            </div>
                            <div class="d-flex justify-content-between small text-muted">
                                <span id="progressText">Connecting...</span>
                                <span>
                                    <span class="text-success fw-medium" id="statSent">0 Sent</span> •
                                    <span class="text-danger fw-medium" id="statFailed">0 Failed</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-main mb-4">Broadcast Guidelines</h6>
                    <div class="d-flex mb-4">
                        <div class="stat-card-icon bg-info bg-opacity-10 text-info me-3" style="width: 40px; height: 40px;">
                            <i class="bi bi-globe small"></i>
                        </div>
                        <div>
                            <div class="fw-bold small text-main">International Formats</div>
                            <div class="text-muted small">Always include country codes (e.g. +1) for reliable delivery.
                            </div>
                        </div>
                    </div>
                    <div class="d-flex mb-4">
                        <div class="stat-card-icon bg-success bg-opacity-10 text-success me-3"
                            style="width: 40px; height: 40px;">
                            <i class="bi bi-shield-check small"></i>
                        </div>
                        <div>
                            <div class="fw-bold small text-main">Anti-Spam</div>
                            <div class="text-muted small">Avoid suspicious links to prevent being flagged by carriers.</div>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div class="stat-card-icon bg-warning bg-opacity-10 text-warning me-3"
                            style="width: 40px; height: 40px;">
                            <i class="bi bi-lightning small"></i>
                        </div>
                        <div>
                            <div class="fw-bold small text-main">Batch Processing</div>
                            <div class="text-muted small">Mass messages are split into smaller batches for optimal
                                performance.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const recipientsInput = document.getElementById('recipients');
        const messageInput = document.getElementById('message');
        const recipientCountSpan = document.getElementById('recipientCount');
        const charCounterSpan = document.getElementById('charCounter');
        const smsForm = document.getElementById('smsForm');
        const sendBtn = document.getElementById('sendBtn');
        const progressSection = document.getElementById('progressSection');
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        const statSent = document.getElementById('statSent');
        const statFailed = document.getElementById('statFailed');
        const statusAlert = document.getElementById('statusAlert');

        if (recipientsInput) {
            recipientsInput.addEventListener('input', function () {
                const count = this.value.split('\n').filter(line => line.trim() !== '').length;
                recipientCountSpan.innerText = `${count} recipient${count !== 1 ? 's' : ''}`;
            });
        }

        if (messageInput) {
            messageInput.addEventListener('input', function () {
                const length = this.value.length;
                charCounterSpan.innerText = `${length} / 160 characters`;
                if (length > 160) {
                    charCounterSpan.classList.add('text-danger');
                } else {
                    charCounterSpan.classList.remove('text-danger');
                }
            });
        }

        if (smsForm) {
            smsForm.addEventListener('submit', function (e) {
                e.preventDefault();

                const recipients = recipientsInput.value.trim();
                const message = messageInput.value.trim();

                if (!recipients || !message) {
                    alert('Please enter recipients and a message.');
                    return;
                }

                if (!confirm('Launch SMS broadcast to selected contacts?')) {
                    return;
                }

                sendBtn.disabled = true;
                sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Broadcasting...';
                progressSection.classList.remove('d-none');
                statusAlert.classList.add('d-none');

                const formData = new FormData(this);

                fetch('<?php echo APP_URL; ?>/messaging/sendSms', {
                    method: 'POST',
                    body: formData
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            progressBar.style.width = '100%';
                            progressBar.classList.remove('progress-bar-animated');
                            progressText.innerText = 'Broadcast Completed';
                            statSent.innerText = `${data.sent} Sent`;
                            statFailed.innerText = `${data.failed} Failed`;

                            statusAlert.className = 'alert badge-soft-success mt-3 border-0';
                            statusAlert.innerHTML = `<i class="bi bi-check-circle me-2"></i> ${data.message}`;
                            statusAlert.classList.remove('d-none');
                        } else {
                            statusAlert.className = 'alert badge-soft-danger mt-3 border-0';
                            statusAlert.innerHTML = `<i class="bi bi-exclamation-circle me-2"></i> ${data.message}`;
                            statusAlert.classList.remove('d-none');
                        }
                    })
                    .catch(err => {
                        alert('Connection error. Check console.');
                        console.error(err);
                    })
                    .finally(() => {
                        sendBtn.disabled = false;
                        sendBtn.innerHTML = '<i class="bi bi-send me-2"></i> Launch Broadcast';
                    });
            });
        }
    });
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>