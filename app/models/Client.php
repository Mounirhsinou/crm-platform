<?php
/**
 * Client Model
 * Handles client data operations
 */

class Client extends Model
{
    protected $table = 'clients';

    /**
     * Get all clients for a user
     * 
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getByCompany($companyId, $limit = null, $offset = null)
    {
        return $this->findAll(
            ['company_id' => $companyId],
            'created_at DESC',
            $limit,
            $offset
        );
    }

    /**
     * Search clients
     * 
     * @param int $userId
     * @param string $search
     * @return array
     */
    public function searchByCompany($companyId, $search)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE company_id = :company_id 
                AND (name LIKE :s1 OR email LIKE :s2 OR phone LIKE :s3 OR company LIKE :s4)
                ORDER BY created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':company_id', $companyId, PDO::PARAM_INT);
        $searchTerm = "%{$search}%";
        $stmt->bindValue(':s1', $searchTerm);
        $stmt->bindValue(':s2', $searchTerm);
        $stmt->bindValue(':s3', $searchTerm);
        $stmt->bindValue(':s4', $searchTerm);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get client with related data
     * 
     * @param int $id
     * @param int $userId
     * @return array|false
     */
    public function getWithRelationsByCompany($id, $companyId)
    {
        $client = $this->findOne(['id' => $id, 'company_id' => $companyId]);

        if (!$client) {
            return false;
        }

        // Get related deals
        $sql = "SELECT * FROM deals WHERE client_id = :client_id ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':client_id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $client['deals'] = $stmt->fetchAll();

        // Get related follow-ups
        $sql = "SELECT * FROM followups WHERE client_id = :client_id ORDER BY followup_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':client_id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $client['followups'] = $stmt->fetchAll();

        // Get related invoices
        $sql = "SELECT * FROM invoices WHERE client_id = :client_id ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':client_id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $client['invoices'] = $stmt->fetchAll();

        return $client;
    }
}
