<?php
/**
 * PaymentTemplate Model
 * Handles configuration for public payment links
 */

class PaymentTemplate extends Model
{
    protected $table = 'payment_templates';

    /**
     * Get template by secure token
     * 
     * @param string $token
     * @return array|false
     */
    public function getByToken($token)
    {
        $sql = "SELECT pt.*, co.company_name, co.logo_path, co.paypal_client_id, co.stripe_publishable_key, co.stripe_secret_key, co.stripe_mode
                FROM {$this->table} pt
                LEFT JOIN companies co ON pt.company_id = co.id
                WHERE pt.token = :token
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':token', $token);
        $stmt->execute();

        return $stmt->fetch();
    }

    public function getUnifiedLinks($companyId)
    {
        require_once APP_PATH . '/helpers/DateFilter.php';
        $range = DateFilter::getRange();

        $params = [
            ':company_id1' => $companyId,
            ':company_id2' => $companyId
        ];

        // Apply date filtering to the subqueries - need unique params for each subquery
        $paymentDateQuery1 = "";
        $paymentDateQuery2 = "";

        if ($range['start'] && $range['end']) {
            $paymentDateQuery1 = " AND created_at BETWEEN :start1 AND :end1";
            $paymentDateQuery2 = " AND created_at BETWEEN :start2 AND :end2";
            $params[':start1'] = $range['start'];
            $params[':end1'] = $range['end'];
            $params[':start2'] = $range['start'];
            $params[':end2'] = $range['end'];
        } elseif ($range['start']) {
            $paymentDateQuery1 = " AND created_at >= :start1";
            $paymentDateQuery2 = " AND created_at >= :start2";
            $params[':start1'] = $range['start'];
            $params[':start2'] = $range['start'];
        } elseif ($range['end']) {
            $paymentDateQuery1 = " AND created_at <= :end1";
            $paymentDateQuery2 = " AND created_at <= :end2";
            $params[':end1'] = $range['end'];
            $params[':end2'] = $range['end'];
        }

        $sql = "
            (SELECT 
                'Client-filled' as link_type,
                id as original_id,
                title,
                token,
                amount,
                payment_closed,
                created_at,
                (SELECT COUNT(*) FROM invoices WHERE template_id = pt.id $paymentDateQuery1) as payments_count 
            FROM payment_templates pt
            WHERE company_id = :company_id1)
            
            UNION ALL
            
            (SELECT 
                'Invoice' as link_type,
                id as original_id,
                invoice_number as title,
                payment_token as token,
                amount,
                payment_closed,
                created_at,
                (SELECT COUNT(*) FROM payments WHERE invoice_id = i.id $paymentDateQuery2) as payments_count
            FROM invoices i
            WHERE company_id = :company_id2 AND payment_token IS NOT NULL)
            
            ORDER BY created_at DESC";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get all templates for a user
     * 
     * @param int $userId
     * @return array
     */
    public function getByCompany($companyId)
    {
        return $this->findAll(['company_id' => $companyId], 'created_at DESC');
    }

    /**
     * Toggle payment link status
     * 
     * @param int $id
     * @param int $userId
     * @param int $status
     * @return bool
     */
    public function updateStatus($id, $companyId, $status)
    {
        $sql = "UPDATE {$this->table} SET payment_closed = :status WHERE id = :id AND company_id = :company_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':status' => $status,
            ':id' => $id,
            ':company_id' => $companyId
        ]);
    }

    public function getFilteredStats($companyId)
    {
        // Defensive: Validate company_id
        if (!$companyId || !is_numeric($companyId)) {
            error_log('PaymentTemplate::getFilteredStats: Invalid company_id: ' . var_export($companyId, true));
            return [
                'revenue' => 0.0,
                'payments' => 0,
                'leads' => 0
            ];
        }

        require_once APP_PATH . '/helpers/DateFilter.php';
        $range = DateFilter::getRange();

        $params = [':company_id' => (int) $companyId];
        $dateQuery = "";


        if ($range['start'] && $range['end']) {
            $dateQuery .= " AND p.created_at BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $range['start'];
            $params[':end_date'] = $range['end'];
        } elseif ($range['start']) {
            $dateQuery .= " AND p.created_at >= :start_date";
            $params[':start_date'] = $range['start'];
        } elseif ($range['end']) {
            $dateQuery .= " AND p.created_at <= :end_date";
            $params[':end_date'] = $range['end'];
        }

        // Revenue & Payments Count
        $sql = "SELECT SUM(p.amount) as total_revenue, COUNT(DISTINCT p.invoice_id) as total_payments
                FROM payments p
                JOIN invoices i ON p.invoice_id = i.id
                WHERE i.company_id = :company_id
                AND (i.template_id IS NOT NULL OR i.payment_token IS NOT NULL)
                $dateQuery";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v)
            $stmt->bindValue($k, $v);
        $stmt->execute();
        $stats = $stmt->fetch();

        // Leads Count
        $leadParams = [':company_id' => $companyId];
        $leadDateQuery = "";
        if ($range['start'] && $range['end']) {
            $leadDateQuery .= " AND created_at BETWEEN :l_start_date AND :l_end_date";
            $leadParams[':l_start_date'] = $range['start'];
            $leadParams[':l_end_date'] = $range['end'];
        } elseif ($range['start']) {
            $leadDateQuery .= " AND created_at >= :l_start_date";
            $leadParams[':l_start_date'] = $range['start'];
        } elseif ($range['end']) {
            $leadDateQuery .= " AND created_at <= :l_end_date";
            $leadParams[':l_end_date'] = $range['end'];
        }

        $sqlLeads = "SELECT COUNT(*) as total_leads FROM leads WHERE company_id = :company_id $leadDateQuery";
        $stmtLeads = $this->db->prepare($sqlLeads);
        foreach ($leadParams as $k => $v)
            $stmtLeads->bindValue($k, $v);
        $stmtLeads->execute();
        $leadResult = $stmtLeads->fetch();

        return [
            'revenue' => (float) ($stats['total_revenue'] ?? 0),
            'payments' => (int) ($stats['total_payments'] ?? 0),
            'leads' => (int) ($leadResult['total_leads'] ?? 0)
        ];
    }

    /**
     * Get total revenue collected from all checkout sources
     */
    public function getTotalRevenue($companyId)
    {
        $stats = $this->getFilteredStats($companyId);
        return $stats['revenue'];
    }
}
