<?php
/**
 * Premium Invoice Template
 * Design: High-end, Modern SaaS / Fintech
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo Security::escape($invoice['invoice_number']); ?></title>
    <!-- Modern Typography: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary-color: #0F172A; /* Deep Slate / Black */
            --accent-color: #2563EB;  /* Vibrant Blue */
            --text-main: #1E293B;
            --text-muted: #64748B;
            --bg-light: #F8FAFC;
            --border-color: #E2E8F0;
            --surface-white: #FFFFFF;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg-light);
            color: var(--text-main);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        .invoice-container {
            max-width: 850px;
            margin: 40px auto;
            padding: 60px;
            background-color: var(--surface-white);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            position: relative;
            overflow: hidden;
        }

        /* Top Accent Bar */
        .top-bar {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background-color: var(--accent-color);
        }

        /* Typography Utilities */
        .text-uppercase { text-transform: uppercase; }
        .ls-wide { letter-spacing: 0.05em; }
        .fw-semibold { font-weight: 600; }
        .fw-bold { font-weight: 700; }
        .fw-extrabold { font-weight: 800; }
        .text-muted { color: var(--text-muted); }
        .text-accent { color: var(--accent-color); }
        .small { font-size: 0.8125rem; }
        .xs { font-size: 0.75rem; }

        /* Layout Grid */
        .flex { display: flex; }
        .justify-between { justify-content: space-between; }
        .items-start { align-items: flex-start; }
        .items-end { align-items: flex-end; }
        .text-right { text-align: right; }

        header {
            margin-bottom: 60px;
        }

        .company-info .logo {
            max-height: 50px;
            margin-bottom: 12px;
            filter: grayscale(100%) contrast(150%);
        }

        .company-info h2 {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 4px;
        }

        .company-info .slogan {
            font-size: 0.875rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .invoice-meta h1 {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary-color);
            line-height: 1;
            margin-bottom: 16px;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .meta-item label {
            display: block;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--text-muted);
            margin-bottom: 4px;
        }

        .meta-item span {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        /* Client Section */
        .client-section {
            background-color: var(--bg-light);
            padding: 32px;
            border-radius: 6px;
            margin-bottom: 48px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        .client-info h3 {
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--accent-color);
            margin-bottom: 12px;
        }

        .client-card .name {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 8px;
        }

        .client-card .details {
            font-size: 0.875rem;
            color: var(--text-muted);
            line-height: 1.6;
        }

        /* Invoice Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 48px;
        }

        th {
            text-align: left;
            padding: 16px 0;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--text-muted);
            border-bottom: 2px solid var(--primary-color);
        }

        td {
            padding: 24px 0;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        .col-description { width: 50%; }
        .col-qty { width: 10%; text-align: center; }
        .col-price { width: 20%; text-align: right; }
        .col-total { width: 20%; text-align: right; }

        .item-title {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 4px;
        }

        .item-desc {
            font-size: 0.8125rem;
            color: var(--text-muted);
        }

        .amount-cell {
            font-size: 0.9375rem;
            font-weight: 500;
            color: var(--text-main);
        }

        .total-cell {
            font-size: 0.9375rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        /* Totals Section */
        .totals-wrapper {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 60px;
        }

        .totals-container {
            width: 300px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 0.875rem;
        }

        .summary-row.final {
            margin-top: 16px;
            padding: 20px 0;
            border-top: 2px solid var(--primary-color);
        }

        .summary-row.final label {
            font-size: 1rem;
            font-weight: 800;
            color: var(--primary-color);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .summary-row.final .amount {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--accent-color);
        }

        /* Footer */
        footer {
            border-top: 1px solid var(--border-color);
            padding-top: 40px;
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 60px;
        }

        .payment-methods h4 {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--primary-color);
            margin-bottom: 16px;
        }

        .payment-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .payment-box {
            font-size: 0.75rem;
            color: var(--text-muted);
            line-height: 1.4;
        }

        .payment-box strong {
            display: block;
            color: var(--text-main);
            margin-bottom: 2px;
        }

        .thank-you {
            text-align: right;
        }

        .thank-you p {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 8px;
        }

        .legal-note {
            font-size: 0.6875rem;
            color: var(--text-muted);
            line-height: 1.5;
        }

        /* Print Optimization */
        @media print {
            body { background: white; }
            .invoice-container {
                margin: 0;
                padding: 40px;
                box-shadow: none;
                width: 100%;
                max-width: 100%;
            }
            .top-bar { display: block !important; -webkit-print-color-adjust: exact; }
            .summary-row.final .amount { color: #000 !important; }
            .no-print { display: none !important; }
            @page { margin: 1cm; }
        }
    </style>
</head>
<body>

    <div class="invoice-container">
        <div class="top-bar"></div>

        <header class="flex justify-between items-start">
            <div class="company-info">
                <?php if (Branding::hasLogo()): ?>
                    <img src="<?php echo Branding::getLogoUrl(); ?>" alt="Logo" class="logo">
                <?php else: ?>
                    <div style="height: 50px; width: 50px; background: var(--primary-color); border-radius: 4px; margin-bottom: 12px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 800;">
                        <?php echo substr(Branding::getCompanyName(), 0, 1); ?>
                    </div>
                <?php endif; ?>
                <h2><?php echo Branding::getCompanyName(); ?></h2>
                <div class="slogan">Premium Financial Operations</div>
                <div class="small text-muted" style="margin-top: 12px; line-height: 1.6;">
                    <?php echo nl2br(Security::escape(Branding::getAddress())); ?><br>
                    <?php if (Branding::getPhone()) echo Security::escape(Branding::getPhone()) . ' • '; ?>
                    <?php echo Security::escape(Branding::getEmail()); ?>
                </div>
            </div>

            <div class="invoice-meta text-right">
                <h1 class="text-uppercase ls-wide">Invoice</h1>
                <div class="meta-grid">
                    <div class="meta-item">
                        <label>No.</label>
                        <span>#<?php echo Security::escape($invoice['invoice_number']); ?></span>
                    </div>
                    <div class="meta-item">
                        <label>Issue Date</label>
                        <span><?php echo date('M d, Y', strtotime($invoice['created_at'])); ?></span>
                    </div>
                    <div class="meta-item">
                        <label>Due Date</label>
                        <span><?php echo date('M d, Y', strtotime($invoice['created_at'] . ' + 15 days')); ?></span>
                    </div>
                    <div class="meta-item">
                        <label>Status</label>
                        <span class="text-accent"><?php echo strtoupper($invoice['status']); ?></span>
                    </div>
                </div>
            </div>
        </header>

        <section class="client-section">
            <div class="client-info">
                <h3>Bill To</h3>
                <div class="client-card">
                    <div class="name"><?php echo Security::escape($invoice['client_name']); ?></div>
                    <div class="details">
                        <?php if (!empty($invoice['client_email'])): ?>
                            <i class="bi bi-envelope xs"></i> <?php echo Security::escape($invoice['client_email']); ?><br>
                        <?php endif; ?>
                        <?php if (!empty($invoice['client_phone'])): ?>
                            <i class="bi bi-telephone xs"></i> <?php echo Security::escape($invoice['client_phone']); ?><br>
                        <?php endif; ?>
                        <?php if (!empty($invoice['client_company'])): ?>
                            <i class="bi bi-building xs"></i> <?php echo Security::escape($invoice['client_company']); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="client-info" style="border-left: 1px solid var(--border-color); padding-left: 40px;">
                <h3>Digital signature</h3>
                <div class="flex items-center">
                    <img src="https://chart.googleapis.com/chart?chs=80x80&cht=qr&chl=<?php echo urlencode(APP_URL . '/portal/invoice/' . $invoice['payment_token']); ?>"
                         alt="QR" style="border-radius: 4px; border: 1px solid var(--border-color); padding: 4px; background: white;">
                    <div class="small text-muted" style="margin-left: 16px; font-style: italic;">
                        Scan to verify and settle this invoice instantly via our secure merchant portal.
                    </div>
                </div>
            </div>
        </section>

        <table>
            <thead>
                <tr>
                    <th class="col-description">Service / Description</th>
                    <th class="col-qty">QTY</th>
                    <th class="col-price">Unit Price</th>
                    <th class="col-total">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="item-title">
                            <?php echo !empty($invoice['deal_title']) ? Security::escape($invoice['deal_title']) : 'Professional Services'; ?>
                        </div>
                        <div class="item-desc">
                            Standard billing for technical engagement #<?php echo $invoice['id']; ?>.
                        </div>
                    </td>
                    <td class="col-qty amount-cell">1</td>
                    <td class="col-price amount-cell"><?php echo CURRENCY_SYMBOL . number_format($invoice['amount'], 2); ?></td>
                    <td class="col-total total-cell"><?php echo CURRENCY_SYMBOL . number_format($invoice['amount'], 2); ?></td>
                </tr>
                <!-- Dynamic items can be looped here if available -->
            </tbody>
        </table>

        <div class="totals-wrapper">
            <div class="totals-container">
                <div class="summary-row">
                    <label class="text-muted fw-semibold">Subtotal</label>
                    <span class="fw-bold"><?php echo CURRENCY_SYMBOL . number_format($invoice['amount'], 2); ?></span>
                </div>
                <div class="summary-row">
                    <label class="text-muted fw-semibold">Tax (0%)</label>
                    <span class="text-muted"><?php echo CURRENCY_SYMBOL; ?>0.00</span>
                </div>
                <div class="summary-row final">
                    <label>Amount Due</label>
                    <span class="amount"><?php echo CURRENCY_SYMBOL . number_format($invoice['amount'], 2); ?></span>
                </div>
            </div>
        </div>

        <footer>
            <div class="payment-methods">
                <h4>Payment Instructions</h4>
                <div class="payment-grid">
                    <div class="payment-box">
                        <strong>Bank Transfer</strong>
                        IBAN: DE89 1234 5678 9012 3456 00<br>
                        BIC: GLOBALBANK88
                    </div>
                    <div class="payment-box">
                        <strong>Digital Assets</strong>
                        BTC: 1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa<br>
                        ETH: 0x742d35Cc6634C0532925a3b844Bc454e4438f44e
                    </div>
                </div>
            </div>
            <div class="thank-you">
                <p>Thank you for your business.</p>
                <div class="legal-note">
                    Registered in the Commercial Register. All prices are net. Payment is due within 15 days from the date of issue.
                </div>
            </div>
        </footer>
    </div>

    <!-- Print Button for Preview -->
    <div class="no-print" style="position: fixed; bottom: 20px; right: 20px; display: flex; gap: 10px;">
        <button onclick="window.print()" style="background: var(--primary-color); color: white; border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer; font-weight: 600; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <i class="bi bi-printer"></i> Print / Save as PDF
        </button>
    </div>

</body>
</html>
