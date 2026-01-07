<?php
/**
 * Role Model
 * Handles role management and permissions
 */

class Role extends Model
{
    protected $table = 'roles';

    /**
     * Get all roles
     * 
     * @return array
     */
    public function getAllRoles()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get role by ID
     * 
     * @param int $id
     * @return array|false
     */
    public function getRoleById($id)
    {
        return $this->findOne(['id' => $id]);
    }

    /**
     * Get all roles using the generic findAll method.
     * 
     * @return array
     */
    public function getAll()
    {
        return $this->findAll();
    }

    /**
     * Get role by slug
     * 
     * @param string $slug Role slug
     * @return array|null Role data or null if not found
     */
    public function getBySlug($slug)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get role by slug
     * 
     * @param string $slug
     * @return array|false
     */
    public function getRoleBySlug($slug)
    {
        return $this->findOne(['slug' => $slug]);
    }

    /**
     * Get role permissions as array
     * 
     * @param int $roleId
     * @return array
     */
    public function getRolePermissions($roleId)
    {
        $role = $this->getRoleById($roleId);

        if (!$role || empty($role['permissions'])) {
            return [];
        }

        // Decode JSON permissions
        $permissions = json_decode($role['permissions'], true);
        return $permissions ?: [];
    }

    /**
     * Check if role has specific permission
     * 
     * @param int $roleId
     * @param string $resource (e.g., 'clients', 'deals')
     * @param string $action (e.g., 'view', 'create', 'edit', 'delete')
     * @return bool
     */
    public function hasPermission($roleId, $resource, $action)
    {
        $permissions = $this->getRolePermissions($roleId);

        if (empty($permissions)) {
            return false;
        }

        // Check if resource exists and has the action
        if (isset($permissions[$resource]) && is_array($permissions[$resource])) {
            return in_array($action, $permissions[$resource]);
        }

        return false;
    }

    /**
     * Get roles that can be created by a specific role
     * Super Admin can create all roles
     * Admin can create Moderator and Viewer
     * 
     * @param string $roleSlug Current user's role slug
     * @return array
     */
    public function getCreatableRoles($roleSlug)
    {
        if ($roleSlug === 'owner') {
            // Owner can create all roles
            return $this->getAllRoles();
        } elseif ($roleSlug === 'admin') {
            // Admin can create Moderator and Viewer
            $sql = "SELECT * FROM {$this->table} WHERE slug IN ('moderator', 'viewer') ORDER BY id ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        }

        // Other roles cannot create users
        return [];
    }

    /**
     * Check if a role can create another role
     * 
     * @param string $creatorRoleSlug
     * @param string $targetRoleSlug
     * @return bool
     */
    public function canCreateRole($creatorRoleSlug, $targetRoleSlug)
    {
        if ($creatorRoleSlug === 'owner') {
            return true;
        }

        if ($creatorRoleSlug === 'admin' && in_array($targetRoleSlug, ['moderator', 'viewer'])) {
            return true;
        }

        return false;
    }
}
