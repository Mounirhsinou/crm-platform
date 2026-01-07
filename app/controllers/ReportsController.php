<?php
/**
 * Report Controller
 * Handles business reporting and analytics
 */

class ReportsController extends Controller
{
    private $invoiceModel;
    private $dealModel;
    private $taskModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->invoiceModel = $this->model('Invoice');
        $this->dealModel = $this->model('Deal');
        $this->taskModel = $this->model('Task');
    }

    /**
     * Reports Dashboard
     */
    public function index()
    {
        $this->requirePermission('reports', 'view');
        $companyId = Session::get('company_id');

        // Integrate with global DateFilter
        require_once APP_PATH . '/helpers/DateFilter.php';
        $range = DateFilter::getRange();

        // Convert to Y-m-d for the internal helpers
        $startDate = $range['start'] ? date('Y-m-d', strtotime($range['start'])) : null;
        $endDate = $range['end'] ? date('Y-m-d', strtotime($range['end'])) : null;

        // 1. Invoice Stats
        $invoiceStats = $this->getInvoiceStats($companyId, $startDate, $endDate);

        // 2. Revenue Stats (from paid invoices)
        $revenueStats = $this->getRevenueStats($companyId, $startDate, $endDate);

        // 3. Deal Stats
        $dealStats = $this->getDealStats($companyId, $startDate, $endDate);

        // 4. Task Stats
        $taskStats = $this->getTaskStats($companyId, $startDate, $endDate);

        $this->view('reports/index', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'invoiceStats' => $invoiceStats,
            'revenueStats' => $revenueStats,
            'dealStats' => $dealStats,
            'taskStats' => $taskStats
        ]);
    }

    /**
     * Get Invoice Statistics
     */
    private function getInvoiceStats($companyId, $start, $end)
    {
        $sql = "SELECT 
                    COUNT(*) as total_count,
                    SUM(amount) as total_amount,
                    SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_count,
                    SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as paid_amount,
                    SUM(CASE WHEN status != 'paid' THEN 1 ELSE 0 END) as unpaid_count,
                    SUM(CASE WHEN status != 'paid' THEN amount ELSE 0 END) as unpaid_amount,
                    SUM(CASE WHEN status != 'paid' AND created_at < CURDATE() THEN 1 ELSE 0 END) as overdue_count
                FROM invoices 
                WHERE company_id = :company_id";

        $params = ['company_id' => $companyId];

        if ($start && $end) {
            $sql .= " AND DATE(created_at) BETWEEN :start AND :end";
            $params['start'] = $start;
            $params['end'] = $end;
        }

        $stmt = $this->invoiceModel->getDb()->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();

        // Ensure we don't return nulls for sums if no data
        return [
            'total_count' => $result['total_count'] ?? 0,
            'total_amount' => $result['total_amount'] ?? 0,
            'paid_count' => $result['paid_count'] ?? 0,
            'paid_amount' => $result['paid_amount'] ?? 0,
            'unpaid_count' => $result['unpaid_count'] ?? 0,
            'unpaid_amount' => $result['unpaid_amount'] ?? 0,
            'overdue_count' => $result['overdue_count'] ?? 0
        ];
    }

    /**
     * Get Revenue Statistics (from paid invoices)
     */
    private function getRevenueStats($companyId, $start, $end)
    {
        // Monthly revenue for the date range (only paid invoices)
        $sql = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    SUM(amount) as revenue
                FROM invoices
                WHERE company_id = :company_id
                AND status = 'paid'";

        $params = ['company_id' => $companyId];

        if ($start && $end) {
            $sql .= " AND DATE(created_at) BETWEEN :start AND :end";
            $params['start'] = $start;
            $params['end'] = $end;
        }

        $sql .= " GROUP BY month ORDER BY month DESC";

        $stmt = $this->invoiceModel->getDb()->prepare($sql);
        $stmt->execute($params);
        $monthly = $stmt->fetchAll();

        // Yearly revenue (current year, always paid)
        $yearSql = "SELECT SUM(amount) as revenue FROM invoices 
                    WHERE company_id = :company_id 
                    AND status = 'paid' 
                    AND YEAR(created_at) = YEAR(CURDATE())";
        $yearStmt = $this->invoiceModel->getDb()->prepare($yearSql);
        $yearStmt->execute(['company_id' => $companyId]);
        $yearly = $yearStmt->fetch();

        return [
            'monthly' => $monthly,
            'yearly' => $yearly['revenue'] ?? 0
        ];
    }

    /**
     * Get Deal Statistics
     */
    private function getDealStats($companyId, $start, $end)
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                    SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new,
                    SUM(amount) as total_value
                FROM deals
                WHERE company_id = :company_id";

        $params = ['company_id' => $companyId];

        if ($start && $end) {
            $sql .= " AND DATE(created_at) BETWEEN :start AND :end";
            $params['start'] = $start;
            $params['end'] = $end;
        }

        $stmt = $this->dealModel->getDb()->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();

        return [
            'total' => $result['total'] ?? 0,
            'completed' => $result['completed'] ?? 0,
            'in_progress' => $result['in_progress'] ?? 0,
            'new' => $result['new'] ?? 0,
            'total_value' => $result['total_value'] ?? 0
        ];
    }

    /**
     * Get Task Statistics
     */
    private function getTaskStats($companyId, $start, $end)
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status != 'completed' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status != 'completed' AND due_date < CURDATE() THEN 1 ELSE 0 END) as overdue
                FROM tasks
                WHERE company_id = :company_id";

        $params = ['company_id' => $companyId];

        if ($start && $end) {
            $sql .= " AND due_date BETWEEN :start AND :end";
            $params['start'] = $start;
            $params['end'] = $end;
        }

        $stmt = $this->taskModel->getDb()->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();

        return [
            'total' => $result['total'] ?? 0,
            'completed' => $result['completed'] ?? 0,
            'pending' => $result['pending'] ?? 0,
            'overdue' => $result['overdue'] ?? 0
        ];
    }
}
