<?php
/**
 * Payment Success - Thank You Page
 * Displayed after successful Stripe or PayPal payment
 */
$pageTitle = 'Payment Successful - Thank You!';
require_once APP_PATH . '/views/layouts/public_header.php';
?>

<div class="thank-you-wrapper mx-auto mt-5 px-3" style="max-width: 680px;">
    <!-- Success Animation -->
    <div class="text-center mb-5">
        <div class="success-icon-wrapper mx-auto mb-4">
            <i class="bi bi-check-circle success-icon"></i>
        </div>
        <h1 class="display-5 fw-bold text-dark mb-2">Payment Successful!</h1>
        <p class="text-muted fs-5">Thank you for your payment. Your transaction has been completed.</p>
    </div>

    <!-- Transaction Summary Card -->
    <div class="card border-0 shadow-lg rounded-24 mb-4">
        <div class="card-body p-4 p-md-5">
            <h5 class="fw-bold text-dark mb-4">Transaction Summary</h5>

            <div class="summary-grid">
                <div class="summary-row">
                    <span class="summary-label">Invoice Number</span>
                    <span class="summary-value">
                        <?php echo Security::escape($invoice['invoice_number']); ?>
                    </span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Amount Paid</span>
                    <span class="summary-value fw-bold text-success">
                        <?php echo Branding::getCurrencySymbol() . number_format($invoice['amount'], 2); ?>
                    </span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Payment Method</span>
                    <span class="summary-value">
                        <?php
                        $paymentMethod = 'Card Payment';
                        if (!empty($payment)) {
                            $paymentMethod = $payment['payment_method'] ?? 'Card Payment';
                        }
                        echo Security::escape($paymentMethod);
                        ?>
                    </span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Date</span>
                    <span class="summary-value">
                        <?php echo date('M d, Y', strtotime($invoice['created_at'])); ?>
                    </span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Status</span>
                    <span class="summary-value">
                        <span class="badge bg-success rounded-pill px-3 py-2">
                            <i class="bi bi-check-circle-fill me-1"></i> PAID
                        </span>
                    </span>
                </div>
            </div>

            <?php if (!empty($invoice['client_name'])): ?>
                <div class="mt-4 pt-4 border-top">
                    <h6 class="fw-bold text-dark mb-3">Billing Information</h6>
                    <div class="text-muted small">
                        <div class="mb-1"><strong>Name:</strong>
                            <?php echo Security::escape($invoice['client_name']); ?>
                        </div>
                        <?php if (!empty($invoice['client_email'])): ?>
                            <div class="mb-1"><strong>Email:</strong>
                                <?php echo Security::escape($invoice['client_email']); ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($invoice['client_phone'])): ?>
                            <div class="mb-1"><strong>Phone:</strong>
                                <?php echo Security::escape($invoice['client_phone']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Action Button -->
    <div class="text-center mb-5">
        <a href="<?php echo APP_URL; ?>/portal/invoicePdf/<?php echo $invoice['payment_token']; ?>"
            class="btn btn-primary btn-lg rounded-12 px-5 py-3 shadow">
            <i class="bi bi-download me-2"></i> Download Invoice (PDF)
        </a>
    </div>

    <!-- Support Information Card -->
    <?php
    $supportEmail = $invoice['company_email'] ?? Branding::getEmail();
    $supportPhone = $invoice['company_phone'] ?? Branding::getPhone();
    ?>
    <?php if ($supportEmail || $supportPhone): ?>
        <div class="card border-0 shadow-sm rounded-24 mb-4"
            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body p-4 p-md-5 text-white text-center text-md-start">
                <div class="row align-items-center">
                    <div class="col-md-7 mb-4 mb-md-0">
                        <h4 class="fw-black mb-2 d-flex align-items-center justify-content-center justify-content-md-start">
                            <i class="bi bi-headset me-3 fs-3"></i> Need Assistance?
                        </h4>
                        <p class="mb-0 opacity-90 fs-6">Our dedicated support team is here to help you with any questions
                            regarding your invoice or payment.</p>
                    </div>
                    <div class="col-md-5">
                        <div class="d-flex flex-column gap-3">
                            <?php if ($supportEmail): ?>
                                <div class="d-flex align-items-center justify-content-center justify-content-md-start">
                                    <div class="icon-circle bg-white text-primary me-3 d-flex align-items-center justify-content-center"
                                        style="width: 40px; height: 40px; border-radius: 50%;">
                                        <i class="bi bi-envelope-fill"></i>
                                    </div>
                                    <div class="text-start">
                                        <div class="x-small text-uppercase fw-bold opacity-75"
                                            style="letter-spacing: 0.05em; font-size: 0.65rem;">Support Email</div>
                                        <a href="mailto:<?php echo Security::escape($supportEmail); ?>"
                                            class="text-white fw-bold text-decoration-none h6 mb-0"><?php echo Security::escape($supportEmail); ?></a>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($supportPhone): ?>
                                <div class="d-flex align-items-center justify-content-center justify-content-md-start">
                                    <div class="icon-circle bg-white text-primary me-3 d-flex align-items-center justify-content-center"
                                        style="width: 40px; height: 40px; border-radius: 50%;">
                                        <i class="bi bi-telephone-fill"></i>
                                    </div>
                                    <div class="text-start">
                                        <div class="x-small text-uppercase fw-bold opacity-75"
                                            style="letter-spacing: 0.05em; font-size: 0.65rem;">Support Phone</div>
                                        <a href="tel:<?php echo Security::escape($supportPhone); ?>"
                                            class="text-white fw-bold text-decoration-none h6 mb-0"><?php echo Security::escape($supportPhone); ?></a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Additional Info -->
    <div class="text-center text-muted small mb-5">
        <p class="mb-2">
            <i class="bi bi-envelope me-2"></i>
            A confirmation email with your invoice has been sent to
            <strong><?php echo Security::escape($invoice['client_email'] ?? 'your email'); ?></strong>
        </p>
        <p class="mb-0">
            Thank you for your business with <?php echo Security::escape(Branding::getCompanyName()); ?>
        </p>
    </div>
</div>

<style>
    :root {
        --success-green: #10B981;
        --success-green-light: #D1FAE5;
        --card-radius: 24px;
    }

    body {
        background-color: #F8FAFC !important;
        font-family: 'Inter', -apple-system, system-ui, sans-serif;
    }

    .thank-you-wrapper {
        padding-top: 40px;
        padding-bottom: 60px;
    }

    /* Success Icon */
    .success-icon-wrapper {
        width: 120px;
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        animation: fadeInScale 0.5s ease-out;
    }

    .success-icon {
        font-size: 64px;
        color: white;
    }

    @keyframes fadeInScale {
        0% {
            opacity: 0;
            transform: scale(0.5);
        }

        100% {
            opacity: 1;
            transform: scale(1);
        }
    }

    /* Summary Grid */
    .summary-grid {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #F1F5F9;
    }

    .summary-row:last-child {
        border-bottom: none;
    }

    .summary-label {
        color: #64748B;
        font-size: 0.95rem;
        font-weight: 500;
    }

    .summary-value {
        color: #1E293B;
        font-size: 1rem;
        text-align: right;
    }

    .rounded-12 {
        border-radius: 12px !important;
    }

    .rounded-24 {
        border-radius: 24px !important;
    }

    /* Responsive */
    @media (max-width: 576px) {
        .success-icon-wrapper {
            width: 100px;
            height: 100px;
        }

        .success-icon {
            font-size: 52px;
        }
    }
</style>

<?php require_once APP_PATH . '/views/layouts/public_footer.php'; ?>