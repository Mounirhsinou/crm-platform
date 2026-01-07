<?php

/**
 * Invoice Mailer Helper
 * Handles PDF generation and email dispatch
 */

require_once APP_PATH . '/helpers/FPDF/fpdf.php';
require_once APP_PATH . '/helpers/PHPMailer/Exception.php';
require_once APP_PATH . '/helpers/PHPMailer/PHPMailer.php';
require_once APP_PATH . '/helpers/PHPMailer/SMTP.php';

class InvoiceMailer
{
    /**
     * Generate Invoice PDF and return the raw string
     */
    public static function generatePdf($invoice, $company)
    {
        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true, 15);

        // --- Colors (Modern Corporate) ---
        $corporateBlue = [45, 90, 136]; // #2D5A88
        $lightBg = [248, 250, 252];     // #F8FAFC
        $textDark = [30, 41, 59];       // #1E293B
        $textMuted = [100, 116, 139];   // #64748B

        // --- Top Decorative Bar ---
        $pdf->SetFillColor($corporateBlue[0], $corporateBlue[1], $corporateBlue[2]);
        $pdf->Rect(0, 0, 210, 4, 'F');

        // --- Header Section ---
        $pdf->SetY(15);
        $pdf->SetFont('Helvetica', 'B', 32);
        $pdf->SetTextColor($corporateBlue[0], $corporateBlue[1], $corporateBlue[2]);
        $pdf->Cell(100, 15, 'INVOICE', 0, 0, 'L');

        // Logo on the right
        $logoPath = null;
        if (!empty($company['logo_path'])) {
            $logoPath = PUBLIC_PATH . $company['logo_path'];
            if (!file_exists($logoPath))
                $logoPath = null;
        }

        if ($logoPath) {
            $pdf->Image($logoPath, 165, 20, 30);
        } else {
            // Branded square placeholder
            $pdf->SetY(20);
            $pdf->SetX(175);
            $pdf->SetFillColor($corporateBlue[0], $corporateBlue[1], $corporateBlue[2]);
            $pdf->Rect(175, 20, 18, 18, 'F');
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('Helvetica', 'B', 16);
            $pdf->Text(180, 32, substr($company['name'], 0, 1));
        }

        $pdf->Ln(15);

        // --- Company Name ---
        $pdf->SetX(10);
        $pdf->SetFont('Helvetica', 'B', 14);
        $pdf->SetTextColor($textDark[0], $textDark[1], $textDark[2]);
        $pdf->Cell(100, 10, strtoupper($company['name']), 0, 1, 'L');

        $pdf->Ln(5);

        // --- Metadata Info Grid ---
        $startY = $pdf->GetY();

        // Left Side: Address
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetTextColor($textDark[0], $textDark[1], $textDark[2]);
        $addressLines = explode("\n", str_replace("\r", "", $company['address']));
        foreach ($addressLines as $line) {
            $pdf->Cell(90, 4, trim($line), 0, 1, 'L');
        }
        if (!empty($company['phone'])) {
            $pdf->SetFont('Helvetica', 'B', 8);
            $pdf->Cell(15, 4, 'NUMBER: ', 0, 0, 'L');
            $pdf->SetFont('Helvetica', '', 9);
            $pdf->Cell(75, 4, $company['phone'], 0, 1, 'L');
        }
        if (!empty($company['email'])) {
            $pdf->SetFont('Helvetica', 'B', 8);
            $pdf->Cell(15, 4, 'EMAIL: ', 0, 0, 'L');
            $pdf->SetFont('Helvetica', '', 9);
            $pdf->Cell(75, 4, $company['email'], 0, 1, 'L');
        }

        // Right Side: Metadata
        $pdf->SetXY(110, $startY + 8);
        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->SetTextColor($textDark[0], $textDark[1], $textDark[2]);

        $metadata = [
            'Invoice #:' => $invoice['invoice_number'],
            'Date:' => date('M d, Y', strtotime($invoice['created_at'])),
            'Due Date:' => date('M d, Y', strtotime($invoice['created_at'] . ' + 15 days')),
        ];

        foreach ($metadata as $label => $value) {
            $pdf->SetX(120);
            $pdf->SetFont('Helvetica', 'B', 9);
            $pdf->Cell(35, 6, $label, 0, 0, 'L');
            $pdf->SetFont('Helvetica', '', 9);
            $pdf->Cell(40, 6, $value, 0, 1, 'R');
        }

        $symbol = CURRENCY_SYMBOL; // Standard global currency constant
        $pdf->SetX(120);
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetTextColor($corporateBlue[0], $corporateBlue[1], $corporateBlue[2]);
        $pdf->Cell(35, 8, 'Amount Due:', 0, 0, 'L');
        $pdf->Cell(40, 8, $symbol . number_format($invoice['amount'], 2), 0, 1, 'R');

