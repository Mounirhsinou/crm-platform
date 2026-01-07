<?php
/**
 * Payments Controller
 * Handles Public Payment Invoices (templates)
 */

class PaymentsController extends Controller
{
    private $templateModel;

    public function __construct()
    {
        $this->templateModel = $this->model('PaymentTemplate');
    }

    /**
     * Admin: List all public links (Templates + Invoices)
     */
    public function index()
    {
        $this->requireAuth();
        $companyId = Session::get('company_id');

        $links = $this->templateModel->getUnifiedLinks($companyId);

        $this->view('payments/index', [
            'links' => $links,
            'csrf_token' => Security::generateCsrfToken()
        ]);
    }

    /**
     * Admin: Handle template creation
     */
    public function create()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->setFlash('error', 'Invalid request');
                $this->redirect('payments');
            }

            $companyId = Session::get('company_id');

            // Process checkout fields
            $fieldSettings = $_POST['fields'] ?? [];
            $settings = [];
            $availableFields = ['name', 'email', 'phone', 'address', 'city', 'country', 'company', 'notes'];

            foreach ($availableFields as $field) {
                $settings[$field] = [
                    'visible' => isset($fieldSettings[$field]['visible']) ? true : false,
                    'required' => isset($fieldSettings[$field]['required']) ? true : false
                ];

                // Name and Email must always be visible for system integrity
                if ($field === 'name' || $field === 'email') {
                    $settings[$field]['visible'] = true;
                }
            }

            $data = [
                'company_id' => $companyId,
                'token' => bin2hex(random_bytes(32)),
                'title' => Security::sanitize($_POST['title'] ?? ''),
                'description' => Security::sanitize($_POST['description'] ?? ''),
                'amount' => (float) ($_POST['amount'] ?? 0),
                'allow_paypal' => isset($_POST['allow_paypal']) ? 1 : 0,
                'allow_stripe' => isset($_POST['allow_stripe']) ? 1 : 0,
                'checkout_settings' => json_encode($settings)
            ];

            if ($this->templateModel->insert($data)) {
                $this->setFlash('success', 'Public Payment Link created successfully!');
            } else {
                $this->setFlash('error', 'Failed to create payment link');
            }
            $this->redirect('payments');
        }
    }

    /**
     * Admin: Edit public link (Handles both Templates and Invoices)
     */
    public function edit($type, $id)
    {
        $this->requireAuth();
        $companyId = Session::get('company_id');

        if ($type === 'Client-filled') {
            $template = $this->templateModel->findOne(['id' => $id, 'company_id' => $companyId]);
            if (!$template) {
                $this->setFlash('error', 'Payment link not found');
                $this->redirect('payments');
            }

            $this->view('payments/edit_template', [
                'template' => $template,
                'csrf_token' => Security::generateCsrfToken()
            ]);
        } else {
            // Regular invoices have their own edit page
            $this->redirect('invoices/edit/' . $id);
        }
    }

    /**
     * Admin: Handle template update
     */
    public function update($id)
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('payments');
        }

        $companyId = Session::get('company_id');
        $template = $this->templateModel->findOne(['id' => $id, 'company_id' => $companyId]);
        if (!$template) {
            $this->setFlash('error', 'Payment link not found');
            $this->redirect('payments');
        }

        // Process checkout fields
        $fieldSettings = $_POST['fields'] ?? [];
        $settings = [];
        $availableFields = ['name', 'email', 'phone', 'address', 'city', 'country', 'company', 'notes'];

        foreach ($availableFields as $field) {
            $settings[$field] = [
                'visible' => isset($fieldSettings[$field]['visible']) ? true : false,
                'required' => isset($fieldSettings[$field]['required']) ? true : false
            ];
            // Name and Email must always be visible
            if ($field === 'name' || $field === 'email') {
                $settings[$field]['visible'] = true;
            }
        }

        $data = [
            'title' => Security::sanitize($_POST['title'] ?? ''),
            'description' => Security::sanitize($_POST['description'] ?? ''),
            'amount' => (float) ($_POST['amount'] ?? 0),
            'allow_paypal' => isset($_POST['allow_paypal']) ? 1 : 0,
            'allow_stripe' => isset($_POST['allow_stripe']) ? 1 : 0,
            'checkout_settings' => json_encode($settings)
        ];

        if ($this->templateModel->update($id, $data)) {
            $this->setFlash('success', 'Payment Link updated successfully!');
        } else {
            $this->setFlash('error', 'Failed to update payment link');
        }

        $this->redirect('payments');
    }

    /**
     * Admin: Delete link (Handles both Templates and Invoices)
     */
    public function delete($type, $id)
    {
        $this->requireAuth();
        $companyId = Session::get('company_id');

        if ($type === 'Client-filled') {
            $template = $this->templateModel->findOne(['id' => $id, 'company_id' => $companyId]);
            if ($template) {
                // Check if payments exist (linked invoices)
                $invoiceModel = $this->model('Invoice');
                $paymentsCount = $invoiceModel->count(['template_id' => $id]);

                if ($paymentsCount > 0 && !isset($_GET['force'])) {
                    $this->setFlash('error', "Cannot delete link: $paymentsCount payments are associated with it.");
                } else {
                    $this->templateModel->delete($id);
                    $this->setFlash('success', 'Payment link deleted successfully');
                }
            }
        } else {
            $invoiceModel = $this->model('Invoice');
            $invoice = $invoiceModel->findOne(['id' => $id, 'company_id' => $companyId]);
            if ($invoice) {
                // Check if payments exist
                $paymentModel = $this->model('Payment');
                $paymentsCount = $paymentModel->count(['invoice_id' => $id]);

                if ($paymentsCount > 0 && !isset($_GET['force'])) {
                    $this->setFlash('error', "Cannot delete invoice: $paymentsCount payments already recorded.");
                } else {
                    $invoiceModel->delete($id);
                    $this->setFlash('success', 'Invoice link deleted successfully');
                }
            }
        }

        $this->redirect('payments');
    }

    /**
     * Admin: Send template link via email (AJAX)
     */
    public function sendEmail($id)
    {
        $this->requireAuth();
        header('Content-Type: application/json');

        $companyId = Session::get('company_id');
        $template = $this->templateModel->findOne(['id' => $id, 'company_id' => $companyId]);
        $toEmail = $_GET['email'] ?? '';

        if (!$template || empty($toEmail)) {
            echo json_encode(['success' => false, 'message' => 'Invalid template or email']);
            exit;
        }

        // Fetch SMTP settings
        $smtpModel = $this->model('SmtpSetting');
        $smtpSettings = $smtpModel->getByCompany($companyId);

        if (!$smtpSettings) {
            echo json_encode(['success' => false, 'message' => 'SMTP settings not configured']);
            exit;
        }

        require_once APP_PATH . '/helpers/InvoiceMailer.php';

        try {
            InvoiceMailer::sendTemplateLink($template, $toEmail, $smtpSettings);
            echo json_encode(['success' => true, 'message' => 'Payment link sent successfully!']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to send email: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Admin: Toggle link status (Open/Closed)
     */
    public function toggle($type, $id)
    {
        $companyId = Session::get('company_id');

        if ($type === 'Client-filled') {
            $template = $this->templateModel->findOne(['id' => $id, 'company_id' => $companyId]);
            if ($template) {
                $newStatus = $template['payment_closed'] ? 0 : 1;
                $this->templateModel->updateStatus($id, $companyId, $newStatus);
                $this->setFlash('success', 'Link status updated successfully');
            }
        } else {
            $invoiceModel = $this->model('Invoice');
            $invoice = $invoiceModel->findOne(['id' => $id, 'company_id' => $companyId]);
            if ($invoice) {
                $newStatus = $invoice['payment_closed'] ? 0 : 1;
                $invoiceModel->updatePaymentStatus($id, $companyId, $newStatus);
                $this->setFlash('success', 'Invoice link status updated successfully');
            }
        }

        $this->redirect('payments');
    }
}
