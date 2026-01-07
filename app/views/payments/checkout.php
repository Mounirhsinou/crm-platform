<?php
/**
 * Public Checkout View - Fully Integrated
 * Multi-gateway on-page payment experience
 */
$pageTitle = $template['title'] . ' - Secure Checkout';
require_once APP_PATH . '/views/layouts/public_header.php';
?>

<div class="checkout-wrapper mx-auto mt-4 px-3" style="max-width: 600px;">
    <!-- Branding Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
        <?php if (Branding::hasLogo()): ?>
            <img src="<?php echo Branding::getLogoUrl(); ?>" alt="Logo" style="max-height: 48px; border-radius: 8px;">
        <?php else: ?>
            <div class="logo-placeholder-pill px-3 py-2 fw-bold text-white bg-primary rounded-8 shadow-sm">
                <?php echo Security::escape(substr(Branding::getCompanyName(), 0, 1)); ?>
            </div>
        <?php endif; ?>
        <div class="badge-secure-checkout px-3 py-1 rounded-pill fw-bold text-uppercase"
            style="font-size: 0.65rem; background: rgba(45, 90, 136, 0.1); color: #2D5A88; letter-spacing: 0.1em;">
            <i class="bi bi-shield-lock-fill me-1 text-success"></i> 256-bit Secure
        </div>
    </div>

    <div class="text-center mb-5 mt-5">
        <h5 class="text-muted fw-bold mb-2 x-small text-uppercase tracking-wider">
            <?php echo Security::escape(Branding::getCompanyName()); ?>
        </h5>
        <h1 class="display-3 fw-black text-dark mb-0">
            <span
                class="h4 fw-normal currency-symbol align-top mt-3 d-inline-block"><?php echo Branding::getCurrencySymbol(); ?></span><?php echo number_format($template['amount'], 2); ?>
        </h1>
    </div>

    <!-- Main Payment Card -->
    <div class="card border-0 shadow-pro rounded-32 overflow-hidden mb-5">
        <div class="card-body p-4 p-md-5">
            <?php if ($template['payment_closed']): ?>
                <div class="text-center py-5">
                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-4"
                        style="width: 80px; height: 80px;">
                        <i class="bi bi-lock-fill display-5 text-warning"></i>
                    </div>
                    <h4 class="fw-black mb-3">Gateway Closed</h4>
                    <p class="text-muted mb-0 px-md-4">This checkout link is no longer accepting payments. Please contact
                        the administrator for assistance.</p>
                </div>
            <?php else: ?>
                <div class="mb-5 text-center">
                    <h4 class="fw-black text-dark mb-2"><?php echo Security::escape($template['title']); ?></h4>
                    <?php if (!empty($template['description'])): ?>
                        <p class="text-muted small px-3 mb-0"><?php echo nl2br(Security::escape($template['description'])); ?>
                        </p>
                    <?php endif; ?>
                </div>

                <form id="payment-form">
                    <!-- Step 1: Data Collection -->
                    <div id="step-billing" class="payment-step">
                        <div class="d-flex align-items-center mb-4">
                            <span class="step-num bg-primary text-white rounded-circle me-3">1</span>
                            <h6 class="text-dark fw-black mb-0 text-uppercase x-small tracking-widest">Billing Details</h6>
                        </div>

                        <div class="row g-3">
                            <?php
                            $settings = json_decode($template['checkout_settings'] ?? '[]', true);
                            if (empty($settings)) {
                                $settings = ['name' => ['visible' => true, 'required' => true], 'email' => ['visible' => true, 'required' => true]];
                            }

                            $fieldMap = [
                                'name' => ['label' => 'Full Name', 'type' => 'text', 'placeholder' => 'Enter your name', 'col' => 'col-12', 'icon' => 'person'],
                                'email' => ['label' => 'Email Address', 'type' => 'email', 'placeholder' => 'you@example.com', 'col' => 'col-12', 'icon' => 'envelope'],
                                'phone' => ['label' => 'Phone Number', 'type' => 'text', 'placeholder' => '+1 (555) 000-0000', 'col' => 'col-12', 'icon' => 'telephone'],
                                'company' => ['label' => 'Company', 'type' => 'text', 'placeholder' => 'Business name', 'col' => 'col-12', 'icon' => 'building'],
                                'address' => ['label' => 'Address', 'type' => 'text', 'placeholder' => 'Street address', 'col' => 'col-12', 'icon' => 'geo-alt'],
                                'city' => ['label' => 'City', 'type' => 'text', 'placeholder' => 'City', 'col' => 'col-md-6', 'icon' => 'map'],
                                'country' => ['label' => 'Country', 'type' => 'text', 'placeholder' => 'Country', 'col' => 'col-md-6', 'icon' => 'globe'],
                                'notes' => ['label' => 'Additional Message', 'type' => 'textarea', 'placeholder' => 'Any special instructions...', 'col' => 'col-12', 'icon' => 'chat-left-text']
                            ];

                            foreach ($fieldMap as $key => $config):
                                if (!($settings[$key]['visible'] ?? false))
                                    continue;
                                $required = ($settings[$key]['required'] ?? false) ? 'required' : '';
                                ?>
                                <div class="<?php echo $config['col']; ?>">
                                    <div class="form-group position-relative">
                                        <label
                                            class="x-small fw-bold text-muted text-uppercase mb-2 ms-1"><?php echo $config['label']; ?></label>
                                        <div class="input-group-custom">
                                            <i class="bi bi-<?php echo $config['icon']; ?> input-icon"></i>
                                            <?php if ($config['type'] === 'textarea'): ?>
                                                <textarea name="<?php echo $key; ?>"
                                                    class="form-control-pro checkout-field h-auto py-3"
                                                    placeholder="<?php echo $config['placeholder']; ?>" rows="3" <?php echo $required; ?>></textarea>
                                            <?php else: ?>
                                                <input type="<?php echo $config['type']; ?>" name="<?php echo $key; ?>"
                                                    class="form-control-pro checkout-field"
                                                    placeholder="<?php echo $config['placeholder']; ?>" <?php echo $required; ?>>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <button type="button" class="btn btn-primary btn-lg w-100 rounded-20 py-3 fw-black shadow-pro mt-5"
                            onclick="goToStep(2)">
                            Continue to Payment <i class="bi bi-arrow-right-short ms-1"></i>
                        </button>
                    </div>

                    <!-- Step 2: Payment Gateway -->
                    <div id="step-payment" class="payment-step d-none">
                        <div class="d-flex align-items-center mb-4">
                            <button type="button" class="btn btn-link p-0 me-3 text-muted" onclick="goToStep(1)">
                                <i class="bi bi-arrow-left fs-4"></i>
                            </button>
                            <span class="step-num bg-primary text-white rounded-circle me-3">2</span>
                            <h6 class="text-dark fw-black mb-0 text-uppercase x-small tracking-widest">Select Payment Method
                            </h6>
                        </div>

                        <div class="payment-selection-stack gap-3 d-flex flex-column mb-5">
                            <?php if ($template['allow_stripe']): ?>
                                <label class="method-tile p-4 rounded-24 border-2 transition-all cursor-pointer"
                                    id="tile-stripe" onclick="initStripePayment()">
                                    <div class="d-flex align-items-center">
                                        <div class="method-indicator me-4"></div>
                                        <div class="flex-grow-1">
                                            <img src="<?php echo APP_URL; ?>/assets/img/stripe-logo.svg" height="20"
                                                class="mb-2 opacity-100"
                                                onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/b/ba/Stripe_Logo%2C_revised_2016.svg'">
                                            <div class="text-muted x-small fw-medium">All major credit & debit cards</div>
                                        </div>
                                        <i class="bi bi-credit-card-2-back fs-3 text-primary opacity-50"></i>
                                    </div>
                                    <div id="stripe-element-mount" class="mt-4 d-none">
                                        <div id="payment-element"></div>
                                        <button type="button" id="submit-stripe"
                                            class="btn btn-primary w-100 py-3 rounded-16 fw-black mt-4 shadow-pro">
                                            Pay
                                            <?php echo Branding::getCurrencySymbol() . number_format($template['amount'], 2); ?>
                                            Now
                                        </button>
                                    </div>
                                </label>
                            <?php endif; ?>

                            <?php if ($template['allow_paypal']): ?>
                                <label class="method-tile p-4 rounded-24 border-2 transition-all cursor-pointer"
                                    id="tile-paypal" onclick="initPaypalPayment()">
                                    <div class="d-flex align-items-center">
                                        <div class="method-indicator me-4"></div>
                                        <div class="flex-grow-1">
                                            <img src="<?php echo APP_URL; ?>/assets/img/paypal-logo.svg" height="20"
                                                class="mb-2 opacity-100"
                                                onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg'">
                                            <div class="text-muted x-small fw-medium">PayPal, Venmo and Pay Later</div>
                                        </div>
                                        <i class="bi bi-paypal fs-3 text-info opacity-50"></i>
                                    </div>
                                    <div id="paypal-element-mount" class="mt-4 d-none">
                                        <div id="paypal-button-container"></div>
                                    </div>
                                </label>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div id="error-message"
                        class="alert alert-danger d-none mt-3 small text-center border-0 rounded-20 py-3 shadow-pro"></div>

                    <div class="text-center mt-4">
                        <div class="d-flex align-items-center justify-content-center text-muted x-small opacity-75">
                            <i class="bi bi-shield-fill-check text-success fs-6 me-2"></i>
                            <span class="fw-bold tracking-wider">SECURE END-TO-END ENCRYPTION</span>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;600;800;900&display=swap');

    body {
        background-color: #f6f9fc !important;
        font-family: 'Public Sans', sans-serif;
        color: #1e293b;
    }

    .fw-black {
        font-weight: 900 !important;
    }

    .x-small {
        font-size: 0.7rem;
    }

    .tracking-widest {
        letter-spacing: 0.2em;
    }

    .shadow-pro {
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1) !important;
    }

    .rounded-32 {
        border-radius: 32px !important;
    }

    .rounded-24 {
        border-radius: 24px !important;
    }

    .rounded-20 {
        border-radius: 20px !important;
    }

    .rounded-16 {
        border-radius: 16px !important;
    }

    .step-num {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        font-size: 0.8rem;
    }

    .form-control-pro {
        border: 2px solid #eef2f6;
        background: #fdfdfe;
        border-radius: 16px;
        padding: 1rem 1rem 1rem 3.2rem;
        font-weight: 600;
        transition: all 0.2s ease;
        font-size: 0.95rem;
        width: 100%;
    }

    .form-control-pro:focus {
        border-color: #2D5A88;
        background: #fff;
        box-shadow: 0 0 0 4px rgba(45, 90, 136, 0.08);
        outline: none;
    }

    .input-icon {
        position: absolute;
        left: 1.2rem;
        top: 2.6rem;
        color: #94a3b8;
        font-size: 1.2rem;
        z-index: 5;
    }

    .method-tile {
        background: #fff;
        border: 2px solid #f1f5f9;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .method-tile:hover {
        border-color: #e2e8f0;
        transform: translateY(-2px);
    }

    .method-tile.active {
        border-color: #2D5A88;
        background: #fcfdfe;
    }

    .method-indicator {
        width: 22px;
        height: 22px;
        border: 2px solid #e2e8f0;
        border-radius: 50%;
        position: relative;
    }

    .method-tile.active .method-indicator {
        border-color: #2D5A88;
        background: #2D5A88;
    }

    .method-tile.active .method-indicator::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 8px;
        height: 8px;
        background: #fff;
        border-radius: 50%;
    }

    .cursor-pointer {
        cursor: pointer;
    }