        // PAID Status below Amount Due
        if ($invoice['status'] === 'paid') {
            $pdf->SetX(120);
            $pdf->SetFont('Helvetica', 'B', 9);
            $pdf->SetTextColor(40, 167, 69); // Green (#28a745)
            $pdf->Cell(75, 8, 'STATUS: PAID', 0, 1, 'R');
        }

        $pdf->Ln(15);

        // --- Bill To ---
        $pdf->SetFont('Helvetica', 'B', 8);
        $pdf->SetTextColor($textMuted[0], $textMuted[1], $textMuted[2]);
        $pdf->Cell(100, 5, 'BILL TO', 0, 1);

        $pdf->SetFont('Helvetica', 'B', 11);
        $pdf->SetTextColor($textDark[0], $textDark[1], $textDark[2]);
        $pdf->Cell(100, 7, Security::escape($invoice['client_name']), 0, 1);

        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetTextColor($textMuted[0], $textMuted[1], $textMuted[2]);
        if (!empty($invoice['client_email'])) {
            $pdf->SetFont('Helvetica', 'B', 8);
            $pdf->Cell(15, 5, 'EMAIL: ', 0, 0);
            $pdf->SetFont('Helvetica', '', 9);
            $pdf->Cell(85, 5, $invoice['client_email'], 0, 1);
        }
        if (!empty($invoice['client_phone'])) {
            $pdf->SetFont('Helvetica', 'B', 8);
            $pdf->Cell(15, 5, 'NUMBER: ', 0, 0);
            $pdf->SetFont('Helvetica', '', 9);
            $pdf->Cell(85, 5, $invoice['client_phone'], 0, 1);
        }

        $pdf->Ln(10);

        // --- Table Header ---
        $pdf->SetFillColor($corporateBlue[0], $corporateBlue[1], $corporateBlue[2]);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->Cell(140, 10, '  DESCRIPTION', 0, 0, 'L', true);
        $pdf->Cell(50, 10, 'TOTAL  ', 0, 1, 'R', true);

        // --- Item Rows ---
        $pdf->SetFillColor($lightBg[0], $lightBg[1], $lightBg[2]);
        $pdf->SetTextColor($textDark[0], $textDark[1], $textDark[2]);
        $pdf->SetFont('Helvetica', '', 10);

        if (!empty($invoice['items'])) {
            $fill = true;
            foreach ($invoice['items'] as $item) {
                $pdf->Cell(140, 12, '  ' . $item['description'], 'B', 0, 'L', $fill);
                $pdf->Cell(50, 12, $symbol . number_format($item['total'], 2) . '  ', 'B', 1, 'R', $fill);
            }
        } else {
            $desc = !empty($invoice['deal_title']) ? $invoice['deal_title'] : 'Professional Services Rendered';
            $pdf->Cell(140, 14, '  ' . $desc, 'B', 0, 'L', true);
            $pdf->Cell(50, 14, $symbol . number_format($invoice['amount'], 2) . '  ', 'B', 1, 'R', true);
        }

        // --- Totals Section ---
        $pdf->Ln(5);
        $pdf->SetX(120);
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetTextColor($textMuted[0], $textMuted[1], $textMuted[2]);
        $pdf->Cell(35, 7, 'Subtotal', 0, 0, 'L');
        $pdf->SetTextColor($textDark[0], $textDark[1], $textDark[2]);
        $pdf->Cell(40, 7, $symbol . number_format($invoice['amount'], 2), 0, 1, 'R');

        $pdf->SetX(120);
        $pdf->SetTextColor($textMuted[0], $textMuted[1], $textMuted[2]);
        $pdf->Cell(35, 7, 'Tax (0%)', 0, 0, 'L');
        $pdf->SetTextColor($textDark[0], $textDark[1], $textDark[2]);
        $pdf->Cell(40, 7, $symbol . '0.00', 0, 1, 'R');

        $pdf->SetX(120);
        $pdf->SetFillColor($lightBg[0], $lightBg[1], $lightBg[2]);
        $pdf->Rect(120, $pdf->GetY(), 75, 12, 'F');
        $pdf->SetDrawColor($corporateBlue[0], $corporateBlue[1], $corporateBlue[2]);
        $pdf->Line(120, $pdf->GetY(), 195, $pdf->GetY());

        $pdf->SetX(120);
        $pdf->SetFont('Helvetica', 'B', 11);
        $pdf->SetTextColor($corporateBlue[0], $corporateBlue[1], $corporateBlue[2]);
        $pdf->Cell(35, 12, 'TOTAL DUE', 0, 0, 'L');
        $pdf->Cell(40, 12, $symbol . number_format($invoice['amount'], 2), 0, 1, 'R');

