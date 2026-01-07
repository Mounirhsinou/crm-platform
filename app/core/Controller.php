<?php
/**
 * Base Controller Class
 * Provides common controller functionality
 */

class Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        Lang::init();
        $this->setSecurityHeaders();
        $this->checkSessionTimeout();
    }

    /**
     * Check for session inactivity timeout
     */
    protected function checkSessionTimeout()
    {
        if (session_status() === PHP_SESSION_NONE)
            return;

        if ($this->isAuthenticated()) {
            $now = time();
            $lastActivity = Session::get('last_activity');

            if ($lastActivity && ($now - $lastActivity > SESSION_IDLE_TIMEOUT)) {
                Security::logSecurityEvent('session_timeout', 'User session timed out after ' . SESSION_IDLE_TIMEOUT . 's', 'low');
                Session::logout();
                Session::setFlash('warning', 'Your session has expired due to inactivity');
                $this->redirect('auth/login');
            }

            Session::set('last_activity', $now);

            // Periodic session ID rotation (every 5 minutes of activity)
            $lastRotation = Session::get('last_rotation');
            if (!$lastRotation || ($now - $lastRotation > 300)) {
                Session::regenerate();
                Session::set('last_rotation', $now);
            }
        }
    }

    /**
     * Set secure HTTP headers
     */
    protected function setSecurityHeaders()
    {
        if (headers_sent())
            return;

        // Prevent framing (Clickjacking protection)
        header("X-Frame-Options: SAMEORIGIN");

        // Prevent MIME type sniffing
        header("X-Content-Type-Options: nosniff");

        // Enable XSS filtering in browsers
        header("X-XSS-Protection: 1; mode=block");

        // Referrer policy
        header("Referrer-Policy: strict-origin-when-cross-origin");

        // HSTS (Only for HTTPS)
        if (ENFORCE_HTTPS) {
            header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
        }

        // Content Security Policy (Basic starting point)
        // Allow self, fonts from Google, and bootstrap/stripe/paypal CDNs
        $csp = "default-src 'self'; ";
        $csp .= "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://js.stripe.com https://www.paypal.com; ";
        $csp .= "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; ";
        $csp .= "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net; ";
        $csp .= "img-src 'self' data: https://chart.googleapis.com https://www.paypalobjects.com; ";
        $csp .= "frame-src https://js.stripe.com https://www.paypal.com; ";
        $csp .= "connect-src 'self' https://api.stripe.com;";

        header("Content-Security-Policy: " . $csp);
    }

    /**
     * Load and return model instance
     * 
     * @param string $model Model name
     * @return object Model instance
     */
    protected function model($model)
    {
        $modelPath = APP_PATH . '/models/' . $model . '.php';

        if (file_exists($modelPath)) {
            require_once $modelPath;
            return new $model();
        }

        die("Model {$model} not found");
    }

    /**
     * Load view file
     * 
     * @param string $view View path (e.g., 'clients/index')
     * @param array $data Data to pass to view
     */
    protected function view($view, $data = [])
    {
        extract($data);

        $viewPath = APP_PATH . '/views/' . $view . '.php';

        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            die("View {$view} not found");
        }
    }

    /**
     * Redirect to another URL
     * 
     * @param string $url URL to redirect to
     */
    protected function redirect($url)
    {
        if (strpos($url, 'http') === false) {
            $url = APP_URL . '/' . ltrim($url, '/');
        }
        header("Location: {$url}");
        exit;
    }

    /**
     * Check if user is authenticated
     * 
     * @return bool
     */
    protected function isAuthenticated()
    {
        return Session::isAuthenticated();
    }

    /**
     * Require authentication (redirect to login if not authenticated)
     */
    protected function requireAuth()
    {
        if (!$this->isAuthenticated()) {
            Session::setFlash('error', 'Please login to access this page');
            $this->redirect('auth/login');
        }

        // Check if 2FA is pending
        if (Session::get('2fa_pending') || !Session::get('2fa_passed')) {
            Session::setFlash('error', 'Please complete two-factor authentication');
            $this->redirect('auth/verify2fa');
        }

        // Check if password change is required
        $userId = $this->getUserId();
        $userModel = $this->model('User');
        if ($userModel->mustChangePassword($userId)) {
            // Prevent infinite redirect
            $currentPath = $_GET['url'] ?? '';
            if ($currentPath !== 'auth/changePassword' && $currentPath !== 'auth/logout') {
                Session::setFlash('warning', 'You must change your password before continuing');
                $this->redirect('auth/changePassword');
            }
        }
    }

    /**
     * Get current user ID
     * 
     * @return int|null
     */
    protected function getUserId()
    {
        return Session::get('user_id');
    }

    private $cachedRole = null;

    /**
     * Get current user's role information
     * 
     * @return array|null
     */
    protected function getUserRole()
    {
        if ($this->cachedRole !== null) {
            return $this->cachedRole;
        }

        $userId = $this->getUserId();
        if (!$userId) {
            return null;
        }

        $userModel = $this->model('User');
        $user = $userModel->getUserWithRole($userId);

        if (!$user) {
            return null;
        }

        // Fallback or Normalize: If user is the company owner, ensure they have the 'owner' slug
        $roleSlug = strtolower(trim($user['role_slug'] ?? ''));

        // Extra check: if user is the company owner according to the companies table
        if (empty($roleSlug) || $roleSlug !== 'owner') {
            $companyModel = $this->model('Company');
            $company = $companyModel->findById($user['company_id']);
            if ($company && (int) $company['owner_id'] === (int) $userId) {
                $roleSlug = 'owner';
            }
        }

        $this->cachedRole = [
            'id' => $user['role_id'],
            'name' => $user['role_name'] ?? 'User',
            'slug' => $roleSlug,
            'permissions' => is_array($user['permissions']) ? $user['permissions'] : json_decode($user['permissions'] ?? '{}', true)
        ];

        return $this->cachedRole;
    }

    /**
     * Check if current user is Owner
     * 
     * @return bool
     */
    protected function isOwner()
    {
        $role = $this->getUserRole();
        return $role && $role['slug'] === 'owner';
    }

    /**
     * Check if current user is Super Admin (Alias for isOwner for backward compatibility)
     * 
     * @return bool
     */
    protected function isSuperAdmin()
    {
        return $this->isOwner();
    }

    /**
     * Check if current user has specific permission
     * 
     * @param string $resource (e.g., 'clients', 'deals')
     * @param string $action (e.g., 'view', 'create', 'edit', 'delete')
     * @return bool
     */
    protected function hasPermission($resource, $action)
    {
        $role = $this->getUserRole();

        if (!$role) {
            return false;
        }

        // Owner has all permissions automatically (Multi-tenant bypass)
        if ($role['slug'] === 'owner') {
            return true;
        }

        if (empty($role['permissions'])) {
            return false;
        }

        $permissions = $role['permissions'];

        if (isset($permissions[$resource]) && is_array($permissions[$resource])) {
            return in_array($action, $permissions[$resource]);
        }

        return false;
    }

    /**
     * Require specific role(s) to access route
     * 
     * @param string|array $roles Role slug(s) required
     */
    protected function requireRole($roles)
    {
        if (!$this->isAuthenticated()) {
            Session::setFlash('error', 'Please login to access this page');
            $this->redirect('auth/login');
        }

        $userRole = $this->getUserRole();

        if (!$userRole) {
            $this->deny('No role assigned to your account');
        }

        // Owner bypasses all role checks
        if ($userRole['slug'] === 'owner') {
            return;
        }

        $roles = is_array($roles) ? $roles : [$roles];

        if (!in_array($userRole['slug'], $roles)) {
            $this->deny('You do not have permission to access this page');
        }
    }

    /**
     * Require specific permission to access route
     * 
     * @param string $resource
     * @param string $action
     */
    protected function requirePermission($resource, $action)
    {
        if (!$this->isAuthenticated()) {
            Session::setFlash('error', 'Please login to access this page');
            $this->redirect('auth/login');
        }

        if (!$this->hasPermission($resource, $action)) {
            $this->deny('You do not have permission to perform this action');
        }
    }

    /**
     * Deny access and show 403 page
     * 
     * @param string $message
     */
    protected function deny($message = 'Access Denied')
    {
        Security::logSecurityEvent('unauthorized_access', $message, 'medium');
        http_response_code(403);
        $this->view('errors/403', ['message' => $message]);
        exit;
    }

    /**
     * Show 404 page
     * 
     * @param string $message
     */
    protected function notFound($message = 'Resource not found')
    {
        http_response_code(404);
        $this->view('errors/404', ['message' => $message]);
        exit;
    }

    /**
     * Internal server error handler
     */
    protected function internalError($message = 'Internal server error')
    {
        if (APP_DEBUG) {
            throw new Exception($message);
        }
        http_response_code(500);
        $this->view('errors/500', ['message' => 'A server error occurred.']);
        exit;
    }

    /**
     * Get and format flash messages
     * 
     * @return string HTML for flash messages
     */
    public function flash()
    {
        $html = '';

        if ($message = Session::getFlash('success')) {
            $html .= '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        ' . Security::escape($message) . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
        }

        if ($message = Session::getFlash('error')) {
            $html .= '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        ' . Security::escape($message) . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
        }

        return $html;
    }

    /**
     * Set flash message
     * 
     * @param string $type Message type (success, error, warning, info)
     * @param string $message Message text
     */
    protected function setFlash($type, $message)
    {
        Session::setFlash($type, $message);
    }

    /**
     * Validate CSRF token
     * 
     * @return bool
     */
    protected function validateCsrf()
    {
        return Security::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '');
    }

    /**
     * Return JSON response
     * 
     * @param mixed $data Data to encode
     * @param int $statusCode HTTP status code
     */
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    /**
     * Check if request is AJAX
     * 
     * @return bool
     */
    protected function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
