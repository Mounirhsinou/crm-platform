<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row mb-4 align-items-center">
    <div class="col">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/checkout" class="text-decoration-none">Checkout Manager</a></li>
                <li class="breadcrumb-item active">Configure Page</li>
            </ol>
        </nav>
        <h1 class="h3 fw-black text-dark mb-0">Edit Checkout Link</h1>
    </div>
    <div class="col-auto">
        <a href="<?php echo APP_URL; ?>/portal/payment/<?php echo $template['token']; ?>" target="_blank" class="btn btn-light rounded-12 shadow-sm border">
            <i class="bi bi-eye me-2"></i> Preview Page
        </a>
    </div>
</div>

<?php echo $this->flash(); ?>

<form action="<?php echo APP_URL; ?>/checkout/update/<?php echo $template['id']; ?>" method="POST">
    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">
    
    <div class="row g-4">
        <!-- Left: Basic Info & Branding -->
        <div class="col-md-7">
            <div class="card border-0 shadow-sm rounded-24 p-5 mb-4">
                <h5 class="fw-black mb-4 d-flex align-items-center">
                    <span class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; font-size: 1rem;">1</span>
                    General Configuration
                </h5>
                
                <div class="mb-4">
                    <label class="form-label fw-bold x-small text-uppercase text-muted tracking-wider">Checkout Title</label>
                    <input type="text" name="title" class="form-control form-control-lg border-subtle bg-light-subtle rounded-12 fw-bold" 
                        value="<?php echo Security::escape($template['title']); ?>" required>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold x-small text-uppercase text-muted tracking-wider">Public Description</label>
                    <textarea name="description" class="form-control border-subtle bg-light-subtle rounded-12" style="height: 120px;"
                        placeholder="Explain what the customer is paying for..."><?php echo Security::escape($template['description']); ?></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold x-small text-uppercase text-muted tracking-wider">Payment Amount (<?php echo Branding::getCurrencyCode(); ?>)</label>
                    <div class="input-group input-group-lg shadow-sm rounded-12 overflow-hidden border">
                        <span class="input-group-text bg-white border-0 text-muted px-4"><?php echo Branding::getCurrencySymbol(); ?></span>
                        <input type="number" name="amount" class="form-control border-0 fw-black text-dark" step="0.01" min="0.01" 
                            value="<?php echo number_format($template['amount'], 2, '.', ''); ?>" required>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm rounded-24 p-5">
                <h5 class="fw-black mb-4 d-flex align-items-center">
                    <span class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; font-size: 1rem;">2</span>
                    Integrated Payment Flow
                </h5>
                <p class="text-muted small mb-4">Select which payment methods will be active on this specific checkout page.</p>
                
                <div class="row g-3">
                    <div class="col-6">
                        <label class="gateway-card p-4 rounded-24 border bg-white d-flex align-items-center w-100 cursor-pointer transition-all <?php echo $template['allow_stripe'] ? 'border-primary' : ''; ?>" for="stripe_toggle">
                            <div class="form-check form-switch flex-grow-1">
                                <label class="form-check-label h6 mb-0 fw-bold d-block" for="stripe_toggle">
                                    <img src="<?php echo APP_URL; ?>/assets/img/stripe-logo.svg" height="20" class="mb-2 d-block opacity-75" onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/b/ba/Stripe_Logo%2C_revised_2016.svg'">
                                    Active
                                </label>
                                <input class="form-check-input mt-2" type="checkbox" name="allow_stripe" id="stripe_toggle" <?php echo $template['allow_stripe'] ? 'checked' : ''; ?>>
                            </div>
                        </label>
                    </div>
                    <div class="col-6">
                        <label class="gateway-card p-4 rounded-24 border bg-white d-flex align-items-center w-100 cursor-pointer transition-all <?php echo $template['allow_paypal'] ? 'border-primary' : ''; ?>" for="paypal_toggle">
                            <div class="form-check form-switch flex-grow-1">
                                <label class="form-check-label h6 mb-0 fw-bold d-block" for="paypal_toggle">
                                    <img src="<?php echo APP_URL; ?>/assets/img/paypal-logo.svg" height="20" class="mb-2 d-block opacity-75" onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg'">
                                    Active
                                </label>
                                <input class="form-check-input mt-2" type="checkbox" name="allow_paypal" id="paypal_toggle" <?php echo $template['allow_paypal'] ? 'checked' : ''; ?>>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right: Fields Configuration -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm rounded-24 p-5 h-100 d-flex flex-column">
                <h5 class="fw-black mb-4 d-flex align-items-center">
                    <span class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; font-size: 1rem;">3</span>
                    Checkout Fields
                </h5>
                <p class="text-muted small mb-4">Enable or disable fields for this checkout page. Active fields will be automatically saved as <strong>Collected Leads</strong>.</p>
                
                <div class="list-group list-group-flush mb-4 checkout-fields-editor">
                    <?php
                    $fields = [
                        'name' => 'Full Name',
                        'email' => 'Email Address',
                        'phone' => 'Phone Number',
                        'address' => 'Physical Address',
                        'city' => 'City / Location',
                        'country' => 'Country',
                        'company' => 'Company Details',
                        'notes' => 'Customer Message'
                    ];
                    $settings = json_decode($template['checkout_settings'] ?? '[]', true);
                    
                    foreach ($fields as $key => $label): 
                        $visible = $settings[$key]['visible'] ?? false;
                        $required = $settings[$key]['required'] ?? false;
                        
                        // Force name/email
                        if (in_array($key, ['name', 'email'])) {
                            $visible = true;
                            $required = true;
                        }
                    ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 border-light">
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    <i class="bi bi-pencil-fill x-small text-muted"></i>
                                </div>
                                <span class="fw-bold text-dark"><?php echo $label; ?></span>
                            </div>
                            <div class="d-flex gap-3 align-items-center">
                                <div class="form-check form-switch mb-0" title="Make visible">
                                    <input class="form-check-input" type="checkbox" name="fields[<?php echo $key; ?>][visible]" 
                                        <?php echo $visible ? 'checked' : ''; ?> <?php echo in_array($key, ['name', 'email']) ? 'disabled' : ''; ?>>
                                    <?php if (in_array($key, ['name', 'email'])): ?>
                                        <input type="hidden" name="fields[<?php echo $key; ?>][visible]" value="1">
                                    <?php endif; ?>
                                </div>
                                <div class="form-check mb-0" title="Mark as Required">
                                    <input class="form-check-input" type="checkbox" name="fields[<?php echo $key; ?>][required]" 
                                        <?php echo $required ? 'checked' : ''; ?> <?php echo in_array($key, ['name', 'email']) ? 'disabled' : ''; ?>>
                                    <?php if (in_array($key, ['name', 'email'])): ?>
                                        <input type="hidden" name="fields[<?php echo $key; ?>][required]" value="1">
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="bg-primary-subtle p-3 rounded-16 mt-auto">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-info-circle-fill text-primary me-2 mt-1"></i>
                        <p class="mb-0 x-small text-primary-emphasis fw-medium">Changes here only affect this specific checkout page. All collected data is synced to your main leads database.</p>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary btn-lg w-100 rounded-16 py-3 fw-black shadow-pro">
                        Save Configuration <i class="bi bi-check2-circle ms-2"></i>
                    </button>
                    <a href="<?php echo APP_URL; ?>/checkout" class="btn btn-link w-100 text-muted x-small mt-2 text-decoration-none">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</form>

