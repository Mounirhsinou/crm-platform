<?php
/**
 * Leads Controller
 * Handles the management of leads collected through public checkout
 */

class LeadsController extends Controller
{
    private $leadModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->leadModel = $this->model('Lead');
    }

    /**
     * List all leads
     */
    public function index()
    {
        $this->requirePermission('leads', 'view');
        $companyId = Session::get('company_id');
        $leads = $this->leadModel->getByCompany($companyId);

        // Get collection settings to check for export permission
        $settingModel = $this->model('Setting');
        $allowExport = $settingModel->get($companyId, 'lead_allow_export', '1') === '1';

        $this->view('leads/index', [
            'leads' => $leads,
            'allow_export' => $allowExport,
            'csrf_token' => Security::generateCsrfToken()
        ]);
    }

    /**
     * Export leads to CSV
     */
    public function export()
    {
        $this->requirePermission('leads', 'view');
        $companyId = Session::get('company_id');

        // Check if export is allowed
        $settingModel = $this->model('Setting');
        if ($settingModel->get($companyId, 'lead_allow_export', '1') !== '1') {
            $this->setFlash('error', 'Lead export is disabled in settings');
            $this->redirect('leads');
        }

        $leads = $this->leadModel->getByCompany($companyId);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="collected_leads_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Name', 'Email', 'Phone', 'Company', 'Address', 'City', 'Country', 'Source', 'Collected Date']);

        foreach ($leads as $lead) {
            fputcsv($output, [
                $lead['name'],
                $lead['email'],
                $lead['phone'],
                $lead['company'],
                $lead['address'],
                $lead['city'],
                $lead['country'],
                ucfirst($lead['source']),
                $lead['created_at']
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Send email to lead (AJAX)
     */
    public function sendEmail($id)
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        $companyId = Session::get('company_id');
        $lead = $this->leadModel->findOne(['id' => $id, 'company_id' => $companyId]);

        if (!$lead) {
            echo json_encode(['success' => false, 'message' => 'Lead not found']);
            exit;
        }

        if (empty($lead['email'])) {
            echo json_encode(['success' => false, 'message' => 'Lead does not have an email address']);
            exit;
        }

        $subject = Security::sanitize($_POST['subject'] ?? '');
        $message = $_POST['message'] ?? ''; // Body might contain HTML if we enable it later, but for now simple text

        if (empty($subject) || empty($message)) {
            echo json_encode(['success' => false, 'message' => 'Subject and message are required']);
            exit;
        }

        // Fetch SMTP settings
        $smtpModel = $this->model('SmtpSetting');
        $smtpSettings = $smtpModel->getByCompany($companyId);

        if (!$smtpSettings || !$smtpSettings['is_verified']) {
            echo json_encode([
                'success' => false,
                'message' => 'SMTP settings not configured or verified. Please set them up in the Mail List Sender tool.'
            ]);
            exit;
        }

        // Send Email
        require_once APP_PATH . '/helpers/EmailNotification.php';
        $result = EmailNotification::sendEmail(
            $smtpSettings,
            $lead['email'],
            $lead['name'] ?: 'Lead',
            $subject,
            nl2br(Security::escape($message)) // Basic conversion for body
        );

        echo json_encode($result);
        exit;
    }

    /**
     * Delete a lead
     */
    public function delete($id)
    {
        $token = $_GET['csrf_token'] ?? $_POST['csrf_token'] ?? '';
        if (!Security::validateCsrfToken($token)) {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('leads');
        }

        $companyId = Session::get('company_id');
        $lead = $this->leadModel->findOne(['id' => $id, 'company_id' => $companyId]);

        if ($lead && $this->leadModel->delete($id)) {
            $this->setFlash('success', 'Lead removed successfully');
        } else {
            $this->setFlash('error', 'Failed to remove lead');
        }

        $this->redirect('leads');
    }
}
