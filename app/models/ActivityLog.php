<?php
/**
 * ActivityLog Model
 * Handles recording and fetching of user audit logs
 */

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    /**
     * Record a new activity
     * 
     * @param int $userId
     * @param string $actionType
     * @param string $description
     * @return int|bool
     */
    public function log($userId, $actionType, $description = null)
    {
        $data = [
            'company_id' => Session::get('company_id'),
            'user_id' => $userId,
            'action_type' => $actionType,
            'description' => $description,
            'ip_address' => $this->getUserIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ];

        return $this->insert($data);
    }

    public function getByCompany($companyId, $limit = 50)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE company_id = :company_id 
                ORDER BY created_at DESC 
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':company_id', $companyId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get activities for a specific user
     * 
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getByUser($userId, $limit = 50)
    {
        $sql = "SELECT id, company_id, user_id, action_type, description, ip_address, created_at
                FROM {$this->table}
                WHERE user_id = :user_id
                ORDER BY created_at DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', (int) $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get global recent activities (for super admins)
     * 
     * @param int $limit
     * @return array
     */
    public function getGlobalRecent($limit = 50)
    {
        $sql = "SELECT l.*, u.full_name, u.email 
                FROM {$this->table} l 
                LEFT JOIN users u ON l.user_id = u.id 
                ORDER BY l.created_at DESC 
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Helper to get user IP
     */
    private function getUserIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
    }
}
