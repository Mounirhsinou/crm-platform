<?php $pageTitle = 'Collected Leads'; ?>
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row mb-4 align-items-center">
    <div class="col">
        <h1 class="h4 mb-1">Collected Leads</h1>
        <p class="text-muted small mb-0">Passive leads captured through external forms</p>
    </div>
    <div class="col-auto">
        <?php if ($allow_export): ?>
            <a href="<?php echo APP_URL; ?>/leads/export" class="btn btn-light btn-sm px-3">
                <i class="bi bi-box-arrow-up me-1"></i> Export Leads
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-4">Lead Information</th>
                    <th>Contact Details</th>
                    <th>Source</th>
                    <th>Capture Date</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($leads)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-funnel fs-2 d-block mb-3 opacity-25"></i>
                                <p class="mb-0">No leads collected yet.</p>
                                <p class="small text-muted">passive data collection from your <a
                                        href="<?php echo APP_URL; ?>/payments"
                                        class="text-primary text-decoration-none">Public Links</a></p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($leads as $lead): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-medium text-main">
                                    <?php echo Security::escape($lead['name'] ?: 'Anonymous Lead'); ?>
                                </div>
                                <div class="text-muted small"><?php echo Security::escape($lead['company'] ?: 'Personal'); ?>
                                </div>
                            </td>
                            <td>
                                <div class="small d-flex align-items-center mb-1 text-secondary">
                                    <i class="bi bi-envelope me-2 opacity-50"></i>
                                    <span><?php echo Security::escape($lead['email']); ?></span>
                                </div>
                                <div class="small d-flex align-items-center text-secondary">
                                    <i class="bi bi-telephone me-2 opacity-50"></i>
                                    <span><?php echo Security::escape($lead['phone'] ?: 'No phone'); ?></span>
                                </div>
                            </td>
                            <td>
                                <?php
                                $sourceColor = ($lead['source'] === 'invoice' ? 'primary' : 'success');
                                ?>
                                <span class="badge badge-soft-<?php echo $sourceColor; ?>">
                                    <?php echo ucfirst($lead['source']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="text-main mb-0"><?php echo date('M d, Y', strtotime($lead['created_at'])); ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    <?php echo date('H:i', strtotime($lead['created_at'])); ?>
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light px-2" data-bs-toggle="dropdown"
                                        data-bs-popper-config='{"strategy":"fixed"}'>
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                                        <li><button class="dropdown-item py-2 small view-lead-btn"
                                                data-details='<?php echo htmlspecialchars($lead['lead_data'] ?? json_encode($lead), ENT_QUOTES, 'UTF-8'); ?>'
                                                data-bs-toggle="modal" data-bs-target="#viewLeadModal">
                                                <i class="bi bi-eye me-2"></i> View Full Details</button></li>
                                        <li><button class="dropdown-item py-2 small send-email-btn" 
                                            data-id="<?php echo $lead['id']; ?>"
                                            data-email="<?php echo Security::escape($lead['email']); ?>"
                                            data-name="<?php echo Security::escape($lead['name'] ?: 'Lead'); ?>">
                                            <i class="bi bi-envelope me-2"></i> Send Email</button></li>
                                        <?php if (!empty($lead['phone'])): ?>
                                            <li><a class="dropdown-item py-2 small"
                                                    href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $lead['phone']); ?>"
                                                    target="_blank"><i class="bi bi-whatsapp me-2"></i> WhatsApp</a></li>
                                        <?php endif; ?>
                                        <li>
                                            <hr class="dropdown-divider opacity-50">
                                        </li>
                                        <li><a class="dropdown-item py-2 small text-danger"
                                                href="<?php echo APP_URL; ?>/leads/delete/<?php echo $lead['id']; ?>?csrf_token=<?php echo $csrf_token; ?>"
                                                onclick="return confirm('Are you sure you want to remove this lead?')"><i
                                                    class="bi bi-trash me-2"></i> Delete Lead</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<!-- Send Email Modal -->
