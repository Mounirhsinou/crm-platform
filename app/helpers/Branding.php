<?php
/**
 * Branding Helper
 * Provides easy access to company branding across the CRM
 */

class Branding
{
    private static $company = null;
    private static $companyModel = null;

    /**
     * Initialize branding data
     */
    private static function init()
    {
        if (self::$companyModel === null) {
            self::$companyModel = new Company();
        }

        if (self::$company === null && Session::has('company_id')) {
            self::$company = self::$companyModel->getBySession();
        }
    }

    /**
     * Get company name
     * 
     * @param string $default
     * @return string
     */
    public static function getCompanyName($default = 'CRM')
    {
        self::init();
        return self::$company && !empty(self::$company['company_name'])
            ? self::$company['company_name']
            : $default;
    }

    /**
     * Get owner name
     * 
     * @return string|null
     */
    public static function getOwnerName()
    {
        self::init();
        return self::$company && !empty(self::$company['owner_name'])
            ? self::$company['owner_name']
            : null;
    }

    /**
     * Get logo URL
     * 
     * @return string
     */
    public static function getLogoUrl()
    {
        self::init();

        if (self::$company && !empty(self::$company['logo_path'])) {
            $fullPath = PUBLIC_PATH . self::$company['logo_path'];
            if (file_exists($fullPath)) {
                return APP_URL . self::$company['logo_path'];
            }
        }

        return APP_URL . '/assets/img/default-logo.png';
    }

    /**
     * Get absolute logo physical path
     * 
     * @return string|null
     */
    public static function getLogoPath()
    {
        self::init();

        if (self::$company && !empty(self::$company['logo_path'])) {
            $fullPath = PUBLIC_PATH . self::$company['logo_path'];
            if (file_exists($fullPath)) {
                return $fullPath;
            }
        }

        return null;
    }

    /**
     * Get company address
     * 
     * @return string|null
     */
    public static function getAddress()
    {
        self::init();
        return self::$company && !empty(self::$company['address'])
            ? self::$company['address']
            : null;
    }

    /**
     * Get company phone
     * 
     * @return string|null
     */
    public static function getPhone()
    {
        self::init();
        return self::$company && !empty(self::$company['phone'])
            ? self::$company['phone']
            : null;
    }

    /**
     * Get company email
     * 
     * @return string|null
     */
    public static function getEmail()
    {
        self::init();
        return self::$company && !empty(self::$company['email'])
            ? self::$company['email']
            : null;
    }

    /**
     * Get company website
     * 
     * @return string|null
     */
    public static function getWebsite()
    {
        self::init();
        return self::$company && !empty(self::$company['website'])
            ? self::$company['website']
            : null;
    }

    /**
     * Get all company data
     * 
     * @return array|null
     */
    public static function getAll()
    {
        self::init();
        return self::$company;
    }

    /**
     * Check if company has logo
     * 
     * @return bool
     */
    public static function hasLogo()
    {
        self::init();

        if (self::$company && !empty(self::$company['logo_path'])) {
            $fullPath = PUBLIC_PATH . self::$company['logo_path'];
            return file_exists($fullPath);
        }

        return false;
    }

    /**
     * Get currency symbol
     * 
     * @return string
     */
    public static function getCurrencySymbol()
    {
        return defined('CURRENCY_SYMBOL') ? CURRENCY_SYMBOL : '$';
    }

    /**
     * Get currency code
     * 
     * @return string
     */
    public static function getCurrencyCode()
    {
        return defined('CURRENCY_CODE') ? CURRENCY_CODE : 'USD';
    }

    /**
     * Get company initials for fallback avatar
     * 
     * @return string
     */
    public static function getInitials()
    {
        $name = self::getCompanyName();
        $words = explode(' ', $name);
        $initials = '';

        if (count($words) >= 2) {
            $initials = strtoupper(substr($words[0], 0, 1) . substr($words[count($words) - 1], 0, 1));
        } else {
            $initials = strtoupper(substr($name, 0, 2));
        }

        return $initials;
    }

    /**
     * Set company data manually (useful for public views without session)
     * 
     * @param array $data
     * @return void
     */
    public static function setCompany($data)
    {
        self::$company = $data;
    }

    /**
     * Clear cached company data
     * Call this after updating company information to force reload
     * 
     * @return void
     */
    public static function clearCache()
    {
        self::$company = null;
        self::$companyModel = null;
    }
}
