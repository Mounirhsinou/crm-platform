<?php
/**
 * View Invoice Page - Modern Corporate Design
 * Replicates the design from user's provided image
 */
$pageTitle = 'View Invoice #' . Security::escape($invoice['invoice_number']);
require_once APP_PATH . '/views/layouts/header.php';
?>

<div class="container-fluid py-4">
    <!-- Action Header (Preserved) -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Invoice #<?php echo Security::escape($invoice['invoice_number']); ?></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/invoices">Invoices</a></li>
                    <li class="breadcrumb-item active">View Details</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                <i class="bi bi-printer me-1"></i> Print
            </button>
            <a href="<?php echo APP_URL; ?>/invoices/pdf/<?php echo $invoice['id']; ?>"
                class="btn btn-outline-danger btn-sm rounded-pill px-3">
                <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
            </a>
            <button onclick="copyPublicLink()" class="btn btn-outline-info btn-sm rounded-pill px-3">
                <i class="bi bi-link-45deg me-1"></i> Copy Public Link
            </button>
            <button onclick="sendInvoiceEmail(<?php echo $invoice['id']; ?>)" id="btn-send-email"
                class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="bi bi-send me-1"></i> Send Email
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-9 mx-auto">
            <!-- Corporate Invoice Sheet -->
            <div class="corporate-invoice-sheet shadow-sm mb-5" id="printable-area">
                <div class="blue-top-border"></div>

                <div class="sheet-content">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-5">
                        <div class="invoice-title-box">
                            <h1 class="corporate-title">INVOICE</h1>
                            <div class="mt-2">
                                <h2 class="h4 fw-bold text-dark mb-0"><?php echo Branding::getCompanyName(); ?></h2>
                            </div>
                        </div>
                        <div class="logo-box">
                            <?php if (Branding::hasLogo()): ?>
                                <img src="<?php echo Branding::getLogoUrl(); ?>" alt="Logo" class="invoice-logo">
                            <?php else: ?>
                                <div class="invoice-logo-placeholder">
                                    <?php echo substr(Branding::getCompanyName(), 0, 1); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Company & Meta Info -->
                    <div class="row mb-5">
                        <div class="col-6">
                            <div class="company-details">
                                <div class="text-dark small">
                                    <?php echo nl2br(Security::escape(Branding::getAddress())); ?><br>
                                    <?php if (Branding::getPhone()): ?>
                                        <span class="text-uppercase fw-bold x-small"
                                            style="color: var(--invoice-text-dark); opacity: 0.9;">Number:</span>
                                        <?php echo Security::escape(Branding::getPhone()); ?><br>
                                    <?php endif; ?>
                                    <span class="text-uppercase fw-bold x-small"
                                        style="color: var(--invoice-text-dark); opacity: 0.9;">Email:</span>
                                    <?php echo Security::escape(Branding::getEmail()); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 text-end pe-5">
                            <div class="meta-details pt-5">
                                <div class="meta-row">
                                    <label>Invoice #:</label>
                                    <span><?php echo Security::escape($invoice['invoice_number']); ?></span>
                                </div>
                                <div class="meta-row">
                                    <label>Date:</label>
                                    <span><?php echo date('M d, Y', strtotime($invoice['created_at'])); ?></span>
                                </div>
                                <div class="meta-row">
                                    <label>Due Date:</label>
                                    <span><?php echo date('M d, Y', strtotime($invoice['created_at'] . ' + 15 days')); ?></span>
                                </div>
                                <div class="meta-row mt-2">
                                    <label class="text-primary">Amount Due:</label>
                                    <span
                                        class="fw-bold text-dark"><?php echo Branding::getCurrencySymbol() . number_format($invoice['amount'], 2); ?></span>
                                </div>
                                <?php if ($invoice['status'] === 'paid'): ?>
                                    <div class="meta-row mt-2">
                                        <div class="text-success fw-bold p-1 px-2 border border-success rounded-pill d-inline-block small"
                                            style="letter-spacing: 1px;">
                                            <i class="bi bi-check-circle-fill me-1"></i> PAID
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Client Info -->
                    <div class="row mb-5">
                        <div class="col-6">
                            <div class="client-details">
                                <h4 class="fw-bold fs-6 mb-1"><?php echo Security::escape($invoice['client_name']); ?>
                                </h4>
                                <div class="text-muted small">
                                    <?php if (!empty($invoice['client_email'])): ?>
                                        <span class="text-uppercase fw-bold x-small opacity-75">Email:</span>
                                        <?php echo Security::escape($invoice['client_email']); ?><br>
                                    <?php endif; ?>
                                    <?php if (!empty($invoice['client_phone'])): ?>
                                        <span class="text-uppercase fw-bold x-small opacity-75">Number:</span>
                                        <?php echo Security::escape($invoice['client_phone']); ?><br>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="table-responsive mb-4">
                        <table class="corporate-table">
                            <thead>
                                <tr>
                                    <th class="col-desc">Description</th>
                                    <th class="col-qty text-center">Quantity</th>
                                    <th class="col-price text-end">Unit Price</th>
                                    <th class="col-total text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($invoice['items'])): ?>
                                    <?php foreach ($invoice['items'] as $item): ?>
                                        <tr>
                                            <td class="col-desc">
                                                <div class="fw-bold mb-0"><?php echo Security::escape($item['description']); ?>
                                                </div>
                                            </td>
                                            <td class="col-qty text-center"><?php echo $item['quantity']; ?></td>
                                            <td class="col-price text-end">
                                                <?php echo CURRENCY_SYMBOL; ?>        <?php echo number_format($item['price'], 2); ?>
                                            </td>
                                            <td class="col-total text-end">
                                                <?php echo CURRENCY_SYMBOL; ?>        <?php echo number_format($item['total'], 2); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td class="col-desc">
                                            <div class="fw-bold mb-0">Professional Services</div>
                                        </td>
                                        <td class="col-qty text-center">1</td>
                                        <td class="col-price text-end">
                                            <?php echo CURRENCY_SYMBOL; ?>    <?php echo number_format($invoice['amount'], 2); ?>
                                        </td>
                                        <td class="col-total text-end">
                                            <?php echo CURRENCY_SYMBOL; ?>    <?php echo number_format($invoice['amount'], 2); ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals Area -->
                    <div class="row justify-content-end mb-5">
                        <div class="col-md-5">
                            <div class="totals-calculation">
                                <div class="total-row">
                                    <label>Subtotal</label>
                                    <span><?php echo Branding::getCurrencySymbol() . number_format($invoice['amount'], 2); ?></span>
                                </div>
                                <div class="total-row">
                                    <label>Tax</label>
                                    <span><?php echo Branding::getCurrencySymbol(); ?>0.00</span>
                                </div>
                                <div class="total-row grand-total-row mt-2">
                                    <label>Total Due:</label>
                                    <span><?php echo Branding::getCurrencySymbol() . number_format($invoice['amount'], 2); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Stamp -->
                    <?php if ($invoice['status'] === 'paid'): ?>
                        <div class="paid-stamp">
                            <span>PAID</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Transaction Audit Log (Preserved) -->
            <div class="card border-0 shadow-sm mb-5 no-print">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Transaction Fulfillment Log</h6>
                </div>
                <div class="card-body">
                    <div class="timeline-small">
                        <div class="timeline-item">
                            <div class="timeline-point"></div>
                            <div class="timeline-content">
                                <span
                                    class="text-muted small"><?php echo date('M d, Y H:i', strtotime($invoice['created_at'])); ?></span>
                                <p class="mb-0 small fw-bold">Statement Generated</p>
                            </div>
                        </div>
                        <?php if ($invoice['status'] === 'paid'): ?>
                            <div class="timeline-item">
                                <div class="timeline-point bg-success"></div>
                                <div class="timeline-content">
                                    <span
                                        class="text-muted small"><?php echo date('M d, Y H:i', strtotime($invoice['updated_at'])); ?></span>
                                    <p class="mb-0 small fw-bold text-success">Document Fully Settled</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Modern Corporate Invoice Styling */
    :root {
        --invoice-blue: #2D5A88;
        --invoice-light-blue: #F8FAFC;
        --invoice-border: #E2E8F0;
        --invoice-text-dark: #1E293B;
        --invoice-text-muted: #64748B;
    }

    .corporate-invoice-sheet {
        background: #fff;
        border: 1px solid var(--invoice-border);
        position: relative;
        color: var(--invoice-text-dark);
        font-family: 'Inter', -apple-system, system-ui, sans-serif;
        min-height: 297mm;
        /* Standard A4 height */
        display: flex;
        flex-direction: column;
    }

    .blue-top-border {
        height: 6px;
        background-color: var(--invoice-blue);
        width: 100%;
    }

    .sheet-content {
        padding: 60px;
        position: relative;
    }

    .corporate-title {
        color: var(--invoice-blue);
        font-weight: 800;
        font-size: 2.8rem;
        margin: 0;
        letter-spacing: -1px;
    }

    .invoice-logo {
        max-height: 70px;
        width: auto;
        object-fit: contain;
        margin-top: 15px;
    }

    .invoice-logo-placeholder {
        width: 70px;
        height: 70px;
        background-color: var(--invoice-blue);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2.2rem;
        margin-top: 15px;
    }

    .meta-row {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-bottom: 2px;
        font-size: 0.9rem;
    }

    .meta-row label {
        font-weight: 600;
        color: var(--invoice-text-dark);
    }

    .meta-row span {
        color: var(--invoice-text-muted);
        min-width: 100px;
    }

    .corporate-table {
        width: 100%;
        border-collapse: collapse;
    }

    .corporate-table th {
        background-color: var(--invoice-blue);
        color: white;
        padding: 12px 15px;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        border: none;
    }

    .corporate-table td {
        padding: 20px 15px;
        border-bottom: 1px solid #F1F5F9;
        font-size: 0.95rem;
    }

    .corporate-table tr:nth-child(even) {
        background-color: var(--invoice-light-blue);
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        padding: 5px 0;
        font-size: 0.95rem;
    }

    .grand-total-row {
        background-color: var(--invoice-light-blue);
        padding: 12px 15px;
        border-top: 2px solid var(--invoice-blue);
    }

    .grand-total-row label {
        font-weight: 800;
        text-transform: uppercase;
        color: var(--invoice-text-dark);
    }

    .grand-total-row span {
        font-weight: 800;
        font-size: 1.25rem;
        color: var(--invoice-text-dark);
    }

    .paid-stamp {
        display: none;
    }

    /* Timeline Styling */
    .timeline-small {
        padding-left: 20px;
        border-left: 2px solid #E2E8F0;
        position: relative;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 20px;
    }

    .timeline-point {
        position: absolute;
        left: -27px;
        top: 0;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #CBD5E1;
        border: 2px solid #fff;
    }

    .x-small {
        font-size: 0.75rem;
    }

    @media print {
        @page {
            size: A4;
            margin: 10mm;
        }

        body {
            background: white !important;
            margin: 0;
            padding: 0 !important;
        }

        .no-print,
        nav,
        .btn,
        .breadcrumb {
            display: none !important;
        }

        .container-fluid,
        .row,
        .col-lg-9 {
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            display: block !important;
        }

        .corporate-invoice-sheet {
            border: none !important;
            box-shadow: none !important;
            width: 100% !important;
            height: auto !important;
            min-height: auto !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: visible !important;
            display: block !important;
        }

        .sheet-content {
            padding: 10mm !important;
            overflow: visible !important;
        }

        .paid-stamp {
            position: absolute !important;
            top: 50mm !important;
            right: 15mm !important;
            z-index: 1000 !important;
        }

        table,
        tr,
        td,
        th {
            page-break-inside: avoid !important;
        }

        /* Ensure totals don't get separated from the bottom of the table */
        .totals-calculation {
            page-break-inside: avoid !important;
        }
    }
</style>

<script>
    function copyPublicLink() {
        const link = '<?php echo APP_URL; ?>/portal/invoice/<?php echo $invoice['payment_token']; ?>';
        navigator.clipboard.writeText(link).then(() => {
            alert('Public link copied to clipboard!');
        });
    }

    function sendInvoiceEmail(id) {
        const btn = document.getElementById('btn-send-email');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending...';

        fetch('<?php echo APP_URL; ?>/invoices/send/' + id, {
            method: 'POST'
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Invoice sent successfully!');
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('An error occurred. Please check SMTP settings.');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-send me-1"></i> Send Email';
            });
    }
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>