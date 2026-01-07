<?php
/**
 * Public Checkout Experience
 * Prioritizes payment actions with a summary sidebar/mobile header
 */
$pageTitle = 'Secure Checkout - Invoice #' . $invoice['invoice_number'];
require_once APP_PATH . '/views/layouts/public_header.php';
?>

<div class="checkout-wrapper mx-auto mt-4 px-3" style="max-width: 580px;">
    <!-- Top Header Branding -->
    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
        <?php if (Branding::hasLogo()): ?>
            <img src="<?php echo Branding::getLogoUrl(); ?>" alt="Logo" style="max-height: 48px; border-radius: 8px;">
        <?php else: ?>
            <div class="logo-placeholder-pill px-3 py-2 fw-bold text-white bg-primary rounded-8 shadow-sm">
                <?php echo Security::escape(substr(Branding::getCompanyName(), 0, 1)); ?>
            </div>
        <?php endif; ?>
        <div class="badge-secure-checkout px-3 py-1 rounded-pill fw-bold text-uppercase"
            style="font-size: 0.65rem; background: rgba(45, 90, 136, 0.1); color: #2D5A88; letter-spacing: 0.05em;">
            <i class="bi bi-shield-check me-1"></i> Secure Checkout
        </div>
    </div>

    <div class="text-center mb-5">
        <h5 class="text-muted fw-bold mb-1" style="font-size: 0.85rem; letter-spacing: 0.05em;">
            <?php echo Security::escape(Branding::getCompanyName()); ?>
        </h5>
        <h2 class="display-4 fw-black text-dark mb-0">
            <span
                class="h4 fw-normal currency-symbol align-top mt-2 d-inline-block"><?php echo Branding::getCurrencySymbol(); ?></span><?php echo number_format($invoice['amount'], 2); ?>
        </h2>
    </div>

    <!-- Main Payment Card -->
    <div class="card border-0 shadow-lg rounded-24 overflow-hidden mb-4">
        <div class="card-body p-4 p-md-5">
            <?php if ($invoice['status'] === 'paid'): ?>
                <div class="text-center py-2 mb-5">
                    <div class="payment-success-icon mx-auto mb-4 bg-success text-white rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 80px; height: 80px; background: linear-gradient(135deg, #10B981 0%, #059669 100%) !important;">
                        <i class="bi bi-check-lg fs-1"></i>
                    </div>
                    <h3 class="fw-bold mb-2">Payment Successfully Processed</h3>
                    <p class="text-muted mb-4 px-md-5">A copy of this invoice has been sent to your email.</p>

                    <div class="d-flex justify-content-center mt-4">
                        <a href="<?php echo APP_URL; ?>/portal/invoicePdf/<?php echo $invoice['payment_token']; ?>"
                            class="btn btn-primary px-5 py-3 rounded-12 fw-bold shadow-lg">
                            <i class="bi bi-download me-2"></i> Download PDF Receipt
                        </a>
                    </div>
                </div>

                <hr class="my-5 opacity-10">

                <!-- Show Invoice Details even when paid -->
                <div class="invoice-details-view">
                    <div class="row mb-5">
                        <div class="col-6">
                            <h6 class="text-muted x-small text-uppercase fw-bold mb-3">Bill To</h6>
                            <div class="fw-bold text-dark"><?php echo Security::escape($invoice['client_name']); ?></div>
                            <div class="text-muted small"><?php echo Security::escape($invoice['client_email']); ?></div>
                        </div>
                        <div class="col-6 text-end">
                            <h6 class="text-muted x-small text-uppercase fw-bold mb-3">Invoice Details</h6>
                            <div class="text-dark small">#<?php echo Security::escape($invoice['invoice_number']); ?></div>
                            <div class="text-muted small"><?php echo date('M d, Y', strtotime($invoice['created_at'])); ?>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-borderless align-middle mb-0">
                            <thead class="border-bottom">
                                <tr class="text-muted x-small text-uppercase">
                                    <th class="py-3 px-0">Description</th>
                                    <th class="py-3 text-end px-0">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($invoice['items'])): ?>
                                    <?php foreach ($invoice['items'] as $item): ?>
                                        <tr class="border-bottom">
                                            <td class="py-4 ps-0">
                                                <div class="fw-bold text-dark"><?php echo Security::escape($item['description']); ?>
                                                </div>
                                            </td>
                                            <td class="py-4 text-end pe-0 fw-bold text-dark">
                                                <?php echo Branding::getCurrencySymbol() . number_format($item['total'], 2); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr class="border-bottom">
                                        <td class="py-4 ps-0">
                                            <div class="fw-bold text-dark">
                                                <?php echo Security::escape($invoice['deal_title'] ?: 'Professional Services'); ?>
                                            </div>
                                        </td>
                                        <td class="py-4 text-end pe-0 fw-bold text-dark">
                                            <?php echo Branding::getCurrencySymbol() . number_format($invoice['amount'], 2); ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td class="pt-4 ps-0 text-muted">Subtotal</td>
                                    <td class="pt-4 text-end pe-0 text-dark">
                                        <?php echo Branding::getCurrencySymbol() . number_format($invoice['amount'], 2); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-2 ps-0 h4 fw-black text-dark">Total Paid</td>
                                    <td class="py-2 text-end pe-0 h4 fw-black text-primary">
                                        <?php echo Branding::getCurrencySymbol() . number_format($invoice['amount'], 2); ?>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            <?php elseif ($invoice['payment_closed']): ?>
                <div class="text-center py-5">
                    <i class="bi bi-lock-fill display-4 text-warning mb-3"></i>
                    <h4 class="fw-bold mb-2">Portal Restricted</h4>
                    <p class="text-muted mb-0 px-md-4">This specific payment link is no longer active. If you believe this
                        is an error, please contact <?php echo Security::escape(Branding::getCompanyName()); ?>.</p>
                </div>
            <?php else: ?>
                <form id="payment-form">
                    <!-- Section 1: Billing -->
                    <div class="mb-5">
                        <h6 class="text-muted fw-bold mb-4 x-small text-uppercase tracking-wider">1. Billing Information
                        </h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-floating shadow-sm rounded-12 overflow-hidden">
                                    <input type="text" class="form-control border-0 bg-white" id="billing_name"
                                        placeholder="Full Name"
                                        value="<?php echo Security::escape($invoice['client_name']); ?>">
                                    <label class="text-muted">Full Name</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating shadow-sm rounded-12 overflow-hidden">
                                    <input type="email" class="form-control border-0 bg-white" id="billing_email"
                                        placeholder="Email Address"
                                        value="<?php echo Security::escape($invoice['client_email']); ?>">
                                    <label class="text-muted">Email Address</label>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-floating shadow-sm rounded-12 overflow-hidden">
                                    <input type="text" class="form-control border-0 bg-white" id="billing_phone"
                                        placeholder="Phone"
                                        value="<?php echo Security::escape($invoice['client_phone']); ?>">
                                    <label class="text-muted">Phone</label>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-floating shadow-sm rounded-12 overflow-hidden">
                                    <input type="text" class="form-control border-0 bg-white" id="billing_company"
                                        placeholder="Company">
                                    <label class="text-muted">Company</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating shadow-sm rounded-12 overflow-hidden">
                                    <input type="text" class="form-control border-0 bg-white" id="billing_address"
                                        placeholder="Address">
                                    <label class="text-muted">Address</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Payment Method -->
                    <div class="mb-5">
                        <h6 class="text-muted fw-bold mb-4 x-small text-uppercase tracking-wider">2. Payment Method</h6>
                        <div class="payment-selection-stack gap-3 d-flex flex-column">
                            <?php if (!empty($invoice['stripe_publishable_key'])): ?>
                                <label
                                    class="payment-method-tile p-3 rounded-12 border-2 d-flex align-items-center transition-all cursor-pointer"
                                    id="method-tile-stripe" for="radio-stripe">
                                    <input type="radio" class="form-check-input me-3 mt-0 custom-radio d-none"
                                        name="payment_method" id="radio-stripe" onclick="selectMethod('stripe')" checked>
                                    <div class="radio-indicator me-3 flex-shrink-0"></div>
                                    <div class="method-icon-box me-3 d-flex align-items-center justify-content-center"
                                        style="width: 48px; height: 48px;">
                                        <svg viewBox="0 0 60 25" xmlns="http://www.w3.org/2000/svg" width="48" height="20"
                                            fill="#635bff">
                                            <path
                                                d="M59.64 14.28h-8.06c.19 1.93 1.6 2.55 3.2 2.55 1.64 0 2.96-.37 4.05-.95v3.32a8.33 8.33 0 0 1-4.56 1.1c-4.01 0-6.83-2.5-6.83-7.48 0-4.19 2.39-7.52 6.3-7.52 3.92 0 5.96 3.28 5.96 7.5 0 .4-.04 1.26-.06 1.48zm-5.92-5.62c-1.03 0-2.17.73-2.17 2.58h4.25c0-1.85-1.07-2.58-2.08-2.58zM40.95 20.3c-1.44 0-2.32-.6-2.9-1.04l-.02 4.63-4.12.87V5.57h3.76l.08 1.02a4.7 4.7 0 0 1 3.23-1.29c2.9 0 5.62 2.6 5.62 7.4 0 5.23-2.7 7.6-5.65 7.6zM40 8.95c-.95 0-1.54.34-1.97.81l.02 6.12c.4.44.98.78 1.95.78 1.52 0 2.54-1.65 2.54-3.87 0-2.15-1.04-3.84-2.54-3.84zM28.24 5.57h4.13v14.44h-4.13V5.57zm0-4.7L32.37 0v3.36l-4.13.88V.88zm-4.32 9.35v9.79H19.8V5.57h3.7l.12 1.22c1-1.77 3.07-1.41 3.62-1.22v3.79c-.52-.17-2.29-.43-3.32.86zm-8.55 4.72c0 2.43 2.6 1.68 3.12 1.46v3.36c-.55.3-1.54.54-2.89.54a4.15 4.15 0 0 1-4.27-4.24l.01-13.17 4.02-.86v3.54h3.14V9.1h-3.13v5.85zm-4.91.7c0 2.97-2.31 4.66-5.73 4.66a11.2 11.2 0 0 1-4.46-.93v-3.93c1.38.75 3.1 1.31 4.46 1.31.92 0 1.53-.24 1.53-1C6.26 13.77 0 14.51 0 9.95 0 7.04 2.28 5.3 5.62 5.3c1.36 0 2.72.2 4.09.75v3.88a9.23 9.23 0 0 0-4.1-1.06c-.86 0-1.44.25-1.44.93 0 1.85 6.29.97 6.29 5.88z" />
                                        </svg>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold text-dark">Card payment</div>
                                        <div class="text-muted x-small">Visa, Mastercard, AMEX</div>
                                    </div>
                                </label>
                            <?php endif; ?>

                            <?php if (!empty($invoice['paypal_client_id'])): ?>
                                <label
                                    class="payment-method-tile p-3 rounded-12 border-2 d-flex align-items-center transition-all cursor-pointer"
                                    id="method-tile-paypal" for="radio-paypal">
                                    <input type="radio" class="form-check-input me-3 mt-0 custom-radio d-none"
                                        name="payment_method" id="radio-paypal" onclick="selectMethod('paypal')">
                                    <div class="radio-indicator me-3 flex-shrink-0"></div>
                                    <div class="method-icon-box me-3 d-flex align-items-center justify-content-center"
                                        style="width: 48px; height: 48px;">
                                        <svg viewBox="0 0 124 33" xmlns="http://www.w3.org/2000/svg" width="48" height="13">
                                            <path fill="#003087"
                                                d="M46.211 6.749h-6.839a.95.95 0 0 0-.939.802l-2.766 17.537a.57.57 0 0 0 .564.658h3.265a.95.95 0 0 0 .939-.803l.746-4.73a.95.95 0 0 1 .938-.803h2.165c4.505 0 7.105-2.18 7.784-6.5.306-1.89.013-3.375-.872-4.415-.972-1.142-2.696-1.746-4.985-1.746zM47 13.154c-.374 2.454-2.249 2.454-4.062 2.454h-1.032l.724-4.583a.57.57 0 0 1 .563-.481h.473c1.235 0 2.4 0 3.002.704.359.42.469 1.044.332 1.906zM66.654 13.075h-3.275a.57.57 0 0 0-.563.481l-.145.916-.229-.332c-.709-1.029-2.29-1.373-3.868-1.373-3.619 0-6.71 2.741-7.312 6.586-.313 1.918.132 3.752 1.22 5.031.998 1.176 2.426 1.666 4.125 1.666 2.916 0 4.533-1.875 4.533-1.875l-.146.91a.57.57 0 0 0 .562.66h2.95a.95.95 0 0 0 .939-.803l1.77-11.209a.568.568 0 0 0-.561-.658zm-4.565 6.374c-.316 1.871-1.801 3.127-3.695 3.127-.951 0-1.711-.305-2.199-.883-.484-.574-.668-1.391-.514-2.301.295-1.855 1.805-3.152 3.67-3.152.93 0 1.686.309 2.184.892.499.589.697 1.411.554 2.317zM84.096 13.075h-3.291a.954.954 0 0 0-.787.417l-4.539 6.686-1.924-6.425a.953.953 0 0 0-.912-.678h-3.234a.57.57 0 0 0-.541.754l3.625 10.638-3.408 4.811a.57.57 0 0 0 .465.9h3.287a.949.949 0 0 0 .781-.408l10.946-15.8a.57.57 0 0 0-.468-.895z" />
                                            <path fill="#0070E0"
                                                d="M94.992 6.749h-6.84a.95.95 0 0 0-.938.802l-2.766 17.537a.569.569 0 0 0 .562.658h3.51a.665.665 0 0 0 .656-.562l.785-4.971a.95.95 0 0 1 .938-.803h2.164c4.506 0 7.105-2.18 7.785-6.5.307-1.89.012-3.375-.873-4.415-.971-1.142-2.694-1.746-4.983-1.746zm.789 6.405c-.373 2.454-2.248 2.454-4.062 2.454h-1.031l.725-4.583a.568.568 0 0 1 .562-.481h.473c1.234 0 2.4 0 3.002.704.359.42.468 1.044.331 1.906zM115.434 13.075h-3.273a.567.567 0 0 0-.562.481l-.145.916-.23-.332c-.709-1.029-2.289-1.373-3.867-1.373-3.619 0-6.709 2.741-7.311 6.586-.312 1.918.131 3.752 1.219 5.031 1 1.176 2.426 1.666 4.125 1.666 2.916 0 4.533-1.875 4.533-1.875l-.146.91a.57.57 0 0 0 .564.66h2.949a.95.95 0 0 0 .938-.803l1.771-11.209a.571.571 0 0 0-.565-.658zm-4.565 6.374c-.314 1.871-1.801 3.127-3.695 3.127-.949 0-1.711-.305-2.199-.883-.484-.574-.666-1.391-.514-2.301.297-1.855 1.805-3.152 3.67-3.152.93 0 1.686.309 2.184.892.501.589.699 1.411.554 2.317zM119.295 7.23l-2.807 17.858a.569.569 0 0 0 .562.658h2.822c.469 0 .867-.34.939-.803l2.768-17.536a.57.57 0 0 0-.562-.659h-3.16a.571.571 0 0 0-.562.482z" />
                                            <path fill="#003087"
                                                d="M7.266 29.154l.523-3.322-1.165-.027H1.061L4.927 1.292a.316.316 0 0 1 .314-.268h9.38c3.114 0 5.263.648 6.385 1.927.526.6.861 1.227 1.023 1.917.17.724.173 1.589.007 2.644l-.012.077v.676l.526.298a3.69 3.69 0 0 1 1.065.812c.45.513.741 1.165.864 1.938.127.795.085 1.741-.123 2.812-.24 1.232-.628 2.305-1.152 3.183a6.547 6.547 0 0 1-1.825 2c-.696.494-1.523.869-2.458 1.109-.906.236-1.939.355-3.072.355h-.73c-.522 0-1.029.188-1.427.525a2.21 2.21 0 0 0-.744 1.328l-.055.299-.924 5.855-.042.215c-.011.068-.03.102-.058.125a.155.155 0 0 1-.096.035H7.266z" />
                                            <path fill="#0070E0"
                                                d="M23.048 7.667c-.028.179-.06.362-.096.55-1.237 6.351-5.469 8.545-10.874 8.545H9.326c-.661 0-1.218.48-1.321 1.132L6.596 26.83l-.399 2.533a.704.704 0 0 0 .695.814h4.881c.578 0 1.069-.42 1.16-.99l.048-.248.919-5.832.059-.32c.09-.572.582-.992 1.16-.992h.73c4.729 0 8.431-1.92 9.513-7.476.452-2.321.218-4.259-.978-5.622a4.667 4.667 0 0 0-1.336-1.03z" />
                                            <path fill="#003087"
                                                d="M21.754 7.151a9.757 9.757 0 0 0-1.203-.267 15.284 15.284 0 0 0-2.426-.177h-7.352a1.172 1.172 0 0 0-1.159.992L8.05 17.605l-.045.289a1.336 1.336 0 0 1 1.321-1.132h2.752c5.405 0 9.637-2.195 10.874-8.545.037-.188.068-.371.096-.55a6.594 6.594 0 0 0-1.017-.429 9.045 9.045 0 0 0-.277-.087z" />
                                            <path fill="#0070E0"
                                                d="M9.614 7.699a1.169 1.169 0 0 1 1.159-.991h7.352c.871 0 1.684.057 2.426.177a9.757 9.757 0 0 1 1.481.353c.365.121.704.264 1.017.429.368-2.347-.003-3.945-1.272-5.392C20.378.682 17.853 0 14.622 0h-9.38c-.66 0-1.223.48-1.325 1.133L.01 25.898a.806.806 0 0 0 .795.932h5.791l1.454-9.225 1.564-9.906z" />
                                        </svg>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold text-dark">PayPal</div>
                                        <div class="text-muted x-small">Express checkout</div>
                                    </div>
                                </label>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div id="stripe-container" class="d-none">
                        <button type="button" id="stripe-button"
                            class="btn btn-primary w-100 py-3 rounded-12 fw-bold shadow-lg checkout-pro-btn mb-4">
                            Pay <?php echo Branding::getCurrencySymbol() . number_format($invoice['amount'], 2); ?> Securely
                        </button>
                    </div>

                    <div id="paypal-container" class="d-none mb-4">
                        <div id="paypal-button-container"></div>
                    </div>

                    <div id="payment-error"
                        class="alert alert-danger d-none mt-3 small text-center border-0 rounded-12 py-3 shadow-sm"></div>

                    <div class="text-center mt-2">
                        <div class="d-flex align-items-center justify-content-center text-muted x-small">
                            <i class="bi bi-shield-lock-fill text-success fs-6 me-2"></i>
                            <span class="fw-medium">Secure encrypted payment</span>
                        </div>
                        <div class="text-muted opacity-50 x-small mt-1">Powered by Stripe & PayPal</div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Redundant summary removed/collapsed -->
    <?php if ($invoice['status'] !== 'paid'): ?>
        <div class="px-2 text-center no-print pb-5">
            <button class="btn btn-link btn-sm text-decoration-none text-muted fw-medium" type="button"
                data-bs-toggle="collapse" data-bs-target="#orderSummary">
                <i class="bi bi-info-circle me-1"></i> What's included in this invoice?
            </button>
            <div class="collapse mt-3" id="orderSummary">
                <div class="bg-white rounded-16 p-3 shadow-sm text-start">
                    <table class="table table-sm table-borderless x-small mb-0">
                        <thead>
                            <tr class="border-bottom text-muted">
                                <th class="py-2">Description</th>
                                <th class="py-2 text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($invoice['items'])): ?>
                                <?php foreach ($invoice['items'] as $item): ?>
                                    <tr>
                                        <td class="py-2"><?php echo Security::escape($item['description']); ?></td>
                                        <td class="py-2 text-end fw-bold">
                                            <?php echo Branding::getCurrencySymbol() . number_format($item['total'], 2); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td class="py-2">Professional Services</td>
                                    <td class="py-2 text-end fw-bold">
                                        <?php echo Branding::getCurrencySymbol() . number_format($invoice['amount'], 2); ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    :root {
        --checkout-blue: #2D5A88;
        --checkout-bg: #F8FAFC;
        --input-border: #E2E8F0;
        --card-radius: 24px;
        --tile-bg: #FFFFFF;
        --tile-active-bg: #F8FAFC;
    }

    body {
        background-color: var(--checkout-bg) !important;
        font-family: 'Inter', -apple-system, system-ui, sans-serif;
    }

    .fw-black {
        font-weight: 900;
    }

    .x-small {
        font-size: 0.75rem;
    }

    .tracking-wider {
        letter-spacing: 0.05em;
    }

    .rounded-12 {
        border-radius: 12px !important;
    }

    .rounded-16 {
        border-radius: 16px !important;
    }

    .rounded-24 {
        border-radius: 24px !important;
    }

    /* Form Styles */
    .form-floating>.form-control {
        border: 1px solid var(--input-border);
        height: calc(3.5rem + 2px);
        line-height: 1.25;
    }

    .form-floating>.form-control:focus {
        border-color: var(--checkout-blue);
        box-shadow: 0 0 0 4px rgba(45, 90, 136, 0.1);
    }

    /* Payment Tile Styles */
    .payment-method-tile {
        background: var(--tile-bg);
        border: 2px solid #F1F5F9;
        transition: all 0.25s ease;
    }

    .payment-method-tile:hover {
        border-color: #E2E8F0;
        background: var(--tile-active-bg);
    }

    .payment-method-tile.active {
        border-color: var(--checkout-blue);
        background: rgba(45, 90, 136, 0.03);
    }

    .radio-indicator {
        width: 20px;
        height: 20px;
        border: 2px solid #CBD5E1;
        border-radius: 50%;
        position: relative;
    }

    .payment-method-tile.active .radio-indicator {
        border-color: var(--checkout-blue);
        background: var(--checkout-blue);
    }

    .payment-method-tile.active .radio-indicator::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 8px;
        height: 8px;
        background: white;
        border-radius: 50%;
    }

    /* Button Styling */
    .checkout-pro-btn {
        background: #0070F3;
        border: none;
        transition: all 0.2s ease;
    }

    .checkout-pro-btn:hover {
        background: #0060d9;
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0, 112, 243, 0.2);
    }

    .checkout-pro-btn:active {
        transform: translateY(0);
    }

    .payment-success-icon {
        background: #10B981 !important;
    }

    .cursor-pointer {
        cursor: pointer;
    }

    .transition-all {
        transition: all 0.2s ease;
    }

    @media print {
        .no-print {
            display: none !important;
        }

        body {
            background: white !important;
        }

        .checkout-wrapper {
            max-width: 100% !important;
            margin: 0 !important;
            width: 100% !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
        }
    }
