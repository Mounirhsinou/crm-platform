<?php
/**
 * Base Model Class
 * Provides common database operations
 */

class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get the database connection
     * 
     * @return PDO
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * Find all records
     * 
     * @param array $conditions WHERE conditions
     * @param string $orderBy ORDER BY clause
     * @param int $limit LIMIT
     * @param int $offset OFFSET
     * @return array
     */
    public function findAll($conditions = [], $orderBy = null, $limit = null, $offset = null)
    {
        // Multi-tenant scoping
        $conditions = $this->applyCompanyScope($conditions);

        $sql = "SELECT * FROM {$this->table}";

        if (!empty($conditions)) {
            $sql .= " WHERE " . $this->buildWhereClause($conditions, 'where_');
        }

        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }

        if ($limit) {
            $sql .= " LIMIT {$limit}";
            if ($offset) {
                $sql .= " OFFSET {$offset}";
            }
        }

        $stmt = $this->db->prepare($sql);
        $this->bindValues($stmt, $conditions, 'where_');
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Find single record by ID
     * 
     * @param int $id
     * @return array|false
     */
    public function findById($id)
    {
        $conditions = ['id' => $id];
        $conditions = $this->applyCompanyScope($conditions);

        $sql = "SELECT * FROM {$this->table} WHERE " . $this->buildWhereClause($conditions, 'where_') . " LIMIT 1";
        $stmt = $this->db->prepare($sql);

        $this->bindValues($stmt, $conditions, 'where_');
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Find single record by conditions
     * 
     * @param array $conditions
     * @return array|false
     */
    public function findOne($conditions)
    {
        $conditions = $this->applyCompanyScope($conditions);

        $sql = "SELECT * FROM {$this->table} WHERE " . $this->buildWhereClause($conditions, 'where_') . " LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $this->bindValues($stmt, $conditions, 'where_');
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Insert new record
     * 
     * @param array $data
     * @return int Last insert ID
     */
    public function insert($data)
    {
        // Multi-tenant injection
        $data = $this->applyCompanyScope($data);

        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }

        $stmt->execute();
        return $this->db->lastInsertId();
    }

    /**
     * Create new record (alias for insert)
     * 
     * @param array $data
     * @return int Last insert ID
     */
    public function create($data)
    {
        return $this->insert($data);
    }

    /**
     * Update record
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data)
    {
        $setParts = [];
        foreach (array_keys($data) as $column) {
            $setParts[] = "{$column} = :set_{$column}";
        }
        $setClause = implode(', ', $setParts);

        $conditions = ['id' => $id];
        $conditions = $this->applyCompanyScope($conditions);
        $whereClause = $this->buildWhereClause($conditions, 'where_');

        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$whereClause}";
        $stmt = $this->db->prepare($sql);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":set_{$key}", $value);
        }

        $this->bindValues($stmt, $conditions, 'where_');

        return $stmt->execute();
    }

    /**
     * Update records by custom conditions
     * 
     * @param array $data
     * @param array $conditions
     * @return bool
     */
    public function updateWhere($data, $conditions)
    {
        $conditions = $this->applyCompanyScope($conditions);

        $setParts = [];
        foreach (array_keys($data) as $column) {
            $setParts[] = "{$column} = :set_{$column}";
        }
        $setClause = implode(', ', $setParts);

        $whereClause = $this->buildWhereClause($conditions, 'where_');

        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$whereClause}";
        $stmt = $this->db->prepare($sql);

        // Bind update values
        foreach ($data as $key => $value) {
            $stmt->bindValue(":set_{$key}", $value);
        }
        // Bind where conditions
        $this->bindValues($stmt, $conditions, 'where_');

        return $stmt->execute();
    }

    /**
     * Delete record
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $conditions = ['id' => $id];
        $conditions = $this->applyCompanyScope($conditions);

        $sql = "DELETE FROM {$this->table} WHERE " . $this->buildWhereClause($conditions, 'where_');
        $stmt = $this->db->prepare($sql);
        $this->bindValues($stmt, $conditions, 'where_');

        return $stmt->execute();
    }

    /**
     * Count records
     * 
     * @param array $conditions
     * @return int
     */
    public function count($conditions = [])
    {
        $conditions = $this->applyCompanyScope($conditions);

        $sql = "SELECT COUNT(*) as total FROM {$this->table}";

        if (!empty($conditions)) {
            $sql .= " WHERE " . $this->buildWhereClause($conditions, 'where_');
        }

        $stmt = $this->db->prepare($sql);
        $this->bindValues($stmt, $conditions, 'where_');
        $stmt->execute();

        $result = $stmt->fetch();
        return (int) $result['total'];
    }

    /**
     * Execute custom query
     * 
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function query($sql, $params = [])
    {
        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            // If key is numeric (positional parameter), it must be 1-indexed for bindValue
            $param = is_int($key) ? $key + 1 : $key;
            $stmt->bindValue($param, $value);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Find all records with global date filter applied
     */
    public function findAllFiltered($conditions = [], $orderBy = null, $limit = null, $offset = null, $dateColumn = 'created_at')
    {
        $conditions = $this->applyCompanyScope($conditions);

        require_once APP_PATH . '/helpers/DateFilter.php';
        $range = DateFilter::getRange();

        $sql = "SELECT * FROM {$this->table}";
        $whereParts = [];

        if (!empty($conditions)) {
            $whereParts[] = $this->buildWhereClause($conditions, 'where_');
        }

        if ($range['start'] && $range['end']) {
            $whereParts[] = "{$dateColumn} BETWEEN :range_start AND :range_end";
        } elseif ($range['start']) {
            $whereParts[] = "{$dateColumn} >= :range_start";
        } elseif ($range['end']) {
            $whereParts[] = "{$dateColumn} <= :range_end";
        }

        if (!empty($whereParts)) {
            $sql .= " WHERE " . implode(' AND ', $whereParts);
        }

        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }

        if ($limit) {
            $sql .= " LIMIT {$limit}";
            if ($offset) {
                $sql .= " OFFSET {$offset}";
            }
        }

        $stmt = $this->db->prepare($sql);
        $this->bindValues($stmt, $conditions, 'where_');

        if ($range['start'])
            $stmt->bindValue(':range_start', $range['start']);
        if ($range['end'])
            $stmt->bindValue(':range_end', $range['end']);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Count records with global date filter applied
     */
    public function countFiltered($conditions = [], $dateColumn = 'created_at')
    {
        $conditions = $this->applyCompanyScope($conditions);

        require_once APP_PATH . '/helpers/DateFilter.php';
        $range = DateFilter::getRange();

        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $whereParts = [];

        if (!empty($conditions)) {
            $whereParts[] = $this->buildWhereClause($conditions, 'where_');
        }

        if ($range['start'] && $range['end']) {
            $whereParts[] = "{$dateColumn} BETWEEN :range_start AND :range_end";
        } elseif ($range['start']) {
            $whereParts[] = "{$dateColumn} >= :range_start";
        } elseif ($range['end']) {
            $whereParts[] = "{$dateColumn} <= :range_end";
        }

        if (!empty($whereParts)) {
            $sql .= " WHERE " . implode(' AND ', $whereParts);
        }

        $stmt = $this->db->prepare($sql);
        $this->bindValues($stmt, $conditions, 'where_');

        if ($range['start'])
            $stmt->bindValue(':range_start', $range['start']);
        if ($range['end'])
            $stmt->bindValue(':range_end', $range['end']);

        $stmt->execute();
        $result = $stmt->fetch();
        return (int) $result['total'];
    }

    /**
     * Apply company scope to conditions/data array
     * 
     * @param array $array
     * @return array
     */
    protected function applyCompanyScope($array)
    {
        // Skip for companies and roles tables (global tables)
        if (in_array($this->table, ['companies', 'roles'])) {
            return $array;
        }

        $companyId = Session::get('company_id');

        if ($companyId) {
            if (!isset($array['company_id'])) {
                $array['company_id'] = $companyId;
            }
        }

        return $array;
    }

    /**
     * Build WHERE clause from conditions array
     * 
     * @param array $conditions
     * @return string
     */
    private function buildWhereClause($conditions, $prefix = '')
    {
        $parts = [];
        foreach (array_keys($conditions) as $column) {
            $parts[] = "{$column} = :{$prefix}{$column}";
        }
        return implode(' AND ', $parts);
    }

    /**
     * Bind values to prepared statement
     * 
     * @param PDOStatement $stmt
     * @param array $conditions
     */
    private function bindValues($stmt, $conditions, $prefix = '')
    {
        foreach ($conditions as $key => $value) {
            $stmt->bindValue(":{$prefix}{$key}", $value);
        }
    }
}
