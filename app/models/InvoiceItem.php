<?php
/**
 * InvoiceItem Model
 * Handles invoice line items data operations
 */

class InvoiceItem extends Model
{
    protected $table = 'invoice_items';

    /**
     * Get all items for an invoice
     * 
     * @param int $invoiceId
     * @return array
     */
    public function getByInvoice($invoiceId)
    {
        $sql = "SELECT ii.*, p.name as product_name, d.title as deal_title
                FROM {$this->table} ii
                LEFT JOIN products p ON ii.product_id = p.id
                LEFT JOIN deals d ON ii.deal_id = d.id
                WHERE ii.invoice_id = :invoice_id
                ORDER BY ii.id ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':invoice_id', $invoiceId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Delete all items for an invoice
     * 
     * @param int $invoiceId
     * @return bool
     */
    public function deleteByInvoice($invoiceId)
    {
        $sql = "DELETE FROM {$this->table} WHERE invoice_id = :invoice_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':invoice_id', $invoiceId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Calculate total for invoice items
     * 
     * @param int $invoiceId
     * @return float
     */
    public function calculateTotal($invoiceId)
    {
        $sql = "SELECT SUM(total) as total FROM {$this->table} 
                WHERE invoice_id = :invoice_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':invoice_id', $invoiceId, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch();
        return (float) ($result['total'] ?? 0);
    }

    /**
     * Bulk insert invoice items
     * 
     * @param int $invoiceId
     * @param array $items
     * @return bool
     */
    public function bulkInsert($invoiceId, $items)
    {
        if (empty($items)) {
            return false;
        }

        $sql = "INSERT INTO {$this->table} 
                (invoice_id, deal_id, product_id, description, quantity, price, total) 
                VALUES ";

        $values = [];
        $params = [];

        foreach ($items as $index => $item) {
            $values[] = "(:invoice_id_{$index}, :deal_id_{$index}, :product_id_{$index}, :description_{$index}, :quantity_{$index}, :price_{$index}, :total_{$index})";

            $params[":invoice_id_{$index}"] = $invoiceId;
            $params[":deal_id_{$index}"] = $item['deal_id'] ?? null;
            $params[":product_id_{$index}"] = $item['product_id'] ?? null;
            $params[":description_{$index}"] = $item['description'];
            $params[":quantity_{$index}"] = $item['quantity'] ?? 1;
            $params[":price_{$index}"] = $item['price'];
            $params[":total_{$index}"] = $item['total'];
        }

        $sql .= implode(', ', $values);

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        return $stmt->execute();
    }
}