        return $pdf->Output('S');
    }

    /**
     * Send Invoice Email with Attachment
     */
    public static function send($invoice, $clientEmail, $smtpSettings, $pdfContent, $bccEmail = null)
    {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        try {
            // Server Settings
            $mail->isSMTP();
            $mail->Host = $smtpSettings['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $smtpSettings['smtp_username'];

            // Handle decrypted password if available
            $password = $smtpSettings['smtp_password_decrypted'] ?? $smtpSettings['smtp_password'];
            $mail->Password = $password;

            $mail->Port = $smtpSettings['smtp_port'];

            if ($smtpSettings['smtp_encryption'] === 'ssl') {
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($smtpSettings['smtp_encryption'] === 'tls') {
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            }

            // Recipients
            $fromName = $smtpSettings['from_name'] ?? Branding::getCompanyName();
            $mail->setFrom($smtpSettings['smtp_username'], $fromName);
            $mail->addAddress($clientEmail);
            if ($bccEmail) {
                $mail->addBCC($bccEmail);
            }

            // Attachment
            $filename = 'Invoice_' . $invoice['invoice_number'] . '.pdf';
            $mail->addStringAttachment($pdfContent, $filename);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Invoice #' . $invoice['invoice_number'] . ' from ' . $fromName;

            $body = "
                <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #eee; padding: 20px; border-radius: 10px;'>
                    <div style='text-align: center; margin-bottom: 20px;'>
                        <h2 style='color: #2D5A88; margin-bottom: 5px;'>INVOICE</h2>
                        <p style='color: #999; margin-top: 0;'>#" . $invoice['invoice_number'] . "</p>
                    </div>
                    <p>Hello,</p>
                    <p>Please find attached the invoice <strong>#" . $invoice['invoice_number'] . "</strong> for your recent business with <strong>" . $fromName . "</strong>.</p>
                    <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 25px 0; border: 1px solid #e9ecef;'>
                        <table style='width: 100%; border-collapse: collapse;'>
                            <tr>
                                <td style='padding: 8px 0; color: #666;'><strong>Client:</strong></td>
                                <td style='text-align: right; padding: 8px 0;'>" . Security::escape($invoice['client_name']) . "</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; color: #666;'><strong>Total Amount:</strong></td>
                                <td style='text-align: right; color: #2D5A88; font-weight: bold; font-size: 1.2em; padding: 8px 0;'>" . CURRENCY_SYMBOL . number_format($invoice['amount'], 2) . "</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; color: #666;'><strong>Status:</strong></td>
                                <td style='text-align: right; padding: 8px 0;'><span style='background: " . ($invoice['status'] === 'paid' ? '#d4edda' : '#f8d7da') . "; color: " . ($invoice['status'] === 'paid' ? '#155724' : '#721c24') . "; padding: 3px 10px; border-radius: 20px; font-size: 0.8em;'>" . strtoupper($invoice['status']) . "</span></td>
                            </tr>
                        </table>
                    </div>
                    <p>You can view the full details in the attached PDF file or access the secure payment portal below:</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='" . APP_URL . "/portal/invoice/" . $invoice['payment_token'] . "' style='background-color: #2D5A88; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>View & Pay Invoice Online</a>
                    </div>
                    <p style='margin-top: 30px;'>Thank you for your business!</p>
                    <hr style='border: 0; border-top: 1px solid #eee; margin: 30px 0;'>
                    <p style='font-size: 12px; color: #999; text-align: center;'>&copy; " . date('Y') . " " . $fromName . "</p>
                </div>
            ";

            $mail->Body = $body;
            $mail->AltBody = strip_tags($body);

            return $mail->send();
        } catch (Exception $e) {
            throw new Exception($mail->ErrorInfo ?: $e->getMessage());
        }
    }

    /**
     * Unified Helper: Send invoice email directly using invoice data
     * Handles everything: SMTP, Branding, PDF, and Dispatch
     */
    public static function sendWithInvoiceData($invoice)
    {
        try {
            if (empty($invoice['client_email']))
                return false;

            // Fetch SMTP settings
            $smtpModel = new SmtpSetting();
            $smtpSettings = $smtpModel->getByCompany($invoice['company_id']);
            if (!$smtpSettings)
                return false;

            // Prepare company data for PDF (Prioritize data from invoice arrays if available)
            $company = [
                'name' => $invoice['company_name'] ?? Branding::getCompanyName(),
                'address' => $invoice['company_address'] ?? Branding::getAddress(),
                'phone' => $invoice['company_phone'] ?? Branding::getPhone(),
                'email' => $invoice['company_email'] ?? Branding::getEmail(),
                'logo_path' => $invoice['logo_path'] ?? null
            ];

            // Generate PDF
            $pdfContent = self::generatePdf($invoice, $company);

            // Send email (BCC admin)
            $adminEmail = $company['email'] ?? null;
            return self::send($invoice, $invoice['client_email'], $smtpSettings, $pdfContent, $adminEmail);
        } catch (Exception $e) {
            error_log('InvoiceMailer Error: ' . $e->getMessage());
            return false;
        }
    }
}
