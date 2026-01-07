<?php
/**
 * Messaging Controller
 */

class MessagingController extends Controller
{
    private $settingsModel;
    private $logModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->settingsModel = $this->model('MessagingSetting');
        $this->logModel = $this->model('MessageLog');
    }

    /**
     * SMS Sender Page
     */
    public function sms()
    {
        $this->requirePermission('messaging', 'send');
        $companyId = Session::get('company_id');
        $settings = $this->settingsModel->getSetting($companyId, 'sms');

        $this->view('messaging/sms', [
            'settings' => $settings,
            'csrf_token' => Security::generateCsrfToken()
        ]);
    }

    /**
     * WhatsApp Sender Page
     */
    public function whatsapp()
    {
        $this->requirePermission('messaging', 'send');
        $companyId = Session::get('company_id');
        $settings = $this->settingsModel->getSetting($companyId, 'whatsapp');

        $this->view('messaging/whatsapp', [
            'settings' => $settings,
            'csrf_token' => Security::generateCsrfToken()
        ]);
    }

    /**
     * Settings Page for Messaging Providers
     */
    public function settings()
    {
        $this->requirePermission('integrations', 'view');
        $companyId = Session::get('company_id');
        $smsSettings = $this->settingsModel->getSetting($companyId, 'sms');
        $whatsappSettings = $this->settingsModel->getSetting($companyId, 'whatsapp');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleSettingsUpdate();
        } else {
            $this->view('settings/integrations', [
                'sms' => $smsSettings,
                'whatsapp' => $whatsappSettings,
                'csrf_token' => Security::generateCsrfToken()
            ]);
        }
    }

    /**
     * View Message Logs
     */
    public function logs()
    {
        $this->requirePermission('messaging', 'view');
        $companyId = Session::get('company_id');
        $logs = $this->logModel->getByCompany($companyId);

        $this->view('messaging/logs', [
            'logs' => $logs
        ]);
    }

    /**
     * Handle Messaging Settings Update
     */
    private function handleSettingsUpdate()
    {
        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('settings/integrations');
        }

        $companyId = Session::get('company_id');
        $type = $_POST['type'] ?? ''; // 'sms' or 'whatsapp'

        $data = [
            'type' => $type,
            'provider' => Security::sanitize($_POST['provider'] ?? ''),
            'api_key' => Security::sanitize($_POST['api_key'] ?? ''),
            'api_secret' => Security::sanitize($_POST['api_secret'] ?? ''),
            'sender_id' => Security::sanitize($_POST['sender_id'] ?? ''),
            'is_enabled' => isset($_POST['is_enabled']) ? 1 : 0
        ];

        if ($this->settingsModel->saveSettings($companyId, $data)) {
            $this->setFlash('success', ucfirst($type) . ' settings updated successfully');
        } else {
            $this->setFlash('error', 'Failed to update settings');
        }

        $this->redirect('settings/integrations');
    }

    /**
     * Handle SMS Sending (Batch)
     */
    public function sendSms()
    {
        $this->requirePermission('messaging', 'send');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->validateCsrf()) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            return;
        }

        $companyId = Session::get('company_id');
        $settings = $this->settingsModel->getSetting($companyId, 'sms');

        if (!$settings || !$settings['is_enabled']) {
            echo json_encode(['success' => false, 'message' => 'SMS provider is not configured or enabled']);
            return;
        }

        $recipients = $_POST['recipients'] ?? '';
        $message = Security::sanitize($_POST['message'] ?? '');

        if (empty($recipients) || empty($message)) {
            echo json_encode(['success' => false, 'message' => 'Recipients and message are required']);
            return;
        }

        // Parse recipients (one per line)
        $phoneList = array_filter(array_map('trim', explode("\n", $recipients)));

        $sentCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach ($phoneList as $phone) {
            $result = MessagingHelper::sendSms($settings, $phone, $message);

            if ($result['success']) {
                $sentCount++;
                $this->logModel->log($companyId, [
                    'type' => 'sms',
                    'recipient' => $phone,
                    'message' => $message,
                    'status' => 'sent'
                ]);
            } else {
                $failedCount++;
                $this->logModel->log($companyId, [
                    'type' => 'sms',
                    'recipient' => $phone,
                    'message' => $message,
                    'status' => 'failed',
                    'error_message' => $result['message'] ?? 'Unknown error'
                ]);
            }
        }

        echo json_encode([
            'success' => true,
            'message' => "Batch completed. Sent: $sentCount, Failed: $failedCount",
            'sent' => $sentCount,
            'failed' => $failedCount
        ]);
    }

    /**
     * Handle WhatsApp Sending
     */
    public function sendWhatsapp()
    {
        $this->requirePermission('messaging', 'send');
        // Similar logic to sendSms but for WhatsApp
        // For now, mirroring as a placeholder
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->validateCsrf()) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            return;
        }

        $companyId = Session::get('company_id');
        $settings = $this->settingsModel->getSetting($companyId, 'whatsapp');

        if (!$settings || !$settings['is_enabled']) {
            echo json_encode(['success' => false, 'message' => 'WhatsApp provider is not configured or enabled']);
            return;
        }

        $recipients = $_POST['recipients'] ?? '';
        $message = Security::sanitize($_POST['message'] ?? '');

        if (empty($recipients) || empty($message)) {
            echo json_encode(['success' => false, 'message' => 'Recipients and message are required']);
            return;
        }

        $phoneList = array_filter(array_map('trim', explode("\n", $recipients)));

        $sentCount = 0;
        $failedCount = 0;

        foreach ($phoneList as $phone) {
            $result = MessagingHelper::sendWhatsapp($settings, $phone, $message);

            if ($result['success']) {
                $sentCount++;
                $this->logModel->log($companyId, [
                    'type' => 'whatsapp',
                    'recipient' => $phone,
                    'message' => $message,
                    'status' => 'sent'
                ]);
            } else {
                $failedCount++;
                $this->logModel->log($companyId, [
                    'type' => 'whatsapp',
                    'recipient' => $phone,
                    'message' => $message,
                    'status' => 'failed',
                    'error_message' => $result['message'] ?? 'Unknown error'
                ]);
            }
        }

        echo json_encode([
            'success' => true,
            'message' => "WhatsApp batch completed. Sent: $sentCount, Failed: $failedCount",
            'sent' => $sentCount,
            'failed' => $failedCount
        ]);
    }
}
