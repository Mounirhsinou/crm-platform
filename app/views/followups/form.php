<?php $pageTitle = ($followup ? 'Adjust' : 'Schedule') . ' Directive'; ?>
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h1 class="h4 mb-1"><?php echo $followup ? 'Adjust' : 'Schedule'; ?> Workflow Directive</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="--bs-breadcrumb-divider: '·';">
                        <li class="breadcrumb-item small"><a href="<?php echo APP_URL; ?>/followups"
                                class="text-muted text-decoration-none">Workflow</a></li>
                        <li class="breadcrumb-item small active text-indigo fw-medium" aria-current="page">
                            <?php echo $followup ? 'Edit Directive' : 'New Schedule'; ?></li>
                    </ol>
                </nav>
            </div>
            <div class="col-auto">
                <a href="<?php echo APP_URL; ?>/followups" class="btn btn-white btn-sm border">
                    <i class="bi bi-arrow-left me-1"></i> Back to Fleet
                </a>
            </div>
        </div>

        <form method="POST">
            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">

            <div class="row g-4">
                <div class="col-lg-8">
                    <!-- Core Directive Content -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3 border-light">
                            <h6 class="mb-0 fw-bold uppercase-xs ls-wide text-muted">Directive Details</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-0">
                                <label for="notes"
                                    class="form-label small fw-bold text-muted uppercase-xs ls-wide">Execution Brief
                                    <span class="text-danger">*</span></label>
                                <textarea
                                    class="form-control bg-light border-0 py-3 <?php echo isset($errors['notes']) ? 'is-invalid' : ''; ?>"
                                    id="notes" name="notes" rows="6"
                                    placeholder="Document the specific objectives or conversation highlights for this engagement..."
                                    required><?php echo Security::escape($followup['notes'] ?? ''); ?></textarea>
                                <?php if (isset($errors['notes'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['notes']; ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Entity Linkage -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3 border-light">
                            <h6 class="mb-0 fw-bold uppercase-xs ls-wide text-muted">Entity Alignment</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="client_id"
                                        class="form-label small fw-bold text-muted uppercase-xs ls-wide">Target
                                        Client</label>
                                    <select class="form-select bg-light border-0 py-2" id="client_id" name="client_id">
                                        <option value="">Independent Directive</option>
                                        <?php foreach ($clients as $client): ?>
                                            <option value="<?php echo $client['id']; ?>" <?php echo (isset($followup['client_id']) && $followup['client_id'] == $client['id']) ? 'selected' : ''; ?>>
                                                <?php echo Security::escape($client['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="deal_id"
                                        class="form-label small fw-bold text-muted uppercase-xs ls-wide">Associated
                                        Deal</label>
                                    <select class="form-select bg-light border-0 py-2" id="deal_id" name="deal_id">
                                        <option value="">General Relationship</option>
                                        <?php foreach ($deals as $deal): ?>
                                            <option value="<?php echo $deal['id']; ?>" <?php echo (isset($followup['deal_id']) && $followup['deal_id'] == $deal['id']) ? 'selected' : ''; ?>>
                                                <?php echo Security::escape($deal['title']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-3 p-3 bg-light rounded-3 d-flex align-items-center">
                                <i class="bi bi-info-circle text-primary me-3"></i>
                                <div class="text-muted small">Linking this directive to a client and deal ensures
                                    historical visibility in their profile timeline.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Scheduling & Status -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3 border-light">
                            <h6 class="mb-0 fw-bold uppercase-xs ls-wide text-muted">Logistics</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-4">
                                <label for="followup_date"
                                    class="form-label small fw-bold text-muted uppercase-xs ls-wide">Scheduled Execution
                                    <span class="text-danger">*</span></label>
                                <input type="date"
                                    class="form-control bg-light border-0 py-2 <?php echo isset($errors['followup_date']) ? 'is-invalid' : ''; ?>"
                                    id="followup_date" name="followup_date"
                                    value="<?php echo $followup['followup_date'] ?? date('Y-m-d'); ?>" required>
                                <?php if (isset($errors['followup_date'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['followup_date']; ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-0">
                                <label for="status"
                                    class="form-label small fw-bold text-muted uppercase-xs ls-wide">Fulfillment
                                    State</label>
                                <select class="form-select bg-light border-0 py-2" id="status" name="status">
                                    <option value="pending" <?php echo (isset($followup['status']) && $followup['status'] === 'pending') ? 'selected' : ''; ?>>Active / Pending</option>
                                    <option value="done" <?php echo (isset($followup['status']) && $followup['status'] === 'done') ? 'selected' : ''; ?>>Concluded / Done</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div
                        class="card border-0 shadow-sm bg-primary bg-opacity-10 border border-primary border-opacity-10">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3 small text-uppercase ls-wide text-primary">Finalize Registry</h6>
                            <p class="text-primary small opacity-75 mb-4">Confirming this record will synchronize it
                                across the CRM pipeline and notify associated units.</p>
                            <button type="submit" class="btn btn-primary w-100 mb-2 py-2 fw-bold">
                                <?php echo $followup ? 'Sync Directive' : 'Commit Schedule'; ?>
                            </button>
                            <a href="<?php echo APP_URL; ?>/followups"
                                class="btn btn-white border w-100 py-2 small fw-medium">Abort Operation</a>
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
        const dealSelect = document.getElementById('deal_id');

        if (clientSelect && dealSelect) {
            // Initially disable deal select if no client is selected and it's a new follow-up
            if (!clientSelect.value && !dealSelect.value) {
                dealSelect.disabled = true;
            }

            clientSelect.addEventListener('change', function () {
                const clientId = this.value;

                if (!clientId) {
                    dealSelect.innerHTML = '<option value="">General Relationship</option>';
                    dealSelect.disabled = true;
                    return;
                }

                // Fetch deals for the selected client
                fetch(`<?php echo APP_URL; ?>/deals/getByClient/${clientId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            dealSelect.disabled = false;
                            let options = '<option value="">General Relationship</option>';

                            if (data.deals.length === 0) {
                                options = '<option value="">No active deals found</option>';
                                dealSelect.disabled = true;
                            } else {
                                data.deals.forEach(deal => {
                                    options += `<option value="${deal.id}">${deal.title}</option>`;
                                });
                            }

                            dealSelect.innerHTML = options;

                            // Auto-select if there is only one deal
                            if (data.deals.length === 1) {
                                dealSelect.value = data.deals[0].id;
                            }
                        }
                    })
                    .catch(error => console.error('Error fetching deals:', error));
            });
        }
    });
</script>

<style>
    .ls-wide {
        letter-spacing: 0.1em;
    }

    .uppercase-xs {
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .btn-white:hover {
        background-color: #f8fafc;
    }
</style>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const clientSelect = document.getElementById('client_id');
        const dealSelect = document.getElementById('deal_id');

        if (clientSelect && dealSelect) {
            // Initially disable deal select if no client is selected
            if (!clientSelect.value) {
                dealSelect.disabled = true;
            }

            clientSelect.addEventListener('change', function () {
                const clientId = this.value;

                if (!clientId) {
                    dealSelect.innerHTML = '<option value="">Select Deal (Optional)</option>';
                    dealSelect.disabled = true;
                    return;
                }

                // Fetch deals for the selected client
                fetch(`<?php echo APP_URL; ?>/deals/getByClient/${clientId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            dealSelect.disabled = false;
                            let options = '<option value="">Select Deal (Optional)</option>';

                            if (data.deals.length === 0) {
                                options = '<option value="">No deals found</option>';
                                dealSelect.disabled = true;
                            } else {
                                data.deals.forEach(deal => {
                                    options += `<option value="${deal.id}">${deal.title}</option>`;
                                });
                            }

                            dealSelect.innerHTML = options;

                            // Auto-select if there is only one deal
                            if (data.deals.length === 1) {
                                dealSelect.value = data.deals[0].id;
                            }
                        }
                    })
                    .catch(error => console.error('Error fetching deals:', error));
            });
        }
    });
</script>