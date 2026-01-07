<?php
/**
 * Lang Controller
 * Handles language switching
 */

class LangController extends Controller
{
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = $this->model('User');
    }

    /**
     * Switch language
     * 
     * @param string $lang Language code
     */
    public function switch($lang)
    {
        $supported = ['en', 'fr', 'ar'];

        if (in_array($lang, $supported)) {
            // Update session
            Session::set('language', $lang);

            // Update user profile if logged in
            if ($this->isAuthenticated()) {
                $this->userModel->update($this->getUserId(), [
                    'language' => $lang
                ]);
            }

            $this->setFlash('success', 'Language changed successfully');
        }

        // Redirect back to previous page or dashboard
        $referrer = $_SERVER['HTTP_REFERER'] ?? APP_URL . '/dashboard';
        header("Location: {$referrer}");
        exit;
    }
}
