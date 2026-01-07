<?php $pageTitle = ($client ? 'Modify' : 'Onboard') . ' Client'; ?>
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row mb-4 align-items-center">
    <div class="col">
        <h1 class="h4 mb-1"><?php echo $client ? 'Update Client' : 'New Client Registration'; ?></h1>
        <p class="text-muted small mb-0">
            <?php echo $client ? 'Refine existing client details and preferences' : 'Add a new business or individual to your CRM ecosystem'; ?>
        </p>
    </div>
    <div class="col-auto">
        <a href="<?php echo APP_URL; ?>/clients" class="btn btn-light btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Fleet
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">
        <form method="POST">
            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">

            <div class="row g-4">
                <!-- Primary Information -->
                <div class="col-md-7">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-4 d-flex align-items-center">
                                <i class="bi bi-person-badge me-2 text-primary"></i>
                                Identify Details
                            </h6>

                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="name" class="form-label small fw-medium">Primary Contact / Full Name
                                        <span class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>"
                                        id="name" name="name"
                                        value="<?php echo Security::escape($client['name'] ?? ''); ?>"
                                        placeholder="e.g. John Doe" required>
                                    <?php if (isset($errors['name'])): ?>
                                        <div class="invalid-feedback small"><?php echo $errors['name']; ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-12">
                                    <label for="company" class="form-label small fw-medium">Legal Entity /
                                        Company</label>
                                    <input type="text" class="form-control" id="company" name="company"
                                        value="<?php echo Security::escape($client['company'] ?? ''); ?>"
                                        placeholder="Organization name (optional)">
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label small fw-medium">Primary Email</label>
                                    <input type="email"
                                        class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>"
                                        id="email" name="email"
                                        value="<?php echo Security::escape($client['email'] ?? ''); ?>"
                                        placeholder="john@example.com">
                                    <?php if (isset($errors['email'])): ?>
                                        <div class="invalid-feedback small"><?php echo $errors['email']; ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-6">
                                    <label for="phone" class="form-label small fw-medium">Direct Phone</label>
                                    <input type="text"
                                        class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>"
                                        id="phone" name="phone"
                                        value="<?php echo Security::escape($client['phone'] ?? ''); ?>"
                                        placeholder="+1...">
                                    <?php if (isset($errors['phone'])): ?>
                                        <div class="invalid-feedback small"><?php echo $errors['phone']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-4 d-flex align-items-center">
                                <i class="bi bi-journals me-2 text-primary"></i>
                                Internal Context
                            </h6>
                            <div class="mb-0">
                                <label for="notes" class="form-label small fw-medium">Private Observations /
                                    Notes</label>
                                <textarea class="form-control bg-light border-light" id="notes" name="notes" rows="5"
                                    placeholder="Historical context, specific preferences, or background info..."><?php echo Security::escape($client['notes'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Secondary / Financial Info -->
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-4 d-flex align-items-center">
                                <i class="bi bi-currency-dollar me-2 text-primary"></i>
                                Commercial Profile
                            </h6>

                            <div class="mb-4">
                                <label for="default_price" class="form-label small fw-medium">Standard Service
                                    Rate</label>
                                <div class="input-group input-group-sm">
                                    <span
                                        class="input-group-text bg-light text-muted border-end-0"><?php echo CURRENCY_SYMBOL; ?></span>
                                    <input type="number" step="0.01"
                                        class="form-control bg-light border-start-0 <?php echo isset($errors['default_price']) ? 'is-invalid' : ''; ?>"
                                        id="default_price" name="default_price"
                                        value="<?php echo Security::escape($client['default_price'] ?? '0.00'); ?>">
                                </div>
                                <div class="form-text x-small mt-1 opacity-75">Base price for future automated invoices.
                                </div>
                            </div>

                            <?php if (!$client): ?>
                                <div class="p-3 bg-primary bg-opacity-10 rounded-3 border-dashed border-primary border">
                                    <label for="initial_price" class="form-label small fw-bold text-primary mb-2">
                                        <i class="bi bi-rocket-takeoff me-1"></i> Quick Start Deal
                                    </label>
                                    <div class="input-group input-group-sm mb-2">
                                        <span
                                            class="input-group-text bg-white border-end-0 text-muted"><?php echo CURRENCY_SYMBOL; ?></span>
                                        <input type="number" step="0.01" class="form-control border-start-0"
                                            id="initial_price" name="initial_price" placeholder="0.00">
                                    </div>
                                    <div class="x-small text-muted">
                                        Entering an amount here will instantly spawn a <strong>Deal</strong> and an
                                        <strong>Invoice</strong> for this new client.
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Summary Card (Sticky) -->
                    <div class="card border-0 shadow-sm bg-light sticky-top" style="top: 100px;">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3 small text-uppercase ls-wide text-muted">Execution</h6>
                            <p class="small text-muted mb-4">Verify all critical fields marked with an asterisk (*)
                                before committing changes.</p>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-sm py-2">
                                    <i class="bi bi-check-lg me-1"></i>
                                    <?php echo $client ? 'Sync Records' : 'Register Core'; ?>
                                </button>
                                <a href="<?php echo APP_URL; ?>/clients" class="btn btn-white btn-sm border py-2">
                                    Discard Changes
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    .x-small {
        font-size: 0.75rem;
    }

    .ls-wide {
        letter-spacing: 0.1em;
    }

    .btn-white:hover {
        background-color: #f8fafc;
    }

    .uppercase-xs {
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
</style>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>