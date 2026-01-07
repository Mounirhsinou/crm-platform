<?php
/**
 * MessagingSetting Model
 */

class MessagingSetting extends Model
{
    protected $table = 'messaging_settings';

    /**
     * Get setting by user ID and type
     * 
     * @param int $userId
     * @param string $type sms or whatsapp
     * @return array|false
     */
    public function getSetting($companyId, $type)
    {
        return $this->findOne(['company_id' => $companyId, 'type' => $type]);
    }

    /**
     * Save or update settings
     * 
     * @param int $userId
     * @param array $data
     * @return bool
     */
    public function saveSettings($companyId, $data)
    {
        $existing = $this->getSetting($companyId, $data['type']);

        if ($existing) {
            return $this->update($existing['id'], $data);
        } else {
            $data['company_id'] = $companyId;
            return $this->insert($data);
        }
    }
}
