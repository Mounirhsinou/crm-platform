<?php
/**
 * SmtpSetting Model
 * Handles database operations for SMTP configurations
 */
class SmtpSetting extends Model
{
    protected $table = 'smtp_settings';

    /**
     * Get SMTP settings for a specific company
     */
    public function getByCompany($companyId)
    {
        $settings = $this->findOne(['company_id' => $companyId]);

        if ($settings && !empty($settings['smtp_password'])) {
            $settings['smtp_password_decrypted'] = $this->decryptPassword($settings['smtp_password']);
        }

        return $settings;
    }

    /**
     * Save or update SMTP settings for a company
     */
    public function saveSettings($companyId, $data)
    {
        $existing = $this->getByCompany($companyId);

        // Map keys if needed (the model insert/update uses $data directly)
        $saveData = [
            'company_id' => $companyId,
            'smtp_host' => $data['smtp_host'],
            'smtp_port' => $data['smtp_port'],
            'smtp_username' => $data['smtp_username'],
            'from_name' => $data['from_name'] ?? ($data['smtp_username'] ?? 'CRM Mailer'),
            'from_email' => $data['from_email'] ?? ($data['smtp_username'] ?? ''),
            'is_verified' => $data['is_verified'] ?? 0
        ];

        // Normalize encryption
        $encryption = $data['smtp_encryption'] ?? 'none';
        if ($encryption === '')
            $encryption = 'none';
        $saveData['smtp_encryption'] = $encryption;

        // Handle password if provided
        if (!empty($data['smtp_password'])) {
            // Only encrypt if it's NOT already the encrypted string from DB
            // We can check if it matches the current encrypted password 
            // OR we can just assume if the user leaves it as dots or something we don't change.
            // A better way is to see if it's different.

            $doEncrypt = true;
            $currentDecrypted = $existing['smtp_password_decrypted'] ?? '';

            if ($existing && $data['smtp_password'] === $currentDecrypted) {
                $doEncrypt = false;
            }

            // Special check: if the user sends many asterisks, they might be just submitting the UI masking
            if ($data['smtp_password'] === '••••••••' || $data['smtp_password'] === '********') {
                $doEncrypt = false;
            }

            if ($doEncrypt) {
                $saveData['smtp_password'] = $this->encryptPassword($data['smtp_password']);
            }
        }

        if ($existing) {
            // Remove company_id from update data to avoid "Invalid parameter number" 
            // when it's added again by applyCompanyScope() in the core Model's UPDATE SQL.
            unset($saveData['company_id']);
            return $this->update($existing['id'], $saveData);
        } else {
            return $this->insert($saveData);
        }
    }

    /**
     * Encrypt SMTP password for storage
     */
    private function encryptPassword($password)
    {
        $key = ENCRYPTION_KEY;
        $iv = ENCRYPTION_IV;
        return openssl_encrypt($password, 'AES-256-CBC', $key, 0, $iv);
    }

    /**
     * Decrypt SMTP password for use
     */
    public function decryptPassword($encryptedPassword)
    {
        if (empty($encryptedPassword))
            return '';
        $key = ENCRYPTION_KEY;
        $iv = ENCRYPTION_IV;
        return openssl_decrypt($encryptedPassword, 'AES-256-CBC', $key, 0, $iv);
    }
}
