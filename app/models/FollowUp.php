<?php
/**
 * FollowUp Model
 * Handles follow-up data operations
 */

class FollowUp extends Model
{
    protected $table = 'followups';

    /**
     * Get all follow-ups for a user
     * 
     * @param int $userId
     * @param string $status Filter by status
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getByCompany($companyId, $status = null, $limit = null, $offset = null)
    {
        $conditions = ['company_id' => $companyId];

        if ($status) {
            $conditions['status'] = $status;
        }

        return $this->findAll($conditions, 'followup_date ASC', $limit, $offset);
    }

    /**
     * Get follow-ups with related data
     * 
     * @param int $userId
     * @param string $status
     * @return array
     */
    public function getWithRelationsByCompany($companyId, $status = null, $filtered = true)
    {
        $sql = "SELECT f.*, 
                       c.name as client_name,
                       d.title as deal_title
                FROM {$this->table} f
                LEFT JOIN clients c ON f.client_id = c.id
                LEFT JOIN deals d ON f.deal_id = d.id
                WHERE f.company_id = ?";

        $params = [$companyId];

        if ($status) {
            $sql .= " AND f.status = ?";
            $params[] = $status;
        }

        if ($filtered) {
            require_once APP_PATH . '/helpers/DateFilter.php';
            DateFilter::applyToSql('f.created_at', $sql, $params);
        }

        $sql .= " ORDER BY f.followup_date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get upcoming follow-ups
     * 
     * @param int $userId
     * @param int $days Number of days ahead
     * @return array
     */
    public function getUpcomingByCompany($companyId, $days = 7)
    {
        $sql = "SELECT f.*, 
                       c.name as client_name,
                       d.title as deal_title
                FROM {$this->table} f
                LEFT JOIN clients c ON f.client_id = c.id
                LEFT JOIN deals d ON f.deal_id = d.id
                WHERE f.company_id = :company_id 
                AND f.status = 'pending'
                AND f.followup_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
                ORDER BY f.followup_date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':company_id', $companyId, PDO::PARAM_INT);
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get overdue follow-ups
     * 
     * @param int $userId
     * @return array
     */
    public function getOverdueByCompany($companyId)
    {
        $sql = "SELECT f.*, 
                       c.name as client_name,
                       d.title as deal_title
                FROM {$this->table} f
                LEFT JOIN clients c ON f.client_id = c.id
                LEFT JOIN deals d ON f.deal_id = d.id
                WHERE f.company_id = :company_id 
                AND f.status = 'pending'
                AND f.followup_date < CURDATE()
                ORDER BY f.followup_date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':company_id', $companyId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
