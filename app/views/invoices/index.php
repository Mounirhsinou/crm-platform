<?php $pageTitle = 'Invoices'; ?>
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row mb-4 align-items-center">
    <div class="col">
        <h1 class="h4 mb-1">Billing & Invoices</h1>
        <p class="text-muted small mb-0">Record and manage your client billing and payments</p>
    </div>
    <?php if ($this->hasPermission('invoices', 'create')): ?>
        <div class="col-auto">
            <a href="<?php echo APP_URL; ?>/invoices/create" class="btn btn-primary btn-sm px-3">
                <i class="bi bi-plus-lg me-1"></i> Create Invoice
            </a>
        </div>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted small fw-medium">Filter List:</span>
            <div class="nav nav-pills bg-light p-1 rounded-pill small">
                <a href="<?php echo APP_URL; ?>/invoices"
                    class="nav-link py-1 px-3 rounded-pill <?php echo !$current_status ? 'active shadow-sm' : 'text-secondary'; ?>">All
                    Invoices</a>
                <a href="<?php echo APP_URL; ?>/invoices?status=unpaid"
                    class="nav-link py-1 px-3 rounded-pill <?php echo $current_status === 'unpaid' ? 'active shadow-sm' : 'text-secondary'; ?>">Unpaid</a>
                <a href="<?php echo APP_URL; ?>/invoices?status=paid"
                    class="nav-link py-1 px-3 rounded-pill <?php echo $current_status === 'paid' ? 'active shadow-sm' : 'text-secondary'; ?>">Paid</a>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <?php if ($current_status): ?>
                <input type="hidden" name="status" value="<?php echo Security::escape($current_status); ?>">
            <?php endif; ?>
            <div class="col-md-10">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent border-end-0 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0 bg-transparent"
                        placeholder="Search by invoice #, client name or email..."
                        value="<?php echo Security::escape($search); ?>">
                </div>
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-light btn-sm">Search</button>
            </div>
            <?php if ($search): ?>
                <div class="col-12 mt-1">
                    <span class="badge badge-soft-info fw-normal">
                        Showing results for: "<?php echo Security::escape($search); ?>"
                        <a href="<?php echo APP_URL; ?>/invoices<?php echo $current_status ? '?status=' . $current_status : ''; ?>"
                            class="text-decoration-none ms-2"><i class="bi bi-x"></i></a>
                    </span>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-4">Invoice #</th>
                    <th>Client</th>
                    <th>Related Deal</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($invoices)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-receipt fs-2 d-block mb-3 opacity-25"></i>
                                <p class="mb-0">No invoices generated yet.</p>
                                <p class="small text-muted">Create an invoice to start collecting payments.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($invoices as $invoice): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="data-numeric text-main">
                                    <a href="<?php echo APP_URL; ?>/invoices/show/<?php echo $invoice['id']; ?>"
                                        class="text-decoration-none text-main hover-primary">
                                        <?php echo Security::escape($invoice['invoice_number']); ?>
                                    </a>
                                </div>
                            </td>
                            <td>
                                <div class="client-name"><?php echo Security::escape($invoice['client_name']); ?></div>
                            </td>
                            <td>
                                <div class="small text-muted text-truncate" style="max-width: 150px;">
                                    <?php echo Security::escape($invoice['deal_title'] ?? 'Direct Billing'); ?>
                                </div>
                            </td>
                            <td>
                                <div class="data-amount text-main">
                                    <?php echo CURRENCY_SYMBOL . number_format($invoice['amount'], 2); ?>
                                </div>
                            </td>
                            <td>
                                <?php $isPaid = $invoice['status'] === 'paid'; ?>
                                <span class="badge badge-soft-<?php echo $isPaid ? 'success' : 'danger'; ?>">
                                    <?php echo ucfirst($invoice['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="small text-muted"><?php echo date('M d, Y', strtotime($invoice['created_at'])); ?>
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light px-2" data-bs-toggle="dropdown"
                                        data-bs-popper-config='{"strategy":"fixed"}'>
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                                        <li><a class="dropdown-item py-2 small"
                                                href="<?php echo APP_URL; ?>/invoices/show/<?php echo $invoice['id']; ?>"><i
                                                    class="bi bi-file-earmark-text me-2"></i> View Invoice</a></li>

                                        <?php if ($this->hasPermission('invoices', 'edit') && $invoice['status'] === 'unpaid'): ?>
                                            <li><a class="dropdown-item py-2 small"
                                                    href="<?php echo APP_URL; ?>/invoices/markPaid/<?php echo $invoice['id']; ?>"><i
                                                        class="bi bi-check2-circle me-2"></i> Mark as Paid</a></li>
                                        <?php endif; ?>

                                        <?php if ($this->hasPermission('invoices', 'send')): ?>
                                            <li><a class="dropdown-item py-2 small" href="#"
                                                    onclick="openSendModal(<?php echo $invoice['id']; ?>); return false;"><i
                                                        class="bi bi-send me-2"></i> Send to Email</a></li>
                                        <?php endif; ?>

                                        <?php if ($this->hasPermission('invoices', 'edit')): ?>
                                            <li><a class="dropdown-item py-2 small"
                                                    href="<?php echo APP_URL; ?>/invoices/edit/<?php echo $invoice['id']; ?>"><i
                                                        class="bi bi-pencil me-2"></i> Edit Record</a></li>
                                        <?php endif; ?>

                                        <?php if ($this->hasPermission('invoices', 'delete')): ?>
                                            <li>
                                                <hr class="dropdown-divider opacity-50">
                                            </li>
                                            <li><a class="dropdown-item py-2 small text-danger"
                                                    href="<?php echo APP_URL; ?>/invoices/delete/<?php echo $invoice['id']; ?>"
                                                    onclick="return confirm('Are you sure you want to delete this invoice?')"><i
                                                        class="bi bi-trash me-2"></i> Delete</a></li>
                                        <?php endif; ?>
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

<!-- Send Invoice Modal -->
<div class="modal fade" id="sendInvoiceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header border-bottom-0 pb-0">
                <h6 class="modal-title fw-bold">Send Invoice</h6>
                <button type="button" class="btn-close small" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-4">
                <div id="modal_loading" class="text-center py-3">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                </div>
                <div id="modal_content" style="display: none;">
                    <div class="d-flex align-items-center mb-4 p-3 bg-light rounded-3">
                        <div class="stat-card-icon bg-primary bg-opacity-10 text-primary me-3">
                            <i class="bi bi-envelope"></i>
                        </div>
                        <div>
                            <div class="small text-muted">Sending to:</div>
                            <div id="m_client_email" class="fw-bold"></div>
                        </div>
                    </div>

                    <div class="list-group list-group-flush small mb-0">
                        <div class="list-group-item d-flex justify-content-between px-0 py-2 border-0">
                            <span class="text-muted">Invoice Number</span>
                            <span id="m_invoice_number" class="fw-medium"></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0 py-2 border-0">
                            <span class="text-muted">Client Name</span>
                            <span id="m_client_name" class="fw-medium"></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0 py-2 border-0">
                            <span class="text-muted">Total Amount</span>
                            <span id="m_amount" class="fw-bold text-primary"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-light btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirm_send_btn" class="btn btn-primary btn-sm px-4" onclick="confirmSend()">
                    Confirm & Send
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentInvoiceId = null;
    let sendModal = null;

    document.addEventListener('DOMContentLoaded', function () {
        const modalElement = document.getElementById('sendInvoiceModal');
        if (modalElement) {
            sendModal = new bootstrap.Modal(modalElement);
        }
    });

    function openSendModal(id) {
        currentInvoiceId = id;
        document.getElementById('modal_loading').style.display = 'block';
        document.getElementById('modal_content').style.display = 'none';
        document.getElementById('confirm_send_btn').disabled = true;

        sendModal.show();

        fetch('<?php echo APP_URL; ?>/invoices/preview/' + id)
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    const data = result.data;
                    document.getElementById('m_invoice_number').innerText = data.invoice_number;
                    document.getElementById('m_client_name').innerText = data.client_name;
                    document.getElementById('m_client_email').innerText = data.client_email || 'No email provided!';
                    document.getElementById('m_amount').innerText = data.currency + parseFloat(data.amount).toLocaleString(undefined, { minimumFractionDigits: 2 });

                    if (!data.client_email) {
                        alert('Error: Client has no email address. Please update client info first.');
                        sendModal.hide();
                        return;
                    }

                    document.getElementById('modal_loading').style.display = 'none';
                    document.getElementById('modal_content').style.display = 'block';
                    document.getElementById('confirm_send_btn').disabled = false;
                } else {
                    alert('Error: ' + result.message);
                    sendModal.hide();
                }
            });
    }

    function confirmSend() {
        const btn = document.getElementById('confirm_send_btn');
        const originalText = btn.innerText;
        btn.disabled = true;
        btn.innerText = 'Sending...';

        fetch('<?php echo APP_URL; ?>/invoices/sendEmail/' + currentInvoiceId, { method: 'POST' })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    sendModal.hide();
                    // Optional: show a small toast or inline success message
                } else {
                    alert('Error: ' + result.message);
                }
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerText = originalText;
            });
    }
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>