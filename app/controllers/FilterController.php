<?php
/**
 * Filter Controller
 * Handles setting of global filters
 */

class FilterController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
    }

    /**
     * Set global date filter
     */
    public function set()
    {
        $filter = $_POST['filter'] ?? 'all';
        $start = $_POST['start_date'] ?? null;
        $end = $_POST['end_date'] ?? null;

        require_once APP_PATH . '/helpers/DateFilter.php';
        DateFilter::setFilter($filter, $start, $end);

        // Redirect back to referring page or dashboard
        $referrer = $_SERVER['HTTP_REFERER'] ?? APP_URL . '/dashboard';
        header("Location: {$referrer}");
        exit;
    }
}
