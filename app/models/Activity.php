<?php
/**
 * Activity Model
 * Handles activity timeline/history data operations
 */

class Activity extends Model
{
    protected $table = 'activities';

    /**
     * Get all activities for a client
     * 
     * @param int $clientId
     * @param int $limit
     * @return array
     */
    public function getByClientByCompany($clientId, $companyId, $limit = null)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE client_id = :client_id 
                AND company_id = :company_id
                ORDER BY created_at DESC";

        if ($limit) {
            $sql .= " LIMIT :limit";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':client_id', $clientId, PDO::PARAM_INT);
        $stmt->bindValue(':company_id', $companyId, PDO::PARAM_INT);

        if ($limit) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get all activities for a user
     * 
     * @param int $userId User ID
     * @param int $limit Maximum number of records to return
     * @return array Activity logs
     */
    public function getByUser($userId, $limit = null)
    {
        $sql = "SELECT id,
                       company_id,
                       user_id,
                       client_id,
                       type as action_type,
                       description,
                       created_at,
                       'N/A' as ip_address
                FROM {$this->table}
                WHERE user_id = :user_id
                ORDER BY created_at DESC";

        if ($limit) {
            $sql .= " LIMIT :limit";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);

        if ($limit) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Log a new activity
     * 
     * @param int $userId
     * @param int $clientId
     * @param string $type
     * @param string $description
     * @return int|false
     */
    public function logByCompany($companyId, $userId, $clientId, $type, $description)
    {
        $data = [
            'company_id' => $companyId,
            'user_id' => $userId,
            'client_id' => $clientId,
            'type' => $type,
            'description' => $description
        ];

        return $this->insert($data);
    }

    /**
     * Log client creation
     * 
     * @param int $userId
     * @param int $clientId
     * @param string $clientName
     * @return int|false
     */
    public static function logClientCreated($companyId, $userId, $clientId, $clientName)
    {
        $activity = new self();
        return $activity->logByCompany(
            $companyId,
            $userId,
            $clientId,
            'client',
            "Client <strong>{$clientName}</strong> was created"
        );
    }

    /**
     * Log client update
     * 
     * @param int $userId
     * @param int $clientId
     * @param string $clientName
     * @return int|false
     */
    public static function logClientUpdated($companyId, $userId, $clientId, $clientName)
    {
        $activity = new self();
        return $activity->logByCompany(
            $companyId,
            $userId,
            $clientId,
            'client',
            "Client information was updated"
        );
    }

    /**
     * Log deal creation
     * 
     * @param int $userId
     * @param int $clientId
     * @param string $dealTitle
     * @param float $amount
     * @return int|false
     */
    public static function logDealCreated($companyId, $userId, $clientId, $dealTitle, $amount)
    {
        $activity = new self();
        $formattedAmount = CURRENCY_SYMBOL . number_format($amount, 2);
        return $activity->logByCompany(
            $companyId,
            $userId,
            $clientId,
            'deal',
            "New deal <strong>{$dealTitle}</strong> created ({$formattedAmount})"
        );
    }

    /**
     * Log deal status change
     * 
     * @param int $userId
     * @param int $clientId
     * @param string $dealTitle
     * @param string $oldStatus
     * @param string $newStatus
     * @return int|false
     */
    public static function logDealStatusChanged($companyId, $userId, $clientId, $dealTitle, $oldStatus, $newStatus)
    {
        $activity = new self();
        $oldStatusLabel = ucfirst(str_replace('_', ' ', $oldStatus));
        $newStatusLabel = ucfirst(str_replace('_', ' ', $newStatus));
        return $activity->logByCompany(
            $companyId,
            $userId,
            $clientId,
            'deal',
            "Deal <strong>{$dealTitle}</strong> status changed from {$oldStatusLabel} to {$newStatusLabel}"
        );
    }

    /**
     * Log invoice creation
     * 
     * @param int $userId
     * @param int $clientId
     * @param string $invoiceNumber
     * @param float $amount
     * @return int|false
     */
    public static function logInvoiceCreated($companyId, $userId, $clientId, $invoiceNumber, $amount)
    {
        $activity = new self();
        $formattedAmount = CURRENCY_SYMBOL . number_format($amount, 2);
        return $activity->logByCompany(
            $companyId,
            $userId,
            $clientId,
            'invoice',
            "Invoice <strong>{$invoiceNumber}</strong> created ({$formattedAmount})"
        );
    }

    /**
     * Log invoice status change
     * 
     * @param int $userId
     * @param int $clientId
     * @param string $invoiceNumber
     * @param string $status
     * @return int|false
     */
    public static function logInvoiceStatusChanged($companyId, $userId, $clientId, $invoiceNumber, $status)
    {
        $activity = new self();
        $statusLabel = ucfirst(str_replace('_', ' ', $status));
        return $activity->logByCompany(
            $companyId,
            $userId,
            $clientId,
            'invoice',
            "Invoice <strong>{$invoiceNumber}</strong> marked as {$statusLabel}"
        );
    }

    /**
     * Log payment
     * 
     * @param int $userId
     * @param int $clientId
     * @param string $invoiceNumber
     * @param float $amount
     * @param string $method
     * @return int|false
     */
    public static function logPaymentAdded($companyId, $userId, $clientId, $invoiceNumber, $amount, $method)
    {
        $activity = new self();
        $formattedAmount = CURRENCY_SYMBOL . number_format($amount, 2);
        $methodLabel = ucfirst($method);
        return $activity->logByCompany(
            $companyId,
            $userId,
            $clientId,
            'payment',
            "Payment of {$formattedAmount} received for invoice <strong>{$invoiceNumber}</strong> via {$methodLabel}"
        );
    }

    /**
     * Log task creation
     * 
     * @param int $userId
     * @param int $clientId
     * @param string $taskTitle
     * @param string $dueDate
     * @return int|false
     */
    public static function logTaskCreated($companyId, $userId, $clientId, $taskTitle, $dueDate)
    {
        $activity = new self();
        $formattedDate = date('M d, Y', strtotime($dueDate));
        return $activity->logByCompany(
            $companyId,
            $userId,
            $clientId,
            'task',
            "Task <strong>{$taskTitle}</strong> created (Due: {$formattedDate})"
        );
    }

    /**
     * Log task completion
     * 
     * @param int $userId
     * @param int $clientId
     * @param string $taskTitle
     * @return int|false
     */
    public static function logTaskCompleted($companyId, $userId, $clientId, $taskTitle)
    {
        $activity = new self();
        return $activity->logByCompany(
            $companyId,
            $userId,
            $clientId,
            'task',
            "Task <strong>{$taskTitle}</strong> marked as completed"
        );
    }

    /**
     * Log follow-up creation
     * 
     * @param int $userId
     * @param int $clientId
     * @param string $notes
     * @param string $followupDate
     * @return int|false
     */
    public static function logFollowupCreated($companyId, $userId, $clientId, $notes, $followupDate)
    {
        $activity = new self();
        $formattedDate = date('M d, Y', strtotime($followupDate));
        $shortNotes = mb_substr($notes, 0, 50) . (mb_strlen($notes) > 50 ? '...' : '');
        return $activity->logByCompany(
            $companyId,
            $userId,
            $clientId,
            'followup',
            "Follow-up scheduled for {$formattedDate}: {$shortNotes}"
        );
    }

    /**
     * Log follow-up completion
     * 
     * @param int $userId
     * @param int $clientId
     * @param string $followupDate
     * @return int|false
     */
    public static function logFollowupCompleted($companyId, $userId, $clientId, $followupDate)
    {
        $activity = new self();
        $formattedDate = date('M d, Y', strtotime($followupDate));
        return $activity->logByCompany(
            $companyId,
            $userId,
            $clientId,
            'followup',
            "Follow-up for {$formattedDate} marked as done"
        );
    }

    /**
     * Get activity icon based on type
     * 
     * @param string $type
     * @return string
     */
    public static function getIcon($type)
    {
        $icons = [
            'client' => 'bi-person-circle',
            'deal' => 'bi-briefcase',
            'invoice' => 'bi-file-text',
            'payment' => 'bi-cash-coin',
            'task' => 'bi-check-square',
            'followup' => 'bi-calendar-check'
        ];

        return $icons[$type] ?? 'bi-circle';
    }

    /**
     * Get activity color based on type
     * 
     * @param string $type
     * @return string
     */
    public static function getColor($type)
    {
        $colors = [
            'client' => 'primary',
            'deal' => 'success',
            'invoice' => 'info',
            'payment' => 'warning',
            'task' => 'secondary',
            'followup' => 'purple'
        ];

        return $colors[$type] ?? 'secondary';
    }
}
