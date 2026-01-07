<?php
/**
 * Email Notification Helper
 * Sends email notifications for user management
 */

require_once APP_PATH . '/helpers/PHPMailer/Exception.php';
require_once APP_PATH . '/helpers/PHPMailer/PHPMailer.php';
require_once APP_PATH . '/helpers/PHPMailer/SMTP.php';

class EmailNotification
{
    /**
     * Send welcome email with credentials to new user
     * 
     * @param array $user User data
     * @param string $password Auto-generated password
     * @return array ['success' => bool, 'message' => string]
     */
    public static function sendWelcomeEmail($user, $password)
    {
        // Get SMTP settings
        $smtpModel = new SmtpSetting();
        $settings = $smtpModel->getByCompany(Session::get('company_id'));

        if (!$settings || !$settings['is_verified']) {
            return [
                'success' => false,
                'message' => 'SMTP settings not configured or verified'
            ];
        }

        // Get company branding
        $branding = Branding::getAll();
        $companyName = $branding['company_name'] ?? 'CRM System';

        // Email subject
        $subject = "Welcome to {$companyName} - Your Account Details";

        // Email body
        $body = self::getWelcomeEmailTemplate($user, $password, $companyName);

        // Send email using PHPMailer
        return self::sendEmail($settings, $user['email'], $user['full_name'], $subject, $body);
    }

    /**
     * Send password reset email
     * 
     * @param array $user User data
     * @param string $newPassword New auto-generated password
     * @return array ['success' => bool, 'message' => string]
     */
    public static function sendPasswordResetEmail($user, $newPassword)
    {
        // Get SMTP settings
        $smtpModel = new SmtpSetting();
        $settings = $smtpModel->getByCompany(Session::get('company_id'));

        if (!$settings || !$settings['is_verified']) {
            return [
                'success' => false,
                'message' => 'SMTP settings not configured or verified'
            ];
        }

        // Get company branding
        $branding = Branding::getAll();
        $companyName = $branding['company_name'] ?? 'CRM System';

        // Email subject
        $subject = "Password Reset - {$companyName}";

        // Email body
        $body = self::getPasswordResetTemplate($user, $newPassword, $companyName);

        // Send email using PHPMailer
        return self::sendEmail($settings, $user['email'], $user['full_name'], $subject, $body);
    }

    /**
     * Send email using PHPMailer
     * 
     * @param array $settings SMTP settings
     * @param string $toEmail Recipient email
     * @param string $toName Recipient name
     * @param string $subject Email subject
     * @param string $body Email body (HTML)
     * @return array ['success' => bool, 'message' => string]
     */
    public static function sendEmail($settings, $toEmail, $toName, $subject, $body)
    {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = $settings['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $settings['smtp_username'];
            $mail->Password = $settings['smtp_password'];
            $mail->SMTPSecure = $settings['smtp_encryption'];
            $mail->Port = $settings['smtp_port'];
            $mail->CharSet = 'UTF-8';

            // Recipients
            $mail->setFrom($settings['from_email'], $settings['from_name']);
            $mail->addAddress($toEmail, $toName);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = strip_tags($body);

            $mail->send();

            return [
                'success' => true,
                'message' => 'Email sent successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Email could not be sent: ' . $mail->ErrorInfo
            ];
        }
    }

    /**
     * Get welcome email template
     * 
     * @param array $user
     * @param string $password
     * @param string $companyName
     * @return string HTML email template
     */
    private static function getWelcomeEmailTemplate($user, $password, $companyName)
    {
        $loginUrl = APP_URL . '/auth/login';

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .credentials { background: white; padding: 20px; border-left: 4px solid #667eea; margin: 20px 0; }
                .button { display: inline-block; background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Welcome to {$companyName}</h1>
                </div>
                <div class='content'>
                    <p>Hello <strong>{$user['full_name']}</strong>,</p>
                    
                    <p>Your account has been created successfully. You can now access the CRM system using the credentials below:</p>
                    
                    <div class='credentials'>
                        <p><strong>Email:</strong> {$user['email']}</p>
                        <p><strong>Temporary Password:</strong> <code style='background: #f0f0f0; padding: 5px 10px; border-radius: 3px;'>{$password}</code></p>
                        <p><strong>Role:</strong> {$user['role_name']}</p>
                    </div>
                    
                    <p><strong>⚠️ Important:</strong> For security reasons, you will be required to change your password upon first login.</p>
                    
                    <div style='text-align: center;'>
                        <a href='{$loginUrl}' class='button'>Login to CRM</a>
                    </div>
                    
                    <p>If you have any questions or need assistance, please contact your administrator.</p>
                    
                    <div class='footer'>
                        <p>This is an automated message from {$companyName}. Please do not reply to this email.</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Get password reset email template
     * 
     * @param array $user
     * @param string $newPassword
     * @param string $companyName
     * @return string HTML email template
     */
    private static function getPasswordResetTemplate($user, $newPassword, $companyName)
    {
        $loginUrl = APP_URL . '/auth/login';

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .credentials { background: white; padding: 20px; border-left: 4px solid #667eea; margin: 20px 0; }
                .button { display: inline-block; background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Password Reset</h1>
                </div>
                <div class='content'>
                    <p>Hello <strong>{$user['full_name']}</strong>,</p>
                    
                    <p>Your password has been reset by an administrator. You can now login using the new temporary password below:</p>
                    
                    <div class='credentials'>
                        <p><strong>Email:</strong> {$user['email']}</p>
                        <p><strong>New Temporary Password:</strong> <code style='background: #f0f0f0; padding: 5px 10px; border-radius: 3px;'>{$newPassword}</code></p>
                    </div>
                    
                    <p><strong>⚠️ Important:</strong> For security reasons, you will be required to change this password upon your next login.</p>
                    
                    <div style='text-align: center;'>
                        <a href='{$loginUrl}' class='button'>Login to CRM</a>
                    </div>
                    
                    <p>If you did not request this password reset, please contact your administrator immediately.</p>
                    
                    <div class='footer'>
                        <p>This is an automated message from {$companyName}. Please do not reply to this email.</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
