<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row mb-4 align-items-center">
    <div class="col">
        <h1 class="h4 mb-1">Public Payment Links</h1>
        <p class="text-muted small mb-0">Create and share landing pages for passive payment collection</p>
    </div>
    <div class="col-auto">
        <div class="d-flex gap-2">
            <a href="<?php echo APP_URL; ?>/invoices" class="btn btn-light btn-sm px-3 shadow-none border">
                <i class="bi bi-receipt me-1"></i> Invoices
            </a>
            <button type="button" class="btn btn-primary btn-sm px-3" data-bs-toggle="modal"
                data-bs-target="#createTemplateModal">
                <i class="bi bi-plus-lg me-1"></i> Create New Link
            </button>
        </div>
    </div>
</div>

<?php echo $this->flash(); ?>

<div class="card border-0 shadow-sm overflow-hidden mb-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-4">Internal Title</th>
                    <th>Link Type</th>
                    <th>Fixed Amount</th>
                    <th>Status</th>
                    <th>Usage</th>
                    <th>Created</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($links)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-link-45deg fs-2 d-block mb-3"></i>
                                <p class="mb-0">No public links found.</p>
                                <p class="small">Create your first link to start collecting payments automatically.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($links as $l):
                        $publicUrl = ($l['link_type'] === 'Invoice') ?
                            APP_URL . '/portal/invoice/' . $l['token'] :
                            APP_URL . '/portal/payment/' . $l['token'];
                        ?>
                        <tr class="transition-all hover-soft-bg">
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-light text-primary rounded-circle d-flex align-items-center justify-content-center me-3 fw-semibold shadow-sm"
                                        style="width: 36px; height: 36px; font-size: 0.8rem; background-color: #f8fafc !important; border: 1px solid #e2e8f0;">
                                        <i
                                            class="bi <?php echo $l['link_type'] === 'Invoice' ? 'bi-file-earmark-text' : 'bi-link-45deg'; ?>"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-main mb-0"><?php echo Security::escape($l['title']); ?></div>
                                        <?php if ($l['link_type'] === 'Invoice'): ?>
                                            <span class="x-small text-muted fw-medium py-0">Direct Bill</span>
                                        <?php else: ?>
                                            <span class="x-small text-muted fw-medium py-0">Reusable Link</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-muted border border-light-subtle fw-medium px-2 py-1"
                                    style="font-size: 0.65rem;"><?php echo strtoupper($l['link_type']); ?></span>
                            </td>
                            <td>
                                <div class="data-amount text-main">
                                    <?php echo Branding::getCurrencySymbol() . number_format($l['amount'], 2); ?>
                                </div>
                            </td>
                            <td>
                                <?php if ($l['payment_closed']): ?>
                                    <span class="badge badge-soft-danger px-2 py-1"><i class="bi bi-record-fill me-1"></i>
                                        Closed</span>
                                <?php else: ?>
                                    <span class="badge badge-soft-success px-2 py-1"><i class="bi bi-record-fill me-1"></i>
                                        Active</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="small d-flex align-items-center">
                                    <div class="usage-count me-2 bg-light px-2 rounded fw-bold text-main"
                                        style="font-size: 0.8rem;"><?php echo $l['payments_count']; ?></div>
                                    <span class="text-muted x-small fw-medium">collected</span>
                                </div>
                            </td>
                            <td>
                                <div class="small text-muted fw-medium">
                                    <?php echo date('M d, Y', strtotime($l['created_at'])); ?>
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light border-0 bg-transparent px-2" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots fs-5 text-muted"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-premium border-0 py-2">
                                        <li><a class="dropdown-item py-2 small fw-medium" href="<?php echo $publicUrl; ?>"
                                                target="_blank"><i class="bi bi-box-arrow-up-right me-2 text-primary"></i> View
                                                Public Page</a></li>
                                        <li><button class="dropdown-item py-2 small fw-medium"
                                                onclick="copyLink('<?php echo $publicUrl; ?>')"><i
                                                    class="bi bi-clipboard2-check me-2 text-info"></i> Copy Secure Link</button>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider opacity-50">
                                        </li>
                                        <li><button class="dropdown-item py-2 small fw-medium"
                                                onclick="sendEmail('<?php echo $l['link_type']; ?>', '<?php echo $l['original_id']; ?>')"><i
                                                    class="bi bi-send me-2 text-primary"></i> Send via Email</button></li>
                                        <li><a class="dropdown-item py-2 small fw-medium"
                                                href="<?php echo APP_URL; ?>/payments/edit/<?php echo $l['link_type']; ?>/<?php echo $l['original_id']; ?>"><i
                                                    class="bi bi-pencil-square me-2 text-muted"></i> Edit Content</a></li>
                                        <li><a class="dropdown-item py-2 small fw-medium"
                                                href="<?php echo APP_URL; ?>/payments/toggle/<?php echo $l['link_type']; ?>/<?php echo $l['original_id']; ?>">
                                                <i
                                                    class="bi <?php echo $l['payment_closed'] ? 'bi-play-circle text-success' : 'bi-stop-circle text-danger'; ?> me-2"></i>
                                                <?php echo $l['payment_closed'] ? 'Re-activate Link' : 'Close Link'; ?>
                                            </a></li>
                                        <li>
                                            <hr class="dropdown-divider opacity-50">
                                        </li>
                                        <li><button class="dropdown-item py-2 small fw-medium text-danger"
                                                onclick="confirmDelete('<?php echo $l['link_type']; ?>', '<?php echo $l['original_id']; ?>', <?php echo $l['payments_count']; ?>)"><i
                                                    class="bi bi-trash3 me-2"></i> Delete Forever</button></li>
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

