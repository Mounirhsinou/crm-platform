<?php
/**
 * Payment Model
 * Handles individual transaction records
 */

class Payment extends Model
{
    protected $table = 'payments';

    /**
     * Get payments for a specific invoice
     * 
     * @param int $invoiceId
     * @return array
     */
    public function getByInvoice($invoiceId)
    {
        return $this->findAll(['invoice_id' => $invoiceId], 'created_at DESC');
    }

    /**
     * Log a new payment transaction
     * 
     * @param array $data
     * @return bool
     */
    public function recordTransaction($data)
    {
        // Prevent duplicate transaction IDs
        if (isset($data['transaction_id'])) {
            $existing = $this->findOne(['transaction_id' => $data['transaction_id']]);
            if ($existing) {
                return false;
            }
        }

        return $this->insert($data);
    }

    /**
     * Get recent payments for a company
     * 
     * @param int $companyId
     * @param int $limit
     * @return array
     */
    public function getRecentForCompany($companyId, $limit = 10)
    {
        $sql = "SELECT p.*, i.invoice_number 
                FROM {$this->table} p
                JOIN invoices i ON p.invoice_id = i.id
                WHERE i.company_id = :company_id
                ORDER BY p.created_at DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':company_id', $companyId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
