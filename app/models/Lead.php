<?php
/**
 * Lead Model
 * Handles operations for leads collected through public checkout
 */

class Lead extends Model
{
    protected $table = 'leads';

    /**
     * Get all leads for a user
     * 
     * @param int $userId
     * @return array
     */
    public function getByUser($userId)
    {
        return $this->findAll(['user_id' => $userId], 'created_at DESC');
    }

    /**
     * Get all leads for a company
     * 
     * @param int $companyId
     * @return array
     */
    public function getByCompany($companyId, $filtered = true)
    {
        if ($filtered) {
            return $this->findAllFiltered(['company_id' => $companyId], 'created_at DESC');
        }
        return $this->findAll(['company_id' => $companyId], 'created_at DESC');
    }

    /**
     * Count leads with filters
     * 
     * @param array $conditions
     * @param string|null $startDate
     * @param string|null $endDate
     * @return int
     */
    public function countFiltered($conditions = [], $startDate = null, $endDate = null)
    {
        $conditions = $this->applyCompanyScope($conditions);
        $params = [];
        $where = [];

        foreach ($conditions as $k => $v) {
            $where[] = "$k = :$k";
            $params[":$k"] = $v;
        }

        if ($startDate) {
            $where[] = "created_at >= :start_date";
            $params[':start_date'] = $startDate . ' 00:00:00';
        }
        if ($endDate) {
            $where[] = "created_at <= :end_date";
            $params[':end_date'] = $endDate . ' 23:59:59';
        }

        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v)
            $stmt->bindValue($k, $v);
        $stmt->execute();

        $result = $stmt->fetch();
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Capture lead data based on settings
     * 
     * @param int $companyId
     * @param int $userId
     * @param array $data
     * @param string $source
     * @param int $sourceId
     * @return bool
     */
    public function capture($companyId, $userId, $data, $source, $sourceId)
    {
        // Load settings directly from the settings table
        $settingKeys = [
            'lead_collect_name',
            'lead_collect_email',
            'lead_collect_phone',
            'lead_collect_address',
            'lead_deduplication'
        ];

        $placeholders = implode(',', array_fill(0, count($settingKeys), '?'));
        $sql = "SELECT setting_key, setting_value FROM settings 
                WHERE company_id = ? AND setting_key IN ($placeholders)";

        $stmt = $this->db->prepare($sql);
        $params = array_merge([$companyId], $settingKeys);
        $stmt->execute($params);
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $leadData = [
            'company_id' => $companyId,
            'user_id' => $userId,
            'source' => $source,
            'source_id' => $sourceId
        ];

        // Only save enabled fields
        if (($settings['lead_collect_name'] ?? '1') === '1')
            $leadData['name'] = Security::sanitize($data['name'] ?? '');
        if (($settings['lead_collect_email'] ?? '1') === '1')
            $leadData['email'] = Security::sanitize($data['email'] ?? '');
        if (($settings['lead_collect_phone'] ?? '1') === '1')
            $leadData['phone'] = Security::sanitize($data['phone'] ?? '');

        if (($settings['lead_collect_address'] ?? '1') === '1') {
            $leadData['address'] = Security::sanitize($data['address'] ?? '');
            $leadData['city'] = Security::sanitize($data['city'] ?? '');
            $leadData['country'] = Security::sanitize($data['country'] ?? '');
        }

        // Company field is often part of generic checkout
        if (isset($data['company'])) {
            $leadData['company'] = Security::sanitize($data['company']);
        }

        // Always store full data payload as JSON
        $leadData['lead_data'] = json_encode($data);

        // Deduplication
        if (($settings['lead_deduplication'] ?? '1') === '1') {
            $existing = false;
            if (!empty($leadData['email'])) {
                $existing = $this->findOne(['company_id' => $companyId, 'email' => $leadData['email']]);
            } elseif (!empty($leadData['phone'])) {
                $existing = $this->findOne(['company_id' => $companyId, 'phone' => $leadData['phone']]);
            }

            if ($existing) {
                // Update existing lead instead of creating new one
                return $this->update($existing['id'], $leadData);
            }
        }

        return $this->insert($leadData);
    }
}
