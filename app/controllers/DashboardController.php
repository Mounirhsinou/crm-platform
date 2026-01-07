<?php
/**
 * Dashboard Controller
 * Displays main dashboard with statistics
 */

class DashboardController extends Controller
{
    private $clientModel;
    private $dealModel;
    private $followUpModel;
    private $invoiceModel;
    private $taskModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->clientModel = $this->model('Client');
        $this->dealModel = $this->model('Deal');
        $this->followUpModel = $this->model('FollowUp');
        $this->invoiceModel = $this->model('Invoice');
        $this->taskModel = $this->model('Task');
    }

    /**
     * Dashboard index page
     */
    public function index()
    {
        $this->requirePermission('dashboard', 'view');
        $companyId = Session::get('company_id');

        // Defensive: Ensure company_id is valid
        if (!$companyId || !is_numeric($companyId)) {
            error_log('Dashboard: Invalid company_id in session');
            $this->redirect('auth/logout');
            return;
        }

        require_once APP_PATH . '/helpers/DateFilter.php';
        $range = DateFilter::getRange();

        // Current Stats with global filter
        $stats = [
            'total_clients' => $this->clientModel->countFiltered(['company_id' => $companyId]),
            'total_deals' => $this->dealModel->countFiltered(['company_id' => $companyId]),
            'total_invoices' => $this->invoiceModel->countFiltered(['company_id' => $companyId]),
            'total_revenue' => $this->getTotalFilteredRevenue($companyId),
            'pending_invoices' => $this->invoiceModel->countFiltered(['company_id' => $companyId, 'status' => 'unpaid']),
            'active_deals' => $this->dealModel->countFiltered(['company_id' => $companyId, 'status' => 'in_progress'])
        ];

        // Growth Calculation Helper (Legacy or keep if still useful)
        $thisMonthStart = date('Y-m-01 00:00:00');
        $lastMonthStart = date('Y-m-01 00:00:00', strtotime('-1 month'));
        $lastMonthEnd = date('Y-m-t 23:59:59', strtotime('-1 month'));

        $calculateGrowth = function ($current, $previous) {
            $current = (float) $current;
            $previous = (float) $previous;
            if ($previous == 0) {
                return $current > 0 ? 'new' : null;
            }
            if ($current == 0 && $previous > 0) {
                return -100;
            }
            return round((($current - $previous) / $previous) * 100, 1);
        };

        // Fetch growth based on actual months for consistency in context
        $clients_this_month = $this->clientModel->query("SELECT COUNT(*) as total FROM clients WHERE company_id = ? AND created_at >= ?", [$companyId, $thisMonthStart])[0]['total'] ?? 0;
        $clients_last_month = $this->clientModel->query("SELECT COUNT(*) as total FROM clients WHERE company_id = ? AND created_at BETWEEN ? AND ?", [$companyId, $lastMonthStart, $lastMonthEnd])[0]['total'] ?? 0;

        $revenue_this_month = $this->invoiceModel->query("SELECT SUM(amount) as total FROM invoices WHERE company_id = ? AND status = 'paid' AND created_at >= ?", [$companyId, $thisMonthStart])[0]['total'] ?? 0;
        $revenue_last_month = $this->invoiceModel->query("SELECT SUM(amount) as total FROM invoices WHERE company_id = ? AND status = 'paid' AND created_at BETWEEN ? AND ?", [$companyId, $lastMonthStart, $lastMonthEnd])[0]['total'] ?? 0;

        $stats['growth'] = [
            'clients' => $calculateGrowth($clients_this_month, $clients_last_month),
            'revenue' => $calculateGrowth($revenue_this_month, $revenue_last_month)
        ];

        // Get upcoming follow-ups
        $upcomingFollowUps = $this->followUpModel->getUpcomingByCompany($companyId, 7);

        // Get overdue follow-ups
        $overdueFollowUps = $this->followUpModel->getOverdueByCompany($companyId);

        // Get recent deals (Filtered) - need client info for display
        $allDeals = $this->dealModel->getWithClientsByCompany($companyId);
        $recentDeals = array_slice($allDeals, 0, 5);

        // Get monthly revenue for chart
        $monthlyRevenue = $this->dealModel->getMonthlyRevenueByCompany($companyId, 6);

        $this->view('dashboard/index', [
            'stats' => $stats,
            'upcoming_followups' => $upcomingFollowUps,
            'overdue_followups' => $overdueFollowUps,
            'recent_deals' => $recentDeals,
            'monthly_revenue' => $monthlyRevenue,
            'upcoming_tasks' => $this->taskModel->getUpcomingByCompany($companyId, 5)
        ]);
    }

    /**
     * Helper for filtered revenue
     */
    private function getTotalFilteredRevenue($companyId)
    {
        // Defensive: Validate company_id
        if (!$companyId || !is_numeric($companyId)) {
            error_log('getTotalFilteredRevenue: Invalid company_id: ' . var_export($companyId, true));
            return 0.0;
        }

        require_once APP_PATH . '/helpers/DateFilter.php';
        $range = DateFilter::getRange();

        $sql = "SELECT SUM(amount) as total FROM invoices WHERE company_id = :company_id AND status = 'paid'";
        $params = [':company_id' => (int) $companyId];

        if ($range['start'] && $range['end']) {
            $sql .= " AND created_at BETWEEN :start AND :end";
            $params[':start'] = $range['start'];
            $params[':end'] = $range['end'];
        } elseif ($range['start']) {
            $sql .= " AND created_at >= :start";
            $params[':start'] = $range['start'];
        } elseif ($range['end']) {
            $sql .= " AND created_at <= :end";
            $params[':end'] = $range['end'];
        }

        $stmt = $this->invoiceModel->getDb()->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        $res = $stmt->fetch();
        return (float) ($res['total'] ?? 0);
    }
}
