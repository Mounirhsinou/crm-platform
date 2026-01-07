<?php
/**
 * Product Model
 * Handles product/service data operations
 */

class Product extends Model
{
    protected $table = 'products';

    /**
     * Get all products for a company
     * 
     * @param int $companyId
     * @return array
     */
    public function getByCompany($companyId)
    {
        return $this->findAll(['company_id' => $companyId], 'name ASC');
    }

    /**
     * Search products by company
     * 
     * @param int $companyId
     * @param string $query
     * @return array
     */
    public function searchByCompany($companyId, $query)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE company_id = :company_id 
                AND (name LIKE :query OR description LIKE :query)
                ORDER BY name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':company_id', $companyId, PDO::PARAM_INT);
        $stmt->bindValue(':query', "%{$query}%");
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get product usage count (how many times used in invoices)
     * 
     * @param int $productId
     * @return int
     */
    public function getUsageCount($productId)
    {
        $sql = "SELECT COUNT(*) as count FROM invoice_items 
                WHERE product_id = :product_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch();
        return (int) ($result['count'] ?? 0);
    }

    /**
     * Get most used products by company
     * 
     * @param int $companyId
     * @param int $limit
     * @return array
     */
    public function getMostUsedByCompany($companyId, $limit = 5)
    {
        $sql = "SELECT p.*, COUNT(ii.id) as usage_count
                FROM {$this->table} p
                LEFT JOIN invoice_items ii ON p.id = ii.product_id
                WHERE p.company_id = :company_id
                GROUP BY p.id
                ORDER BY usage_count DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':company_id', $companyId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
