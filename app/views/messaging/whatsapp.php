<?php $pageTitle = 'WhatsApp Broadcast'; ?>
<!-- Quill Rich Text Editor -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row mb-4 align-items-center">
    <div class="col">
        <h1 class="h4 mb-1">WhatsApp Broadcast</h1>
        <p class="text-muted small mb-0">Send professional WhatsApp messages to your client base</p>
    </div>
</div>

<?php if (!$settings || !$settings['is_enabled']): ?>
    <div class="alert badge-soft-warning border-0 shadow-sm d-flex align-items-center mb-4">
        <i class="bi bi-exclamation-triangle me-3 fs-4"></i>
        <div>
            <strong>Configuration Required:</strong> WhatsApp provider is not enabled.
            <a href="<?php echo APP_URL; ?>/settings/integrations" class="alert-link ms-1">Configure Settings</a>
        </div>
    </div>
<?php else: ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form id="whatsappForm">
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
                                <div class="nav nav-pills bg-light p-1 rounded-pill small">
                                    <button type="button" id="customModeBtn"
                                        class="nav-link py-1 px-3 rounded-pill active shadow-sm">Custom</button>
                                    <button type="button" id="templateModeBtn"
                                        class="nav-link py-1 px-3 rounded-pill text-secondary">Template</button>
                                </div>
                            </div>

                            <!-- Custom Mode (Simple Textarea) -->
                            <div id="customMessageBox">
                                <textarea name="message" id="message" class="form-control border-0 bg-light px-3 py-3"
                                    rows="5" placeholder="Type your WhatsApp message here..."
                                    style="border-radius: 12px;"></textarea>
                                <div class="form-text mt-2 small">
                                    <i class="bi bi-info-circle me-1 opacity-50"></i> Supports *bold* and _italic_
                                    formatting.
                                </div>
                            </div>

                            <!-- Template Mode (Rich Text Editor) -->
                            <div id="templateMessageBox" style="display: none;">
                                <div class="d-flex justify-content-end mb-2">
                                    <button type="button" id="toggleHtmlBtn" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-code-slash me-1"></i> HTML Source
                                    </button>
                                </div>
                                <div id="templateEditor" style="height: 200px; border-radius: 12px; background: #f8f9fa;">
                                </div>
                                <textarea id="htmlSource" class="form-control border-0 bg-light font-monospace" rows="10"
                                    style="display: none; border-radius: 12px; font-size: 0.85rem;"
                                    placeholder="Paste your HTML template here..."></textarea>
                                <textarea name="template_message" id="templateMessage" style="display: none;"></textarea>
                                <div class="form-text mt-2 small">
                                    <i class="bi bi-info-circle me-1 opacity-50"></i> Use the toolbar to format your
                                    template or toggle HTML source to paste HTML.
                                    template message.
                                </div>
                            </div>
                        </div>

                        <!-- Message Preview -->
                        <div class="mb-4" id="previewSection" style="display: none;">
                            <label class="form-label fw-semibold text-main mb-2">Preview</label>
                            <div class="card border-0 shadow-sm" style="background: #e5ddd5;">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-end">
                                        <div class="bg-white rounded-3 p-3 shadow-sm"
                                            style="max-width: 80%; word-wrap: break-word;">
                                            <div id="messagePreview" class="small"></div>
                                            <div class="text-end mt-2">
                                                <small class="text-muted" style="font-size: 0.7rem;">
                                                    <i class="bi bi-check-all text-primary"></i> <?php echo date('H:i'); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="statusAlert" class="alert d-none border-0"></div>

                        <button type="submit" id="sendBtn" class="btn btn-primary px-4">
                            <i class="bi bi-whatsapp me-2"></i> Launch Broadcast
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
                            <i class="bi bi-check-circle small"></i>
                        </div>
                        <div>
                            <div class="fw-bold small text-main">Verified Account</div>
                            <div class="text-muted small">Ensure your Business API is properly verified and active.</div>
                        </div>
                    </div>
                    <div class="d-flex mb-4">
                        <div class="stat-card-icon bg-success bg-opacity-10 text-success me-3"
                            style="width: 40px; height: 40px;">
                            <i class="bi bi-layout-text-window small"></i>
                        </div>
                        <div>
                            <div class="fw-bold small text-main">Use Templates</div>
                            <div class="text-muted small">Templates often yield 40% higher open rates than custom text.
                            </div>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div class="stat-card-icon bg-warning bg-opacity-10 text-warning me-3"
                            style="width: 40px; height: 40px;">
                            <i class="bi bi-shield-check small"></i>
                        </div>
                        <div>
                            <div class="fw-bold small text-main">Opt-in Only</div>
                            <div class="text-muted small">Confirm recipients have explicitly opted-in to receive messages.
                            </div>
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
        const recipientCountSpan = document.getElementById('recipientCount');
        const whatsappForm = document.getElementById('whatsappForm');
        const sendBtn = document.getElementById('sendBtn');
        const progressSection = document.getElementById('progressSection');
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        const statSent = document.getElementById('statSent');
        const statFailed = document.getElementById('statFailed');
        const statusAlert = document.getElementById('statusAlert');

        // Handle Custom/Template toggle
        const customModeBtn = document.getElementById('customModeBtn');
        const templateModeBtn = document.getElementById('templateModeBtn');
        const customMessageBox = document.getElementById('customMessageBox');
        const templateMessageBox = document.getElementById('templateMessageBox');

        // Initialize Quill editor for Template mode
        let quill = null;

        if (templateModeBtn && customModeBtn) {
            // Initialize Quill when Template mode is first activated
            templateModeBtn.addEventListener('click', function () {
                // Toggle button states
                templateModeBtn.classList.add('active', 'shadow-sm');
                templateModeBtn.classList.remove('text-secondary');
                customModeBtn.classList.remove('active', 'shadow-sm');
                customModeBtn.classList.add('text-secondary');

                // Show/hide boxes
                customMessageBox.style.display = 'none';
                templateMessageBox.style.display = 'block';

                // Initialize Quill if not already initialized
                if (!quill) {
                    quill = new Quill('#templateEditor', {
                        theme: 'snow',
                        placeholder: 'Type your message here...',
                        modules: {
                            toolbar: [
                                [{ 'header': [1, 2, 3, false] }],
                                ['bold', 'italic', 'underline'],
                                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                                [{ 'align': [] }],
                                ['link'],
                                ['clean']
                            ]
                        }
                    });
                }
            });

            customModeBtn.addEventListener('click', function () {
                // Toggle button states
                customModeBtn.classList.add('active', 'shadow-sm');
                customModeBtn.classList.remove('text-secondary');
                templateModeBtn.classList.remove('active', 'shadow-sm');
                templateModeBtn.classList.add('text-secondary');

                // Show/hide boxes
                customMessageBox.style.display = 'block';
                templateMessageBox.style.display = 'none';
            });

            // Handle HTML Source toggle
            const toggleHtmlBtn = document.getElementById('toggleHtmlBtn');
            const templateEditor = document.getElementById('templateEditor');
            const htmlSource = document.getElementById('htmlSource');
            let isHtmlMode = false;

            if (toggleHtmlBtn) {
                toggleHtmlBtn.addEventListener('click', function () {
                    isHtmlMode = !isHtmlMode;

                    if (isHtmlMode) {
                        // Switch to HTML mode
                        if (quill) {
                            htmlSource.value = quill.root.innerHTML;
                        }
                        templateEditor.style.display = 'none';
                        htmlSource.style.display = 'block';
                        toggleHtmlBtn.innerHTML = '<i class="bi bi-eye me-1"></i> Visual Editor';
                        toggleHtmlBtn.classList.add('active');
                    } else {
                        // Switch to Visual mode
                        if (quill) {
                            quill.root.innerHTML = htmlSource.value;
                        }
                        templateEditor.style.display = 'block';
                        htmlSource.style.display = 'none';
                        toggleHtmlBtn.innerHTML = '<i class="bi bi-code-slash me-1"></i> HTML Source';
                        toggleHtmlBtn.classList.remove('active');
                    }
                });
            }
        }

        // Preview functionality
        const messageTextarea = document.getElementById('message');
        const messagePreview = document.getElementById('messagePreview');
        const previewSection = document.getElementById('previewSection');

        // Update preview for custom message
        if (messageTextarea && messagePreview) {
            messageTextarea.addEventListener('input', function () {
                if (this.value.trim()) {
                    previewSection.style.display = 'block';
                    // Convert *bold* and _italic_ to HTML
                    let preview = this.value
                        .replace(/\*(.*?)\*/g, '<strong>$1</strong>')
                        .replace(/_(.*?)_/g, '<em>$1</em>')
                        .replace(/\n/g, '<br>');
                    messagePreview.innerHTML = preview;
                } else {
                    previewSection.style.display = 'none';
                }
            });
        }

        // Update preview for template mode (Quill editor)
        if (quill) {
            quill.on('text-change', function () {
                if (quill.getText().trim()) {
                    previewSection.style.display = 'block';
                    messagePreview.innerHTML = quill.root.innerHTML;
                } else {
                    previewSection.style.display = 'none';
                }
            });
        }

        // Update preview for HTML source mode
        const htmlSource = document.getElementById('htmlSource');
        if (htmlSource && messagePreview) {
            htmlSource.addEventListener('input', function () {
                if (this.value.trim()) {
                    previewSection.style.display = 'block';
                    messagePreview.innerHTML = this.value;
                } else {
                    previewSection.style.display = 'none';
                }
            });
        }

        if (recipientsInput) {
            recipientsInput.addEventListener('input', function () {
                const count = this.value.split('\n').filter(line => line.trim() !== '').length;
                recipientCountSpan.innerText = `${count} recipient${count !== 1 ? 's' : ''}`;
            });
        }

        if (whatsappForm) {
            whatsappForm.addEventListener('submit', function (e) {
                e.preventDefault();

                const recipients = recipientsInput.value.trim();
                const message = document.getElementById('message').value.trim();

                if (!recipients || !message) {
                    alert('Please enter recipients and a message.');
                    return;
                }

                if (!confirm('Proceed with WhatsApp message broadcast?')) {
                    return;
                }

                sendBtn.disabled = true;
                sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Broadcasting...';
                progressSection.classList.remove('d-none');
                statusAlert.classList.add('d-none');

                const formData = new FormData(this);

                fetch('<?php echo APP_URL; ?>/messaging/sendWhatsapp', {
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
                        sendBtn.innerHTML = '<i class="bi bi-whatsapp me-2"></i> Launch Broadcast';
                    });
            });
        }
    });
</script>

<!-- Quill Rich Text Editor JS -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>