<?php
/**
 * Invoices Controller
 * Handles invoice CRUD operations and PDF generation
 */
require_once APP_PATH . '/helpers/InvoiceMailer.php';

class InvoicesController extends Controller
{
    private $invoiceModel;
    private $clientModel;
    private $dealModel;
    private $invoiceItemModel;

    public function __construct()
    {
        parent::__construct();
        $this->invoiceModel = $this->model('Invoice');
        $this->clientModel = $this->model('Client');
        $this->dealModel = $this->model('Deal');
        $this->invoiceItemModel = $this->model('InvoiceItem');
    }

    /**
     * List all invoices
     */
    public function index()
    {
        $this->requirePermission('invoices', 'view');
        $companyId = Session::get('company_id');
        $status = $_GET['status'] ?? null;
        $search = $_GET['search'] ?? '';

        if ($search) {
            $invoices = $this->invoiceModel->searchByCompany($companyId, $search);
        } else {
            $invoices = $this->invoiceModel->getWithClientsByCompany($companyId, $status);
        }

        $this->view('invoices/index', [
            'invoices' => $invoices,
            'current_status' => $status,
            'search' => $search
        ]);
    }

    /**
     * View single invoice
     */
    public function show($id)
    {
        $this->requirePermission('invoices', 'view');
        $companyId = Session::get('company_id');
        $invoice = $this->invoiceModel->getWithClientByCompany($id, $companyId);

        if (!$invoice) {
            $this->setFlash('error', 'Invoice not found');
            $this->redirect('invoices');
        }

        $this->view('invoices/view', ['invoice' => $invoice]);
    }

    /**
     * View premium invoice design
     */
    public function premium($id)
    {
        $this->requirePermission('invoices', 'view');
        $companyId = Session::get('company_id');
        $invoice = $this->invoiceModel->getWithClientByCompany($id, $companyId);

        if (!$invoice) {
            $this->setFlash('error', 'Invoice not found');
            $this->redirect('invoices');
        }

        $this->view('invoices/premium', ['invoice' => $invoice]);
    }

    /**
     * Create new invoice
     */
    public function create()
    {
        $this->requirePermission('invoices', 'create');
        $companyId = Session::get('company_id');
        $clients = $this->clientModel->getByCompany($companyId);
        $deals = $this->dealModel->getByCompany($companyId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCreate();
        } else {
            // Initialize invoice data
            $invoice = null;

            // Auto-fill if client_id is provided
            if (isset($_GET['client_id'])) {
                $clientId = (int) $_GET['client_id'];
                $client = $this->clientModel->findOne(['id' => $clientId, 'company_id' => $companyId]);

                if ($client) {
                    // Get latest deal for this client
                    $latestDeal = $this->dealModel->getLatestByClient($clientId, $companyId);

                    // Pre-fill invoice data
                    $invoice = [
                        'client_id' => $clientId,
                        'client_name' => $client['name'], // For display purposes
                        'deal_id' => $latestDeal['id'] ?? null,
                        'amount' => $latestDeal['amount'] ?? 0.00,
                        'status' => 'unpaid'
                    ];
                }
            }

            $this->view('invoices/form', [
                'invoice' => $invoice,
                'clients' => $clients,
                'deals' => $deals,
                'csrf_token' => Security::generateCsrfToken()
            ]);
        }
    }

    /**
     * Handle create form submission
     */
    private function handleCreate()
    {
        $companyId = Session::get('company_id');
        $clients = $this->clientModel->getByCompany($companyId);
        $deals = $this->dealModel->getByCompany($companyId);

        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('invoices/create');
        }

        $data = [
            'client_id' => (int) ($_POST['client_id'] ?? 0),
            'amount' => (float) ($_POST['amount'] ?? 0),
            'status' => Security::sanitize($_POST['status'] ?? 'unpaid')
        ];

        $dealIds = $_POST['deal_ids'] ?? [];
        if (!is_array($dealIds) && !empty($dealIds)) {
            $dealIds = [$dealIds];
        }

