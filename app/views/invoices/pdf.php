<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo Security::escape($invoice['invoice_number']); ?></title>
    <style>
        /* Modern Corporate PDF Styling - Optimized for Paging */
        :root {
            --invoice-blue: #2D5A88;
            --invoice-light-blue: #F8FAFC;
            --invoice-text-dark: #1E293B;
            --invoice-text-muted: #64748B;
        }

        @page {
            size: A4;
            margin: 15mm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            background-color: white;
            color: #1E293B;
            margin: 0;
            padding: 0;
            line-height: 1.4;
            font-size: 10pt;
            overflow: visible;
        }

        .blue-top-border {
            height: 6pt;
            background-color: var(--invoice-blue);
            width: 100%;
            position: fixed;
            top: 0;
            left: 0;
        }

        .sheet-content {
            padding-top: 20pt;
            position: relative;
            overflow: visible;
        }

        .corporate-title {
            color: var(--invoice-blue);
            font-weight: bold;
            font-size: 28pt;
            margin: 0;
        }

        .logo-box {
            text-align: right;
        }

        .invoice-logo {
            max-height: 60pt;
            /* Increased from 40pt */
        }

        .clear {
            clear: both;
        }

        .header-section {
            margin-bottom: 25pt;
            overflow: visible;
            display: block;
        }

        .header-left {
            float: left;
            width: 55%;
        }

        .header-right {
            float: right;
            width: 45%;
            text-align: right;
        }

        .company-details {
            margin-bottom: 15pt;
        }

        .company-name {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 2pt;
        }

        .meta-details {
            font-size: 9pt;
        }

        .meta-row {
            margin-bottom: 2pt;
        }

        .meta-row b {
            display: inline-block;
            width: 70pt;
            color: #1E293B;
        }

        .meta-row span {
            color: #64748B;
        }

        .client-section {
            margin-top: 15pt;
            margin-bottom: 25pt;
        }

        .client-name {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 2pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20pt;
            table-layout: fixed;
        }

        th {
            background-color: var(--invoice-blue);
            color: white;
            padding: 10pt;
            text-align: left;
            font-size: 8pt;
            text-transform: uppercase;
        }

        td {
            padding: 12pt 10pt;
            border-bottom: 1px solid #F1F5F9;
            font-size: 9pt;
            word-wrap: break-word;
        }

        tr {
            page-break-inside: avoid;
        }

        tr:nth-child(even) {
            background-color: var(--invoice-light-blue);
        }

        .totals-section {
            float: right;
            width: 200pt;
            margin-top: 5pt;
            page-break-inside: avoid;
        }

        .total-row {
            padding: 3pt 0;
            font-size: 9pt;
        }

        .total-row b {
            float: left;
        }

        .total-row span {
            float: right;
        }

        .grand-total-row {
            background-color: var(--invoice-light-blue);
            padding: 8pt;
            border-top: 2pt solid var(--invoice-blue);
            font-weight: bold;
            margin-top: 4pt;
        }

        .grand-total-row b {
            font-size: 10pt;
            text-transform: uppercase;
        }

        .grand-total-row span {
            font-size: 12pt;
            color: #1E293B;
        }

        .paid-stamp {
            position: absolute;
            top: 100pt;
            right: 40pt;
            border: 3pt solid var(--invoice-blue);
            padding: 4pt 10pt;
            color: var(--invoice-blue);
            font-weight: bold;
            font-size: 18pt;
            text-transform: uppercase;
            transform: rotate(-10deg);
            border-radius: 4pt;
            opacity: 0.5;
            z-index: 100;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-muted {
            color: #64748B;
        }
    </style>
</head>

<body>

    <div class="blue-top-border"></div>

    <div class="sheet-content">
        <!-- Header -->
        <div class="header-section">
            <div class="header-left">
                <h1 class="corporate-title">INVOICE</h1>
                <div style="font-weight: bold; font-size: 14pt; color: #1E293B; margin-top: 5pt;">
                    <?php echo Branding::getCompanyName(); ?>
                </div>
            </div>
            <div class="header-right logo-box">
                <?php if (Branding::hasLogo()): ?>
                    <img src="<?php echo Branding::getLogoUrl(); ?>" class="invoice-logo">
                <?php else: ?>
                    <div
                        style="width: 50pt; height: 50pt; background: var(--invoice-blue); border-radius: 8pt; float: right; margin-top: 5pt; display: flex; align-items: center; justify-content: center; color: white; font-size: 20pt; font-weight: bold;">
                        <?php echo substr(Branding::getCompanyName(), 0, 1); ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="clear"></div>
        </div>

        <div class="header-section" style="margin-top: 10pt;">
            <div class="header-left">
                <div class="text-muted" style="font-size: 9pt;">
                    <?php echo nl2br(Security::escape(Branding::getAddress())); ?><br>
                    <?php if (Branding::getPhone())
                        echo Security::escape(Branding::getPhone()) . '<br>'; ?>
                    <?php echo Security::escape(Branding::getEmail()); ?>
                </div>
            </div>
            <div class="header-right meta-details">
                <div class="meta-row"><b>Invoice #:</b>
                    <span><?php echo Security::escape($invoice['invoice_number']); ?></span>
                </div>
                <div class="meta-row"><b>Date:</b>
                    <span><?php echo date('M d, Y', strtotime($invoice['created_at'])); ?></span>
                </div>
                <div class="meta-row"><b>Due Date:</b>
                    <span><?php echo date('M d, Y', strtotime($invoice['created_at'] . ' + 15 days')); ?></span>
                </div>
                <div class="meta-row" style="margin-top: 5pt;"><b>Amount Due:</b> <span
                        style="font-weight: bold; color: #1E293B;"><?php echo Branding::getCurrencySymbol() . number_format($invoice['amount'], 2); ?></span>
                </div>
            </div>
            <div class="clear"></div>
        </div>

        <div class="client-section">
            <div class="client-name"><?php echo Security::escape($invoice['client_name']); ?></div>
            <div class="text-muted" style="font-size: 9pt;">
                <?php if (!empty($invoice['client_email'])): ?>
                    <?php echo Security::escape($invoice['client_email']); ?><br>
                <?php endif; ?>
                <?php if (!empty($invoice['client_phone'])): ?>
                    <?php echo Security::escape($invoice['client_phone']); ?><br>
                <?php endif; ?>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 50%;">Description</th>
                    <th style="width: 10%; text-align: center;">Qty</th>
                    <th style="width: 20%; text-align: right;">Unit Price</th>
                    <th style="width: 20%; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <b><?php echo !empty($invoice['deal_title']) ? Security::escape($invoice['deal_title']) : 'Professional Services'; ?></b><br>
                        <span class="text-muted" style="font-size: 8pt;">Standard billing ref
                            #<?php echo $invoice['id']; ?></span>
                    </td>
                    <td class="text-center">1</td>
                    <td class="text-right">
                        <?php echo Branding::getCurrencySymbol() . number_format($invoice['amount'], 2); ?>
                    </td>
                    <td class="text-right">
                        <?php echo Branding::getCurrencySymbol() . number_format($invoice['amount'], 2); ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="totals-section">
            <div class="total-row"><b>Subtotal</b>
                <span><?php echo Branding::getCurrencySymbol() . number_format($invoice['amount'], 2); ?></span>
                <div class="clear"></div>
            </div>
            <div class="total-row"><b>Tax</b> <span><?php echo Branding::getCurrencySymbol(); ?>0.00</span>
                <div class="clear"></div>
            </div>
            <div class="grand-total-row">
                <b>Total Due:</b>
                <span><?php echo Branding::getCurrencySymbol() . number_format($invoice['amount'], 2); ?></span>
                <div class="clear"></div>
            </div>
        </div>

        <?php if ($invoice['status'] === 'paid'): ?>
            <div class="paid-stamp">PAID</div>
        <?php endif; ?>
    </div>

</body>

</html>