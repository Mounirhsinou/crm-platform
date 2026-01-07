<?php $pageTitle = 'Mail List Sender'; ?>
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<!-- TinyMCE Rich Text Editor -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js" referrerpolicy="no-referrer"></script>
<script>
    tinymce.init({
        selector: '#message_body',
        plugins: 'advlist autolink lists link code table help wordcount',
        toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link code | removeformat | help',
        menubar: false,
        height: 400,
        branding: false,
        promotion: false,
        setup: function (editor) {
            editor.on('change', function () {
                editor.save();
            });
        }
    });
</script>

<div class="row mb-4">
    <div class="col">
        <h1 class="h3 mb-0">Mail List Sender</h1>
        <p class="text-muted">Simple bulk email tool. All on one page.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Mail List and Message -->
    <div class="col-lg-8">
        <!-- SMTP Settings -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-gear-fill me-2"></i>1. SMTP Configuration</h6>
                <button class="btn btn-success btn-sm" id="save_smtp_btn" onclick="saveSmtpSettings()">
                    <i class="bi bi-save me-1"></i>Save SMTP
                </button>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">SMTP Host</label>
                        <input type="text" id="smtp_host" class="form-control" placeholder="smtp.example.com"
                            value="<?php echo Security::escape($smtp['smtp_host'] ?? ''); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Port</label>
                        <input type="number" id="smtp_port" class="form-control" placeholder="587"
                            value="<?php echo Security::escape($smtp['smtp_port'] ?? ''); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Encryption</label>
                        <select id="smtp_enc" class="form-select">
                            <option value="tls" <?php echo ($smtp['smtp_encryption'] ?? '') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                            <option value="ssl" <?php echo ($smtp['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                            <option value="none" <?php echo ($smtp['smtp_encryption'] ?? '') === 'none' ? 'selected' : ''; ?>>None</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Username (Email)</label>
                        <input type="email" id="smtp_user" class="form-control" placeholder="user@example.com"
                            value="<?php echo Security::escape($smtp['smtp_username'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password</label>
                        <input type="password" id="smtp_pass" class="form-control" placeholder="••••••••"
                            value="<?php echo Security::escape($smtp['smtp_password_decrypted'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">From Name</label>
                        <input type="text" id="from_name" class="form-control" placeholder="John Doe"
                            value="<?php echo Security::escape($smtp['from_name'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">From Email</label>
                        <input type="email" id="from_email" class="form-control" placeholder="no-reply@example.com"
                            value="<?php echo Security::escape($smtp['from_email'] ?? ''); ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Message Composer -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-envelope-fill me-2"></i>2. Compose Message</h6>
                <button class="btn btn-outline-primary btn-sm" onclick="showPreview()">
                    <i class="bi bi-eye-fill me-1"></i>Preview
                </button>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Subject</label>
                    <input type="text" id="subject" class="form-control" placeholder="Email Subject">
                </div>
                <div class="mb-3">
                    <label class="form-label">Message Body</label>
                    <textarea id="message_body" class="form-control" rows="10"
                        placeholder="Type your message here..."></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Recipient List and Progress -->
    <div class="col-lg-4">
        <!-- Recipient List -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-people-fill me-2"></i>3. Mail List</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Enter Emails (one per line)</label>
                    <textarea id="recipient_list" class="form-control" rows="12"
                        placeholder="recipient1@example.com&#10;recipient2@example.com"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label small">Or upload .txt / .csv</label>
                    <input type="file" id="file_upload" class="form-control form-control-sm" accept=".txt,.csv">
                </div>
                <div class="d-grid">
                    <button class="btn btn-primary btn-lg" id="send_btn" onclick="startSending()">
                        <i class="bi bi-send-fill me-2"></i>Send Emails
                    </button>
                </div>
            </div>
        </div>

        <!-- Progress Widget -->
        <div id="progress_card" class="card border-0 shadow-sm" style="display: none;">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">Sending Progress</h6>
            </div>
            <div class="card-body">
                <div class="progress mb-3" style="height: 10px;">
                    <div id="progress_bar" class="progress-bar progress-bar-striped progress-bar-animated"
                        role="progressbar" style="width: 0%"></div>
                </div>
                <div class="d-flex justify-content-between text-center">
                    <div class="text-primary">
                        <small class="d-block text-muted">Total</small>
                        <span id="total_count" class="h5 fw-bold">0</span>
                    </div>
                    <div class="text-success">
                        <small class="d-block text-muted">Sent</small>
                        <span id="sent_count" class="h5 fw-bold">0</span>
                    </div>
                    <div class="text-danger">
                        <small class="d-block text-muted">Failed</small>
                        <span id="failed_count" class="h5 fw-bold">0</span>
                    </div>
                </div>
                <div id="error_log" class="mt-3 small text-danger"
                    style="max-height: 150px; overflow-y: auto; display: none;">
                    <hr>
                    <div id="error_list"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="previewSubject"></h5>
                    <small class="text-muted">Email Preview</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="previewIframe" style="width: 100%; height: 500px; border: none;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close Preview</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Handle file upload
    document.getElementById('file_upload').addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function (e) {
            const content = e.target.result;
            document.getElementById('recipient_list').value = content;
        };
        reader.readAsText(file);
    });

    let isSending = false;

    async function startSending() {
        if (isSending) return;

        // Get recipients
        const rawRecipients = document.getElementById('recipient_list').value;
        const recipientList = rawRecipients.split(/\r?\n/).map(e => e.trim()).filter(e => e.includes('@'));

        if (recipientList.length === 0) {
            alert('Please enter at least one valid email address.');
            return;
        }

        // Get SMTP settings
        const smtpData = {
            smtp_host: document.getElementById('smtp_host').value.trim(),
            smtp_port: document.getElementById('smtp_port').value.trim(),
            smtp_enc: document.getElementById('smtp_enc').value,
            smtp_user: document.getElementById('smtp_user').value.trim(),
            smtp_pass: document.getElementById('smtp_pass').value.trim(),
        };

        if (!smtpData.smtp_host || !smtpData.smtp_port || !smtpData.smtp_user || !smtpData.smtp_pass) {
            alert('Please complete all SMTP configuration fields.');
            return;
        }

        // Get Email content
        const emailContent = {
            subject: document.getElementById('subject').value.trim(),
            body: tinymce.get('message_body').getContent(),
            email_type: 'html' // TinyMCE always produces HTML
        };

        if (!emailContent.subject || !emailContent.body) {
            alert('Please enter a subject and message body.');
            return;
        }

        // Initialize Progress
        isSending = true;
        document.getElementById('send_btn').disabled = true;
        document.getElementById('progress_card').style.display = 'block';
        document.getElementById('error_log').style.display = 'none';
        document.getElementById('error_list').innerHTML = '';

        const total = recipientList.length;
        let sent = 0;
        let failed = 0;

        document.getElementById('total_count').innerText = total;
        document.getElementById('sent_count').innerText = '0';
        document.getElementById('failed_count').innerText = '0';
        document.getElementById('progress_bar').style.width = '0%';

        // Batch sending
        for (let i = 0; i < total; i++) {
            const email = recipientList[i];

            const formData = new FormData();
            Object.keys(smtpData).forEach(key => formData.append(key, smtpData[key]));
            Object.keys(emailContent).forEach(key => formData.append(key, emailContent[key]));
            formData.append('to_email', email);

            try {
                const response = await fetch('<?php echo APP_URL; ?>/mailer/send', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    sent++;
                } else {
                    failed++;
                    logError(email, result.message);
                }
            } catch (error) {
                failed++;
                logError(email, 'Network error or server timeout');
            }

            // Update UI
            document.getElementById('sent_count').innerText = sent;
            document.getElementById('failed_count').innerText = failed;
            const progress = Math.round(((i + 1) / total) * 100);
            document.getElementById('progress_bar').style.width = progress + '%';
        }

        isSending = false;
        document.getElementById('send_btn').disabled = false;
        alert(`Sending finished! \nSent: ${sent}\nFailed: ${failed}`);
    }

    function logError(email, message) {
        document.getElementById('error_log').style.display = 'block';
        const list = document.getElementById('error_list');
        const err = document.createElement('div');
        err.className = 'mb-1';
        err.innerHTML = `<strong>${email}:</strong> ${message}`;
        list.appendChild(err);
    }

    function showPreview() {
        const body = tinymce.get('message_body').getContent();
        const subject = document.getElementById('subject').value;

        if (!body) {
            alert('Please enter a message to preview.');
            return;
        }

        document.getElementById('previewSubject').innerText = subject ? 'Subject: ' + subject : '(No Subject)';

        const iframe = document.getElementById('previewIframe');
        const doc = iframe.contentDocument || iframe.contentWindow.document;

        doc.open();
        doc.write(body);
        doc.close();

        const modal = new bootstrap.Modal(document.getElementById('previewModal'));
        modal.show();
    }

    async function saveSmtpSettings() {
        const btn = document.getElementById('save_smtp_btn');
        const originalText = btn.innerHTML;

        const smtpData = {
            smtp_host: document.getElementById('smtp_host').value.trim(),
            smtp_port: document.getElementById('smtp_port').value.trim(),
            smtp_enc: document.getElementById('smtp_enc').value,
            smtp_user: document.getElementById('smtp_user').value.trim(),
            smtp_pass: document.getElementById('smtp_pass').value.trim(),
            from_name: document.getElementById('from_name').value.trim(),
            from_email: document.getElementById('from_email').value.trim(),
        };

        // If not clearing, check for required host/port
        const isEmpty = !smtpData.smtp_host && !smtpData.smtp_user && !smtpData.smtp_pass;
        if (!isEmpty && (!smtpData.smtp_host || !smtpData.smtp_port)) {
            alert('Please fill in SMTP Host and Port before saving.');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

        try {
            const formData = new FormData();
            Object.keys(smtpData).forEach(key => formData.append(key, smtpData[key]));

            const response = await fetch('<?php echo APP_URL; ?>/mailer/saveSmtp', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                alert('SMTP settings saved successfully!');
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            alert('An unexpected error occurred while saving.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>