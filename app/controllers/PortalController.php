<?php
/**
 * Public Portal Controller
 * Handles all guest-facing interactions (Invoices, Payments)
 * Ensures complete isolation from admin dashboard components.
 */

class PortalController extends Controller
{
    private $invoiceModel;
    private $templateModel;
    private $paymentModel;
    private $leadModel;
    private $activityModel;

    public function __construct()
    {
        parent::__construct();
        $this->invoiceModel = $this->model('Invoice');
        $this->templateModel = $this->model('PaymentTemplate');
        $this->paymentModel = $this->model('Payment');
        $this->leadModel = $this->model('Lead');
        $this->activityModel = $this->model('ActivityLog');
    }

    /**
     * Public View of a specific Invoice
     */
    public function invoice($token)
    {
        if (empty($token)) {
            $this->deny('Invalid access token');
        }

        $invoice = $this->invoiceModel->getByToken($token);
        if (!$invoice) {
            $this->notFound('Invoice not found or link expired');
        }

        // Initialize branding with invoice's company data
        Branding::setCompany($invoice);

        // Allow viewing if invoice is paid, even if payment link is closed
        if ($invoice['payment_closed'] && $invoice['status'] !== 'paid') {
            $this->deny('This invoice payment link has been deactivated');
        }

        // Fetch payment history for this invoice
        $payments = $this->paymentModel->getByInvoice($invoice['id']);

        $this->view('invoices/public_view', [
            'invoice' => $invoice,
            'payments' => $payments
        ]);
    }

    /**
     * Public View for a Generic Payment Link (Template)
     */
    public function payment($token)
    {
        if (empty($token)) {
            die('Invalid access token');
        }

        $template = $this->templateModel->getByToken($token);
        if (!$template) {
            die('Payment link not found or expired');
        }

        if ($template['payment_closed']) {
            die('This payment link has been deactivated by the administrator');
        }

        $this->view('payments/checkout', [
            'template' => $template
        ]);
    }

    /**
     * Thank You Page - Displayed after successful payment
     */
    public function thankYou($token)
    {
        if (empty($token)) {
            die('Invalid access');
        }

        $invoice = $this->invoiceModel->getByToken($token);
        if (!$invoice) {
            die('Invoice not found');
        }

        // Initialize branding with invoice's company data
        Branding::setCompany($invoice);

        // If coming from Stripe, verify session/intent and update status immediately 
        if (isset($_GET['session_id'])) {
            $this->verifyStripeSession($invoice, $_GET['session_id']);
            $invoice = $this->invoiceModel->getByToken($token); // Refresh
        } elseif (isset($_GET['payment_intent'])) {
            $this->verifyStripePaymentIntent($invoice, $_GET['payment_intent']);
            $invoice = $this->invoiceModel->getByToken($token); // Refresh
        }

        // Fetch the most recent payment for this invoice
        $payment = null;
        if ($invoice['id']) {
            $payments = $this->paymentModel->getByInvoice($invoice['id']);
            $payment = !empty($payments) ? $payments[0] : null;
        }

        $this->view('payments/thank_you', [
            'invoice' => $invoice,
            'payment' => $payment
        ]);
    }

    /**
     * Download Invoice PDF (Public - uses payment token)
     */
    public function invoicePdf($token)
    {
        if (empty($token)) {
            die('Invalid access');
        }

        $invoice = $this->invoiceModel->getByToken($token);
        if (!$invoice) {
            die('Invoice not found');
        }

        // Initialize branding with invoice's company data
        Branding::setCompany($invoice);

        // Prepare company data for PDF helper
        $company = [
            'name' => $invoice['company_name'] ?? Branding::getCompanyName(),
            'address' => $invoice['company_address'] ?? Branding::getAddress(),
            'phone' => $invoice['company_phone'] ?? Branding::getPhone(),
            'email' => $invoice['company_email'] ?? Branding::getEmail(),
            'logo_path' => $invoice['logo_path'] ?? null
        ];

        require_once APP_PATH . '/helpers/InvoiceMailer.php';
        $pdfContent = InvoiceMailer::generatePdf($invoice, $company);

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="Invoice-' . $invoice['invoice_number'] . '.pdf"');
        echo $pdfContent;
        exit;
    }

