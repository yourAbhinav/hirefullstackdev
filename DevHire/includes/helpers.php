<?php

function detectAppBaseUrl() {
    $projectRoot = realpath(__DIR__ . '/..');
    $documentRoot = realpath((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''));

    if ($projectRoot !== false && $documentRoot !== false) {
        $projectRootNormalized = str_replace('\\', '/', $projectRoot);
        $documentRootNormalized = rtrim(str_replace('\\', '/', $documentRoot), '/');

        if ($documentRootNormalized !== '' && str_starts_with(strtolower($projectRootNormalized), strtolower($documentRootNormalized))) {
            $relativePath = trim(substr($projectRootNormalized, strlen($documentRootNormalized)), '/');
            if ($relativePath !== '') {
                return '/' . $relativePath;
            }
        }
    }

    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? ''));

    if ($scriptName === '') {
        return '';
    }

    $scriptBaseName = basename($scriptName);
    $basePath = $scriptBaseName === 'index.php' ? dirname($scriptName) : dirname(dirname($scriptName));
    $basePath = str_replace('\\', '/', $basePath);

    if ($basePath === '.' || $basePath === '/') {
        return '';
    }

    return rtrim($basePath, '/');
}

if (!defined('APP_BASE_URL')) {
    $configuredBaseUrl = getenv('APP_BASE_URL');
    define('APP_BASE_URL', $configuredBaseUrl !== false && $configuredBaseUrl !== '' ? rtrim($configuredBaseUrl, '/') : detectAppBaseUrl());
}

/**
 * Generate URL relative to site base
 */
function appUrl($path = '') {
    $normalizedPath = ltrim($path, '/');
    $baseUrl = APP_BASE_URL !== '' ? rtrim(APP_BASE_URL, '/') : '';

    if ($normalizedPath === '') {
        return $baseUrl !== '' ? $baseUrl : '/';
    }

    return ($baseUrl !== '' ? $baseUrl : '') . '/' . $normalizedPath;
}

/**
 * Check if a database table exists
 */
function dbTableExists($conn, $tableName) {
    $result = $conn->query("SHOW TABLES LIKE '$tableName'");
    return $result && $result->num_rows > 0;
}

/**
 * Start secure session with proper configuration
 */
function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 86400, // 24 hours
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        session_start();
    }
}

/**
 * Check if public user is logged in
 */
function isPublicUserLoggedIn() {
    startSecureSession();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user id from session
 */
function currentUserId() {
    startSecureSession();
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

/**
 * Get current user name from session
 */
function currentUserName() {
    startSecureSession();
    return $_SESSION['user_name'] ?? '';
}

/**
 * Get current user email from session
 */
function currentUserEmail() {
    startSecureSession();
    return $_SESSION['user_email'] ?? '';
}

/**
 * Get current user role from session
 */
function currentUserRole() {
    startSecureSession();
    return $_SESSION['user_role'] ?? '';
}

/**
 * Get current user photo from session
 */
function currentUserPhoto() {
    startSecureSession();
    return $_SESSION['user_photo'] ?? '';
}

/**
 * Get current admin id from session
 */
function currentAdminId() {
    startSecureSession();
    return isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null;
}

/**
 * Check if admin is logged in
 */
function isAdminLoggedIn() {
    startSecureSession();
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Get current admin role from session
 */
function currentAdminRole() {
    startSecureSession();
    return $_SESSION['admin_role'] ?? '';
}

/**
 * Get dashboard path for user role
 */
function roleDashboardPath($role) {
    switch ($role) {
        case 'admin':
            return 'admin/dashboard.php';
        case 'company':
            return 'company/dashboard.php';
        case 'developer':
            return 'developer/dashboard.php';
        default:
            return 'index.php';
    }
}

/**
 * Check if current user is a developer
 */
function isDeveloper() {
    startSecureSession();
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'developer';
}

/**
 * Check if current user is a company account
 */
function isCompany() {
    startSecureSession();
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'company';
}

/**
 * Get a human-friendly label for a role
 */
function roleLabel($role) {
    switch ((string) $role) {
        case 'admin':
            return 'Admin';
        case 'company':
            return 'Company';
        case 'developer':
            return 'Developer';
        default:
            return 'User';
    }
}

/**
 * Get flash message from session
 */
function getFlash($key) {
    startSecureSession();
    $value = $_SESSION[$key] ?? '';
    unset($_SESSION[$key]);
    return $value;
}

/**
 * Store flash message in session
 */
function setFlash($key, $value) {
    startSecureSession();
    $_SESSION[$key] = (string) $value;
}

/**
 * Generate or return the current CSRF token
 */
function csrfToken() {
    startSecureSession();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Validate a CSRF token
 */
function verifyCsrf($token) {
    startSecureSession();

    if (!is_string($token) || $token === '' || empty($_SESSION['csrf_token'])) {
        return false;
    }

    return hash_equals((string) $_SESSION['csrf_token'], $token);
}

/**
 * Require any authenticated user
 */
function requireAuth() {
    if (!isPublicUserLoggedIn()) {
        header('Location: ' . appUrl('pages/login.php'));
        exit;
    }
}

/**
 * Require a developer account
 */
function requireDeveloper() {
    if (!isPublicUserLoggedIn()) {
        header('Location: ' . appUrl('pages/login.php'));
        exit;
    }

    if (!isDeveloper()) {
        header('Location: ' . appUrl(roleDashboardPath(currentUserRole())));
        exit;
    }
}

/**
 * Require a company account
 */
function requireCompany() {
    if (!isPublicUserLoggedIn()) {
        header('Location: ' . appUrl('pages/login.php'));
        exit;
    }

    if (!isCompany()) {
        header('Location: ' . appUrl(roleDashboardPath(currentUserRole())));
        exit;
    }
}

function sanitize($value) {
    if (is_array($value)) return array_map('sanitize', $value);
    return trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function logError($message, $context = '') {
    $entry = '[' . date('Y-m-d H:i:s') . '] ' . $message;
    if (!empty($context)) {
        $entry .= ' | ' . $context;
    }
    error_log($entry . PHP_EOL, 3, __DIR__ . '/../logs/errors.log');
}
