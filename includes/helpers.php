<?php

if (!defined('APP_BASE_URL')) {
    define('APP_BASE_URL', '/hieringfullstackdeveloper/DevHire');
}

function startSecureSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    session_set_cookie_params([
        'lifetime' => 60 * 60 * 24 * 30,
        'path' => '/',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

function appUrl(string $path = ''): string
{
    $normalizedPath = ltrim($path, '/');

    if ($normalizedPath === '') {
        return APP_BASE_URL;
    }

    return APP_BASE_URL . '/' . $normalizedPath;
}

function sanitize($value) {
    if (is_array($value)) return array_map('sanitize', $value);
    return trim(htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function csrfToken(): string
{
    startSecureSession();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') . '">';
}

function verifyCsrf(?string $token): bool
{
    startSecureSession();

    return isset($_SESSION['csrf_token']) && is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
}

function isLoggedIn(): bool
{
    startSecureSession();

    return !empty($_SESSION['admin_user_id']) || !empty($_SESSION['user_id']);
}

function isAdminLoggedIn(): bool
{
    startSecureSession();

    return !empty($_SESSION['admin_user_id']);
}

function currentUserId()
{
    startSecureSession();

    return $_SESSION['admin_user_id'] ?? $_SESSION['user_id'] ?? null;
}

function currentUserName(): string
{
    startSecureSession();

    return (string) ($_SESSION['admin_name'] ?? $_SESSION['user_name'] ?? $_SESSION['fullName'] ?? 'Account');
}

function currentUserEmail(): string
{
    startSecureSession();

    return (string) ($_SESSION['admin_email'] ?? $_SESSION['email'] ?? '');
}

function requireLogin(): void
{
    startSecureSession();

    if (!isLoggedIn()) {
        header('Location: ' . appUrl('pages/login.php'));
        exit;
    }
}

function requireAdmin(): void
{
    startSecureSession();

    if (!isAdminLoggedIn()) {
        header('Location: ' . appUrl('pages/login.php'));
        exit;
    }
}

function dbColumnExists(mysqli $conn, string $table, string $column): bool
{
    $stmt = $conn->prepare('SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1');
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('ss', $table, $column);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result && $result->num_rows > 0;
    $stmt->close();

    return $exists;
}

function logError($message, $context = '') {
    $entry = '[' . date('Y-m-d H:i:s') . '] ' . $message;
    if (!empty($context)) {
        $entry .= ' | ' . $context;
    }
    error_log($entry . PHP_EOL, 3, __DIR__ . '/../logs/error.log');
}

?>
