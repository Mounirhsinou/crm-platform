<?php
/**
 * BackupCode Model
 * Handles 2FA backup codes
 */

class BackupCode extends Model
{
    protected $table = 'backup_codes';

    /**
     * Get unused backup codes for a user
     * 
     * @param int $userId
     * @return array
     */
    public function getUnusedByUser($userId)
    {
        return $this->findAll([
            'user_id' => $userId,
            'used' => 0
        ], 'created_at DESC');
    }

    /**
     * Verify and use a backup code
     * 
     * @param int $userId
     * @param string $code
     * @return bool
     */
    public function verifyAndUse($userId, $code)
    {
        $backupCode = $this->findOne([
            'user_id' => $userId,
            'code' => strtoupper($code),
            'used' => 0
        ]);

        if ($backupCode) {
            $this->update($backupCode['id'], [
                'used' => 1,
                'used_at' => date('Y-m-d H:i:s')
            ]);

            return true;
        }

        return false;
    }

    /**
     * Generate backup codes for a user
     * 
     * @param int $userId
     * @param int $count
     * @return array
     */
    public function generate($userId, $count = 10)
    {
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            // Generate 10-character code
            $code = strtoupper(substr(bin2hex(random_bytes(5)), 0, 10));

            $this->insert([
                'user_id' => $userId,
                'code' => $code
            ]);

            $codes[] = $code;
        }

        return $codes;
    }

    /**
     * Delete all backup codes for a user
     * 
     * @param int $userId
     * @return bool
     */
    public function deleteByUser($userId)
    {
        $sql = "DELETE FROM {$this->table} WHERE user_id = :user_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Count unused backup codes
     * 
     * @param int $userId
     * @return int
     */
    public function countUnused($userId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE user_id = :user_id AND used = 0";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch();
        return (int) ($result['count'] ?? 0);
    }
}
