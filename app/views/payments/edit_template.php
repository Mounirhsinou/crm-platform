<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-premium overflow-hidden rounded-24">
            <div class="card-header bg-white border-subtle py-4 px-4 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0 text-main">Edit Payment Link</h5>
                    <p class="text-muted x-small mb-0">Update your public landing page settings</p>
                </div>
                <a href="<?php echo APP_URL; ?>/payments" class="btn btn-light btn-sm px-3 shadow-none border">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>
            
            <form action="<?php echo APP_URL; ?>/payments/update/<?php echo $template['id']; ?>" method="POST">
                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">
                
                <div class="card-body p-4 p-md-5">
                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label fw-bold x-small text-uppercase text-muted tracking-wider">General Information</label>
                            <div class="form-floating mb-3">
                                <input type="text" name="title" class="form-control border-subtle fw-bold" id="titleInput"
                                    value="<?php echo Security::escape($template['title']); ?>" placeholder="Service Title" required>
                                <label for="titleInput">Service/Link Title</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-floating mb-4">
                                <textarea name="description" class="form-control border-subtle" id="descInput" style="height: 100px;" placeholder="Description"><?php echo Security::escape($template['description']); ?></textarea>
                                <label for="descInput">Description (Optional)</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold x-small text-uppercase text-muted tracking-wider">Fixed Amount</label>
                            <div class="input-group input-group-lg shadow-sm rounded-12 overflow-hidden border">
                                <span class="input-group-text bg-white border-0 text-muted"><?php echo Branding::getCurrencySymbol(); ?></span>
                                <input type="number" name="amount" class="form-control border-0 fw-black text-main p-3" 
                                    step="0.01" min="1" value="<?php echo number_format($template['amount'], 2, '.', ''); ?>" required>
                                <span class="input-group-text bg-white border-0 text-muted small fw-bold"><?php echo Branding::getCurrencyCode(); ?></span>
                            </div>
                        </div>

                        <div class="col-12 mt-5">
                            <h6 class="fw-bold x-small text-uppercase mb-4 text-muted tracking-wider">Checkout Form Configuration</h6>
                            <?php $settings = json_decode($template['checkout_settings'] ?? '[]', true); ?>
                            <div class="table-responsive border border-subtle rounded-16 bg-white shadow-sm">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr class="text-muted x-small text-uppercase fw-bold">
                                            <th class="ps-4 py-3">Customer Field</th>
                                            <th class="text-center py-3">Display Field</th>
                                            <th class="text-center pe-4 py-3">Mark Required</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $fields = [
                                            'name' => 'Full Name',
                                            'email' => 'Email Address',
                                            'phone' => 'Phone Number',
                                            'address' => 'Physical Address',
                                            'city' => 'City',
                                            'country' => 'Country',
                                            'company' => 'Company Name',
                                            'notes' => 'Customer Notes'
                                        ];
                                        foreach($fields as $key => $label): 
                                            $visible = $settings[$key]['visible'] ?? false;
                                            $required = $settings[$key]['required'] ?? false;
                                            $isFixed = in_array($key, ['name', 'email']);
                                        ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="field-dot me-2 <?php echo $visible ? 'bg-primary' : 'bg-light'; ?>"></div>
                                                    <span class="fw-semibold text-main"><?php echo $label; ?></span>
                                                    <?php if($isFixed): ?><span class="badge badge-soft-primary ms-2 x-small fw-normal">System Fixed</span><?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-inline-block">
                                                    <input class="form-check-input" type="checkbox" 
                                                        name="fields[<?php echo $key; ?>][visible]" value="1" 
                                                        <?php echo $isFixed || $visible ? 'checked' : ''; ?>
                                                        <?php echo $isFixed ? 'disabled' : ''; ?>>
                                                    <?php if($isFixed): ?>
                                                        <input type="hidden" name="fields[<?php echo $key; ?>][visible]" value="1">
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="text-center pe-4">
                                                <div class="form-check form-switch d-inline-block">
                                                    <input class="form-check-input" type="checkbox" 
                                                        name="fields[<?php echo $key; ?>][required]" value="1"
                                                        <?php echo $isFixed || $required ? 'checked' : ''; ?>>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="col-12 mt-5">
                            <h6 class="fw-bold x-small text-uppercase mb-4 text-muted tracking-wider">Enabled Payment Gateways</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="gateway-tile p-3 rounded-16 border d-flex align-items-center transition-all cursor-pointer <?php echo $template['allow_paypal'] ? 'active' : ''; ?>" data-target="allow_paypal">
                                        <div class="form-check form-switch me-3 mb-0">
                                            <input class="form-check-input" type="checkbox" name="allow_paypal"
                                                id="allow_paypal" <?php echo $template['allow_paypal'] ? 'checked' : ''; ?>>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold text-main">PayPal</div>
                                            <div class="text-muted x-small">Accept credit cards & PayPal</div>
                                        </div>
                                        <div class="gateway-icon-box text-primary fs-4 ms-2">
                                            <i class="bi bi-paypal"></i>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    <label class="gateway-tile p-3 rounded-16 border d-flex align-items-center transition-all cursor-pointer <?php echo $template['allow_stripe'] ? 'active' : ''; ?>" data-target="allow_stripe">
                                        <div class="form-check form-switch me-3 mb-0">
                                            <input class="form-check-input" type="checkbox" name="allow_stripe"
                                                id="allow_stripe" <?php echo $template['allow_stripe'] ? 'checked' : ''; ?>>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold text-main">Stripe</div>
                                            <div class="text-muted x-small">Secure credit card payments</div>
                                        </div>
                                        <div class="gateway-icon-box text-info fs-4 ms-2">
                                            <i class="bi bi-credit-card"></i>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer bg-light border-0 p-4 text-end px-5">
                    <a href="<?php echo APP_URL; ?>/payments" class="btn btn-link text-muted me-3 text-decoration-none fw-medium">Discard Changes</a>
                    <button type="submit" class="btn btn-primary px-5 py-2 fw-bold shadow-lg rounded-12">
                        Update Payment Link
                    </button>
                </div>
            </form>
        </div>
        
        <div class="mt-4 p-3 bg-white border border-subtle rounded-24 shadow-sm">
            <div class="d-flex align-items-center text-muted small px-2">
                <i class="bi bi-info-circle-fill me-3 text-primary fs-5"></i>
                <div>
                    <strong>Pro-tip:</strong> Changes to this link are live immediately. Any client currently viewing the page will see the updated title, amount, or required fields upon their next interaction.
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .x-small { font-size: 0.75rem; }
    .rounded-16 { border-radius: 16px !important; }
    .rounded-12 { border-radius: 12px !important; }
    .rounded-24 { border-radius: 24px !important; }
    .tracking-wider { letter-spacing: 0.05em; }
    .fw-black { font-weight: 800; }
    .border-subtle { border-color: #f1f5f9 !important; }
    .shadow-premium { box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.05); }

    .field-dot { width: 8px; height: 8px; border-radius: 50%; }
    
    .gateway-tile {
        background: #fff;
        border: 1px solid #e2e8f0;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .gateway-tile:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
    }
    
    .gateway-tile.active {
        border-color: var(--primary-accent);
        background: rgba(59, 130, 246, 0.03);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.05);
    }

    .cursor-pointer { cursor: pointer; }
    .transition-all { transition: all 0.2s ease; }
</style>

<script>
document.querySelectorAll('.gateway-tile').forEach(tile => {
    tile.addEventListener('click', function(e) {
        if (e.target.tagName !== 'INPUT') {
            const checkbox = this.querySelector('input[type="checkbox"]');
            checkbox.checked = !checkbox.checked;
            this.classList.toggle('active', checkbox.checked);
        }
    });
    
    tile.querySelector('input[type="checkbox"]').addEventListener('change', function() {
        tile.classList.toggle('active', this.checked);
    });
});
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
