<?php
/**
 * User Model
 * Handles user authentication and management
 */

class User extends Model
{
    protected $table = 'users';

    /**
     * Find user by email
     * 
     * @param string $email
     * @return array|false
     */
    public function findByEmail($email)
    {
        return $this->findOne(['email' => $email]);
    }

    /**
     * Create new user
     * 
     * @param array $data User data
     * @return int User ID
     */
    public function create($data)
    {
        // Hash password
        if (isset($data['password'])) {
            $data['password_hash'] = Security::hashPassword($data['password']);
            unset($data['password']);
        }

        return $this->insert($data);
    }

    /**
     * Authenticate user and check if active
     * 
     * @param string $email
     * @param string $password
     * @return array|bool|string User data, false on failure, or string for specific states (e.g. deactivated)
     */
    public function authenticate($email, $password)
    {
        $user = $this->getUserWithRoleByEmail($email);

        if ($user && Security::verifyPassword($password, $user['password_hash'])) {
            if (!$user['is_active']) {
                return 'deactivated';
            }
            return $user;
        }

        return false;
    }

    /**
     * Get user with role details by email
     */
    public function getUserWithRoleByEmail($email)
    {
        $sql = "SELECT u.*, r.name as role_name, r.slug as role_slug, r.permissions, c.company_name 
                FROM {$this->table} u 
                LEFT JOIN roles r ON u.role_id = r.id 
                LEFT JOIN companies c ON u.company_id = c.id
                WHERE u.email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Delete a user with protection
     * 
     * @param int $userId
     * @return array ['success' => bool, 'message' => string]
     */
    public function deleteUser($userId)
    {
        $user = $this->getUserWithRole($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        if (!$this->canBeDeleted($userId, $user['role_slug'])) {
            return ['success' => false, 'message' => 'This user cannot be deleted (Last Super Admin protection)'];
        }

        if ($this->delete($userId)) {
            return ['success' => true, 'message' => 'User deleted successfully'];
        }

        return ['success' => false, 'message' => 'Failed to delete user'];
    }

    /**
     * Check if user can be deleted or deactivated
     * Prevents system lock-out by protecting the last Super Admin
     */
    public function canBeDeleted($userId, $roleSlug = null)
    {
        if ($roleSlug === null) {
            $user = $this->getUserWithRole($userId);
            $roleSlug = $user['role_slug'] ?? '';
        }

        if ($roleSlug !== 'super_admin') {
            return true;
        }

        // Count active super admins
        $sql = "SELECT COUNT(*) as count FROM users u 
                JOIN roles r ON u.role_id = r.id 
                WHERE r.slug = 'super_admin' AND u.is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();

        return $result['count'] > 1;
    }

    /**
     * Check if user must change password
     * 
     * @param int $id User ID
     * @param array $data User data
     * @return bool
     */
    public function updateProfile($id, $data)
    {
        // Remove password_hash from data if present
        unset($data['password_hash']);

        // Hash new password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password_hash'] = Security::hashPassword($data['password']);
            unset($data['password']);
        }

        return $this->update($id, $data);
    }

    /**
     * Check if email exists
     * 
     * @param string $email
     * @param int $excludeId User ID to exclude (for updates)
     * @return bool
     */
    public function emailExists($email, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = :email";

        if ($excludeId) {
            $sql .= " AND id != :id";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $email);

        if ($excludeId) {
            $stmt->bindValue(':id', $excludeId, PDO::PARAM_INT);
        }

        $stmt->execute();
        $result = $stmt->fetch();

        return $result['count'] > 0;
    }

    /**
     * Get all users with role information (DEPRECATED - use getAllUsersByCompany)
     * 
     * @return array
     * @deprecated Use getAllUsersByCompany() for multi-tenant isolation
     */
    public function getAllUsers()
    {
        $sql = "SELECT u.*, r.name as role_name, r.slug as role_slug 
                FROM {$this->table} u 
                LEFT JOIN roles r ON u.role_id = r.id 
                ORDER BY u.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get all users for a specific company with role information
     * 
     * @param int $companyId
     * @return array
     */
    public function getAllUsersByCompany($companyId)
    {
        $sql = "SELECT u.*, r.name as role_name, r.slug as role_slug 
                FROM {$this->table} u 
                LEFT JOIN roles r ON u.role_id = r.id 
                WHERE u.company_id = :company_id
                ORDER BY u.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':company_id', $companyId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get user with role details
     * 
     * @param int $id
     * @param int $companyId Optional company_id for authorization check
     * @return array|false
     */
    public function getUserWithRole($id, $companyId = null)
    {
        $sql = "SELECT u.*, r.name as role_name, r.slug as role_slug, r.permissions, c.company_name 
                FROM {$this->table} u 
                LEFT JOIN roles r ON u.role_id = r.id 
                LEFT JOIN companies c ON u.company_id = c.id
                WHERE u.id = :id";

        if ($companyId !== null) {
            $sql .= " AND u.company_id = :company_id";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if ($companyId !== null) {
            $stmt->bindValue(':company_id', $companyId, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Create new user with role
     * 
     * @param array $data User data including role_id
     * @return int User ID
     */
    public function createUser($data)
    {
        // Hash password if provided
        if (isset($data['password'])) {
            $data['password_hash'] = Security::hashPassword($data['password']);
            unset($data['password']);
        }

        // Set defaults
        $data['must_change_password'] = $data['must_change_password'] ?? 1;
        $data['is_active'] = $data['is_active'] ?? 1;

        return $this->insert($data);
    }

    /**
     * Update user's role
     * 
     * @param int $userId
     * @param int $roleId
     * @return bool
     */
    public function updateUserRole($userId, $roleId)
    {
        return $this->update($userId, ['role_id' => $roleId]);
    }

    /**
     * Toggle user active status
     * 
     * @param int $userId
     * @return bool
     */
    public function toggleUserStatus($userId)
    {
        $user = $this->findOne(['id' => $userId]);

        if (!$user) {
            return false;
        }

        $newStatus = $user['is_active'] ? 0 : 1;
        return $this->update($userId, ['is_active' => $newStatus]);
    }

    /**
     * Reset user password and set must_change_password flag
     * 
     * @param int $userId
     * @param string $newPassword
     * @return bool
     */
    public function resetUserPassword($userId, $newPassword)
    {
        $passwordHash = Security::hashPassword($newPassword);

        return $this->update($userId, [
            'password_hash' => $passwordHash,
            'must_change_password' => 1
        ]);
    }

    /**
     * Check if user must change password
     * 
     * @param int $userId
     * @return bool
     */
    public function mustChangePassword($userId)
    {
        $user = $this->findOne(['id' => $userId]);
        return $user && $user['must_change_password'] == 1;
    }

    /**
     * Clear must_change_password flag
     * 
     * @param int $userId
     * @return bool
     */
    public function clearPasswordChangeFlag($userId)
    {
        return $this->update($userId, ['must_change_password' => 0]);
    }
}