    /**
     * AJAX: Record payment for a direct invoice
     */
    public function recordPayment($token)
    {
        // Suppress errors from corrupting JSON output
        ini_set('display_errors', 0);
        ob_start();

        $invoice = $this->invoiceModel->getByToken($token);
        if (!$invoice) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid invoice token']);
            exit;
        }

        if ($invoice['payment_closed']) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Payment for this invoice is closed']);
            exit;
        }

        $data = [
            'invoice_id' => $invoice['id'],
            'user_id' => $invoice['user_id'],
            'provider' => Security::sanitize($_POST['provider'] ?? 'paypal'),
            'transaction_id' => Security::sanitize($_POST['transaction_id'] ?? ''),
            'amount' => (float) ($_POST['amount'] ?? 0),
            'status' => 'completed'
        ];

        // Strict validation
        if (empty($data['transaction_id']) || strlen($data['transaction_id']) < 5) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid transaction ID']);
            exit;
        }

        if ($data['amount'] <= 0) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid amount']);
            exit;
        }

        if ($this->paymentModel->recordTransaction($data)) {
            // Mark invoice as paid
            $this->invoiceModel->update($invoice['id'], ['status' => 'paid']);

            // Capture as Lead
            $this->leadModel->capture($invoice['company_id'], $invoice['user_id'], [
                'name' => $invoice['client_name'],
                'email' => $invoice['client_email'],
                'phone' => $invoice['client_phone'] ?? '',
                'address' => $invoice['client_address'] ?? ''
            ], 'invoice', $invoice['id']);

            // Send invoice email automatically
            $invoice['status'] = 'paid';
            $this->sendInvoiceEmail($invoice);

            ob_clean();
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Payment recorded successfully!',
                'invoice_token' => $invoice['payment_token']
            ]);
        } else {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to record payment or duplicate transaction']);
        }
        exit;
    }

    /**
     * AJAX: Create Stripe Checkout Session for a direct invoice
     */
    public function createStripeSession($token)
    {
        // Suppress errors from corrupting JSON output
        ini_set('display_errors', 0);
        ob_start();

        $invoice = $this->invoiceModel->getByToken($token);
        if (!$invoice || empty($invoice['stripe_secret_key'])) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Stripe is not configured or invalid token']);
            exit;
        }

        if ($invoice['payment_closed']) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Payment is closed']);
            exit;
        }

        try {
            $stripeSecret = $invoice['stripe_secret_key'];
            $submittedData = $this->getSubmittedClientData();

            $clientEmail = $submittedData['email'] ?: $invoice['client_email'];
            $clientName = $submittedData['name'] ?: $invoice['client_name'];

            $postFields = [
                'payment_method_types[]' => 'card',
                'line_items[0][price_data][currency]' => strtolower(Branding::getCurrencyCode() ?: 'usd'),
                'line_items[0][price_data][product_data][name]' => 'Invoice #' . $invoice['invoice_number'],
                'line_items[0][price_data][unit_amount]' => round($invoice['amount'] * 100),
                'line_items[0][quantity]' => 1,
                'mode' => 'payment',
                'success_url' => APP_URL . '/portal/thankYou/' . $token . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => APP_URL . '/portal/invoice/' . $token . '?cancel=true',
                'client_reference_id' => (string) $invoice['id'],
                'customer_email' => $clientEmail,
                'metadata[client_name]' => substr($clientName, 0, 40),
                'metadata[invoice_token]' => $token
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/checkout/sessions');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
            curl_setopt($ch, CURLOPT_USERPWD, $stripeSecret . ':');
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);

            $result = curl_exec($ch);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($result === false) {
                throw new Exception('Connection to Stripe failed: ' . $curlError);
            }

            $session = json_decode($result, true);

            if (isset($session['id'])) {
                // Capture Lead even on session creation
                $this->leadModel->capture($invoice['company_id'], $invoice['user_id'], [
                    'name' => $clientName,
                    'email' => $clientEmail,
                    'phone' => $submittedData['phone'] ?: ($invoice['client_phone'] ?? ''),
                    'address' => $submittedData['address'] ?: ($invoice['client_address'] ?? '')
                ], 'invoice', $invoice['id']);

                ob_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'id' => $session['id']]);
            } else {
                $errorMessage = isset($session['error']['message']) ? $session['error']['message'] : 'Error creating session';
                ob_clean();
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $errorMessage]);
            }
        } catch (Exception $e) {
            ob_clean();
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }



    /**
    /**
     * AJAX: Create Stripe Payment Intent for Integrated Checkout
     */
    public function createPaymentIntent($token)
    {
        ini_set('display_errors', 0);
        ob_start();

        $template = $this->templateModel->getByToken($token);
        if (!$template || empty($template['stripe_secret_key'])) {
            error_log("Stripe Payment Intent Error: Template not found or Stripe not configured for token: $token");
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Stripe not configured']);
            exit;
        }

        // Log the Stripe mode being used
        $stripeMode = $template['stripe_mode'] ?? 'test';
        error_log("Stripe Payment Intent: Using mode=$stripeMode for template token=$token");

        try {
            $submittedData = $this->getSubmittedClientData();
            $validationResult = $this->validateCheckoutFields($template, $submittedData);

            if (!$validationResult['success']) {
                error_log("Stripe Payment Intent Error: Validation failed - " . $validationResult['message']);
                ob_clean();
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $validationResult['message']]);
                exit;
            }

            $companyId = $template['company_id'];
            $userId = (int) ($template['creator_id'] ?? 1);

            $stripeSecret = $template['stripe_secret_key'];

            // Verify we're using test keys in test mode
            if ($stripeMode === 'test' && strpos($stripeSecret, 'sk_test_') !== 0) {
                error_log("Stripe Payment Intent Error: Test mode enabled but using non-test secret key");
                ob_clean();
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Stripe configuration error: Invalid test key']);
                exit;
            }

            // Capture Lead immediately
            $this->leadModel->capture($companyId, $userId, $submittedData, 'template', $template['id']);

            // Pre-create the invoice (PENDING)
            $clientModel = $this->model('Client');
            $existingClient = null;
            if (!empty($submittedData['email'])) {
                $existingClient = $clientModel->findOne(['email' => $submittedData['email'], 'company_id' => $companyId]);
            }

            if ($existingClient) {
                $clientId = $existingClient['id'];
            } else {
                $clientId = $clientModel->insert([
                    'company_id' => (int) $companyId,
                    'user_id' => (int) $userId,
                    'name' => $submittedData['name'] ?? 'Generic Client',
                    'email' => $submittedData['email'] ?? '',
                    'phone' => $submittedData['phone'] ?? '',
                    'address' => $submittedData['address'] ?? ''
                ]);
            }

            // Create invoice with pending status
            $paymentToken = bin2hex(random_bytes(32));
            $invoiceId = $this->invoiceModel->insert([
                'company_id' => (int) $companyId,
                'user_id' => (int) $userId,
                'client_id' => $clientId,
                'template_id' => $template['id'],
                'invoice_number' => $this->invoiceModel->generateInvoiceNumber($companyId),
                'deal_title' => $template['title'],
                'amount' => $template['amount'],
                'status' => 'pending',
                'payment_token' => $paymentToken,
                'notes' => 'Generated from Integrated Checkout Service: ' . $template['title'],
                'client_data' => json_encode($submittedData)
            ]);

            error_log("Stripe Payment Intent: Created invoice ID=$invoiceId for amount=" . $template['amount']);

            $postFields = [
                'amount' => round($template['amount'] * 100),
                'currency' => strtolower(Branding::getCurrencyCode() ?: 'usd'),
                'payment_method_types[]' => 'card',
                'description' => substr($template['title'], 0, 100),
                'metadata[template_token]' => $token,
                'metadata[invoice_id]' => $invoiceId,
                'metadata[client_email]' => $submittedData['email'] ?? '',
                'metadata[client_name]' => substr($submittedData['name'] ?? '', 0, 40)
            ];

            error_log("Stripe Payment Intent: Sending request to Stripe API with amount=" . $postFields['amount'] . " " . $postFields['currency']);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
            curl_setopt($ch, CURLOPT_USERPWD, $stripeSecret . ':');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Stripe-Version: 2023-10-16'
            ]);

            $result = curl_exec($ch);
            $curlError = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($curlError) {
                error_log("Stripe Payment Intent Error: cURL error - $curlError");
                ob_clean();
                http_response_code(502);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Connection to payment processor failed']);
                exit;
            }

            error_log("Stripe Payment Intent: Received response with HTTP code=$httpCode");
            error_log("Stripe Payment Intent Response: " . substr($result, 0, 500));

            $intent = json_decode($result, true);

            if (isset($intent['client_secret'])) {
                error_log("Stripe Payment Intent: Successfully created PaymentIntent ID=" . ($intent['id'] ?? 'unknown'));
                ob_clean();
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'clientSecret' => $intent['client_secret'],
                    'invoiceToken' => $paymentToken
                ]);
            } else {
                $errorMessage = $intent['error']['message'] ?? 'Could not create payment intent';
                $errorType = $intent['error']['type'] ?? 'unknown';
                $errorCode = $intent['error']['code'] ?? 'unknown';

                error_log("Stripe Payment Intent Error: type=$errorType, code=$errorCode, message=$errorMessage");

                ob_clean();
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $errorMessage]);
            }
        } catch (Exception $e) {
            error_log("Stripe Payment Intent Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            ob_clean();
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'A processing error occurred. Please try again.']);
        }
        exit;
    }

    public function createTemplateSession($token)
    {
        // Suppress errors from corrupting JSON output
        ini_set('display_errors', 0);
        ob_start();

        $template = $this->templateModel->getByToken($token);

        if (!$template || empty($template['stripe_secret_key']) || !$template['allow_stripe']) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Stripe not configured or disabled for this link']);
            exit;
        }

        if ($template['payment_closed']) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Payment link is closed']);
            exit;
        }

        try {
            $submittedData = $this->getSubmittedClientData();
            $validationResult = $this->validateCheckoutFields($template, $submittedData);

            if (!$validationResult['success']) {
                ob_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $validationResult['message']]);
                exit;
            }

            $companyId = $template['company_id'];

            // Pre-create the invoice so we can redirect to Thank You page immediately
            $clientModel = $this->model('Client');
            $existingClient = null;
            if (!empty($submittedData['email'])) {
                $existingClient = $clientModel->findOne(['email' => $submittedData['email'], 'company_id' => $companyId]);
            }

            if ($existingClient) {
                $clientId = $existingClient['id'];
            } else {
                $clientId = $clientModel->insert([
                    'company_id' => (int) $companyId,
                    'user_id' => (int) ($template['creator_id'] ?? 1),
                    'name' => $submittedData['name'] ?? 'Generic Client',
                    'email' => $submittedData['email'] ?? '',
                    'phone' => $submittedData['phone'] ?? '',
                    'address' => $submittedData['address'] ?? ''
                ]);
            }

            // Create invoice with pending status
            $invoiceId = $this->invoiceModel->insert([
                'company_id' => (int) $companyId,
                'user_id' => (int) ($template['creator_id'] ?? 1),
                'client_id' => $clientId,
                'template_id' => $template['id'],
                'invoice_number' => $this->invoiceModel->generateInvoiceNumber($companyId),
                'deal_title' => $template['title'],
                'amount' => $template['amount'],
                'status' => 'pending',
                'payment_token' => bin2hex(random_bytes(32)),
                'notes' => 'Generated from Public Payment Link: ' . $template['title'],
                'client_data' => json_encode($submittedData)
            ]);

            $invoice = $this->invoiceModel->findById($invoiceId);

            $stripeSecret = $template['stripe_secret_key'] ?? '';

            $postFields = [
                'payment_method_types[]' => 'card',
                'line_items[0][price_data][currency]' => strtolower(Branding::getCurrencyCode() ?: 'usd'),
                'line_items[0][price_data][product_data][name]' => substr($template['title'], 0, 100),
                'line_items[0][price_data][unit_amount]' => round($template['amount'] * 100),
                'line_items[0][quantity]' => 1,
                'mode' => 'payment',
                'success_url' => APP_URL . '/portal/thankYou/' . $invoice['payment_token'] . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => APP_URL . '/portal/payment/' . $token . '?cancel=true',
                'client_reference_id' => (string) $invoiceId,
                'customer_email' => $submittedData['email'] ?? '',
                'metadata[invoice_id]' => $invoiceId,
                'metadata[client_name]' => substr($submittedData['name'] ?? '', 0, 40),
                'metadata[template_token]' => $token
            ];

            $metadataSnapshot = array_intersect_key($submittedData, array_flip(['name', 'email', 'phone', 'company']));
            $postFields['metadata[client_snapshot]'] = substr(json_encode($metadataSnapshot), 0, 450);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/checkout/sessions');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
            curl_setopt($ch, CURLOPT_USERPWD, $stripeSecret . ':');
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);

            $result = curl_exec($ch);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($result === false) {
                throw new Exception('Connection to Stripe failed: ' . $curlError);
            }

            $session = json_decode($result, true);

            if (isset($session['id'])) {
                // Capture Lead using creator_id from template
                $userId = (int) ($template['creator_id'] ?? 1);
                $this->leadModel->capture($template['company_id'], $userId, $submittedData, 'template', $template['id']);

                ob_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'id' => $session['id']]);
            } else {
                $errorMessage = isset($session['error']['message']) ? $session['error']['message'] : 'Could not create payment session';
                ob_clean();
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $errorMessage]);
            }
        } catch (Exception $e) {
            ob_clean();
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'System Error: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * AJAX: Process Template Payment (Create Client -> Create Invoice -> Record Payment)
     */
    public function processTemplatePayment($token)
    {
        // Suppress errors from corrupting JSON output
        ini_set('display_errors', 0);
        ob_start();

        $template = $this->templateModel->getByToken($token);
        if (!$template || $template['payment_closed']) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid or closed payment link']);
            exit;
        }

        $companyId = $template['company_id'];
        $provider = Security::sanitize($_POST['provider'] ?? 'paypal');
        $transactionId = Security::sanitize($_POST['transaction_id'] ?? '');
        // Validate inputs
        $invoiceId = (int) ($_POST['invoice_id'] ?? 0);
        $token = Security::sanitize($_POST['token'] ?? '');
        $amount = (float) ($_POST['amount'] ?? 0);

        $invoice = $this->invoiceModel->getByToken($token);
        if (!$invoice || $invoice['id'] != $invoiceId) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            exit;
        }

        try {
            // Collect and Validate Fields
            $submittedData = $this->getSubmittedClientData();
            $validationResult = $this->validateCheckoutFields($template, $submittedData);

            if (!$validationResult['success']) {
                ob_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $validationResult['message']]);
                exit;
            }

            $clientModel = $this->model('Client');
            $existingClient = null;
            if (!empty($submittedData['email'])) {
                $existingClient = $clientModel->findOne(['email' => $submittedData['email'], 'company_id' => $companyId]);
            }

            if ($existingClient) {
                $clientId = $existingClient['id'];
            } else {
                $clientId = $clientModel->insert([
                    'company_id' => (int) $companyId,
                    'user_id' => (int) ($template['creator_id'] ?? 1),
                    'name' => $submittedData['name'] ?? 'Generic Client',
                    'email' => $submittedData['email'] ?? '',
                    'phone' => $submittedData['phone'] ?? '',
                    'address' => $submittedData['address'] ?? ''
                ]);
            }

            $invoiceId = $this->invoiceModel->insert([
                'company_id' => (int) $companyId,
                'user_id' => (int) ($template['creator_id'] ?? 1),
                'client_id' => $clientId,
                'template_id' => $template['id'],
                'invoice_number' => $this->invoiceModel->generateInvoiceNumber($companyId),
                'deal_title' => $template['title'],
                'amount' => $amount,
                'status' => 'paid', // Mark as paid since it's a template link
                'payment_token' => bin2hex(random_bytes(32)),
                'notes' => 'Generated from Public Payment Link: ' . $template['title'],
                'client_data' => json_encode($submittedData)
            ]);

            $this->paymentModel->recordTransaction([
                'company_id' => (int) $companyId,
                'collector_id' => (int) ($template['creator_id'] ?? 1),
                'invoice_id' => $invoiceId,
                'provider' => $provider,
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'payment_method' => ucfirst($provider),
                'payment_date' => date('Y-m-d H:i:s'),
                'status' => 'completed'
            ]);

            // Get the generated invoice token for redirect
            $generatedInvoice = $this->invoiceModel->getWithClientByCompany($invoiceId, $companyId);

            // Capture Lead (or update if exists)
            $userId = (int) ($template['creator_id'] ?? 1);
            $this->leadModel->capture($template['company_id'], $userId, $submittedData, 'template', $template['id']);

            // Send invoice email automatically
            $this->sendInvoiceEmail($generatedInvoice);

            ob_clean();
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Payment recorded successfully',
                'invoice_token' => $generatedInvoice['payment_token'] ?? null
            ]);

        } catch (Exception $e) {
            ob_clean();
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Helper: Collect all submitted checkout fields
     */
    private function getSubmittedClientData()
    {
        $data = [];
        $fields = ['name', 'email', 'phone', 'address', 'city', 'country', 'company', 'notes'];

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                $data[$field] = Security::sanitize($_POST[$field]);
            }
        }

        return $data;
    }

    /**
     * Helper: Validate checkout fields against template settings
     */
    private function validateCheckoutFields($template, $submittedData)
    {
        $settings = json_decode($template['checkout_settings'] ?? '[]', true);
        if (empty($settings))
            return ['success' => true];

        $fieldLabels = [
            'name' => 'Full Name',
            'email' => 'Email Address',
            'phone' => 'Phone Number',
            'address' => 'Physical Address',
            'city' => 'City',
            'country' => 'Country',
            'company' => 'Company Name',
            'notes' => 'Notes'
        ];

        foreach ($settings as $key => $config) {
            if (($config['required'] ?? false) && ($config['visible'] ?? false)) {
                if (empty($submittedData[$key])) {
                    return [
                        'success' => false,
                        'message' => 'Please fill in the ' . ($fieldLabels[$key] ?? $key) . ' field'
                    ];
                }
            }
        }

        return ['success' => true];
    }

    /**
     * Helper: Send invoice email automatically after payment
     */
    private function sendInvoiceEmail($invoice)
    {
        // Use the unified CRM Mailer to ensure identical invoice layout and data
        require_once APP_PATH . '/helpers/InvoiceMailer.php';
        InvoiceMailer::sendWithInvoiceData($invoice);
    }

    /**
     * Helper: Verify Stripe Session and update status
     */
    private function verifyStripeSession($invoice, $sessionId)
    {
        try {
            $stripeSecret = $invoice['stripe_secret_key'];
            if (!$stripeSecret)
                return;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/checkout/sessions/' . $sessionId);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_USERPWD, $stripeSecret . ':');
            $result = curl_exec($ch);
            curl_close($ch);

            $session = json_decode($result, true);

            if (isset($session['payment_status']) && $session['payment_status'] === 'paid') {
                // Update Invoice Status
                $this->invoiceModel->update($invoice['id'], ['status' => 'paid']);

                // Record Transaction if not already exists
                $transactionId = $session['payment_intent'] ?? $sessionId;
                $this->paymentModel->recordTransaction([
                    'company_id' => $invoice['company_id'],
                    'invoice_id' => $invoice['id'],
                    'provider' => 'stripe',
                    'transaction_id' => $transactionId,
                    'amount' => $session['amount_total'] / 100,
                    'payment_method' => 'Stripe',
                    'payment_date' => date('Y-m-d H:i:s'),
                    'status' => 'completed'
                ]);

                // Send invoice email automatically
                $invoice['status'] = 'paid';
                $this->sendInvoiceEmail($invoice);
            }
        } catch (Exception $e) {
            error_log('Stripe verification failed: ' . $e->getMessage());
        }
    }
    /**
     * Helper: Verify Stripe Payment Intent and update status
     */
    private function verifyStripePaymentIntent($invoice, $paymentIntentId)
    {
        try {
            $stripeSecret = $invoice['stripe_secret_key'];
            if (!$stripeSecret)
                return;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents/' . $paymentIntentId);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_USERPWD, $stripeSecret . ':');
            $result = curl_exec($ch);
            curl_close($ch);

            $intent = json_decode($result, true);

            if (isset($intent['status']) && $intent['status'] === 'succeeded') {
                // Update Invoice Status
                $this->invoiceModel->update($invoice['id'], ['status' => 'paid']);

                // Record Transaction
                $this->paymentModel->recordTransaction([
                    'company_id' => $invoice['company_id'],
                    'invoice_id' => $invoice['id'],
                    'provider' => 'stripe',
                    'transaction_id' => $paymentIntentId,
                    'amount' => $intent['amount'] / 100,
                    'payment_method' => 'Stripe Card',
                    'payment_date' => date('Y-m-d H:i:s'),
                    'status' => 'completed'
                ]);

                // Send invoice email
                $invoice['status'] = 'paid';
                $this->sendInvoiceEmail($invoice);
            }
        } catch (Exception $e) {
            error_log('Stripe intent verification failed: ' . $e->getMessage());
        }
    }
}
