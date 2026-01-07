<?php
/**
 * Deal Model
 * Handles deal/order data operations
 */

class Deal extends Model
{
    protected $table = 'deals';

    /**
     * Get all deals for a user
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

        return $this->findAll($conditions, 'created_at DESC', $limit, $offset);
    }

    /**
     * Get deals with client information
     * 
     * @param int $userId
     * @param string $status
     * @return array
     */
    public function getWithClientsByCompany($companyId, $status = null, $filtered = true)
    {
        $sql = "SELECT d.*, c.name as client_name, c.email as client_email 
                FROM {$this->table} d
                LEFT JOIN clients c ON d.client_id = c.id
                WHERE d.company_id = ?";

        $params = [$companyId];

        if ($status) {
            $sql .= " AND d.status = ?";
            $params[] = $status;
        }

        if ($filtered) {
            require_once APP_PATH . '/helpers/DateFilter.php';
            DateFilter::applyToSql('d.created_at', $sql, $params);
        }

        $sql .= " ORDER BY d.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get deal by ID with client info
     * 
     * @param int $id
     * @param int $userId
     * @return array|false
     */
    public function getWithClientByCompany($id, $companyId)
    {
        $sql = "SELECT d.*, c.name as client_name, c.email as client_email, c.phone as client_phone
                FROM {$this->table} d
                LEFT JOIN clients c ON d.client_id = c.id
                WHERE d.id = :id AND d.company_id = :company_id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':company_id', $companyId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Get total revenue
     * 
     * @param int $userId
     * @param string $status
     * @return float
     */
    public function getTotalRevenueByCompany($companyId, $status = 'completed')
    {
        $sql = "SELECT SUM(amount) as total FROM {$this->table} 
                WHERE company_id = :company_id AND status = :status";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':company_id', $companyId, PDO::PARAM_INT);
        $stmt->bindValue(':status', $status);
        $stmt->execute();

        $result = $stmt->fetch();
        return (float) ($result['total'] ?? 0);
    }

    /**
     * Get monthly revenue
     * 
     * @param int $userId
     * @param int $months Number of months to retrieve
     * @return array
     */
    public function getMonthlyRevenueByCompany($companyId, $months = 6)
    {
        $sql = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    SUM(amount) as total
                FROM {$this->table}
                WHERE company_id = :company_id 
                AND status = 'completed'
                AND created_at >= DATE_SUB(NOW(), INTERVAL :months MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':company_id', $companyId, PDO::PARAM_INT);
        $stmt->bindValue(':months', $months, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get latest deal for a client
     * 
     * @param int $clientId
     * @param int $userId
     * @return array|false
     */
    public function getLatestByClient($clientId, $companyId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE client_id = :client_id 
                AND company_id = :company_id 
                ORDER BY created_at DESC 
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':client_id', $clientId, PDO::PARAM_INT);
        $stmt->bindValue(':company_id', $companyId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Search deals
     * 
     * @param int $companyId
     * @param string $search
     * @return array
     */
    public function searchByCompany($companyId, $search)
    {
        $sql = "SELECT d.*, c.name as client_name, c.email as client_email 
                FROM {$this->table} d
                LEFT JOIN clients c ON d.client_id = c.id
                WHERE d.company_id = :company_id 
                AND (d.title LIKE :s1 OR c.name LIKE :s2 OR c.email LIKE :s3)
                ORDER BY d.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':company_id', $companyId, PDO::PARAM_INT);
        $searchTerm = "%{$search}%";
        $stmt->bindValue(':s1', $searchTerm);
        $stmt->bindValue(':s2', $searchTerm);
        $stmt->bindValue(':s3', $searchTerm);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