<div class="modal fade" id="sendEmailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Compose Email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 pt-3">
                <form id="sendEmailForm">
                    <input type="hidden" id="email_lead_id" name="id">
                    <div class="mb-3">
                        <label class="small text-muted d-block mb-1">To</label>
                        <input type="text" id="email_to_display" class="form-control bg-light border-0" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="small text-muted d-block mb-1">Subject</label>
                        <input type="text" id="email_subject" name="subject" class="form-control border-light" required>
                    </div>
                    <div class="mb-0">
                        <label class="small text-muted d-block mb-1">Message</label>
                        <textarea id="email_message" name="message" class="form-control border-light" rows="6" required placeholder="Type your message here..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirmSendBtn" class="btn btn-indigo btn-sm px-4">
                    <i class="bi bi-send me-2"></i> Send via SMTP
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Lead Modal -->
<div class="modal fade" id="viewLeadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Lead Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 pt-3">
                <div id="leadContent">
                    <!-- Dynamic Content -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('viewLeadModal');
        const content = document.getElementById('leadContent');

        document.querySelectorAll('.view-lead-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const data = JSON.parse(this.getAttribute('data-details'));
                renderLeadData(data);
            });
        });

        // Email Sending Logic
        const sendEmailModal = new bootstrap.Modal(document.getElementById('sendEmailModal'));
        const sendEmailForm = document.getElementById('sendEmailForm');
        const sendBtn = document.getElementById('confirmSendBtn');

        document.querySelectorAll('.send-email-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const email = this.getAttribute('data-email');
                const name = this.getAttribute('data-name');

                document.getElementById('email_lead_id').value = id;
                document.getElementById('email_to_display').value = `${name} (${email})`;
                document.getElementById('email_subject').value = `Hello ${name}`;

                sendEmailModal.show();
            });
        });

        sendBtn.addEventListener('click', function () {
            if (!sendEmailForm.checkValidity()) {
                sendEmailForm.reportValidity();
                return;
            }

            const id = document.getElementById('email_lead_id').value;
            const originalText = this.innerHTML;

            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';

            const formData = new FormData(sendEmailForm);

            fetch(`<?php echo APP_URL; ?>/leads/sendEmail/${id}`, {
                method: 'POST',
                body: new URLSearchParams(formData)
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Email sent successfully!');
                        sendEmailModal.hide();
                        sendEmailForm.reset();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(err => alert('Failed to send email. Please check your connection.'))
                .finally(() => {
                    this.disabled = false;
                    this.innerHTML = originalText;
                });
        });

        function renderLeadData(data) {
            let html = '';

            const sections = {
                'Contact Info': ['name', 'email', 'phone', 'company'],
                'Address Info': ['address', 'city', 'country'],
                'Additional Details': []
            };

            // Identify which fields are handled in specific sections
            const handledFields = [...sections['Contact Info'], ...sections['Address Info']];

            // Add anything else to Additional Details
            Object.keys(data).forEach(key => {
                if (!handledFields.includes(key) && !['id', 'user_id', 'source_id', 'lead_data', 'created_at', 'updated_at', 'source'].includes(key)) {
                    sections['Additional Details'].push(key);
                }
            });

            // Always show capture info at the bottom or top
            const metaInfo = {
                'Source': data.source ? data.source.charAt(0).toUpperCase() + data.source.slice(1) : 'N/A',
                'Capture Date': data.created_at || 'N/A'
            };

            Object.entries(sections).forEach(([title, fields]) => {
                const filteredFields = fields.filter(f => data[f] && data[f].trim() !== '');
                if (filteredFields.length > 0 || (title === 'Additional Details' && Object.keys(metaInfo).length > 0)) {
                    html += `<div class="mb-4">
                    <h6 class="text-uppercase small fw-bold text-secondary mb-3 pb-2 border-bottom opacity-75">${title}</h6>
                    <div class="row g-3">`;

                    filteredFields.forEach(field => {
                        const label = field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                        html += `<div class="col-12">
                        <label class="small text-muted d-block mb-1">${label}</label>
                        <div class="fw-medium text-dark">${data[field]}</div>
                    </div>`;
                    });

                    if (title === 'Additional Details') {
                        Object.entries(metaInfo).forEach(([label, value]) => {
                            html += `<div class="col-12">
                            <label class="small text-muted d-block mb-1">${label}</label>
                            <div class="fw-medium text-dark">${value}</div>
                        </div>`;
                        });
                    }

                    html += `</div></div>`;
                }
            });

            if (!html) {
                html = '<div class="text-center py-4 text-muted small">No detailed information available for this lead.</div>';
            }

            content.innerHTML = html;
        }
    });
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>