<?php
/**
 * Auth Controller
 * Handles authentication (login, register, logout)
 */

class AuthController extends Controller
{
    private $userModel;
    private $activityModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = $this->model('User');
        $this->activityModel = $this->model('ActivityLog');
    }

    /**
     * Index redirects to login
     */
    public function index()
    {
        $this->redirect('auth/login');
    }

    /**
     * Show login form
     */
    public function login()
    {
        // Redirect if already logged in
        if ($this->isAuthenticated()) {
            $this->redirect('dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleLogin();
        } else {
            $this->view('auth/login', [
                'csrf_token' => Security::generateCsrfToken()
            ]);
        }
    }

    /**
     * Handle login form submission
     */
    private function handleLogin()
    {
        // Validate CSRF token
        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid request. Please try again.');
            $this->redirect('auth/login');
        }

        $email = Security::sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $ip = $_SERVER['REMOTE_ADDR'];

        // Brute-force protection
        $rateLimiter = new RateLimiter();
        if ($rateLimiter->isLockedOut($email, $ip)) {
            $minutes = $rateLimiter->getLockoutTimeRemaining($email, $ip);
            $this->setFlash('error', "Too many failed attempts. Please try again in {$minutes} minutes.");
            $this->redirect('auth/login');
        }

        // Validate input
        $validator = new Validator();
        $validator->required('email', $email, 'Email')
            ->email('email', $email)
            ->required('password', $password, 'Password');

        if ($validator->fails()) {
            $this->view('auth/login', [
                'errors' => $validator->getErrors(),
                'email' => $email,
                'csrf_token' => Security::generateCsrfToken()
            ]);
            return;
        }

        // Authenticate user
        $user = $this->userModel->authenticate($email, $password);

        if ($user === 'deactivated') {
            $rateLimiter->recordAttempt($email, $ip, false);
            $this->view('auth/login', [
                'errors' => ['email' => 'Your account has been disabled. Please contact an administrator.'],
                'email' => $email,
                'csrf_token' => Security::generateCsrfToken()
            ]);
            return;
        }

        if ($user) {
            $rateLimiter->recordAttempt($email, $ip, true);

            // Check if 2FA is enabled
            if (!empty($user['two_factor_enabled']) && !empty($user['two_factor_secret'])) {
                // Store partial session
                Session::set('2fa_user_id', $user['id']);
                Session::set('2fa_pending', true);
                $this->redirect('auth/verify2fa');
            }

            // Normal login (no 2FA)
            $this->completeLogin($user);
        } else {
            $rateLimiter->recordAttempt($email, $ip, false);
            $remaining = $rateLimiter->getRemainingAttempts($email, $ip);
            $message = 'Invalid email or password.';
            if ($remaining <= 2) {
                $message .= " You have {$remaining} attempts remaining before lockout.";
            }

            $this->view('auth/login', [
                'errors' => ['email' => $message],
                'email' => $email,
                'csrf_token' => Security::generateCsrfToken()
            ]);
        }
    }

    /**
     * Finalize the login process
     */
    private function completeLogin($user)
    {
        Session::login($user);
        Session::set('2fa_passed', true); // Mark 2FA as passed (even if not used)
        Session::regenerate();

        // Log activity
        $this->activityModel->log($user['id'], 'login', 'User logged in');

        // Check if password change is required
        if (isset($user['must_change_password']) && $user['must_change_password'] == 1) {
            $this->setFlash('warning', 'You must change your password before continuing');
            $this->redirect('auth/changePassword');
        }

        $this->setFlash('success', 'Welcome back!');

        // Redirect to first available module
        $redirectUrl = $this->getFirstAvailableModule($user);
        $this->redirect($redirectUrl);
    }

    /**
     * Show 2FA verification form
     */
    public function verify2fa()
    {
        if (!Session::get('2fa_pending')) {
            $this->redirect('auth/login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handle2faVerification();
        } else {
            $this->view('auth/verify_2fa', [
                'csrf_token' => Security::generateCsrfToken()
            ]);
        }
    }

    /**
     * Handle 2FA verification submission
     */
    private function handle2faVerification()
    {
        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('auth/verify2fa');
        }

        $userId = Session::get('2fa_user_id');
        $code = $_POST['code'] ?? '';

        if (!$userId) {
            $this->redirect('auth/login');
        }

        $user = $this->userModel->findOne(['id' => $userId]);

        if ($user && TOTP::verify($user['two_factor_secret'], $code)) {
            Session::remove('2fa_pending');
            Session::remove('2fa_user_id');
            $this->completeLogin($user);
        } else {
            $this->setFlash('error', 'Invalid authentication code. Please try again.');
            $this->redirect('auth/verify2fa');
        }
    }

    /**
     * Show backup code verification form
     */
    public function backup2fa()
    {
        if (!Session::get('2fa_pending')) {
            $this->redirect('auth/login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleBackupVerification();
        } else {
            $this->view('auth/backup_2fa', [
                'csrf_token' => Security::generateCsrfToken()
            ]);
        }
    }

    /**
     * Handle backup code verification
     */
    private function handleBackupVerification()
    {
        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('auth/backup2fa');
        }

        $userId = Session::get('2fa_user_id');
        $code = $_POST['code'] ?? '';

        if (!$userId) {
            $this->redirect('auth/login');
        }

        $backupCodeModel = $this->model('BackupCode');

        if ($backupCodeModel->verify($userId, $code)) {
            $user = $this->userModel->findOne(['id' => $userId]);
            Session::remove('2fa_pending');
            Session::remove('2fa_user_id');
            $this->completeLogin($user);
        } else {
            $this->setFlash('error', 'Invalid or already used backup code.');
            $this->redirect('auth/backup2fa');
        }
    }

    /**
     * Show registration form
     */
    public function register()
    {
        // Redirect if already logged in
        if ($this->isAuthenticated()) {
            $this->redirect('dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleRegister();
        } else {
            $this->view('auth/register', [
                'csrf_token' => Security::generateCsrfToken()
            ]);
        }
    }

    /**
     * Handle registration form submission
     */
    private function handleRegister()
    {
        // Validate CSRF token
        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid request. Please try again.');
            $this->redirect('auth/register');
        }

        $companyName = Security::sanitize($_POST['company_name'] ?? '');
        $fullName = Security::sanitize($_POST['full_name'] ?? '');
        $email = Security::sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validate input
        $validator = new Validator();
        $validator->required('company_name', $companyName, 'Company Name')
            ->min('company_name', $companyName, 3, 'Company Name')
            ->required('full_name', $fullName, 'Full Name')
            ->min('full_name', $fullName, 3, 'Full Name')
            ->required('email', $email, 'Email')
            ->email('email', $email)
            ->required('password', $password, 'Password')
            ->min('password', $password, 6, 'Password')
            ->match('confirm_password', $confirmPassword, $password, 'Password Confirmation');

        // Check if email already exists
        if ($this->userModel->emailExists($email)) {
            $validator->addError('email', 'Email already registered');
        }

        if ($validator->fails()) {
            $this->view('auth/register', [
                'errors' => $validator->getErrors(),
                'company_name' => $companyName,
                'full_name' => $fullName,
                'email' => $email,
                'csrf_token' => Security::generateCsrfToken()
            ]);
            return;
        }

        // 1. Create Company (Workspace)
        $companyModel = $this->model('Company');
        $companyId = $companyModel->insert([
            'company_name' => $companyName,
            'owner_email' => $email,  // Set primary account email
            'owner_id' => null  // Will be updated after user creation
        ]);

        if (!$companyId) {
            $this->setFlash('error', 'Failed to create workspace. Please try again.');
            $this->redirect('auth/register');
        }

        // 2. Create User linked to Company as Owner
        $roleModel = $this->model('Role');
        $ownerRole = $roleModel->getBySlug('owner');
        $roleId = $ownerRole ? $ownerRole['id'] : 1; // Fallback to 1 if slug not found

        $userId = $this->userModel->create([
            'company_id' => $companyId,
            'full_name' => $fullName,
            'email' => $email,
            'password' => $password,
            'role_id' => $roleId,
            'is_active' => 1,
            'must_change_password' => 0
        ]);

        if ($userId) {
            // 3. Update Company with owner_id
            $companyModel->updateWhere(['owner_id' => $userId], ['id' => $companyId]);

            // Log activity
            $this->activityModel->log($userId, 'workspace_create', "New workspace created: $companyName", $companyId);

            $this->setFlash('success', 'Registration successful! Your workspace is ready.');
            $this->redirect('auth/login');
        } else {
            // Cleanup company if user creation fails
            $companyModel->delete($companyId);
            $this->setFlash('error', 'Registration failed. Please try again.');
            $this->redirect('auth/register');
        }
    }

    /**
     * Logout user
     */
    public function logout()
    {
        $userId = $this->getUserId();
        if ($userId) {
            $this->activityModel->log($userId, 'logout', 'User logged out');
        }

        Session::logout();
        $this->setFlash('success', 'You have been logged out successfully.');
        $this->redirect('auth/login');
    }

    /**
     * Redirect to Google OAuth
     */
    public function google()
    {
        $oauth = new GoogleOAuth();

        if (!$oauth->isConfigured()) {
            $this->setFlash('error', 'Google OAuth is not configured');
            $this->redirect('auth/login');
        }

        $authUrl = $oauth->getAuthUrl();
        header('Location: ' . $authUrl);
        exit;
    }

    /**
     * Handle Google OAuth callback
     */
    public function googleCallback()
    {
        $code = $_GET['code'] ?? null;

        if (!$code) {
            $this->setFlash('error', 'Google authentication failed');
            $this->redirect('auth/login');
        }

        $oauth = new GoogleOAuth();
        $accessToken = $oauth->getAccessToken($code);

        if (!$accessToken) {
            $this->setFlash('error', 'Failed to get access token from Google');
            $this->redirect('auth/login');
        }

        $userInfo = $oauth->getUserInfo($accessToken);

        if (!$userInfo || empty($userInfo['email'])) {
            $this->setFlash('error', 'Failed to get user information from Google');
            $this->redirect('auth/login');
        }

        // Check if user exists
        $user = $this->userModel->findOne(['email' => $userInfo['email']]);

        if ($user) {
            // Update google_id if not set
            if (empty($user['google_id'])) {
                $this->userModel->update($user['id'], [
                    'google_id' => $userInfo['id'],
                    'auth_provider' => 'google'
                ]);
            }

            // Log user in
            Session::login($user);
            Session::regenerate();
            $this->setFlash('success', 'Welcome back, ' . $user['company_name'] . '!');
            $this->redirect('dashboard');
        } else {
            // Create new user
            $userId = $this->userModel->insert([
                'company_name' => $userInfo['name'] ?? $userInfo['email'],
                'email' => $userInfo['email'],
                'password_hash' => '', // No password for Google users
                'google_id' => $userInfo['id'],
                'auth_provider' => 'google'
            ]);

            if ($userId) {
                $newUser = $this->userModel->findOne(['id' => $userId]);
                Session::login($newUser);
                Session::regenerate();
                $this->setFlash('success', 'Welcome to CRM!');
                $this->redirect('dashboard');
            } else {
                $this->setFlash('error', 'Failed to create account');
                $this->redirect('auth/login');
            }
        }
    }

    /**
     * Force password change (first login)
     */
    public function changePassword()
    {
        // Must be authenticated
        $this->requireAuth();

        $userId = $this->getUserId();
        $user = $this->userModel->findOne(['id' => $userId]);

        // Check if password change is required
        if (!isset($user['must_change_password']) || $user['must_change_password'] != 1) {
            $this->redirect('dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePasswordChange();
        } else {
            $this->view('auth/change_password', [
                'csrf_token' => Security::generateCsrfToken()
            ]);
        }
    }

    /**
     * Handle password change submission
     */
    private function handlePasswordChange()
    {
        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('auth/changePassword');
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $userId = $this->getUserId();
        $user = $this->userModel->findOne(['id' => $userId]);

        // Validate current password
        if (!password_verify($currentPassword, $user['password_hash'])) {
            $this->view('auth/change_password', [
                'errors' => ['current_password' => 'Current password is incorrect'],
                'csrf_token' => Security::generateCsrfToken()
            ]);
            return;
        }

        // Validate new password
        $validation = PasswordGenerator::validate($newPassword);

        if (!$validation['valid']) {
            $this->view('auth/change_password', [
                'errors' => ['new_password' => implode(', ', $validation['errors'])],
                'csrf_token' => Security::generateCsrfToken()
            ]);
            return;
        }

        // Check password confirmation
        if ($newPassword !== $confirmPassword) {
            $this->view('auth/change_password', [
                'errors' => ['confirm_password' => 'Passwords do not match'],
                'csrf_token' => Security::generateCsrfToken()
            ]);
            return;
        }

        // Update password and clear must_change_password flag
        $passwordHash = Security::hashPassword($newPassword);

        if ($this->userModel->update($userId, ['password_hash' => $passwordHash, 'must_change_password' => 0])) {
            $this->setFlash('success', 'Password changed successfully');
            $this->redirect('dashboard');
        } else {
            $this->setFlash('error', 'Failed to change password');
            $this->redirect('auth/changePassword');
        }
    }

    /**
     * Show/Handle forgot password form
     */
    public function forgotPassword()
    {
        // Redirect if already logged in
        if ($this->isAuthenticated()) {
            $this->redirect('dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleForgotPassword();
        } else {
            $this->view('auth/forgot_password', [
                'csrf_token' => Security::generateCsrfToken()
            ]);
        }
    }

    /**
     * Handle forgot password submission
     */
    private function handleForgotPassword()
    {
        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('auth/forgotPassword');
            return;
        }

        $email = Security::sanitize($_POST['email'] ?? '');
        $ip = $_SERVER['REMOTE_ADDR'];

        // Rate limiting for forgot password
        $rateLimiter = new RateLimiter();
        if ($rateLimiter->isLockedOut($email, $ip)) {
            $minutes = $rateLimiter->getLockoutTimeRemaining($email, $ip);
            $this->setFlash('error', "Too many requests. Please try again in {$minutes} minutes.");
            $this->activityModel->log(null, 'password_reset_rate_limited', "Rate limit hit for email: $email, IP: $ip");
            $this->redirect('auth/forgotPassword');
            return;
        }

        $validator = new Validator();
        $validator->required('email', $email, 'Email')
            ->email('email', $email);

        if ($validator->fails()) {
            $rateLimiter->recordAttempt($email, $ip, false); // Record failed attempt due to validation
            $this->activityModel->log(null, 'password_reset_attempt_failed', "Validation failed for email: $email, IP: $ip", null, ['errors' => $validator->getErrors()]);
            $this->view('auth/forgot_password', [
                'errors' => $validator->getErrors(),
                'email' => $email,
                'csrf_token' => Security::generateCsrfToken()
            ]);
            return;
        }

        $user = $this->userModel->findOne(['email' => $email]);

        if ($user) {
            $rateLimiter->recordAttempt($email, $ip, true);
            $this->activityModel->log($user['id'], 'password_reset_request', "Password reset request for email: $email", $user['id']);
            // In a real app, we would send a reset email here
            // For this demo, we'll just show a success message
            $this->setFlash('success', 'If an account exists with that email, reset instructions have been sent.');
            $this->redirect('auth/login');
        } else {
            $rateLimiter->recordAttempt($email, $ip, false); // Record failed attempt for non-existent email
            $this->activityModel->log(null, 'password_reset_attempt_failed', "Password reset request for non-existent email: $email, IP: $ip");
            // For security, we still show the same success message even if the email doesn't exist
            $this->setFlash('success', 'If an account exists with that email, reset instructions have been sent.');
            $this->redirect('auth/login');
        }
    }

    /**
     * Get the first available module for a user based on their permissions
     * 
     * @param array $user User data with role information
     * @return string URL to redirect to
     */
    private function getFirstAvailableModule($user)
    {
        // Owner always goes to dashboard
        if (isset($user['role_slug']) && $user['role_slug'] === 'owner') {
            return 'dashboard';
        }

        // Get user's role permissions
        $roleModel = $this->model('Role');
        $role = $roleModel->getBySlug($user['role_slug']);

        if (!$role || empty($role['permissions'])) {
            return 'dashboard'; // Fallback
        }

        $permissions = json_decode($role['permissions'], true);

        // Define module priority order (most common first)
        $modulePriority = [
            'dashboard' => 'dashboard',
            'clients' => 'clients',
            'leads' => 'leads',
            'deals' => 'deals',
            'tasks' => 'tasks',
            'invoices' => 'invoices',
            'followups' => 'followups',
            'reports' => 'reports',
            'public_links' => 'public-links',
            'messaging' => 'messaging',
            'integrations' => 'settings/integrations',
            'settings' => 'settings',
            'security' => 'settings/security',
            'users' => 'settings/users'
        ];

        // Find first module user has view permission for
        foreach ($modulePriority as $module => $url) {
            if (isset($permissions[$module]) && in_array('view', $permissions[$module])) {
                return $url;
            }
        }

        // If no specific permissions found, try dashboard
        return 'dashboard';
    }
}
