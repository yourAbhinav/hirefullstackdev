<?php

/**
 * DevHire Site Configuration
 * Centralized contact information and site settings
 */

// Company Information
define('SITE_COMPANY_NAME', function_exists('getSiteName') ? getSiteName('DevHire') : 'DevHire');

// Contact Information
define('CONTACT_ADDRESS', '123 Tech Street<br>San Francisco, CA 94102<br>United States');
define('CONTACT_PHONE', '+1 (234) 567-8900');
define('CONTACT_SUPPORT_EMAIL', 'support@devhire.com');

// Site URLs - derive from current request if env var not set
if (getenv('SITE_URL') !== false && getenv('SITE_URL') !== '') {
    define('SITE_URL', rtrim(getenv('SITE_URL'), '/'));
} else {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443) ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    // Use defined constant if available, otherwise check environment, otherwise empty
    if (defined('APP_BASE_URL') && APP_BASE_URL !== '') {
        $baseUrl = rtrim(APP_BASE_URL, '/');
    } elseif (getenv('APP_BASE_URL') !== false && getenv('APP_BASE_URL') !== '') {
        $baseUrl = rtrim(getenv('APP_BASE_URL'), '/');
    } else {
        $baseUrl = '';
    }
    define('SITE_URL', $scheme . '://' . $host . $baseUrl);
}

// SEO Defaults
define('SEO_DEFAULT_TITLE', SITE_COMPANY_NAME . ' - Hire Top Full Stack Developers');
define('SEO_DEFAULT_DESCRIPTION', 'Connect with talented full stack developers. Verified companies, quality talent, seamless hiring process.');