<style>
    .fw-black { font-weight: 900 !important; }
    .x-small { font-size: 0.75rem; }
    .tracking-wider { letter-spacing: 0.1em; }
    .rounded-24 { border-radius: 24px !important; }
    .rounded-16 { border-radius: 16px !important; }
    .shadow-pro { box-shadow: 0 10px 30px rgba(59, 130, 246, 0.2) !important; }
    
    .gateway-card {
        border-width: 2px !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .gateway-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    
    .gateway-card .form-check-input {
        width: 3.5em;
        height: 1.75em;
    }
    
    .checkout-fields-editor .form-check-input {
        width: 3em;
        height: 1.5em;
    }

    .checkout-fields-editor .form-check-input[type="checkbox"]:not(.form-switch) {
        width: 1.5rem;
        height: 1.5rem;
        border-radius: 6px;
    }
</style>

<script>
    document.querySelectorAll('.gateway-card').forEach(card => {
        const checkbox = card.querySelector('input[type="checkbox"]');
        card.addEventListener('click', (e) => {
            if (e.target !== checkbox) {
                checkbox.checked = !checkbox.checked;
                checkbox.dispatchEvent(new Event('change'));
            }
        });
        
        checkbox.addEventListener('change', () => {
            if (checkbox.checked) {
                card.classList.add('border-primary');
            } else {
                card.classList.remove('border-primary');
            }
        });
    });
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
