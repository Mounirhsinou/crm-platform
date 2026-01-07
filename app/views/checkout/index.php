<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row mb-4 align-items-center">
    <div class="col">
        <h1 class="h3 fw-black text-dark mb-1">Checkout Manager</h1>
        <p class="text-muted small mb-0">High-converting payment pages with integrated lead capture</p>
    </div>
    <div class="col-auto">
        <button type="button" class="btn btn-primary btn-lg rounded-12 px-4 shadow-pro" data-bs-toggle="modal"
            data-bs-target="#createCheckoutModal">
            <i class="bi bi-magic me-2"></i> Create Checkout Link
        </button>
    </div>
</div>

<?php echo $this->flash(); ?>

<!-- Stats Overview -->
<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-24 p-3 bg-white h-100 border-start border-primary border-4">
            <div class="d-flex align-items-center">
                <div class="icon-box bg-soft-primary text-primary rounded-16 me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <i class="bi bi-wallet2 fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-bold text-uppercase tracking-wider">Total Balance</div>
                    <div class="h3 mb-0 fw-black text-dark"><?php echo Branding::getCurrencySymbol() . number_format($stats['revenue'], 2); ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-24 p-3 bg-white h-100">
            <div class="d-flex align-items-center">
                <div class="icon-box bg-soft-info text-info rounded-16 me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <i class="bi bi-people-fill fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-bold text-uppercase tracking-wider">Period Leads</div>
                    <div class="h3 mb-0 fw-black text-dark"><?php echo number_format($stats['leads']); ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-24 p-3 bg-white h-100">
            <div class="d-flex align-items-center">
                <div class="icon-box bg-soft-success text-success rounded-16 me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <i class="bi bi-credit-card-check-fill fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-bold text-uppercase tracking-wider">Conversions</div>
                    <div class="h3 mb-0 fw-black text-dark"><?php echo number_format($stats['payments']); ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-24 p-3 bg-white h-100">
            <div class="d-flex align-items-center">
                <div class="icon-box bg-soft-warning text-warning rounded-16 me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <i class="bi bi-graph-up-arrow fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-bold text-uppercase tracking-wider">Conv. Rate</div>
                    <div class="h3 mb-0 fw-black text-dark">
                        <?php echo $stats['leads'] > 0 ? round(($stats['payments'] / $stats['leads']) * 100, 1) : 0; ?>%
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Links Table -->
<div class="card border-0 shadow-premium rounded-24 mb-5">
    <div class="card-header bg-white border-0 py-4 px-4 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0">Active Checkout Links</h5>
        <div class="btn-group shadow-sm rounded-12 overflow-hidden">
            <a href="<?php echo APP_URL; ?>/checkout?view=all" 
               class="btn btn-white btn-sm px-3 border-end <?php echo ($viewFilter ?? 'all') === 'all' ? 'active bg-primary text-white' : ''; ?>">
                All Links
            </a>
            <a href="<?php echo APP_URL; ?>/checkout?view=recent" 
               class="btn btn-white btn-sm px-3 <?php echo ($viewFilter ?? 'all') === 'recent' ? 'active bg-primary text-white' : ''; ?>">
                Recent
            </a>
        </div>
    </div>
    <div class="table-responsive" style="overflow: visible;">
        <table class="table table-hover align-middle mb-0 custom-checkout-table">
            <thead class="bg-light-subtle">
                <tr>
                    <th class="ps-4 py-3">Checkout Name</th>
                    <th>Fixed Amount</th>
                    <th>Leads</th>
                    <th>Payments</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Control</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($links)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <img src="<?php echo APP_URL; ?>/assets/img/empty-checkout.svg" class="mb-3 opacity-25" style="width: 120px;">
                            <p class="text-muted mb-0">No checkout links found. Start by creating one!</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($links as $l): 
                        if ($l['link_type'] !== 'Client-filled') continue; // Only show specialized templates here
                        $publicUrl = APP_URL . '/portal/payment/' . $l['token'];
                        $convRate = $l['leads_count'] > 0 ? round(($l['payments_count'] / $l['leads_count']) * 100, 1) : 0;
                    ?>
                        <tr class="transition-all">
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary-subtle text-primary rounded-16 d-flex align-items-center justify-content-center me-3" style="width: 44px; height: 44px;">
                                        <i class="bi bi-lightning-charge-fill"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark mb-0"><?php echo Security::escape($l['title']); ?></div>
                                        <div class="x-small text-muted d-flex align-items-center">
                                            <i class="bi bi-clock me-1"></i> <?php echo date('M d, Y', strtotime($l['created_at'])); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-black text-dark h6 mb-0"><?php echo Branding::getCurrencySymbol() . number_format($l['amount'], 2); ?></div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-bold" style="font-size: 0.8rem;">
                                    <i class="bi bi-person-plus me-1 text-primary"></i> <?php echo $l['leads_count']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-bold" style="font-size: 0.8rem;">
                                    <i class="bi bi-cash-stack me-1"></i> <?php echo $l['payments_count']; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($l['payment_closed']): ?>
                                    <span class="badge bg-danger rounded-pill px-3 py-1">Closed</span>
                                <?php else: ?>
                                    <span class="badge bg-success rounded-pill px-3 py-1">Active</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm rounded-12 px-3 border-0" 
                                            type="button"
                                            id="dropdownAction<?php echo $l['original_id']; ?>" 
                                            data-bs-toggle="dropdown" 
                                            aria-expanded="false"
                                            aria-haspopup="true">
                                        Action <i class="bi bi-chevron-down ms-1 small"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-pro border-0 rounded-16 py-2" 
                                        aria-labelledby="dropdownAction<?php echo $l['original_id']; ?>">
                                        <li><a class="dropdown-item py-2 fw-medium" href="<?php echo $publicUrl; ?>" target="_blank"><i class="bi bi-eye-fill me-2 text-primary"></i> View Checkout</a></li>
                                        <li><button class="dropdown-item py-2 fw-medium" onclick="copyToClipboard('<?php echo $publicUrl; ?>')"><i class="bi bi-link-45deg me-2 text-info"></i> Copy Secure Link</button></li>
                                        <li><hr class="dropdown-divider opacity-50"></li>
                                        <li><a class="dropdown-item py-2 fw-medium" href="<?php echo APP_URL; ?>/checkout/edit/<?php echo $l['original_id']; ?>"><i class="bi bi-sliders me-2 text-muted"></i> Configure Page</a></li>
                                        <li><a class="dropdown-item py-2 fw-medium" href="<?php echo APP_URL; ?>/checkout/toggle/<?php echo $l['original_id']; ?>"><i class="bi <?php echo $l['payment_closed'] ? 'bi-play-circle text-success' : 'bi-pause-circle text-warning'; ?> me-2"></i> <?php echo $l['payment_closed'] ? 'Activate' : 'Pause'; ?></a></li>
                                        <li><hr class="dropdown-divider opacity-50"></li>
                                        <li><button class="dropdown-item py-2 fw-medium text-danger" onclick="deleteLink(<?php echo $l['original_id']; ?>)"><i class="bi bi-trash3 me-2"></i> Delete Forever</button></li>
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

