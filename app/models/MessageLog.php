<?php
/**
 * MessageLog Model
 */

class MessageLog extends Model
{
    protected $table = 'message_logs';

    public function log($companyId, $data)
    {
        $data['company_id'] = $companyId;
        return $this->insert($data);
    }

    /**
     * Get logs for a company
     * 
     * @param int $companyId
     * @param int $limit
     * @return array
     */
    public function getByCompany($companyId, $limit = 50)
    {
        return $this->findAll(['company_id' => $companyId], 'created_at DESC', $limit);
    }
}
