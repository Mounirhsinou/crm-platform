<?php
/**
 * Setting Model
 * Handles generic settings storage
 */

class Setting extends Model
{
    protected $table = 'settings';

    /**
     * Get a single setting value
     * 
     * @param int $userId
     * @param string $key
     * @param mixed $default
     * @return string
     */
    public function get($companyId, $key, $default = null)
    {
        $setting = $this->findOne(['company_id' => $companyId, 'setting_key' => $key]);
        return $setting ? $setting['setting_value'] : $default;
    }

    /**
     * Get multiple settings at once
     * 
     * @param int $userId
     * @param array $keys
     * @return array
     */
    public function getMultiple($companyId, $keys)
    {
        if (empty($keys))
            return [];

        $conditions = ['company_id' => $companyId];
        $allSettings = $this->findAll($conditions);

        $results = [];
        foreach ($allSettings as $row) {
            if (in_array($row['setting_key'], $keys)) {
                $results[$row['setting_key']] = $row['setting_value'];
            }
        }

        // Fill defaults for missing keys
        $final = [];
        foreach ($keys as $key) {
            $final[$key] = $results[$key] ?? null;
        }

        return $final;
    }

    /**
     * Save a setting
     * 
     * @param int $userId
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function save($companyId, $key, $value)
    {
        $existing = $this->findOne(['company_id' => $companyId, 'setting_key' => $key]);

        if ($existing) {
            return $this->update($existing['id'], ['setting_value' => $value]);
        } else {
            return $this->insert([
                'company_id' => $companyId,
                'setting_key' => $key,
                'setting_value' => $value
            ]);
        }
    }
}