</style>

<script>
    let selectedMethod = null;

    function selectMethod(method) {
        selectedMethod = method;
        document.querySelectorAll('.payment-method-tile').forEach(t => t.classList.remove('active'));
        document.getElementById('method-tile-' + method).classList.add('active');

        document.getElementById('stripe-container').classList.add('d-none');
        document.getElementById('paypal-container').classList.add('d-none');

        if (method === 'stripe') {
            document.getElementById('stripe-container').classList.remove('d-none');
        } else {
            document.getElementById('paypal-container').classList.remove('d-none');
        }
    }

    window.addEventListener('load', () => {
        <?php if (!empty($invoice['stripe_publishable_key'])): ?>
            selectMethod('stripe');
        <?php elseif (!empty($invoice['paypal_client_id'])): ?>
            selectMethod('paypal');
        <?php endif; ?>
    });
</script>

<?php if ($invoice['status'] !== 'paid' && !$invoice['payment_closed']): ?>
    <?php if (!empty($invoice['stripe_publishable_key'])): ?>
        <script src="https://js.stripe.com/v3/"></script>
        <script>
            const stripe = Stripe('<?php echo Security::escape($invoice['stripe_publishable_key']); ?>');
            const stripeBtn = document.getElementById('stripe-button');
            const paymentError = document.getElementById('payment-error');

            stripeBtn?.addEventListener('click', function () {
                const name = document.getElementById('billing_name')?.value;
                const email = document.getElementById('billing_email')?.value;
                const phone = document.getElementById('billing_phone')?.value;
                const address = document.getElementById('billing_address')?.value;

                stripeBtn.disabled = true;
                stripeBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing Securely...';
                paymentError.classList.add('d-none');
                paymentError.textContent = '';

                const formData = new FormData();
                formData.append('name', name);
                formData.append('email', email);
                formData.append('phone', phone);
                formData.append('address', address);

                fetch('<?php echo APP_URL; ?>/portal/createStripeSession/<?php echo $invoice['payment_token']; ?>', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(session => {
                        if (session.id) {
                            return stripe.redirectToCheckout({ sessionId: session.id });
                        } else {
                            throw new Error(session.message || 'Gateway communication failure');
                        }
                    })
                    .catch(error => {
                        stripeBtn.disabled = false;
                        stripeBtn.innerHTML = 'Pay <?php echo Branding::getCurrencySymbol() . number_format($invoice['amount'], 2); ?> Securely';
                        paymentError.textContent = error.message;
                        paymentError.classList.remove('d-none');
                    });
            });
        </script>
    <?php endif; ?>

    <?php if (!empty($invoice['paypal_client_id'])): ?>
        <script
            src="https://www.paypal.com/sdk/js?client-id=<?php echo Security::escape($invoice['paypal_client_id']); ?>&currency=USD"></script>
        <script>
            paypal.Buttons({
                style: { layout: 'vertical', color: 'black', shape: 'pill', label: 'pay' },
                createOrder: function (data, actions) {
                    return actions.order.create({
                        purchase_units: [{
                            amount: { value: '<?php echo $invoice['amount']; ?>' },
                            description: 'Secure Checkout - Ref #<?php echo $invoice['invoice_number']; ?>'
                        }]
                    });
                },
                onApprove: function (data, actions) {
                    return actions.order.capture().then(function (details) {
                        fetch('<?php echo APP_URL; ?>/portal/recordPayment/<?php echo $invoice['payment_token']; ?>', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: 'provider=paypal&transaction_id=' + details.id + '&amount=' + details.purchase_units[0].amount.value
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success && data.invoice_token) {
                                    window.location.href = '<?php echo APP_URL; ?>/portal/thankYou/' + data.invoice_token;
                                } else {
                                    alert('FATAL GATEWAY ERROR: ' + data.message);
                                }
                            });
                    });
                }
            }).render('#paypal-button-container');
        </script>
    <?php endif; ?>
<?php endif; ?>


<?php require_once APP_PATH . '/views/layouts/public_footer.php'; ?>