        $validator = new Validator();
        $validator->required('client_id', $data['client_id'], 'Client')
            ->numeric('amount', $data['amount'], 'Amount');

        if ($validator->fails()) {
            $this->view('invoices/form', [
                'invoice' => $data,
                'clients' => $clients,
                'deals' => $deals,
                'errors' => $validator->getErrors(),
                'csrf_token' => Security::generateCsrfToken()
            ]);
            return;
        }

        // Generate invoice number
        $data['invoice_number'] = $this->invoiceModel->generateInvoiceNumber($companyId);
        $data['company_id'] = $companyId;
        $data['user_id'] = $this->getUserId();
        $data['payment_token'] = bin2hex(random_bytes(32));

        $invoiceId = $this->invoiceModel->insert($data);

        if ($invoiceId) {
            // Create line items for each deal
            if (!empty($dealIds)) {
                $lineItems = [];
                foreach ($dealIds as $dealId) {
                    $deal = $this->dealModel->findOne(['id' => $dealId]);
                    if ($deal) {
                        $lineItems[] = [
                            'deal_id' => $deal['id'],
                            'description' => $deal['title'],
                            'quantity' => 1,
                            'price' => $deal['amount'],
                            'total' => $deal['amount']
                        ];
                    }
                }
                if (!empty($lineItems)) {
                    $this->invoiceItemModel->bulkInsert($invoiceId, $lineItems);
                }
            } else {
                // If no deals but we have an amount, create a generic item
                $this->invoiceItemModel->bulkInsert($invoiceId, [
                    [
                        'description' => 'Professional Services',
                        'quantity' => 1,
                        'price' => $data['amount'],
                        'total' => $data['amount']
                    ]
                ]);
            }

            $this->setFlash('success', 'Invoice created successfully');
            $this->redirect('invoices/show/' . $invoiceId);
        } else {
            $this->setFlash('error', 'Failed to create invoice');
            $this->redirect('invoices/create');
        }
    }

    /**
     * Edit invoice
     */
    public function edit($id)
    {
        $this->requirePermission('invoices', 'edit');
        $companyId = Session::get('company_id');
        $invoice = $this->invoiceModel->findOne(['id' => $id, 'company_id' => $companyId]);
        $clients = $this->clientModel->getByCompany($companyId);
        $deals = $this->dealModel->getByCompany($companyId);

        if (!$invoice) {
            $this->setFlash('error', 'Invoice not found');
            $this->redirect('invoices');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleEdit($id);
        } else {
            $this->view('invoices/form', [
                'invoice' => $invoice,
                'clients' => $clients,
                'deals' => $deals,
                'csrf_token' => Security::generateCsrfToken()
            ]);
        }
    }

    /**
     * Handle edit form submission
     */
    private function handleEdit($id)
    {
        $companyId = Session::get('company_id');
        $clients = $this->clientModel->getByCompany($companyId);
        $deals = $this->dealModel->getByCompany($companyId);

        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('invoices/edit/' . $id);
        }

        $data = [
            'client_id' => (int) ($_POST['client_id'] ?? 0),
            'amount' => (float) ($_POST['amount'] ?? 0),
            'status' => Security::sanitize($_POST['status'] ?? 'unpaid')
        ];

        $dealIds = $_POST['deal_ids'] ?? [];
        if (!is_array($dealIds) && !empty($dealIds)) {
            $dealIds = [$dealIds];
        }

        $validator = new Validator();
        $validator->required('client_id', $data['client_id'], 'Client')
            ->numeric('amount', $data['amount'], 'Amount');

        if ($validator->fails()) {
            $data['id'] = $id;
            $this->view('invoices/form', [
                'invoice' => $data,
                'clients' => $clients,
                'deals' => $deals,
                'errors' => $validator->getErrors(),
                'csrf_token' => Security::generateCsrfToken()
            ]);
            return;
        }

        if ($this->invoiceModel->update($id, $data)) {
            // Update line items: clear and re-insert
            $this->invoiceItemModel->deleteByInvoice($id);

            if (!empty($dealIds)) {
                $lineItems = [];
                foreach ($dealIds as $dealId) {
                    $deal = $this->dealModel->findOne(['id' => $dealId]);
                    if ($deal) {
                        $lineItems[] = [
                            'deal_id' => $deal['id'],
                            'description' => $deal['title'],
                            'quantity' => 1,
                            'price' => $deal['amount'],
                            'total' => $deal['amount']
                        ];
                    }
                }
                if (!empty($lineItems)) {
                    $this->invoiceItemModel->bulkInsert($id, $lineItems);
                }
            } else {
                $this->invoiceItemModel->bulkInsert($id, [
                    [
                        'description' => 'Professional Services',
                        'quantity' => 1,
                        'price' => $data['amount'],
                        'total' => $data['amount']
                    ]
                ]);
            }

            $this->setFlash('success', 'Invoice updated successfully');
            $this->redirect('invoices/show/' . $id);
        } else {
            $this->setFlash('error', 'Failed to update invoice');
            $this->redirect('invoices/edit/' . $id);
        }
    }

    /**
     * Delete invoice
     */
    public function delete($id)
    {
        $this->requirePermission('invoices', 'delete');
        $companyId = Session::get('company_id');
        $invoice = $this->invoiceModel->findOne(['id' => $id, 'company_id' => $companyId]);

        if (!$invoice) {
            $this->setFlash('error', 'Invoice not found');
            $this->redirect('invoices');
        }

        if ($this->invoiceModel->delete($id)) {
            $this->setFlash('success', 'Invoice deleted successfully');
        } else {
            $this->setFlash('error', 'Failed to delete invoice');
        }

        $this->redirect('invoices');
    }

    /**
     * Mark invoice as paid
     */
    public function markPaid($id)
    {
        $this->requirePermission('invoices', 'edit');
        $companyId = Session::get('company_id');
        $invoice = $this->invoiceModel->findOne(['id' => $id, 'company_id' => $companyId]);

        if (!$invoice) {
            $this->setFlash('error', 'Invoice not found');
            $this->redirect('invoices');
        }

        if ($this->invoiceModel->update($id, ['status' => 'paid'])) {
            $this->setFlash('success', 'Invoice marked as paid');
        } else {
            $this->setFlash('error', 'Failed to update invoice');
        }

        $this->redirect('invoices/show/' . $id);
    }

    /**
     * Close or Open payment link
     */
    public function togglePayment($id)
    {
        $this->requirePermission('invoices', 'edit');
        $companyId = Session::get('company_id');

        $invoice = $this->invoiceModel->findOne(['id' => $id, 'company_id' => $companyId]);
        if (!$invoice) {
            $this->setFlash('error', 'Invoice not found');
            $this->redirect('invoices');
        }

        $newStatus = $invoice['payment_closed'] ? 0 : 1;
        if ($this->invoiceModel->updatePaymentStatus($id, $companyId, $newStatus)) {
            $msg = $newStatus ? 'Payment link closed' : 'Payment link opened';
            $this->setFlash('success', $msg);
        } else {
            $this->setFlash('error', 'Failed to update payment status');
        }

        $this->redirect('invoices/show/' . $id);
    }

    /**
     * Handle Stripe Webhook
     */
    public function stripeWebhook()
    {
        $payload = @file_get_contents('php://input');
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

        // In a real implementation with SDK, we would use \Stripe\Webhook::constructEvent
        // Since we are using manual verification or trusting the secret in this demo environment:
        $event = json_decode($payload, true);

        if ($event && isset($event['type'])) {
            $obj = $event['data']['object'];

            if ($event['type'] === 'checkout.session.completed') {
                $invoiceId = $obj['client_reference_id'] ?? ($obj['metadata']['invoice_id'] ?? null);
                $transactionId = $obj['payment_intent'];
                $amount = $obj['amount_total'] / 100;
                $metadata = $obj['metadata'] ?? [];

                // Handle if it's from a template (client-filled)
                if (isset($metadata['template_token'])) {
                    $this->processWebhookTemplatePayment($obj);
                } else if ($invoiceId) {
                    $this->finalizeInvoicePayment($invoiceId, $transactionId, $amount);
                }
            } elseif ($event['type'] === 'payment_intent.succeeded') {
                $invoiceId = $obj['metadata']['invoice_id'] ?? null;
                $transactionId = $obj['id'];
                $amount = $obj['amount'] / 100;

                if ($invoiceId) {
                    $this->finalizeInvoicePayment($invoiceId, $transactionId, $amount);
                }
            }
        }

        http_response_code(200);
        exit;
    }

    /**
     * Handle Stripe Webhook for Template-based payments
     */
    private function processWebhookTemplatePayment($session)
    {
        $metadata = $session['metadata'] ?? [];
        $templateToken = $metadata['template_token'] ?? null;

        if (!$templateToken)
            return;

        $templateModel = $this->model('PaymentTemplate');
        $template = $templateModel->getByToken($templateToken);

        if (!$template)
            return;

        $userId = $template['user_id'];
        $amount = $session['amount_total'] / 100;
        $transactionId = $session['payment_intent'];

        // Create or find client
        $clientModel = $this->model('Client');
        $email = $session['customer_email'];
        $existingClient = $clientModel->findOne(['email' => $email, 'company_id' => $template['company_id']]);

        if ($existingClient) {
            $clientId = $existingClient['id'];
        } else {
            $clientId = $clientModel->insert([
                'company_id' => $template['company_id'],
                'user_id' => $userId,
                'name' => $metadata['client_name'] ?? 'Generic Client',
                'email' => $email,
                'phone' => $metadata['client_phone'] ?? '',
                'address' => $metadata['client_address'] ?? ''
            ]);
        }

        // Create Invoice
        $invoiceId = $this->invoiceModel->insert([
            'company_id' => $template['company_id'],
            'user_id' => $userId,
            'client_id' => $clientId,
            'template_id' => $template['id'],
            'invoice_number' => $this->invoiceModel->generateInvoiceNumber($template['company_id']),
            'deal_title' => $template['title'],
            'amount' => $amount,
            'status' => 'paid',
            'payment_token' => bin2hex(random_bytes(32)),
            'notes' => 'Generated via Stripe Webhook for Public Payment Link: ' . $template['title'],
            'client_data' => $metadata['client_data'] ?? null // Save the dynamic snapshot
        ]);

        // Record Payment
        $paymentModel = $this->model('Payment');
        $paymentModel->recordTransaction([
            'company_id' => $template['company_id'],
            'user_id' => $userId,
            'invoice_id' => $invoiceId,
            'provider' => 'stripe',
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'status' => 'completed'
        ]);

        // Capture/Update Lead
        $leadModel = $this->model('Lead');
        $leadModel->capture($template['company_id'], $userId, [
            'name' => $metadata['client_name'] ?? 'Generic Client',
            'email' => $email,
            'phone' => $metadata['client_phone'] ?? '',
            'address' => $metadata['client_address'] ?? '',
            'company' => $metadata['client_company'] ?? ''
        ], 'template', $template['id']);

        // Send receipt email
        $fullInvoice = $this->invoiceModel->getWithClientByCompany($invoiceId, $template['company_id']);
        if ($fullInvoice) {
            $fullInvoice['status'] = 'paid';
            $this->sendInvoiceEmail($fullInvoice);
        }
    }

    /**
     * Download invoice as PDF
     */
    public function pdf($id)
    {
        $this->requirePermission('invoices', 'view');
        $companyId = Session::get('company_id');
        $invoice = $this->invoiceModel->getWithClientByCompany($id, $companyId);

        if (!$invoice) {
            $this->setFlash('error', 'Invoice not found');
            $this->redirect('invoices');
        }

        // Generate PDF using helper with standardized branding
        $company = [
            'name' => $invoice['company_name'] ?? Branding::getCompanyName(),
            'address' => $invoice['company_address'] ?? Branding::getAddress(),
            'phone' => $invoice['company_phone'] ?? Branding::getPhone(),
            'email' => $invoice['company_email'] ?? Branding::getEmail(),
            'logo_path' => $invoice['logo_path'] ?? null
        ];

        $pdfContent = InvoiceMailer::generatePdf($invoice, $company);

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="Invoice-' . $invoice['invoice_number'] . '.pdf"');
        echo $pdfContent;
    }

    /**
     * Get invoice data for preview modal (AJAX)
     */
    public function preview($id)
    {
        $this->requirePermission('invoices', 'view');
        header('Content-Type: application/json');
        $companyId = Session::get('company_id');
        $invoice = $this->invoiceModel->getWithClientByCompany($id, $companyId);

        if (!$invoice) {
            echo json_encode(['success' => false, 'message' => 'Invoice not found']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'invoice_number' => $invoice['invoice_number'],
                'client_name' => $invoice['client_name'],
                'client_email' => $invoice['client_email'],
                'amount' => $invoice['amount'],
                'currency' => CURRENCY_SYMBOL
            ]
        ]);
        exit;
    }

    /**
     * Send invoice email (AJAX)
     */
    public function sendEmail($id)
    {
        $this->requirePermission('invoices', 'send');
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        $companyId = Session::get('company_id');
        $invoice = $this->invoiceModel->getWithClientByCompany($id, $companyId);

        if (!$invoice) {
            echo json_encode(['success' => false, 'message' => 'Invoice not found']);
            exit;
        }

        if (empty($invoice['client_email'])) {
            echo json_encode(['success' => false, 'message' => 'Client does not have an email address']);
            exit;
        }

        if (InvoiceMailer::sendWithInvoiceData($invoice)) {
            echo json_encode(['success' => true, 'message' => 'Invoice sent successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send invoice. Please check SMTP settings.']);
        }
        exit;
    }

    /**
     * Helper: Finalize invoice payment (Record Transaction -> Update Status -> Send Email)
     */
    private function finalizeInvoicePayment($invoiceId, $transactionId, $amount)
    {
        $invoice = $this->invoiceModel->findOne(['id' => $invoiceId]);
        if (!$invoice)
            return;

        $companyId = $invoice['company_id'];
        $paymentModel = $this->model('Payment');

        // Record Transaction
        $paymentModel->recordTransaction([
            'invoice_id' => $invoiceId,
            'company_id' => $companyId,
            'provider' => 'stripe',
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'status' => 'completed'
        ]);

        // Mark as paid
        $this->invoiceModel->update($invoiceId, ['status' => 'paid']);

        // Send Email
        $fullInvoice = $this->invoiceModel->getWithClientByCompany($invoiceId, $companyId);
        if ($fullInvoice) {
            $fullInvoice['status'] = 'paid';
            $this->sendInvoiceEmail($fullInvoice);
        }
    }

    public function export()
    {
        $this->requirePermission('invoices', 'export');
        $companyId = Session::get('company_id');
        $invoices = $this->invoiceModel->getWithClientsByCompany($companyId);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="invoices_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Invoice Number', 'Client', 'Amount', 'Status', 'Due Date', 'Created At']);

        foreach ($invoices as $invoice) {
            fputcsv($output, [
                $invoice['invoice_number'],
                $invoice['client_name'] ?? 'N/A',
                $invoice['total_amount'],
                ucfirst($invoice['status']),
                $invoice['due_date'] ?? 'N/A',
                $invoice['created_at']
            ]);
        }

        fclose($output);
        exit;
    }

    private function sendInvoiceEmail($invoice)
    {
        // Use the unified CRM Mailer to ensure identical invoice layout and data
        require_once APP_PATH . '/helpers/InvoiceMailer.php';
        InvoiceMailer::sendWithInvoiceData($invoice);
    }
}