</style>

<script>
    let stripe = null;
    let elements = null;
    let currentStep = 1;
    let stripeInitialized = false;
    let paypalInitialized = false;
    let isInitializingStripe = false;
    let isInitializingPaypal = false;
    let stripeSubmitListenerAdded = false;

    function goToStep(step) {
        if (step === 2) {
            if (!validateBilling()) return;
        }

        // Clear any previous errors when moving between steps
        hideError();

        document.querySelectorAll('.payment-step').forEach(s => s.classList.add('d-none'));
        if (step === 1) document.getElementById('step-billing').classList.remove('d-none');
        if (step === 2) document.getElementById('step-payment').classList.remove('d-none');
        currentStep = step;
    }

    function validateBilling() {
        const fields = document.querySelectorAll('.checkout-field[required]');
        let valid = true;
        fields.forEach(f => {
            if (!f.value.trim()) {
                f.classList.add('is-invalid');
                f.style.borderColor = '#ef4444';
                valid = false;
            } else {
                f.classList.remove('is-invalid');
                f.style.borderColor = '#eef2f6';
            }
        });
        return valid;
    }

    function getFormData() {
        const params = new URLSearchParams();
        document.querySelectorAll('.checkout-field').forEach(f => {
            params.append(f.name, f.value);
        });
        return params;
    }

    // --- Stripe Logic ---
    async function initStripePayment() {
        if (stripeInitialized || isInitializingStripe) return activateTile('stripe');

        isInitializingStripe = true;
        hideError(); // Clear existing errors

        const mount = document.getElementById('stripe-element-mount');
        mount.classList.remove('d-none');
        activateTile('stripe');

        try {
            const response = await fetch('<?php echo APP_URL; ?>/portal/createPaymentIntent/<?php echo $template['token']; ?>', {
                method: 'POST',
                body: getFormData()
            });

            const data = await response.json();

            if (!response.ok || data.success === false) {
                throw new Error(data.message || 'Failed to initialize payment');
            }

            const { clientSecret, invoiceToken } = data;

            stripe = Stripe('<?php echo Security::escape($template['stripe_publishable_key']); ?>');
            elements = stripe.elements({ clientSecret });

            const paymentElement = elements.create('payment', {
                layout: 'tabs',
            });
            paymentElement.mount('#payment-element');

            stripeInitialized = true;
            isInitializingStripe = false;

            // Ensure event listener is only added once
            if (!stripeSubmitListenerAdded) {
                document.getElementById('submit-stripe').addEventListener('click', async () => {
                    const btn = document.getElementById('submit-stripe');
                    if (btn.disabled) return; // Prevent double submission

                    hideError();
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

                    const { error } = await stripe.confirmPayment({
                        elements,
                        confirmParams: {
                            return_url: "<?php echo APP_URL; ?>/portal/thankYou/" + invoiceToken,
                            payment_method_data: {
                                billing_details: {
                                    name: document.getElementsByName('name')[0]?.value,
                                    email: document.getElementsByName('email')[0]?.value,
                                }
                            }
                        }
                    });

                    if (error) {
                        showError(error.message);
                        btn.disabled = false;
                        btn.innerHTML = 'Pay <?php echo Branding::getCurrencySymbol() . number_format($template['amount'], 2); ?> Now';
                    }
                });
                stripeSubmitListenerAdded = true;
            }

        } catch (e) {
            isInitializingStripe = false;
            showError(e.message);
        }
    }

    // --- PayPal Logic ---
    function initPaypalPayment() {
        if (paypalInitialized || isInitializingPaypal) return activateTile('paypal');

        isInitializingPaypal = true;
        activateTile('paypal');
        hideError();

        document.getElementById('paypal-element-mount').classList.remove('d-none');

        paypal.Buttons({
            style: { layout: 'vertical', color: 'blue', shape: 'rect', label: 'pay' },
            createOrder: (data, actions) => {
                hideError();
                return actions.order.create({
                    purchase_units: [{
                        amount: { value: '<?php echo $template['amount']; ?>' },
                        description: 'Payment for <?php echo Security::escape($template['title']); ?>'
                    }]
                });
            },
            onApprove: (data, actions) => {
                const params = new URLSearchParams(getFormData());
                params.append('provider', 'paypal');
                params.append('transaction_id', data.orderID);
                params.append('amount', '<?php echo $template['amount']; ?>');

                return actions.order.capture().then(details => {
                    fetch('<?php echo APP_URL; ?>/portal/processTemplatePayment/<?php echo $template['token']; ?>', {
                        method: 'POST',
                        body: params
                    })
                        .then(res => res.json())
                        .then(res => {
                            if (res.success) {
                                window.location.href = '<?php echo APP_URL; ?>/portal/thankYou/' + res.invoice_token;
                            } else {
                                showError(res.message);
                            }
                        })
                        .catch(err => showError('Failed to record payment. Please contact support.'));
                });
            },
            onInit: () => {
                paypalInitialized = true;
                isInitializingPaypal = false;
            },
            onError: (err) => {
                isInitializingPaypal = false;
                showError('PayPal encountered an error. Please try again.');
            }
        }).render('#paypal-button-container');
    }

    function activateTile(method) {
        document.querySelectorAll('.method-tile').forEach(t => t.classList.remove('active'));
        document.getElementById('tile-' + method).classList.add('active');

        // Hide others elements
        if (method === 'stripe') {
            document.getElementById('stripe-element-mount').classList.remove('d-none');
            const pp = document.getElementById('paypal-element-mount');
            if (pp) pp.classList.add('d-none');
        } else {
            document.getElementById('paypal-element-mount').classList.remove('d-none');
            const st = document.getElementById('stripe-element-mount');
            if (st) st.classList.add('d-none');
        }
    }

    function showError(msg) {
        const err = document.getElementById('error-message');
        err.textContent = msg;
        err.classList.remove('d-none');
        err.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function hideError() {
        const err = document.getElementById('error-message');
        if (err) err.classList.add('d-none');
    }
</script>

<?php if (!$template['payment_closed']): ?>
    <?php if (!empty($template['stripe_publishable_key'])): ?>
        <script src="https://js.stripe.com/v3/"></script>
    <?php endif; ?>
    <?php if (!empty($template['paypal_client_id'])): ?>
        <script
            src="https://www.paypal.com/sdk/js?client-id=<?php echo Security::escape($template['paypal_client_id']); ?>&currency=USD"></script>
    <?php endif; ?>
<?php endif; ?>

<?php require_once APP_PATH . '/views/layouts/public_footer.php'; ?>