<!-- Create Template Modal -->
<div class="modal fade" id="createTemplateModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-premium rounded-24">
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold">Create Public Payment Link</h5>
                <button type="button" class="btn-close small" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?php echo APP_URL; ?>/payments/create" method="POST">
                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">
                <div class="modal-body p-4p-md-5">
                    <div class="row g-4 text-start">
                        <div class="col-md-7">
                            <h6 class="fw-bold x-small text-uppercase mb-4 text-muted tracking-wider">General
                                Information</h6>
                            <div class="form-floating mb-3">
                                <input type="text" name="title" class="form-control border-subtle" id="modalTitle"
                                    placeholder="e.g., SEO Project Deposit" required>
                                <label for="modalTitle">Landing Page Title</label>
                            </div>

                            <div class="form-floating mb-3">
                                <textarea name="description" class="form-control border-subtle" id="modalDesc"
                                    style="height: 100px;" placeholder="Description"></textarea>
                                <label for="modalDesc">Description (Publicly visible)</label>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold x-small text-uppercase text-muted tracking-wider">Fixed
                                    Amount (<?php echo Branding::getCurrencyCode(); ?>)</label>
                                <div class="input-group input-group-lg shadow-sm rounded-12 overflow-hidden border">
                                    <span
                                        class="input-group-text bg-white border-0 text-muted"><?php echo Branding::getCurrencySymbol(); ?></span>
                                    <input type="number" name="amount" class="form-control border-0 fw-bold text-main"
                                        step="0.01" min="1" placeholder="0.00" required>
                                </div>
                            </div>

                            <h6 class="fw-bold x-small text-uppercase mb-3 text-muted tracking-wider mt-4">Payment
                                Methods</h6>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label
                                        class="gateway-tile p-3 rounded-12 border d-flex align-items-center transition-all cursor-pointer bg-white"
                                        for="modal_paypal">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" name="allow_paypal"
                                                id="modal_paypal" checked>
                                            <label class="form-check-label fw-bold ms-1"
                                                for="modal_paypal">PayPal</label>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-6">
                                    <label
                                        class="gateway-tile p-3 rounded-12 border d-flex align-items-center transition-all cursor-pointer bg-white"
                                        for="modal_stripe">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" name="allow_stripe"
                                                id="modal_stripe" checked>
                                            <label class="form-check-label fw-bold ms-1"
                                                for="modal_stripe">Stripe</label>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="p-4 bg-light rounded-24 h-100 border border-subtle">
                                <h6 class="fw-bold x-small text-uppercase mb-3 text-muted tracking-wider">Checkout
                                    Fields</h6>
                                <p class="text-muted small mb-4">Select fields for the checkout form.</p>

                                <div class="list-group list-group-flush bg-transparent">
                                    <?php
                                    $fields = [
                                        'name' => 'Full Name',
                                        'email' => 'Email Address',
                                        'phone' => 'Phone Number',
                                        'address' => 'Physical Address',
                                        'company' => 'Company Name'
                                    ];
                                    foreach ($fields as $key => $label): ?>
                                        <div
                                            class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0 py-2 border-light">
                                            <span class="small fw-semibold text-main"><?php echo $label; ?></span>
                                            <div class="d-flex gap-2">
                                                <div class="form-check form-switch" title="Visible">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="fields[<?php echo $key; ?>][visible]" <?php echo in_array($key, ['name', 'email']) ? 'checked disabled' : 'checked'; ?>>
                                                    <?php if (in_array($key, ['name', 'email'])): ?>
                                                        <input type="hidden" name="fields[<?php echo $key; ?>][visible]"
                                                            value="1">
                                                    <?php endif; ?>
                                                </div>
                                                <div class="form-check" title="Required field">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="fields[<?php echo $key; ?>][required]" <?php echo in_array($key, ['name', 'email']) ? 'checked' : ''; ?>>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mt-3 x-small text-muted d-flex align-items-center">
                                    <i class="bi bi-info-circle-fill me-2 text-primary"></i>
                                    <span>2nd toggle marks field as required.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-4 px-5">
                    <button type="button" class="btn btn-link text-muted fw-medium text-decoration-none"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 py-2 fw-bold shadow-lg rounded-12">
                        Generate Secure Link
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .ls-wide {
        letter-spacing: 0.05em;
    }

    .method-selection {
        transition: all 0.2s ease;
        cursor: pointer;
        background: #fff;
    }

    .method-selection:hover {
        border-color: var(--primary-accent) !important;
        background-color: rgba(59, 130, 246, 0.02);
    }

    .method-selection .form-check-input:checked {
        background-color: var(--primary-accent);
        border-color: var(--primary-accent);
    }

    .x-small {
        font-size: 0.75rem;
    }

    .rounded-24 {
        border-radius: 24px !important;
    }

    .rounded-12 {
        border-radius: 12px !important;
    }

    .hover-soft-bg:hover {
        background-color: #f8fafc !important;
    }
