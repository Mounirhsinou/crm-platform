<?php $pageTitle = ($invoice ? 'Modify' : 'Generate') . ' Invoice'; ?>
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row mb-4 align-items-center">
    <div class="col">
        <h1 class="h4 mb-1"><?php echo $invoice ? 'Adjust Invoice' : 'Create Billing Request'; ?></h1>
        <p class="text-muted small mb-0">
            <?php echo $invoice ? 'Fine-tune financial details for an existing record' : 'Draft a new professional invoice for client settlement'; ?>
        </p>
    </div>
    <div class="col-auto">
        <a href="<?php echo APP_URL; ?>/invoices" class="btn btn-light btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Billing Registry
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-11 col-xl-10">
        <form method="POST" id="invoiceForm">
            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">

            <div class="row g-4">
                <!-- Main Invoice Geometry -->
                <div class="col-md-7">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-4 d-flex align-items-center">
                                <i class="bi bi-person-lines-fill me-2 text-primary"></i>
                                Recipient Assignment
                            </h6>

                            <?php if (isset($invoice['client_name'])): ?>
                                <div
                                    class="p-3 bg-success bg-opacity-10 rounded-3 mb-4 small text-success border-0 d-flex align-items-center">
                                    <i class="bi bi-magic me-3 fs-5"></i>
                                    <div>
                                        <strong>Intelligent Pre-fill:</strong> Data synchronized for
                                        <span
                                            class="fw-bold"><?php echo Security::escape($invoice['client_name']); ?></span>.
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="client_id" class="form-label small fw-medium">Active Client <span
                                            class="text-danger">*</span></label>
                                    <select
                                        class="form-select border-light bg-light <?php echo isset($errors['client_id']) ? 'is-invalid' : ''; ?>"
                                        id="client_id" name="client_id" required>
                                        <option value="">Select Target Client...</option>
                                        <?php foreach ($clients as $client): ?>
                                            <option value="<?php echo $client['id']; ?>" <?php echo (isset($invoice['client_id']) && $invoice['client_id'] == $client['id']) ? 'selected' : ''; ?>>
                                                <?php echo Security::escape($client['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (isset($errors['client_id'])): ?>
                                        <div class="invalid-feedback small"><?php echo $errors['client_id']; ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-12">
                                    <label class="form-label small fw-medium">Select Services/Deals to Include</label>
                                    <div id="deal_selection_container" class="p-3 bg-light rounded-3 border">
                                        <p class="text-muted small mb-0" id="no_deals_msg">Please select a client first
                                            to see available deals.</p>
                                        <div id="deal_checklist" class="row g-2">
                                            <!-- Dynamic Checkboxes -->
                                        </div>
                                    </div>
                                    <div class="form-text x-small opacity-75 mt-2">
                                        <i class="bi bi-info-circle me-1"></i> You can select multiple deals to
                                        consolidate them into one invoice.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-4 d-flex align-items-center">
                                <i class="bi bi-list-check me-2 text-primary"></i>
                                Line Items Preview
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-sm x-small align-middle" id="line_items_table">
                                    <thead>
                                        <tr class="text-muted text-uppercase">
                                            <th>Description</th>
                                            <th class="text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="2" class="text-center py-3 text-muted">No items selected</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="row g-3 text-center mt-3 border-top pt-3">
                                <div class="col-6">
                                    <div class="p-2 bg-light rounded-2 border border-dashed">
                                        <label
                                            class="x-small fw-bold text-muted text-uppercase ls-wide mb-1 d-block">Subtotal</label>
                                        <h5 class="mb-0 fw-bold text-dark" id="displayAmount">
                                            <?php echo CURRENCY_SYMBOL; ?><?php echo number_format($invoice['amount'] ?? 0, 2); ?>
                                        </h5>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 bg-light rounded-2 border border-dashed">
                                        <label
                                            class="x-small fw-bold text-muted text-uppercase ls-wide mb-1 d-block">Final
                                            Total</label>
                                        <h5 class="mb-0 fw-bold text-primary" id="displayTotal">
                                            <?php echo CURRENCY_SYMBOL; ?><?php echo number_format($invoice['amount'] ?? 0, 2); ?>
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Logistics & Metadata -->
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-4 d-flex align-items-center">
                                <i class="bi bi-wallet2 me-2 text-primary"></i>
                                Ledger Details
                            </h6>

                            <div class="mb-4">
                                <label for="amount" class="form-label small fw-medium">Statement Amount <span
                                        class="text-danger">*</span></label>
                                <div class="input-group input-group-sm shadow-sm">
                                    <span
                                        class="input-group-text bg-white border-end-0 text-muted"><?php echo CURRENCY_SYMBOL; ?></span>
                                    <input type="number" step="0.01"
                                        class="form-control bg-white border-start-0 fw-bold <?php echo isset($errors['amount']) ? 'is-invalid' : ''; ?>"
                                        id="amount" name="amount" value="<?php echo $invoice['amount'] ?? '0.00'; ?>"
                                        readonly required>
                                </div>
                                <div class="form-text x-small opacity-75">This is automatically calculated from selected
                                    deals.</div>
                                <?php if (isset($errors['amount'])): ?>
                                    <div class="invalid-feedback small"><?php echo $errors['amount']; ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-0">
                                <label for="status" class="form-label small fw-medium">Payment Status</label>
                                <div class="d-flex gap-2">
                                    <div class="flex-grow-1">
                                        <input type="radio" class="btn-check" name="status" id="status_unpaid"
                                            value="unpaid" <?php echo (!isset($invoice['status']) || $invoice['status'] === 'unpaid') ? 'checked' : ''; ?> autocomplete="off">
                                        <label class="btn btn-outline-warning btn-sm w-100 py-2 border-dashed"
                                            for="status_unpaid">
                                            <i class="bi bi-hourglass-split me-1"></i> Pending
                                        </label>
                                    </div>
                                    <div class="flex-grow-1">
                                        <input type="radio" class="btn-check" name="status" id="status_paid"
                                            value="paid" <?php echo (isset($invoice['status']) && $invoice['status'] === 'paid') ? 'checked' : ''; ?> autocomplete="off">
                                        <label class="btn btn-outline-success btn-sm w-100 py-2 border-dashed"
                                            for="status_paid">
                                            <i class="bi bi-check-circle me-1"></i> Reconciled
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm sticky-top" style="top: 100px;">
                        <div class="card-body p-4 text-center">
                            <div class="mb-4">
                                <div class="h3 fw-bold text-dark mb-1 currency-symbol">
                                    <?php echo CURRENCY_SYMBOL; ?><span
                                        id="summaryTotal"><?php echo number_format($invoice['amount'] ?? 0, 2); ?></span>
                                </div>
                                <div class="text-uppercase x-small fw-bold text-muted ls-wide">Total Invoice Value</div>
                            </div>

                            <p class="small text-secondary mb-4">Click authorize to generate the official PDF and secure
                                payment link for this client.</p>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary py-3 fw-bold">
                                    <i class="bi bi-file-earmark-check me-2"></i>
                                    <?php echo $invoice ? 'Commit Changes' : 'Authorize & Issue'; ?>
                                </button>
                                <a href="<?php echo APP_URL; ?>/invoices"
                                    class="btn btn-link btn-sm text-muted text-decoration-none">
                                    Discard Draft
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const clientSelect = document.getElementById('client_id');
        const checklist = document.getElementById('deal_checklist');
        const noDealsMsg = document.getElementById('no_deals_msg');
        const amountInput = document.getElementById('amount');
        const displayAmount = document.getElementById('displayAmount');
        const displayTotal = document.getElementById('displayTotal');
        const summaryTotal = document.getElementById('summaryTotal');
        const itemsTableBody = document.querySelector('#line_items_table tbody');

        let allDeals = [];
        let selectedDeals = [];

        // Pre-fill selected deals if editing
        const prefilledDeals = <?php echo isset($invoice['items']) ? json_encode(array_column($invoice['items'], 'deal_id')) : '[]'; ?>;

        function updateTotals() {
            let total = 0;
            itemsTableBody.innerHTML = '';

            const checkboxes = document.querySelectorAll('.deal-checkbox:checked');

            if (checkboxes.length === 0) {
                itemsTableBody.innerHTML = '<tr><td colspan="2" class="text-center py-3 text-muted">No items selected</td></tr>';
            } else {
                checkboxes.forEach(cb => {
                    const dealId = cb.value;
                    const deal = allDeals.find(d => d.id == dealId);
                    if (deal) {
                        total += parseFloat(deal.amount);

                        const row = `<tr>
                            <td><div class="fw-medium">${deal.title}</div></td>
                            <td class="text-end fw-bold">${parseFloat(deal.amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                        </tr>`;
                        itemsTableBody.insertAdjacentHTML('beforeend', row);
                    }
                });
            }

            const formattedTotal = total.toFixed(2);
            amountInput.value = formattedTotal;
            displayAmount.innerText = '<?php echo CURRENCY_SYMBOL; ?>' + total.toLocaleString(undefined, { minimumFractionDigits: 2 });
            displayTotal.innerText = '<?php echo CURRENCY_SYMBOL; ?>' + total.toLocaleString(undefined, { minimumFractionDigits: 2 });
            summaryTotal.innerText = total.toLocaleString(undefined, { minimumFractionDigits: 2 });
        }

        function loadDeals(clientId) {
            if (!clientId) {
                checklist.innerHTML = '';
                noDealsMsg.classList.remove('d-none');
                updateTotals();
                return;
            }

            noDealsMsg.innerText = 'Synchronizing with ledger...';

            fetch(`<?php echo APP_URL; ?>/deals/getByClient/${clientId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        allDeals = data.deals;
                        checklist.innerHTML = '';
                        noDealsMsg.classList.add('d-none');

                        if (allDeals.length === 0) {
                            noDealsMsg.innerText = 'No active deals found for this client.';
                            noDealsMsg.classList.remove('d-none');
                        } else {
                            allDeals.forEach(deal => {
                                const isChecked = prefilledDeals.includes(parseInt(deal.id)) ? 'checked' : '';
                                const dealHtml = `
                                    <div class="col-12 col-lg-6">
                                        <div class="form-check p-2 border rounded-3 bg-white hover-shadow-sm transition-all h-100">
                                            <input class="form-check-input deal-checkbox ms-1 me-2" type="checkbox" name="deal_ids[]" value="${deal.id}" id="deal_${deal.id}" ${isChecked}>
                                            <label class="form-check-label w-100 cursor-pointer" for="deal_${deal.id}">
                                                <div class="fw-bold small">${deal.title}</div>
                                                <div class="text-primary fw-bold x-small"><?php echo CURRENCY_SYMBOL; ?>${parseFloat(deal.amount).toLocaleString(undefined, { minimumFractionDigits: 2 })}</div>
                                            </label>
                                        </div>
                                    </div>
                                `;
                                checklist.insertAdjacentHTML('beforeend', dealHtml);
                            });

                            document.querySelectorAll('.deal-checkbox').forEach(cb => {
                                cb.addEventListener('change', updateTotals);
                            });
                        }
                        updateTotals();
                    }
                })
                .catch(error => {
                    console.error('Error fetching deals:', error);
                    noDealsMsg.innerText = 'Failed to load deals.';
                    noDealsMsg.classList.remove('d-none');
                });
        }

        if (clientSelect) {
            clientSelect.addEventListener('change', function () {
                loadDeals(this.value);
            });

            // Initial load if client is pre-selected
            if (clientSelect.value) {
                loadDeals(clientSelect.value);
            }
        }
    });
</script>

<style>
    .x-small {
        font-size: 0.75rem;
    }

    .ls-wide {
        letter-spacing: 0.1em;
    }

    .cursor-pointer {
        cursor: pointer;
    }

    .hover-shadow-sm:hover {
        box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075) !important;
    }

    .transition-all {
        transition: all 0.2s ease;
    }

    .border-dashed {
        border-style: dashed !important;
    }

    .currency-symbol {
        letter-spacing: -1px;
    }
</style>

<style>
    .x-small {
        font-size: 0.75rem;
    }

    .ls-wide {
        letter-spacing: 0.1em;
    }

    .btn-outline-warning:hover {
        background-color: #fffbeb;
        color: #d97706;
    }

    .btn-outline-success:hover {
        background-color: #f0fdf4;
        color: #16a34a;
    }

    .btn-white:hover {
        background-color: #f8fafc;
    }
</style>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>