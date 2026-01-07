<?php
/**
 * MailerController
 * Standalone Single Page Mail List Sender
 */

require_once APP_PATH . '/helpers/PHPMailer/Exception.php';
require_once APP_PATH . '/helpers/PHPMailer/PHPMailer.php';
require_once APP_PATH . '/helpers/PHPMailer/SMTP.php';

class MailerController extends Controller
{
    private $smtpModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->smtpModel = $this->model('SmtpSetting');
    }

    /**
     * Display the single-page Mail List Sender tool
     */
    public function index()
    {
        $companyId = Session::get('company_id');
        $smtpSettings = $this->smtpModel->getByCompany($companyId);

        $this->view('mailer/index', [
            'csrf_token' => Security::generateCsrfToken(),
            'smtp' => $smtpSettings
        ]);
    }

    /**
     * Save SMTP settings (AJAX)
     */
    public function saveSmtp()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        try {
            $companyId = Session::get('company_id');

            $data = [
                'smtp_host' => $_POST['smtp_host'] ?? '',
                'smtp_port' => $_POST['smtp_port'] ?? '',
                'smtp_username' => $_POST['smtp_user'] ?? '',
                'smtp_password' => $_POST['smtp_pass'] ?? '',
                'smtp_encryption' => $_POST['smtp_enc'] ?? 'none',
                'from_name' => $_POST['from_name'] ?? ($_POST['smtp_user'] ?? 'CRM Mailer'),
                'from_email' => $_POST['from_email'] ?? ($_POST['smtp_user'] ?? ''),
                'is_verified' => 1
            ];

            // If everything is empty, they might be trying to clear it
            $isEmpty = empty($data['smtp_host']) && empty($data['smtp_username']) && empty($data['smtp_password']);

            if (!$isEmpty && (empty($data['smtp_host']) || empty($data['smtp_port']))) {
                echo json_encode(['success' => false, 'message' => 'SMTP Host and Port are required.']);
                exit;
            }

            if ($this->smtpModel->saveSettings($companyId, $data)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database update failed.']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * AJAX action to send a single email using provided SMTP settings
     */
    public function send()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        // SMTP Settings from POST
        $smtpHost = $_POST['smtp_host'] ?? '';
        $smtpPort = $_POST['smtp_port'] ?? '';
        $smtpUser = $_POST['smtp_user'] ?? '';
        $smtpPass = $_POST['smtp_pass'] ?? '';
        $smtpEnc = $_POST['smtp_enc'] ?? '';

        // Email Details from POST
        $toEmail = $_POST['to_email'] ?? '';
        $subject = $_POST['subject'] ?? '';
        $body = $_POST['body'] ?? '';
        $isHtml = ($_POST['email_type'] === 'html');

        if (!$smtpHost || !$smtpPort || !$toEmail || !$subject || !$body) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = $smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $smtpUser;
            $mail->Password = $smtpPass;
            $mail->Port = $smtpPort;

            if ($smtpEnc === 'ssl') {
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($smtpEnc === 'tls') {
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            }

            // Recipients
            $mail->setFrom($smtpUser);
            $mail->addAddress($toEmail);

            // Content
            $mail->isHTML($isHtml);
            $mail->Subject = $subject;
            $mail->Body = $body;

            if ($isHtml) {
                $mail->AltBody = strip_tags($body);
            }

            $mail->send();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $mail->ErrorInfo ?: $e->getMessage()]);
        }
        exit;
    }
}
