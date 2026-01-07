<?php
/**
 * Task Model
 * Handles task/to-do data operations
 */

class Task extends Model
{
    protected $table = 'tasks';

    /**
     * Get all tasks for a user
     * 
     * @param int $userId
     * @param string $status Filter by status
     * @return array
     */
    public function getByCompany($companyId, $status = null)
    {
        $conditions = ['company_id' => $companyId];

        if ($status) {
            $conditions['status'] = $status;
        }

        return $this->findAll($conditions, 'due_date ASC');
    }

    /**
     * Get upcoming tasks (not completed, ordered by due date)
     * 
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getUpcomingByCompany($companyId, $limit = 5)
    {
        $sql = "SELECT t.*, c.name as client_name, d.title as deal_title
                FROM {$this->table} t
                LEFT JOIN clients c ON t.client_id = c.id
                LEFT JOIN deals d ON t.deal_id = d.id
                WHERE t.company_id = :company_id 
                AND t.status = 'pending'
                ORDER BY t.priority = 'high' DESC, t.priority = 'medium' DESC, t.due_date ASC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':company_id', $companyId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get overdue tasks
     * 
     * @param int $userId
     * @return array
     */
    public function getOverdueByCompany($companyId)
    {
        $sql = "SELECT t.*, c.name as client_name, d.title as deal_title
                FROM {$this->table} t
                LEFT JOIN clients c ON t.client_id = c.id
                LEFT JOIN deals d ON t.deal_id = d.id
                WHERE t.company_id = :company_id 
                AND t.status = 'pending'
                AND t.due_date < CURDATE()
                ORDER BY t.priority = 'high' DESC, t.due_date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':company_id', $companyId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get tasks with related data
     * 
     * @param int $userId
     * @param string $status
     * @return array
     */
    public function getWithRelationsByCompany($companyId, $status = null, $filtered = true)
    {
        $sql = "SELECT t.*, 
                       c.name as client_name, 
                       d.title as deal_title
                FROM {$this->table} t
                LEFT JOIN clients c ON t.client_id = c.id
                LEFT JOIN deals d ON t.deal_id = d.id
                WHERE t.company_id = ?";

        $params = [$companyId];

        if ($status) {
            $sql .= " AND t.status = ?";
            $params[] = $status;
        }

        if ($filtered) {
            require_once APP_PATH . '/helpers/DateFilter.php';
            DateFilter::applyToSql('t.created_at', $sql, $params);
        }

        $sql .= " ORDER BY t.status = 'pending' DESC, t.priority = 'high' DESC, t.due_date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get task by ID with relations
     * 
     * @param int $id
     * @param int $userId
     * @return array|false
     */
    public function getWithRelationByCompany($id, $companyId)
    {
        $sql = "SELECT t.*, 
                       c.name as client_name, 
                       d.title as deal_title
                FROM {$this->table} t
                LEFT JOIN clients c ON t.client_id = c.id
                LEFT JOIN deals d ON t.deal_id = d.id
                WHERE t.id = :id AND t.company_id = :company_id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':company_id', $companyId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Mark task as completed
     * 
     * @param int $id
     * @return bool
     */
    public function markCompleted($id)
    {
        return $this->update($id, ['status' => 'completed']);
    }

    /**
     * Get tasks for calendar
     * 
     * @param int $userId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getForCalendarByCompany($companyId, $startDate, $endDate)
    {
        $sql = "SELECT t.*, c.name as client_name, d.title as deal_title
                FROM {$this->table} t
                LEFT JOIN clients c ON t.client_id = c.id
                LEFT JOIN deals d ON t.deal_id = d.id
                WHERE t.company_id = :company_id 
                AND t.due_date BETWEEN :start_date AND :end_date
                ORDER BY t.due_date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':company_id', $companyId, PDO::PARAM_INT);
        $stmt->bindValue(':start_date', $startDate);
        $stmt->bindValue(':end_date', $endDate);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
