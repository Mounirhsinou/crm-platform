<?php
/**
 * Invoice Model
 * Handles invoice data operations
 */

class Invoice extends Model
{
    protected $table = 'invoices';

    /**
     * Get all invoices for a user
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
     * Get invoices with client information
     * 
     * @param int $userId
     * @param string $status
     * @return array
     */
    public function getWithClientsByCompany($companyId, $status = null, $filtered = true)
    {
        $sql = "SELECT i.*, 
                       c.name as client_name,
                       c.email as client_email,
                       d.title as deal_title
                FROM {$this->table} i
                LEFT JOIN clients c ON i.client_id = c.id
                LEFT JOIN deals d ON i.deal_id = d.id
                WHERE i.company_id = ?";

        $params = [$companyId];

        if ($status) {
            $sql .= " AND i.status = ?";
            $params[] = $status;
        }

        if ($filtered) {
            require_once APP_PATH . '/helpers/DateFilter.php';
            DateFilter::applyToSql('i.created_at', $sql, $params);
        }

        $sql .= " ORDER BY i.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get invoice by ID with client info
     * 
     * @param int $id
     * @param int $userId
     * @return array|false
     */
    public function getWithClientByCompany($id, $companyId)
    {
        $sql = "SELECT i.*, 
                       c.name as client_name,
                       c.email as client_email,
                       c.phone as client_phone,
                       c.notes as client_notes,
                       co.company_name, co.logo_path, co.address as company_address, co.phone as company_phone, co.email as company_email, co.website
                FROM {$this->table} i
                LEFT JOIN clients c ON i.client_id = c.id
                LEFT JOIN companies co ON i.company_id = co.id
                WHERE i.id = :id AND i.company_id = :company_id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':company_id', $companyId, PDO::PARAM_INT);
        $stmt->execute();

        $invoice = $stmt->fetch();

        if ($invoice) {
            // Fetch line items
            $itemModel = new InvoiceItem();
            $invoice['items'] = $itemModel->getByInvoice($id);
        }

        return $invoice;
    }

    /**
     * Get invoice by secure token (Public View)
     * 
     * @param string $token
     * @return array|false
     */
    public function getByToken($token)
    {
        $sql = "SELECT i.*, 
                       c.name as client_name,
                       c.email as client_email,
                       c.phone as client_phone,
                       co.company_name, co.logo_path, co.address, co.phone as company_phone, co.email as company_email, co.website, co.paypal_client_id, 
                       co.stripe_publishable_key, co.stripe_secret_key, co.stripe_mode
                FROM {$this->table} i
                LEFT JOIN clients c ON i.client_id = c.id
                LEFT JOIN companies co ON i.company_id = co.id
                WHERE i.payment_token = :token
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':token', $token);
        $stmt->execute();

        $invoice = $stmt->fetch();

        if ($invoice) {
            $itemModel = new InvoiceItem();
            $invoice['items'] = $itemModel->getByInvoice($invoice['id']);
        }

        return $invoice;
    }

    /**
     * Generate next invoice number with retry logic
     * 
     * @param int $userId
     * @param string $prefix
     * @return string
     */
    public function generateInvoiceNumber($companyId, $prefix = 'INV')
    {
        $year = date('Y');
        $maxRetries = 5;

        for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
            // Get the highest invoice number for this year
            $sql = "SELECT MAX(CAST(SUBSTRING_INDEX(invoice_number, '-', -1) AS UNSIGNED)) as max_num
                    FROM {$this->table} 
                    WHERE company_id = :company_id 
                    AND invoice_number LIKE :pattern
                    FOR UPDATE"; // Lock the rows to prevent race conditions

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':company_id', $companyId, PDO::PARAM_INT);
            $stmt->bindValue(':pattern', "{$prefix}-{$year}-%");
            $stmt->execute();

            $result = $stmt->fetch();
            $nextNumber = $result && $result['max_num'] ? (int) $result['max_num'] + 1 : 1;

            $invoiceNumber = sprintf("%s-%s-%04d", $prefix, $year, $nextNumber);

            // Check if this number already exists (extra safety)
            $checkSql = "SELECT COUNT(*) as count FROM {$this->table} WHERE invoice_number = :invoice_number";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->bindValue(':invoice_number', $invoiceNumber);
            $checkStmt->execute();
            $exists = $checkStmt->fetch();

            if (!$exists || $exists['count'] == 0) {
                return $invoiceNumber;
            }

            // If it exists, try again with next number
            usleep(100000); // Wait 100ms before retry
        }

        // Fallback: use timestamp to ensure uniqueness
        return sprintf("%s-%s-%04d-%s", $prefix, $year, $nextNumber, time());
    }

    /**
     * Get total amount
     * 
     * @param int $userId
     * @param string $status
     * @return float
     */
    public function getTotalAmountByCompany($companyId, $status = null)
    {
        $sql = "SELECT SUM(amount) as total FROM {$this->table} WHERE company_id = :company_id";

        if ($status) {
            $sql .= " AND status = :status";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':company_id', $companyId, PDO::PARAM_INT);

        if ($status) {
            $stmt->bindValue(':status', $status);
        }

        $stmt->execute();

        $result = $stmt->fetch();
        return (float) ($result['total'] ?? 0);
    }

    /**
     * Close or Open payment link
     * 
     * @param int $id
     * @param int $userId
     * @param bool $isClosed
     * @return bool
     */
    public function updatePaymentStatus($id, $companyId, $isClosed)
    {
        return $this->updateWhere(
            ['payment_closed' => $isClosed ? 1 : 0],
            ['id' => $id, 'company_id' => $companyId]
        );
    }

    /**
     * Search invoices
     * 
     * @param int $companyId
     * @param string $search
     * @return array
     */
    public function searchByCompany($companyId, $search)
    {
        $sql = "SELECT i.*, c.name as client_name, c.email as client_email 
                FROM {$this->table} i
                LEFT JOIN clients c ON i.client_id = c.id
                WHERE i.company_id = :company_id 
                AND (i.invoice_number LIKE :s1 OR c.name LIKE :s2 OR c.email LIKE :s3)
                ORDER BY i.created_at DESC";

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