</style>

<script>
    function copyLink(link) {
        navigator.clipboard.writeText(link).then(() => {
            showToast('<i class="bi bi-check-circle-fill me-2 text-success"></i> Secure Link successfully copied!');
        });
    }

    function showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'position-fixed bottom-0 start-50 translate-middle-x mb-5 badge bg-dark p-3 shadow-lg';
        toast.style.zIndex = '9999';
        toast.style.borderRadius = '12px';
        toast.innerHTML = message;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.transition = 'opacity 0.5s';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 500);
        }, 2000);
    }

    function confirmDelete(type, id, paymentsCount) {
        let message = `Are you sure you want to delete this ${type.toLowerCase()}?`;
        if (paymentsCount > 0) {
            message += `\n\nWARNING: There are ${paymentsCount} payments recorded for this ${type.toLowerCase()}. Deleting it may cause data inconsistencies!`;
        }

        if (confirm(message)) {
            window.location.href = `<?php echo APP_URL; ?>/payments/delete/${type}/${id}`;
        }
    }

    function sendEmail(type, id) {
        let recipient = "";
        if (type === 'Invoice') {
            if (!confirm('Send this invoice to the client via email?')) return;
        } else {
            recipient = prompt("Enter the recipient's email address:");
            if (!recipient) return;
            if (!/^\S+@\S+\.\S+$/.test(recipient)) {
                alert("Please enter a valid email address.");
                return;
            }
        }

        const url = type === 'Invoice'
            ? `<?php echo APP_URL; ?>/invoices/sendEmail/${id}`
            : `<?php echo APP_URL; ?>/payments/sendEmail/${id}?email=${encodeURIComponent(recipient)}`;

        showToast('<span class="spinner-border spinner-border-sm me-2"></span> Sending... Please wait.');

        fetch(url, { method: 'POST' })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Success: ' + data.message);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => alert('Error sending email: ' + error));
    }
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>