<!-- Create Modal -->
<div class="modal fade" id="createCheckoutModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-pro rounded-24 overflow-hidden">
            <div class="modal-header bg-primary text-white py-4 px-5 border-0">
                <div>
                    <h4 class="modal-title fw-black mb-1">New Integrated Checkout</h4>
                    <p class="mb-0 text-white opacity-75 small text-uppercase fw-bold tracking-wider">Configure your landing page</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?php echo APP_URL; ?>/checkout/create" method="POST">
                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">
                <div class="modal-body p-4 p-md-5">
                    <div class="row g-4">
                        <div class="col-md-7">
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark x-small text-uppercase mb-2">Display Title</label>
                                <input type="text" name="title" class="form-control form-control-lg border-subtle bg-light-subtle rounded-12" placeholder="e.g., Annual Subscription Deposit" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark x-small text-uppercase mb-2">Public Description</label>
                                <textarea name="description" class="form-control border-subtle bg-light-subtle rounded-12" style="height: 100px;" placeholder="What are they paying for?"></textarea>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark x-small text-uppercase mb-2">Fixed Checkout Amount</label>
                                <div class="input-group input-group-lg shadow-sm rounded-12 overflow-hidden border">
                                    <span class="input-group-text bg-white border-0 text-muted px-4"><?php echo Branding::getCurrencySymbol(); ?></span>
                                    <input type="number" name="amount" class="form-control border-0 fw-black text-dark" step="0.01" min="1" placeholder="0.00" required>
                                </div>
                            </div>
                            
                            <div class="bg-light p-4 rounded-24 border border-dashed text-center">
                                <div class="text-dark fw-bold mb-3 small text-uppercase tracking-wider">Payment Integrated Flow</div>
                                <div class="d-flex justify-content-center gap-4">
                                    <div class="form-check form-switch gateway-switch d-flex flex-column align-items-center">
                                        <img src="<?php echo APP_URL; ?>/assets/img/stripe-logo.svg" height="20" class="mb-2 opacity-75" onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/b/ba/Stripe_Logo%2C_revised_2016.svg'">
                                        <input class="form-check-input ms-0" type="checkbox" name="allow_stripe" checked id="stripe_switch">
                                    </div>
                                    <div class="form-check form-switch gateway-switch d-flex flex-column align-items-center">
                                        <img src="<?php echo APP_URL; ?>/assets/img/paypal-logo.svg" height="20" class="mb-2 opacity-75" onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg'">
                                        <input class="form-check-input ms-0" type="checkbox" name="allow_paypal" checked id="paypal_switch">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="card border-0 bg-light p-4 rounded-24 h-100">
                                <h6 class="fw-bold text-dark x-small text-uppercase mb-4 tracking-wider d-flex align-items-center">
                                    <i class="bi bi-ui-checks-grid me-2 text-primary"></i> Data Collection (Leads)
                                </h6>
                                <p class="x-small text-muted mb-4 px-1">Selected fields will be automatically captured as <strong>Collected Leads</strong>.</p>
                                
                                <div class="d-flex flex-column gap-3 checkout-fields-config">
                                    <?php
                                    $checkoutFields = [
                                        'name' => 'Full Name',
                                        'email' => 'Email Address',
                                        'phone' => 'Phone Number',
                                        'address' => 'Full Address',
                                        'company' => 'Company Details',
                                        'notes' => 'Customer Message'
                                    ];
                                    foreach ($checkoutFields as $key => $label): ?>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="small fw-semibold text-dark"><?php echo $label; ?></span>
                                            <div class="d-flex gap-2">
                                                <div class="form-check form-switch toggle-visible" title="Show field">
                                                    <input class="form-check-input" type="checkbox" name="fields[<?php echo $key; ?>][visible]" 
                                                        <?php echo in_array($key, ['name', 'email']) ? 'checked disabled' : 'checked'; ?>>
                                                    <?php if (in_array($key, ['name', 'email'])): ?>
                                                        <input type="hidden" name="fields[<?php echo $key; ?>][visible]" value="1">
                                                    <?php endif; ?>
                                                </div>
                                                <div class="form-check toggle-required" title="Mark as Required">
                                                    <input class="form-check-input" type="checkbox" name="fields[<?php echo $key; ?>][required]" 
                                                        <?php echo in_array($key, ['name', 'email']) ? 'checked' : ''; ?>>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <div class="mt-auto pt-4 text-center">
                                    <span class="x-small text-primary fw-bold"><i class="bi bi-shield-check me-1 fs-6"></i> GDPR & CCPA Compliant Lead Capture</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-5 bg-light-subtle">
                    <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none me-auto" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-lg rounded-16 px-5 py-3 shadow-pro fw-black">
                        Deploy Checkout Page <i class="bi bi-arrow-right-short ms-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .fw-black { font-weight: 900 !important; }
    .tracking-wider { letter-spacing: 0.1em; }
    .bg-soft-primary { background-color: rgba(59, 130, 246, 0.1); }
    .bg-soft-success { background-color: rgba(16, 185, 129, 0.1); }
    .bg-soft-info { background-color: rgba(6, 182, 212, 0.1); }
    .rounded-16 { border-radius: 16px !important; }
    .rounded-24 { border-radius: 24px !important; }
    .shadow-pro { box-shadow: 0 10px 30px rgba(59, 130, 246, 0.15) !important; }
    
    .custom-checkout-table thead th {
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        border-bottom: 2px solid #f1f5f9;
        padding-top: 20px;
        padding-bottom: 20px;
    }
    
    .custom-checkout-table tr {
        border-bottom: 1px solid #f8fafc;
    }
    
    .custom-checkout-table tr:last-child {
        border-bottom: none;
    }
    
    .custom-checkout-table tr:hover {
        background-color: #f8fafc !important;
    }

    .gateway-switch .form-check-input {
        width: 3.5em;
        height: 1.75em;
        cursor: pointer;
    }

    .checkout-fields-config .form-check-input {
        cursor: pointer;
    }

    .badge-soft-success { background-color: #d1fae5; color: #065f46; }
    .shadow-premium { box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06); }
    
    /* Fix dropdown menu visibility */
    .custom-checkout-table td {
        position: relative;
    }
    
    .dropdown-menu {
        background-color: #ffffff !important;
        z-index: 9999 !important;
        min-width: 200px;
        border: 1px solid #e5e7eb !important;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
        margin-top: 0.25rem !important;
    }
    
    .dropdown-item {
        color: #1f2937 !important;
        padding: 0.5rem 1rem !important;
        transition: all 0.2s ease;
        display: block !important;
    }
    
    .dropdown-item:hover {
        background-color: #f3f4f6 !important;
        color: #111827 !important;
    }
    
    .dropdown-item i {
        opacity: 0.7;
    }
    
    .dropdown-item:hover i {
        opacity: 1;
    }
    
    .dropdown.show .dropdown-menu {
        display: block !important;
    }
</style>

<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Checkout Link copied to clipboard!');
        });
    }

    function deleteLink(id) {
        if(confirm('Are you sure you want to delete this checkout link? All associated conversion stats will be lost.')) {
            window.location.href = '<?php echo APP_URL; ?>/checkout/delete/' + id;
        }
    }
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
