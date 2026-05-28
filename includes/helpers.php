<?php

function detectAppBaseUrl(): string
{
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

function startSecureSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);

    session_set_cookie_params([
        'lifetime' => 60 * 60 * 24 * 30,
        'path' => APP_BASE_URL !== '' ? APP_BASE_URL : '/',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

function appUrl(string $path = ''): string
{
    $normalizedPath = ltrim($path, '/');
    $baseUrl = APP_BASE_URL !== '' ? rtrim(APP_BASE_URL, '/') : '';

    if ($normalizedPath === '') {
        return $baseUrl !== '' ? $baseUrl : '/';
    }

    return ($baseUrl !== '' ? $baseUrl : '') . '/' . $normalizedPath;
}

function escape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function sanitize($value)
{
    if (is_array($value)) {
        return array_map('sanitize', $value);
    }

    $cleanValue = trim((string) $value);
    return preg_replace('/[\x00-\x1F\x7F]/u', '', $cleanValue);
}

function validateEmail($email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function normalizeEmail(string $email): string
{
    return strtolower(trim($email));
}

function csrfToken(): string
{
    startSecureSession();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['csrf_token'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . escape(csrfToken()) . '">';
}

function verifyCsrf(?string $token): bool
{
    startSecureSession();

    return isset($_SESSION['csrf_token']) && is_string($token) && hash_equals((string) $_SESSION['csrf_token'], $token);
}

function setFlash(string $key, string $message): void
{
    startSecureSession();
    $_SESSION['flash_messages'][$key] = $message;
}

function getFlash(string $key, string $default = ''): string
{
    startSecureSession();

    $message = (string) ($_SESSION['flash_messages'][$key] ?? $default);
    unset($_SESSION['flash_messages'][$key]);

    return $message;
}

function hasFlash(string $key): bool
{
    startSecureSession();

    return isset($_SESSION['flash_messages'][$key]);
}

function isLoggedIn(): bool
{
    startSecureSession();

    return !empty($_SESSION['user_id']) || !empty($_SESSION['admin_id']);
}

function isAdminLoggedIn(): bool
{
    return isAdmin();
}

function currentUserId()
{
    startSecureSession();

    return $_SESSION['user_id'] ?? null;
}

function currentAdminId()
{
    startSecureSession();

    return $_SESSION['admin_id'] ?? null;
}

function currentUserRole(): string
{
    startSecureSession();

    $role = strtolower((string) ($_SESSION['user_role'] ?? $_SESSION['role'] ?? ''));

    if ($role !== '') {
        return $role;
    }

    $adminRole = currentAdminRole();
    if ($adminRole !== '') {
        return $adminRole;
    }

    return '';
}

function currentAdminRole(): string
{
    startSecureSession();

    return strtolower((string) ($_SESSION['admin_role'] ?? ''));
}

function isAdmin(): bool
{
    startSecureSession();

    return !empty($_SESSION['admin_id']);
}

function isDeveloper(): bool
{
    return currentUserRole() === 'developer';
}

function isCompany(): bool
{
    return currentUserRole() === 'company';
}

function roleLabel(?string $role = null): string
{
    $normalizedRole = strtolower(trim((string) ($role ?? currentUserRole())));

    return match ($normalizedRole) {
        'developer', 'user' => 'User',
        'company' => 'Company',
        'admin', 'super_admin', 'manager', 'reviewer' => 'Admin',
        default => 'User',
    };
}

function roleDashboardPath(?string $role = null): string
{
    $normalizedRole = strtolower(trim((string) ($role ?? currentUserRole())));

    if (isAdmin()) {
        return 'admin/dashboard.php';
    }

    return match ($normalizedRole) {
        'company' => 'company/dashboard.php',
        'developer' => 'pages/profile.php',
        default => 'pages/login.php',
    };
}

function redirectByRole(?string $role = null): void
{
    header('Location: ' . appUrl(roleDashboardPath($role)));
    exit;
}

function redirectWithFlash(string $path, string $flashKey, string $message): void
{
    setFlash($flashKey, $message);
    header('Location: ' . appUrl($path));
    exit;
}

function requestClientIp(): string
{
    $candidates = [
        $_SERVER['HTTP_CF_CONNECTING_IP'] ?? '',
        $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '',
        $_SERVER['HTTP_X_REAL_IP'] ?? '',
        $_SERVER['REMOTE_ADDR'] ?? '',
    ];

    foreach ($candidates as $candidate) {
        $candidate = trim((string) $candidate);
        if ($candidate === '') {
            continue;
        }

        if (str_contains($candidate, ',')) {
            $candidate = trim(explode(',', $candidate)[0]);
        }

        if (filter_var($candidate, FILTER_VALIDATE_IP)) {
            return $candidate;
        }
    }

    return '0.0.0.0';
}

function requestUserAgent(): string
{
    $userAgent = trim((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));
    return substr($userAgent, 0, 255);
}

function buildLoginThrottleKey(string $identifier = ''): string
{
    return hash('sha256', normalizeEmail($identifier) . '|' . requestClientIp());
}

function loginThrottleExceeded(mysqli $conn, string $throttleKey, int $maxAttempts = 5, int $windowSeconds = 900): bool
{
    $cutoff = date('Y-m-d H:i:s', time() - $windowSeconds);
    $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM login_attempts WHERE throttle_key = ? AND success = 0 AND created_at >= ?');

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('ss', $throttleKey, $cutoff);
    $stmt->execute();
    $total = (int) ($stmt->get_result()->fetch_assoc()['total'] ?? 0);
    $stmt->close();

    return $total >= $maxAttempts;
}

function recordLoginFailure(mysqli $conn, string $throttleKey, string $email = ''): void
{
    $ipAddress = requestClientIp();
    $userAgent = requestUserAgent();
    $stmt = $conn->prepare('INSERT INTO login_attempts (throttle_key, email, ip_address, user_agent, success, created_at) VALUES (?, ?, ?, ?, 0, NOW())');

    if (!$stmt) {
        logSecurityEvent('Unable to record login failure', ['throttle_key' => $throttleKey, 'email' => $email]);
        return;
    }

    $stmt->bind_param('ssss', $throttleKey, $email, $ipAddress, $userAgent);
    $stmt->execute();
    $stmt->close();

    logSecurityEvent('Login failure recorded', ['email' => $email, 'ip' => $ipAddress]);
}

function clearLoginFailures(mysqli $conn, string $throttleKey): void
{
    $stmt = $conn->prepare('DELETE FROM login_attempts WHERE throttle_key = ?');

    if (!$stmt) {
        return;
    }

    $stmt->bind_param('s', $throttleKey);
    $stmt->execute();
    $stmt->close();
}

function rememberMeCookieName(): string
{
    return 'devhire_remember_me';
}

function parseRememberMeCookie(?string $cookieValue): ?array
{
    $cookieValue = trim((string) $cookieValue);
    if ($cookieValue === '' || !str_contains($cookieValue, ':')) {
        return null;
    }

    [$selector, $validator] = explode(':', $cookieValue, 2);
    $selector = trim($selector);
    $validator = trim($validator);

    if ($selector === '' || $validator === '') {
        return null;
    }

    return [
        'selector' => $selector,
        'validator' => $validator,
    ];
}

function clearRememberMeCookie(): void
{
    setcookie(rememberMeCookieName(), '', [
        'expires' => time() - 3600,
        'path' => APP_BASE_URL !== '' ? APP_BASE_URL : '/',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function issueRememberMeToken(mysqli $conn, int $userId): void
{
    $selector = bin2hex(random_bytes(12));
    $validator = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $validator);
    $expiresAt = date('Y-m-d H:i:s', time() + 60 * 60 * 24 * 30);
    $ipAddress = requestClientIp();
    $userAgent = requestUserAgent();

    $stmt = $conn->prepare('INSERT INTO remember_me_tokens (user_id, selector, token_hash, ip_address, user_agent, expires_at, created_at, last_used_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())');

    if (!$stmt) {
        logError('Unable to create remember-me token', ['user_id' => $userId]);
        return;
    }

    $stmt->bind_param('isssss', $userId, $selector, $tokenHash, $ipAddress, $userAgent, $expiresAt);
    $stmt->execute();
    $stmt->close();

    setcookie(rememberMeCookieName(), $selector . ':' . $validator, [
        'expires' => strtotime($expiresAt),
        'path' => APP_BASE_URL !== '' ? APP_BASE_URL : '/',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function revokeRememberMeToken(mysqli $conn, ?string $cookieValue = null): void
{
    $cookie = parseRememberMeCookie($cookieValue ?? ($_COOKIE[rememberMeCookieName()] ?? null));
    if ($cookie === null) {
        clearRememberMeCookie();
        return;
    }

    $stmt = $conn->prepare('DELETE FROM remember_me_tokens WHERE selector = ?');
    if ($stmt) {
        $stmt->bind_param('s', $cookie['selector']);
        $stmt->execute();
        $stmt->close();
    }

    clearRememberMeCookie();
}

function loadUserById(mysqli $conn, int $userId): ?array
{
    $stmt = $conn->prepare('SELECT id, fullName, email, password, role, phone, experience, techStack, portfolio_url, profile_image, provider, firebase_uid, verified, company_name, company_description, bio, created_at, updated_at FROM users WHERE id = ? LIMIT 1');

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();

    return $user;
}

function authenticateFromRememberMe(mysqli $conn): void
{
    startSecureSession();

    if (isLoggedIn()) {
        return;
    }

    $cookie = parseRememberMeCookie($_COOKIE[rememberMeCookieName()] ?? null);
    if ($cookie === null) {
        return;
    }

    $stmt = $conn->prepare('SELECT id, user_id, selector, token_hash, expires_at, ip_address, user_agent FROM remember_me_tokens WHERE selector = ? LIMIT 1');
    if (!$stmt) {
        clearRememberMeCookie();
        return;
    }

    $stmt->bind_param('s', $cookie['selector']);
    $stmt->execute();
    $tokenRecord = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();

    if ($tokenRecord === null || strtotime((string) ($tokenRecord['expires_at'] ?? '')) < time()) {
        revokeRememberMeToken($conn, $cookie['selector'] . ':' . $cookie['validator']);
        return;
    }

    if (!hash_equals((string) ($tokenRecord['token_hash'] ?? ''), hash('sha256', $cookie['validator']))) {
        revokeRememberMeToken($conn, $cookie['selector'] . ':' . $cookie['validator']);
        return;
    }

    $user = loadUserById($conn, (int) $tokenRecord['user_id']);
    if ($user === null) {
        revokeRememberMeToken($conn, $cookie['selector'] . ':' . $cookie['validator']);
        return;
    }

    completeSessionLogin($user, false, (string) ($user['provider'] ?? 'password'), (string) ($user['profile_image'] ?? ''), (string) ($user['firebase_uid'] ?? ''));

    $rotate = $conn->prepare('DELETE FROM remember_me_tokens WHERE selector = ?');
    if ($rotate) {
        $rotate->bind_param('s', $cookie['selector']);
        $rotate->execute();
        $rotate->close();
    }

    issueRememberMeToken($conn, (int) $user['id']);
}

// Provide a shared implementation of completing a session login so other pages
// (including non-auth handlers) can authenticate users without depending on
// `auth/login_handler.php`.
if (!function_exists('completeSessionLogin')) {
    function completeSessionLogin(array $user, bool $isAdmin, string $provider, string $photo = '', string $firebaseUid = ''): void
    {
        startSecureSession();

        if (function_exists('session_regenerate_id')) {
            @session_regenerate_id(true);
        }

        $userId = (int) ($user['id'] ?? 0);
        $fullName = (string) ($user['fullName'] ?? ($user['name'] ?? 'Account'));
        $email = (string) ($user['email'] ?? '');
        $role = strtolower((string) ($user['role'] ?? 'developer'));

        if ($isAdmin) {
            $_SESSION['admin_id'] = $userId;
            $_SESSION['admin_name'] = $fullName;
            $_SESSION['admin_email'] = $email;
            $_SESSION['admin_role'] = in_array($role, ['super_admin', 'manager', 'reviewer'], true) ? $role : 'reviewer';
            $_SESSION['admin_provider'] = $provider;
            $_SESSION['admin_photo'] = $photo !== '' ? $photo : (string) ($user['profile_image'] ?? '');
            $_SESSION['admin_firebase_uid'] = $firebaseUid !== '' ? $firebaseUid : (string) ($user['firebase_uid'] ?? '');

            unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email'], $_SESSION['user_role'], $_SESSION['email'], $_SESSION['role'], $_SESSION['fullName'], $_SESSION['user_photo']);
        } else {
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_name'] = $fullName;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = $role !== '' ? $role : 'developer';
            $_SESSION['user_provider'] = $provider;
            $_SESSION['user_photo'] = $photo !== '' ? $photo : (string) ($user['profile_image'] ?? '');
            $_SESSION['user_firebase_uid'] = $firebaseUid !== '' ? $firebaseUid : (string) ($user['firebase_uid'] ?? '');
            $_SESSION['email'] = $email;
            $_SESSION['role'] = $_SESSION['user_role'];
            $_SESSION['fullName'] = $fullName;

            unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_email'], $_SESSION['admin_role'], $_SESSION['admin_provider'], $_SESSION['admin_photo'], $_SESSION['admin_firebase_uid']);
        }

        unset($_SESSION['error']);
    }
}

function logoutCurrentUser(?mysqli $conn = null): void
{
    if ($conn instanceof mysqli) {
        revokeRememberMeToken($conn);
    } else {
        clearRememberMeCookie();
    }

    destroyAuthSession();
}

function renderLogoutForm(string $label = 'Logout', string $buttonClass = 'btn-login footer-auth-link'): string
{
    $action = htmlspecialchars(appUrl('auth/logout.php'), ENT_QUOTES, 'UTF-8');
    $buttonClass = htmlspecialchars($buttonClass, ENT_QUOTES, 'UTF-8');

    return '<form method="post" action="' . $action . '" class="logout-form" style="display:inline;">'
        . csrfField()
        . '<button type="submit" class="' . $buttonClass . '">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</button>'
        . '</form>';
}

function ensureLogPath(): string
{
    $logDirectory = __DIR__ . '/../logs';
    $logFile = $logDirectory . '/error.log';

    if (!is_dir($logDirectory) && !@mkdir($logDirectory, 0755, true) && !is_dir($logDirectory)) {
        return '';
    }

    if (!file_exists($logFile) && @touch($logFile) === false) {
        return '';
    }

    @chmod($logDirectory, 0755);
    @chmod($logFile, 0644);

    return is_writable($logFile) ? $logFile : '';
}

function normalizeLogContext($context): string
{
    if (is_array($context) || is_object($context)) {
        $encoded = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $encoded !== false ? $encoded : 'context_unavailable';
    }

    return trim((string) $context);
}

function writeLog(string $channel, string $message, $context = null): bool
{
    $logFile = ensureLogPath();
    $entry = '[' . date('Y-m-d H:i:s') . '] [' . strtoupper($channel) . '] ' . trim($message);

    $normalizedContext = normalizeLogContext($context);
    if ($normalizedContext !== '') {
        $entry .= ' | ' . $normalizedContext;
    }

    $entry .= PHP_EOL;

    if ($logFile === '') {
        error_log($entry);
        return false;
    }

    $written = @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
    if ($written === false) {
        error_log($entry);
        return false;
    }

    return true;
}

function requireRole($roles): void
{
    startSecureSession();

    $allowedRoles = array_map('strtolower', is_array($roles) ? $roles : [$roles]);

    if (!in_array(currentUserRole(), $allowedRoles, true)) {
        redirectByRole();
    }
}

function currentUserName(): string
{
    startSecureSession();

    return (string) ($_SESSION['admin_name'] ?? $_SESSION['user_name'] ?? $_SESSION['fullName'] ?? 'Account');
}

function currentUserEmail(): string
{
    startSecureSession();

    return (string) ($_SESSION['admin_email'] ?? $_SESSION['user_email'] ?? $_SESSION['email'] ?? '');
}

function currentUserPhoto(): string
{
    startSecureSession();

    return (string) ($_SESSION['admin_photo'] ?? $_SESSION['user_photo'] ?? '');
}

function requireUser(): void
{
    startSecureSession();

    if (empty($_SESSION['user_id'])) {
        header('Location: ' . appUrl('pages/login.php'));
        exit;
    }
}

function requireLogin(): void
{
    requireUser();
}

function requireAdmin(): void
{
    startSecureSession();

    if (empty($_SESSION['admin_id'])) {
        setFlash('error', 'Please sign in using an admin account.');
        header('Location: ' . appUrl('admin/login.php'));
        exit;
    }
}

function requireDeveloper(): void
{
    startSecureSession();

    if (empty($_SESSION['user_id']) || currentUserRole() !== 'developer') {
        redirectByRole();
    }
}

function requireCompany(): void
{
    startSecureSession();

    if (empty($_SESSION['user_id']) || currentUserRole() !== 'company') {
        redirectByRole();
    }
}

function currentCompanyId(): int
{
    return (int) currentUserId();
}

function destroyAuthSession(): void
{
    startSecureSession();

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
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

function logError($message, $context = ''): void
{
    writeLog('ERROR', (string) $message, $context);
}

function logSecurityEvent($message, $context = ''): void
{
    writeLog('SECURITY', (string) $message, $context);
}

?>
