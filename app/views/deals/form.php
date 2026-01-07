<?php $pageTitle = ($deal ? 'Modify' : 'Architect') . ' Deal'; ?>
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row mb-4 align-items-center">
    <div class="col">
        <h1 class="h4 mb-1"><?php echo $deal ? 'Execute Deal Update' : 'New Commercial Deal'; ?></h1>
        <p class="text-muted small mb-0"><?php echo $deal ? 'Adjusting pipeline parameters for an active negotiation' : 'Initiate a new revenue-generating opportunity in the pipeline'; ?></p>
    </div>
    <div class="col-auto">
        <a href="<?php echo APP_URL; ?>/deals" class="btn btn-light btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Pipeline View
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">
        <form method="POST">
            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">
            
            <div class="row g-4">
                <!-- Deal Context -->
                <div class="col-md-7">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-4 d-flex align-items-center">
                                <i class="bi bi-briefcase me-2 text-primary"></i>
                                Deal Parameters
                            </h6>
                            
                            <div class="mb-4">
                                <label for="title" class="form-label small fw-medium">Engagement Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?php echo isset($errors['title']) ? 'is-invalid' : ''; ?>" 
                                       id="title" name="title" value="<?php echo Security::escape($deal['title'] ?? ''); ?>" placeholder="e.g. Website Redesign Project" required>
                                <?php if (isset($errors['title'])): ?>
                                    <div class="invalid-feedback small"><?php echo $errors['title']; ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-0">
                                <label for="client_id" class="form-label small fw-medium">Assigned Account <span class="text-danger">*</span></label>
                                <select class="form-select border-light bg-light <?php echo isset($errors['client_id']) ? 'is-invalid' : ''; ?>" 
                                        id="client_id" name="client_id" required>
                                    <option value="">Search or Select Client...</option>
                                    <?php foreach ($clients as $client): ?>
                                        <option value="<?php echo $client['id']; ?>" 
                                                <?php echo (isset($deal['client_id']) && $deal['client_id'] == $client['id']) ? 'selected' : ''; ?>>
                                            <?php echo Security::escape($client['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['client_id'])): ?>
                                    <div class="invalid-feedback small"><?php echo $errors['client_id']; ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm bg-light">
                        <div class="card-body p-4 text-center">
                            <i class="bi bi-info-circle text-muted mb-2 d-block fs-4"></i>
                            <p class="small text-muted mb-0">Deals represent active business negotiations. Once a deal reaches a high enough probability, consider issuing an invoice via the <strong>Invoices</strong> module.</p>
                        </div>
                    </div>
                </div>

                <!-- Logistics & Action -->
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-4 d-flex align-items-center">
                                <i class="bi bi-graph-up-arrow me-2 text-primary"></i>
                                Financial Logic
                            </h6>
                            
                            <div class="mb-4">
                                <label for="amount" class="form-label small fw-medium">Proposed Valuation <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light text-muted border-end-0"><?php echo CURRENCY_SYMBOL; ?></span>
                                    <input type="number" step="0.01" class="form-control bg-light border-start-0 <?php echo isset($errors['amount']) ? 'is-invalid' : ''; ?>" 
                                           id="amount" name="amount" value="<?php echo $deal['amount'] ?? '0.00'; ?>" required>
                                </div>
                                <?php if (isset($errors['amount'])): ?>
                                    <div class="invalid-feedback small"><?php echo $errors['amount']; ?></div>
                                <?php endif; ?>
                                <div class="form-text x-small mt-1 opacity-75">Automatically populated from client defaults if available.</div>
                            </div>
                            
                            <div class="mb-0">
                                <label for="status" class="form-label small fw-medium">Pipeline Stage</label>
                                <div class="d-flex flex-column gap-2">
                                    <div class="form-check p-2 border rounded-3 bg-white d-flex align-items-center">
                                        <input class="form-check-input ms-0 me-2" type="radio" name="status" id="status_new" value="new" <?php echo (!isset($deal['status']) || $deal['status'] === 'new') ? 'checked' : ''; ?>>
                                        <label class="form-check-label small fw-medium text-main" for="status_new">Discovery (New)</label>
                                    </div>
                                    <div class="form-check p-2 border rounded-3 bg-white d-flex align-items-center">
                                        <input class="form-check-input ms-0 me-2" type="radio" name="status" id="status_progress" value="in_progress" <?php echo (isset($deal['status']) && $deal['status'] === 'in_progress') ? 'checked' : ''; ?>>
                                        <label class="form-check-label small fw-medium text-main" for="status_progress">Negotiation (In Progress)</label>
                                    </div>
                                    <div class="form-check p-2 border rounded-3 bg-white d-flex align-items-center">
                                        <input class="form-check-input ms-0 me-2" type="radio" name="status" id="status_done" value="completed" <?php echo (isset($deal['status']) && $deal['status'] === 'completed') ? 'checked' : ''; ?>>
                                        <label class="form-check-label small fw-medium text-main" for="status_done">Closed Won (Completed)</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm sticky-top" style="top: 100px;">
                        <div class="card-body p-4">
                            <h3 class="h6 fw-bold mb-3 small text-uppercase ls-wide text-muted">Verification</h3>
                            <p class="small text-muted mb-4">Ensure the client and amount correctly represent the contract value.</p>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-sm py-2">
                                    <i class="bi bi-check2-circle me-1"></i> <?php echo $deal ? 'Confirm Lifecycle Update' : 'Initialize Deal Flow'; ?>
                                </button>
                                <a href="<?php echo APP_URL; ?>/deals" class="btn btn-light text-muted btn-sm border py-2">
                                    Ignore Changes
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
document.addEventListener('DOMContentLoaded', function() {
    const clientSelect = document.getElementById('client_id');
    const amountInput = document.getElementById('amount');

    if (clientSelect && amountInput) {
        clientSelect.addEventListener('change', function() {
            const clientId = this.value;
            if (!clientId) return;

            fetch(`<?php echo APP_URL; ?>/clients/getAmountInfo/${clientId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        amountInput.value = data.amount;
                        
                        // Premium highlight effect
                        amountInput.style.backgroundColor = '#f0fdf4';
                        amountInput.style.transition = 'background-color 0.5s ease';
                        setTimeout(() => amountInput.style.backgroundColor = '', 2000);
                    }
                })
                .catch(error => console.error('Error fetching client info:', error));
        });
    }
});
</script>

<style>
    .x-small { font-size: 0.75rem; }
    .ls-wide { letter-spacing: 0.1em; }
    .btn-indigo:hover { opacity: 0.9; }
</style>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
