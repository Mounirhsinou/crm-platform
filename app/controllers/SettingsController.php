<?php
/**
 * Settings Controller
 * Handles application settings and company branding
 */

class SettingsController extends Controller
{
    private $companyModel;
    private $activityModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->companyModel = $this->model('Company');
        $this->activityModel = $this->model('ActivityLog');
    }

    /**
     * Settings index page
     */
    public function index()
    {
        $this->requirePermission('settings', 'view');
        $company = $this->companyModel->getBySession();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleUpdate();
        } else {
            $this->view('settings/index', [
                'company' => $company,
                'csrf_token' => Security::generateCsrfToken(),
                'active_tab' => 'branding'
            ]);
        }
    }

    /**
     * Payment settings page
     */
    public function payments()
    {
        $this->requirePermission('payments', 'view');
        $company = $this->companyModel->getBySession();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleUpdate('payments');
        } else {
            $this->view('settings/payments', [
                'company' => $company,
                'csrf_token' => Security::generateCsrfToken(),
                'active_tab' => 'payments'
            ]);
        }
    }

    /**
     * Data collection settings page
     */
    public function dataCollection()
    {
        $this->requirePermission('settings', 'view');
        $companyId = Session::get('company_id');
        $settingModel = $this->model('Setting');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->setFlash('error', 'Invalid request');
                $this->redirect('settings/dataCollection');
            }

            $settings = [
                'lead_collect_name' => $_POST['lead_collect_name'] ?? '0',
                'lead_collect_email' => $_POST['lead_collect_email'] ?? '0',
                'lead_collect_phone' => $_POST['lead_collect_phone'] ?? '0',
                'lead_collect_address' => $_POST['lead_collect_address'] ?? '0',
                'lead_deduplication' => $_POST['lead_deduplication'] ?? '0',
                'lead_allow_export' => $_POST['lead_allow_export'] ?? '0'
            ];

            foreach ($settings as $key => $value) {
                $settingModel->save($companyId, $key, $value);
            }

            $this->setFlash('success', 'Data collection settings updated');
            $this->redirect('settings/dataCollection');
        } else {
            $keys = [
                'lead_collect_name',
                'lead_collect_email',
                'lead_collect_phone',
                'lead_collect_address',
                'lead_deduplication',
                'lead_allow_export'
            ];
            $currentSettings = $settingModel->getMultiple($companyId, $keys);

            $this->view('settings/data_collection', [
                'settings' => $currentSettings,
                'csrf_token' => Security::generateCsrfToken(),
                'active_tab' => 'data_collection'
            ]);
        }
    }

    /**
     * Handle settings update
     */
    private function handleUpdate($redirectTab = 'index')
    {
        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('settings' . ($redirectTab !== 'index' ? '/' . $redirectTab : ''));
        }

        $companyId = Session::get('company_id');
        $data = [];
        $updateFields = [
            'company_name',
            'owner_email',
            'owner_name',
            'address',
            'phone',
            'email',
            'website',
            'paypal_client_id',
            'paypal_secret',
            'stripe_publishable_key',
            'stripe_secret_key',
            'stripe_webhook_secret',
            'stripe_mode'
        ];

        foreach ($updateFields as $field) {
            if (isset($_POST[$field])) {
                $data[$field] = Security::sanitize($_POST[$field]);
            }
        }

        if (empty($data) && !isset($_FILES['logo'])) {
            $this->redirect('settings' . ($redirectTab !== 'index' ? '/' . $redirectTab : ''));
        }

        // Handle logo upload
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->handleLogoUpload($companyId);

            if ($uploadResult['success']) {
                $data['logo_path'] = $uploadResult['path'];
            } else {
                $this->setFlash('error', $uploadResult['message']);
                $this->redirect('settings');
                return;
            }
        }

        // Validate
        $validator = new Validator();
        if (isset($data['company_name'])) {
            $validator->max('company_name', $data['company_name'], 255, 'Company Name');
        }

        if ($validator->fails()) {
            $this->view('settings/index', [
                'company' => $data,
                'errors' => $validator->getErrors(),
                'csrf_token' => Security::generateCsrfToken()
            ]);
            return;
        }

        // Save
        if ($this->companyModel->update($companyId, $data)) {
            // Force branding cache to refresh
            Branding::clearCache();
            $this->setFlash('success', 'Settings updated successfully');
        } else {
            $this->setFlash('error', 'Failed to update settings');
        }

        $this->redirect('settings' . ($redirectTab !== 'index' ? '/' . $redirectTab : ''));
    }

    /**
     * Handle logo file upload
     * 
     * @param int $userId
     * @return array
     */
    private function handleLogoUpload($companyId)
    {
        $file = $_FILES['logo'];

        // Validate file size (2MB max)
        if ($file['size'] > 2 * 1024 * 1024) {
            return ['success' => false, 'message' => 'Logo file size must be less than 2MB'];
        }

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/svg+xml'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            return ['success' => false, 'message' => 'Only JPG, PNG, and SVG files are allowed'];
        }

        // Get file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Create uploads directory if it doesn't exist
        $uploadDir = PUBLIC_PATH . '/uploads/logos';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Create .htaccess to prevent PHP execution in uploads directory
        $htaccessPath = PUBLIC_PATH . '/uploads/.htaccess';
        if (!file_exists($htaccessPath)) {
            $htaccessContent = "Options -ExecCGI -Indexes\n";
            $htaccessContent .= "RemoveHandler .php .phtml .php3 .php4 .php5 .phps\n";
            $htaccessContent .= "RemoveType .php .phtml .php3 .php4 .php5 .phps\n";
            $htaccessContent .= "php_flag engine off\n";
            $htaccessContent .= "<FilesMatch \"\.(?i:php|phtml|php3|php4|php5|phps)$\">\n";
            $htaccessContent .= "    Order allow,deny\n";
            $htaccessContent .= "    Deny from all\n";
            $htaccessContent .= "</FilesMatch>\n";
            file_put_contents($htaccessPath, $htaccessContent);
        }

        // Delete old logo if exists
        $this->companyModel->deleteLogo($companyId);

        // Generate unique filename with timestamp to prevent browser caching
        $filename = 'logo_' . $companyId . '_' . time() . '.' . $extension;
        $uploadPath = $uploadDir . '/' . $filename;
        $relativePath = '/uploads/logos/' . $filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return ['success' => true, 'path' => $relativePath];
        } else {
            return ['success' => false, 'message' => 'Failed to upload logo'];
        }
    }

    /**
     * Delete logo
     */
    public function deleteLogo()
    {
        $this->requirePermission('settings', 'edit');
        $companyId = Session::get('company_id');

        if ($this->companyModel->deleteLogo($companyId)) {
            $this->setFlash('success', 'Logo deleted successfully');
        } else {
            $this->setFlash('error', 'Failed to delete logo');
        }

        $this->redirect('settings');
    }

    /**
     * Security settings page (password change)
     */
    public function security()
    {
        $this->requirePermission('security', 'view');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePasswordChange();
        } else {
            $this->view('settings/security', [
                'csrf_token' => Security::generateCsrfToken()
            ]);
        }
    }

    /**
     * Handle password change
     */
    private function handlePasswordChange()
    {
        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('settings/security');
        }

        $userId = $this->getUserId();
        $userModel = $this->model('User');
        $user = $userModel->findOne(['id' => $userId]);

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validation
        $validator = new Validator();
        $errors = [];

        // Check if user has a local password (not Google-only account)
        if (empty($user['password_hash'])) {
            $errors['general'] = 'Cannot change password for Google-authenticated accounts';
        } else {
            // Verify current password
            if (!password_verify($currentPassword, $user['password_hash'])) {
                $errors['current_password'] = 'Current password is incorrect';
            }
        }

        // Validate new password
        if (strlen($newPassword) < 8) {
            $errors['new_password'] = 'New password must be at least 8 characters';
        }

        // Check password confirmation
        if ($newPassword !== $confirmPassword) {
            $errors['confirm_password'] = 'Passwords do not match';
        }

        if (!empty($errors)) {
            $this->view('settings/security', [
                'errors' => $errors,
                'csrf_token' => Security::generateCsrfToken()
            ]);
            return;
        }

        // Update password
        $newPasswordHash = password_hash($newPassword, PASSWORD_HASH_ALGO, ['cost' => PASSWORD_HASH_COST]);

        if ($userModel->update($userId, ['password_hash' => $newPasswordHash])) {
            $this->setFlash('success', 'Password changed successfully');
        } else {
            $this->setFlash('error', 'Failed to change password');
        }

        $this->redirect('settings/security');
    }

    /**
     * Enable 2FA - Show QR code
     */
    public function enableTwoFactor()
    {
        $this->requirePermission('security', 'edit');
        $userId = $this->getUserId();
        $userModel = $this->model('User');
        $user = $userModel->findOne(['id' => $userId]);

        // Generate secret
        $secret = TOTP::generateSecret();
        Session::set('2fa_setup_secret', $secret);

        $this->view('settings/2fa-setup', [
            'secret' => $secret,
            'qr_url' => TOTP::getQRCodeUrl($user, $secret),
            'csrf_token' => Security::generateCsrfToken()
        ]);
    }

    /**
     * Verify and activate 2FA
     */
    public function verifyTwoFactor()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('settings/security');
        }

        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('settings/security');
        }

        $code = $_POST['code'] ?? '';
        $secret = Session::get('2fa_setup_secret');

        if (!$secret) {
            $this->setFlash('error', '2FA setup expired. Please try again.');
            $this->redirect('settings/security');
        }

        if (TOTP::verify($secret, $code)) {
            $userId = $this->getUserId();
            $userModel = $this->model('User');

            // Enable 2FA
            $userModel->update($userId, [
                'two_factor_secret' => $secret,
                'two_factor_enabled' => 1,
                'two_factor_verified_at' => date('Y-m-d H:i:s')
            ]);

            // Generate backup codes
            $backupCodeModel = $this->model('BackupCode');
            $backupCodes = $backupCodeModel->generate($userId, 10);

            Session::remove('2fa_setup_secret');

            $this->view('settings/2fa-backup-codes', [
                'backup_codes' => $backupCodes
            ]);
        } else {
            $this->setFlash('error', 'Invalid verification code. Please try again.');
            $this->redirect('settings/enableTwoFactor');
        }
    }

    /**
     * Disable 2FA
     */
    public function disableTwoFactor()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('settings/security');
        }

        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('settings/security');
        }

        $password = $_POST['password'] ?? '';
        $userId = $this->getUserId();
        $userModel = $this->model('User');
        $user = $userModel->findOne(['id' => $userId]);

        if (password_verify($password, $user['password_hash'])) {
            $userModel->update($userId, [
                'two_factor_secret' => null,
                'two_factor_enabled' => 0,
                'two_factor_verified_at' => null
            ]);

            $backupCodeModel = $this->model('BackupCode');
            $backupCodeModel->deleteByUser($userId);

            $this->setFlash('success', 'Two-Factor Authentication has been disabled');
        } else {
            $this->setFlash('error', 'Incorrect password');
        }

        $this->redirect('settings/security');
    }

    /**
     * Email settings page (SMTP)
     */
    public function email()
    {
        $this->requirePermission('settings', 'view');
        $companyId = Session::get('company_id');
        $smtpModel = $this->model('SmtpSetting');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleEmailSettings();
        } else {
            $settings = $smtpModel->getByCompany($companyId);

            $this->view('settings/email', [
                'settings' => $settings,
                'csrf_token' => Security::generateCsrfToken()
            ]);
        }
    }

    /**
     * Handle email settings update
     */
    private function handleEmailSettings()
    {
        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('settings/email');
        }

        $companyId = Session::get('company_id');

        // Validate and save SMTP settings
        $data = [
            'smtp_host' => Security::sanitize($_POST['smtp_host'] ?? ''),
            'smtp_port' => (int) ($_POST['smtp_port'] ?? 587),
            'smtp_username' => Security::sanitize($_POST['smtp_username'] ?? ''),
            'smtp_password' => $_POST['smtp_password'] ?? '',
            'smtp_encryption' => $_POST['smtp_encryption'] ?? 'tls',
            'from_name' => Security::sanitize($_POST['from_name'] ?? ''),
            'from_email' => Security::sanitize($_POST['from_email'] ?? '')
        ];

        // Basic validation
        if (empty($data['smtp_host']) || empty($data['smtp_username']) || empty($data['from_email'])) {
            $this->setFlash('error', 'Please fill in all required fields');
            $this->redirect('settings/email');
            return;
        }

        $smtpModel = $this->model('SmtpSetting');

        // Test connection
        $testResult = $smtpModel->testConnection($data);

        if ($testResult['success']) {
            $data['is_verified'] = 1;

            if ($smtpModel->saveSettings($companyId, $data)) {
                $this->setFlash('success', 'SMTP settings saved and verified successfully');
            } else {
                $this->setFlash('error', 'Failed to save settings');
            }
        } else {
            // Save anyway but mark as unverified
            $data['is_verified'] = 0;
            $smtpModel->saveSettings($companyId, $data);
            $this->setFlash('error', 'Settings saved but connection Failed: ' . $testResult['message']);
        }

        $this->redirect('settings/email');
    }

    public function integrations()
    {
        $this->requirePermission('integrations', 'view');
        $companyId = Session::get('company_id');
        $messagingModel = $this->model('MessagingSetting');

        $smsSettings = $messagingModel->getSetting($companyId, 'sms');
        $whatsappSettings = $messagingModel->getSetting($companyId, 'whatsapp');

        $this->view('settings/integrations', [
            'sms' => $smsSettings,
            'whatsapp' => $whatsappSettings,
            'csrf_token' => Security::generateCsrfToken(),
            'active_tab' => 'integrations'
        ]);
    }

    /**
     * User management page (Super Admin only)
     */
    public function users()
    {
        // Require Users View permission
        $this->requirePermission('users', 'view');

        $userModel = $this->model('User');
        $roleModel = $this->model('Role');
        $companyId = Session::get('company_id');

        // CRITICAL: Only fetch users from the current company
        $users = $userModel->getAllUsersByCompany($companyId);
        $roles = $roleModel->getAllRoles();

        $this->view('settings/users', [
            'users' => $users,
            'roles' => $roles,
            'csrf_token' => Security::generateCsrfToken(),
            'active_tab' => 'users'
        ]);
    }

    /**
     * Create new user
     */
    public function createUser()
    {
        // Require Users Create permission
        $this->requirePermission('users', 'create');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleUserCreation();
        } else {
            $roleModel = $this->model('Role');
            $currentUserRole = $this->getUserRole();

            // Get roles that current user can assign
            $availableRoles = $roleModel->getCreatableRoles($currentUserRole['slug']);

            $this->view('settings/create_user', [
                'roles' => $availableRoles,
                'csrf_token' => Security::generateCsrfToken()
            ]);
        }
    }

    /**
     * Handle user creation
     */
    private function handleUserCreation()
    {
        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('settings/users');
        }

        $fullName = Security::sanitize($_POST['full_name'] ?? '');
        $email = Security::sanitize($_POST['email'] ?? '');
        $roleId = (int) ($_POST['role_id'] ?? 0);
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $mustChangePassword = isset($_POST['must_change_password']) ? 1 : 0;

        // Validation
        $validator = new Validator();
        $validator->required('full_name', $fullName, 'Full Name');
        $validator->email('email', $email, 'Email');
        $validator->required('role_id', $roleId, 'Role');
        $validator->required('password', $password, 'Password');
        $validator->min('password', $password, 8, 'Password');

        if ($validator->fails()) {
            $this->setFlash('error', implode(', ', $validator->getErrors()));
            $this->redirect('settings/createUser');
        }

        // Validate password confirmation
        if ($password !== $confirmPassword) {
            $this->setFlash('error', 'Passwords do not match');
            $this->redirect('settings/createUser');
        }

        // Validate password strength
        $passwordValidation = PasswordGenerator::validate($password);
        if (!$passwordValidation['valid']) {
            $this->setFlash('error', 'Password: ' . implode(', ', $passwordValidation['errors']));
            $this->redirect('settings/createUser');
        }

        // Check if email already exists
        $userModel = $this->model('User');
        if ($userModel->emailExists($email)) {
            $this->setFlash('error', 'Email address already exists');
            $this->redirect('settings/createUser');
        }

        // Verify current user can create this role
        $roleModel = $this->model('Role');
        $currentUserRole = $this->getUserRole();
        $targetRole = $roleModel->getRoleById($roleId);

        if (!$targetRole || !$roleModel->canCreateRole($currentUserRole['slug'], $targetRole['slug'])) {
            $this->setFlash('error', 'You do not have permission to create users with this role');
            $this->redirect('settings/createUser');
        }

        // Create user with admin-provided password
        $companyId = Session::get('company_id');
        $userId = $userModel->createUser([
            'company_id' => $companyId,  // CRITICAL: Assign to current company
            'full_name' => $fullName,
            'email' => $email,
            'password' => $password,
            'role_id' => $roleId,
            'must_change_password' => $mustChangePassword,
            'is_active' => 1
        ]);

        if ($userId) {
            $companyId = Session::get('company_id');
            $this->activityModel->log($this->getUserId(), 'user_create', "Created user: $email");
            $this->setFlash('success', 'User created successfully.');
        } else {
            $this->setFlash('error', 'Failed to create user');
        }

        $this->redirect('settings/users');
    }

    /**
     * Update user role
     */
    public function updateUserRole()
    {
        $this->requirePermission('users', 'edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('settings/users');
        }

        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('settings/users');
        }

        $userId = (int) ($_POST['user_id'] ?? 0);
        $roleId = (int) ($_POST['role_id'] ?? 0);

        if (!$userId || !$roleId) {
            $this->setFlash('error', 'Invalid parameters');
            $this->redirect('settings/users');
        }

        // Prevent changing own role
        if ($userId == $this->getUserId()) {
            $this->setFlash('error', 'You cannot change your own role');
            $this->redirect('settings/users');
        }

        // Verify permission to assign this role
        $roleModel = $this->model('Role');
        $currentUserRole = $this->getUserRole();
        $targetRole = $roleModel->getRoleById($roleId);

        if (!$targetRole || !$roleModel->canCreateRole($currentUserRole['slug'], $targetRole['slug'])) {
            $this->setFlash('error', 'You do not have permission to assign this role');
            $this->redirect('settings/users');
        }

        $userModel = $this->model('User');
        $companyId = Session::get('company_id');

        // CRITICAL: Verify user belongs to current company
        $user = $userModel->getUserWithRole($userId, $companyId);

        if (!$user) {
            if ($this->isAjax()) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized: User not found or belongs to another company']);
                exit;
            }
            $this->setFlash('error', 'Unauthorized access');
            $this->redirect('settings/users');
        }

        if ($userModel->updateUserRole($userId, $roleId)) {
            $companyId = Session::get('company_id');
            $this->activityModel->log($this->getUserId(), 'user_role_change', "Changed role for {$user['email']} to {$targetRole['name']}");

            if ($this->isAjax()) {
                echo json_encode(['success' => true, 'message' => 'User role updated successfully']);
                exit;
            }
            $this->setFlash('success', 'User role updated successfully');
        } else {
            if ($this->isAjax()) {
                echo json_encode(['success' => false, 'message' => 'Failed to update user role']);
                exit;
            }
            $this->setFlash('error', 'Failed to update user role');
        }

        $this->redirect('settings/users');
    }

    /**
     * Toggle user active status
     */
    public function toggleUser()
    {
        $this->requirePermission('users', 'edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('settings/users');
        }

        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('settings/users');
        }

        $userId = (int) ($_POST['user_id'] ?? 0);

        if (!$userId) {
            $this->setFlash('error', 'Invalid user ID');
            $this->redirect('settings/users');
        }

        // Prevent disabling own account
        if ($userId == $this->getUserId()) {
            $this->setFlash('error', 'You cannot disable your own account');
            $this->redirect('settings/users');
        }

        $userModel = $this->model('User');
        $companyId = Session::get('company_id');

        // CRITICAL: Verify user belongs to current company
        $user = $userModel->getUserWithRole($userId, $companyId);

        if (!$user) {
            if ($this->isAjax()) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized: User not found or belongs to another company']);
                exit;
            }
            $this->setFlash('error', 'Unauthorized access');
            $this->redirect('settings/users');
        }

        if ($userModel->toggleUserStatus($userId)) {
            $companyId = Session::get('company_id');
            $newStatus = $user['is_active'] ? 'disabled' : 'enabled';
            $this->activityModel->log($this->getUserId(), "user_$newStatus", "User account $newStatus: {$user['email']}");

            if ($this->isAjax()) {
                echo json_encode(['success' => true, 'message' => 'User ' . $newStatus . ' successfully']);
                exit;
            }
            $this->setFlash('success', 'User ' . $newStatus . ' successfully');
        } else {
            if ($this->isAjax()) {
                echo json_encode(['success' => false, 'message' => 'Failed to update user status']);
                exit;
            }
            $this->setFlash('error', 'Failed to update user status');
        }

        $this->redirect('settings/users');
    }

    /**
     * Reset user password
     */
    public function resetUserPassword()
    {
        $this->requirePermission('users', 'edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('settings/users');
        }

        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('settings/users');
        }

        $userId = (int) ($_POST['user_id'] ?? 0);

        if (!$userId) {
            $this->setFlash('error', 'Invalid user ID');
            $this->redirect('settings/users');
        }

        // Generate new password
        $newPassword = PasswordGenerator::generate(12);

        $userModel = $this->model('User');
        $companyId = Session::get('company_id');

        // CRITICAL: Verify user belongs to current company
        $user = $userModel->getUserWithRole($userId, $companyId);

        if (!$user) {
            $this->setFlash('error', 'Unauthorized: User not found or belongs to another company');
            $this->redirect('settings/users');
        }

        if (!$user) {
            $this->setFlash('error', 'User not found');
            $this->redirect('settings/users');
        }

        // Reset password
        if ($userModel->resetUserPassword($userId, $newPassword)) {
            $companyId = Session::get('company_id');
            $this->activityModel->log($this->getUserId(), 'user_password_reset', "Reset password for {$user['email']}");
            // Send email with new password
            $emailResult = EmailNotification::sendPasswordResetEmail($user, $newPassword);

            if ($emailResult['success']) {
                $this->setFlash('success', 'Password reset successfully. New credentials sent to ' . $user['email']);
            } else {
                $this->setFlash('warning', 'Password reset but email could not be sent. New password: ' . $newPassword);
            }
        } else {
            $this->setFlash('error', 'Failed to reset password');
        }

        $this->redirect('settings/users');
    }

    /**
     * Admin set user password manually
     */
    public function adminSetPassword()
    {
        $this->requirePermission('users', 'edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->validateCsrf()) {
            $this->redirect('settings/users');
        }

        $userId = (int) ($_POST['user_id'] ?? 0);
        $newPassword = $_POST['password'] ?? '';
        $mustChange = isset($_POST['must_change_password']) ? 1 : 0;

        if (!$userId || strlen($newPassword) < 8) {
            $this->setFlash('error', 'Invalid password (min 8 characters)');
            $this->redirect('settings/users');
        }

        $userModel = $this->model('User');
        $user = $userModel->findOne(['id' => $userId]);

        if (
            $userModel->update($userId, [
                'password_hash' => Security::hashPassword($newPassword),
                'must_change_password' => $mustChange
            ])
        ) {
            $this->activityModel->log($this->getUserId(), 'user_password_change', "Admin manually set password for {$user['email']}");
            $this->setFlash('success', 'Password updated successfully');
        } else {
            $this->setFlash('error', 'Failed to update password');
        }

        $this->redirect('settings/users');
    }

    /**
     * Delete user
     */
    public function deleteUser()
    {
        $this->requirePermission('users', 'delete');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->validateCsrf()) {
            $this->redirect('settings/users');
        }

        $userId = (int) ($_POST['user_id'] ?? 0);
        $userModel = $this->model('User');

        // Prevent deleting self
        if ($userId == $this->getUserId()) {
            $this->setFlash('error', 'You cannot delete your own account');
            $this->redirect('settings/users');
        }

        $user = $userModel->findOne(['id' => $userId]);
        $result = $userModel->deleteUser($userId);

        if ($result['success']) {
            $this->activityModel->log($this->getUserId(), 'user_delete', "Deleted user account: {$user['email']}");
            $this->setFlash('success', $result['message']);
        } else {
            $this->setFlash('error', $result['message']);
        }

        $this->redirect('settings/users');
    }

    /**
     * Get user activity log (AJAX)
     */
    public function getUserActivity()
    {
        $this->requirePermission('users', 'view');

        $userId = (int) ($_GET['user_id'] ?? 0);
        if (!$userId) {
            echo json_encode([]);
            exit;
        }

        $logs = $this->activityModel->getByUser($userId, 30);

        // Format dates
        foreach ($logs as &$log) {
            $log['formatted_date'] = date('M d, Y H:i', strtotime($log['created_at']));
        }

        echo json_encode($logs);
        exit;
    }

}
