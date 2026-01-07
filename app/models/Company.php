<?php
/**
 * Company Model
 * Handles company branding data operations
 */

class Company extends Model
{
    protected $table = 'companies';

    public function getBySession()
    {
        $companyId = Session::get('company_id');
        if (!$companyId)
            return false;
        return $this->findById($companyId);
    }

    /**
     * Get company by user ID (Deprecated in SaaS mode, use getBySession)
     * 
     * @param int $userId
     * @return array|false
     */
    public function getByUser($userId)
    {
        return $this->findOne(['owner_id' => $userId]);
    }

    /**
     * Alias for getByUser to fix AuthController error
     * 
     * @param int $userId
     * @return array|false
     */
    public function getByUserId($userId)
    {
        return $this->getByUser($userId);
    }

    /**
     * Create or update company information
     * 
     * @param int $userId
     * @param array $data
     * @return bool
     */
    public function createOrUpdate($userId, $data)
    {
        $existing = $this->getByUser($userId);

        if ($existing) {
            return $this->update($existing['id'], $data);
        } else {
            $data['user_id'] = $userId;
            return $this->insert($data);
        }
    }

    /**
     * Get company name or fallback
     * 
     * @param int $userId
     * @param string $fallback
     * @return string
     */
    public function getCompanyName($userId, $fallback = 'CRM')
    {
        $company = $this->getByUser($userId);
        return $company && !empty($company['company_name']) ? $company['company_name'] : $fallback;
    }

    /**
     * Get logo path or default
     * 
     * @param int $userId
     * @return string
     */
    public function getLogoPath($userId)
    {
        $company = $this->getByUser($userId);

        if ($company && !empty($company['logo_path']) && file_exists(PUBLIC_PATH . $company['logo_path'])) {
            return APP_URL . $company['logo_path'];
        }

        return APP_URL . '/assets/img/default-logo.png';
    }

    /**
     * Delete logo file
     * 
     * @param int $userId
     * @return bool
     */
    public function deleteLogo($userId)
    {
        $company = $this->getByUser($userId);

        if ($company && !empty($company['logo_path'])) {
            $filePath = PUBLIC_PATH . $company['logo_path'];

            if (file_exists($filePath)) {
                unlink($filePath);
            }

            return $this->update($company['id'], ['logo_path' => null]);
        }

        return false;
    }
}
