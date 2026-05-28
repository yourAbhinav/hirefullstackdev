<?php

require_once '../config/db.php';

startSecureSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . appUrl('pages/login.php'));
    exit;
}

$requestBody = json_decode((string) file_get_contents('php://input'), true);
$payload = is_array($requestBody) ? $requestBody : $_POST;
$isJsonRequest = is_array($requestBody) || str_contains((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json');

// Build throttle key primarily per-email (if provided) falling back to client IP
$submittedEmail = normalizeEmail((string) ($payload['email'] ?? ''));
$loginThrottleKey = buildLoginThrottleKey($submittedEmail !== '' ? $submittedEmail : requestClientIp());

if (loginThrottleExceeded($conn, $loginThrottleKey)) {
    loginResponse(['success' => false, 'message' => 'Too many login attempts. Please wait a few minutes and try again.'], $isJsonRequest, 429);
}

function loginResponse(array $payload, bool $isJsonRequest, int $statusCode = 200, string $fallbackRedirect = 'pages/login.php'): void
{
    http_response_code($statusCode);

    if ($isJsonRequest) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    if ($statusCode >= 400) {
        $_SESSION['error'] = $payload['message'] ?? 'Login failed.';
    }

    header('Location: ' . appUrl($fallbackRedirect));
    exit;
}

function base64UrlDecode(string $value): string|false
{
    $remainder = strlen($value) % 4;
    if ($remainder > 0) {
        $value .= str_repeat('=', 4 - $remainder);
    }

    return base64_decode(strtr($value, '-_', '+/'), true);
}

function fetchRemoteContents(string $url): string|false
{
    $context = stream_context_create([
        'http' => ['timeout' => 5],
        'https' => ['timeout' => 5],
    ]);

    $contents = @file_get_contents($url, false, $context);
    if ($contents !== false) {
        return $contents;
    }

    if (!function_exists('curl_init')) {
        return false;
    }

    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 5,
    ]);

    $response = curl_exec($curl);
    curl_close($curl);

    return is_string($response) ? $response : false;
}

function verifyFirebaseIdToken(string $idToken): array|false
{
    $projectId = getenv('FIREBASE_PROJECT_ID') ?: 'abhhire-e8807';
    $parts = explode('.', $idToken);

    if (count($parts) !== 3) {
        return false;
    }

    [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;
    $headerJson = base64UrlDecode($encodedHeader);
    $payloadJson = base64UrlDecode($encodedPayload);
    $signature = base64UrlDecode($encodedSignature);

    if ($headerJson === false || $payloadJson === false || $signature === false) {
        return false;
    }

    $header = json_decode($headerJson, true);
    $payload = json_decode($payloadJson, true);

    if (!is_array($header) || !is_array($payload)) {
        return false;
    }

    if (($header['alg'] ?? '') !== 'RS256' || empty($header['kid'])) {
        return false;
    }

    if (($payload['iss'] ?? '') !== 'https://securetoken.google.com/' . $projectId) {
        return false;
    }

    if (($payload['aud'] ?? '') !== $projectId) {
        return false;
    }

    $now = time();
    if (($payload['exp'] ?? 0) < $now || ($payload['iat'] ?? 0) > ($now + 60)) {
        return false;
    }

    if (empty($payload['sub']) || empty($payload['email'])) {
        return false;
    }

    $certResponse = fetchRemoteContents('https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com');
    if ($certResponse === false) {
        return false;
    }

    $certs = json_decode($certResponse, true);
    if (!is_array($certs) || empty($certs[$header['kid']])) {
        return false;
    }

    $publicKey = openssl_pkey_get_public($certs[$header['kid']]);
    if ($publicKey === false) {
        return false;
    }

    $verified = openssl_verify($encodedHeader . '.' . $encodedPayload, $signature, $publicKey, OPENSSL_ALGO_SHA256);
    if ($verified !== 1) {
        return false;
    }

    if (empty($payload['email_verified'])) {
        return false;
    }

    return $payload;
}


function findUserByFirebaseUid(mysqli $conn, string $firebaseUid): ?array
{
    $stmt = $conn->prepare('SELECT id, fullName, email, password, role, phone, experience, techStack, portfolio_url, profile_image, provider, firebase_uid, verified, company_name, company_description, bio, created_at, updated_at FROM users WHERE firebase_uid = ? LIMIT 1');
    $stmt->bind_param('s', $firebaseUid);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();

    return $user;
}

function findUserByEmail(mysqli $conn, string $email): ?array
{
    $stmt = $conn->prepare('SELECT id, fullName, email, password, role, phone, experience, techStack, portfolio_url, profile_image, provider, firebase_uid, verified, company_name, company_description, bio, created_at, updated_at FROM users WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();

    return $user;
}

function findAdminLink(mysqli $conn, ?int $userId, string $firebaseUid, string $email): ?array
{
    $stmt = $conn->prepare('SELECT id, name, email, password, role, status, last_login_at FROM admin_accounts WHERE email = ? LIMIT 1');

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $record = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();

    return $record;
}

function upsertFirebaseUser(mysqli $conn, string $firebaseUid, string $email, string $name, string $photo, string $provider, string $requestedRole): array
{
    $user = findUserByFirebaseUid($conn, $firebaseUid) ?? findUserByEmail($conn, $email);
    $fullName = $name !== '' ? $name : explode('@', $email)[0];
    $allowedRoles = ['developer', 'company'];
    $normalizedRole = in_array($requestedRole, $allowedRoles, true) ? $requestedRole : 'developer';

    if ($user === null) {
        $password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
        $insert = $conn->prepare('INSERT INTO users (fullName, email, password, firebase_uid, provider, profile_image, role, verified, last_login_at) VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())');
        $insert->bind_param('sssssss', $fullName, $email, $password, $firebaseUid, $provider, $photo, $normalizedRole);
        $insert->execute();
        $insert->close();

        return findUserByFirebaseUid($conn, $firebaseUid) ?? findUserByEmail($conn, $email) ?? [];
    }

    $currentRole = strtolower((string) ($user['role'] ?? ''));
    if (!in_array($currentRole, $allowedRoles, true)) {
        $currentRole = $normalizedRole;
    }

    $userId = (int) $user['id'];
    $update = $conn->prepare('UPDATE users SET fullName = COALESCE(NULLIF(?, \'\'), fullName), email = ?, firebase_uid = COALESCE(NULLIF(?, \'\'), firebase_uid), provider = COALESCE(NULLIF(?, \'\'), provider), profile_image = COALESCE(NULLIF(?, \'\'), profile_image), role = ?, verified = 1, last_login_at = NOW(), updated_at = NOW() WHERE id = ?');
    $update->bind_param('ssssssi', $fullName, $email, $firebaseUid, $provider, $photo, $currentRole, $userId);
    $update->execute();
    $update->close();

    return findUserByFirebaseUid($conn, $firebaseUid) ?? findUserByEmail($conn, $email) ?? $user;
}

function upsertAdminLink(mysqli $conn, int $userId, string $firebaseUid, string $name, string $email, string $photo, string $provider): void
{
    return;
}

$csrfToken = $payload['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
if (!verifyCsrf(is_string($csrfToken) ? $csrfToken : null)) {
    loginResponse(['success' => false, 'message' => 'Your session expired. Please reload and try again.'], $isJsonRequest, 403);
}

$mode = strtolower(trim((string) ($payload['mode'] ?? 'password')));

// Special mode: set error message in session (used by JavaScript error handlers)
if ($mode === 'set_error') {
    // Keep server-controlled copy to avoid arbitrary client-provided session content.
    $_SESSION['error'] = 'Sign-in failed. Please try again.';
    http_response_code(200);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'message' => 'Error logged.'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$requestedRole = strtolower(trim((string) ($payload['account_type'] ?? $payload['role'] ?? 'developer')));
$provider = trim((string) ($payload['provider'] ?? 'google'));
$rememberMe = !empty($payload['remember_me']);

if (in_array($mode, ['firebase', 'google', 'admin'], true) || !empty($payload['idToken'])) {
    $idToken = trim((string) ($payload['idToken'] ?? ''));

    if ($idToken === '') {
        recordLoginFailure($conn, $loginThrottleKey);
        loginResponse(['success' => false, 'message' => 'Missing identity token.'], $isJsonRequest, 422);
    }

    $claims = verifyFirebaseIdToken($idToken);
    if ($claims === false) {
        recordLoginFailure($conn, $loginThrottleKey);
        loginResponse(['success' => false, 'message' => 'Unable to verify your Google sign-in.'], $isJsonRequest, 401);
    }

    $firebaseUid = normalizeEmail((string) ($claims['sub'] ?? ''));
    $email = normalizeEmail((string) ($claims['email'] ?? ''));
    $name = trim((string) ($claims['name'] ?? ''));
    $photo = trim((string) ($claims['picture'] ?? ''));
    $provider = trim((string) ($claims['firebase']['sign_in_provider'] ?? $provider));

    if ($firebaseUid === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        recordLoginFailure($conn, $loginThrottleKey, $email);
        loginResponse(['success' => false, 'message' => 'Google account verification failed.'], $isJsonRequest, 401);
    }

    if ($mode === 'admin') {
        $adminAccount = findAdminLink($conn, null, $firebaseUid, $email);

        if ($adminAccount === null || strtolower((string) ($adminAccount['status'] ?? 'inactive')) !== 'active') {
            recordLoginFailure($conn, $loginThrottleKey, $email);
            loginResponse(['success' => false, 'message' => 'This account is not approved for admin access.'], $isJsonRequest, 403);
        }

        completeSessionLogin($adminAccount, true, $provider, $photo, $firebaseUid);
        clearLoginFailures($conn, $loginThrottleKey);

        $adminUpdate = $conn->prepare('UPDATE admin_accounts SET last_login_at = NOW() WHERE email = ?');
        if ($adminUpdate) {
            $adminUpdate->bind_param('s', $email);
            $adminUpdate->execute();
            $adminUpdate->close();
        }

        loginResponse([
            'success' => true,
            'message' => 'Admin login successful.',
            'redirect' => appUrl('admin/dashboard.php'),
            'user' => [
                'id' => (int) $adminAccount['id'],
                'name' => $adminAccount['name'] ?? $name,
                'email' => $adminAccount['email'] ?? $email,
                'photo' => $photo,
                'role' => $adminAccount['role'] ?? 'reviewer',
            ],
        ], $isJsonRequest);
    }

    $existingUser = findUserByFirebaseUid($conn, $firebaseUid) ?? findUserByEmail($conn, $email);

    if ($existingUser === null) {
        $existingUser = upsertFirebaseUser($conn, $firebaseUid, $email, $name, $photo, $provider, $requestedRole);
    }

    if ($existingUser === []) {
        recordLoginFailure($conn, $loginThrottleKey, $email);
        loginResponse(['success' => false, 'message' => 'Unable to create your account.'], $isJsonRequest, 500);
    }

    completeSessionLogin($existingUser, false, $provider, $photo, $firebaseUid);

    if ($rememberMe) {
        revokeRememberMeToken($conn);
        issueRememberMeToken($conn, (int) $existingUser['id']);
    } else {
        revokeRememberMeToken($conn);
    }

    clearLoginFailures($conn, $loginThrottleKey);

    $loginUpdate = $conn->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?');
    $userId = (int) $existingUser['id'];
    $loginUpdate->bind_param('i', $userId);
    $loginUpdate->execute();
    $loginUpdate->close();

    loginResponse([
        'success' => true,
        'message' => 'Login successful.',
        'redirect' => appUrl(roleDashboardPath($existingUser['role'] ?? 'developer')),
        'user' => [
            'id' => (int) $existingUser['id'],
            'name' => $existingUser['fullName'] ?? $name,
            'email' => $existingUser['email'] ?? $email,
            'photo' => $photo,
            'role' => $existingUser['role'] ?? 'developer',
        ],
    ], $isJsonRequest);
}

// Admin password login (email/password authentication for admin_accounts table)
if ($mode === 'admin_password') {
    $email = normalizeEmail((string) ($payload['email'] ?? ''));
    $password = (string) ($payload['password'] ?? '');

    if ($email === '' || $password === '') {
        recordLoginFailure($conn, $loginThrottleKey, $email);
        loginResponse(['success' => false, 'message' => 'Email and password are required.'], $isJsonRequest, 422);
    }

    $stmt = $conn->prepare('SELECT id, name, email, password, role, status FROM admin_accounts WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $adminAccount = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();

    if ($adminAccount === null || !password_verify($password, (string) $adminAccount['password'])) {
        recordLoginFailure($conn, $loginThrottleKey, $email);
        loginResponse(['success' => false, 'message' => 'Invalid admin email or password.'], $isJsonRequest, 401);
    }

    if (strtolower((string) ($adminAccount['status'] ?? 'inactive')) !== 'active') {
        recordLoginFailure($conn, $loginThrottleKey, $email);
        loginResponse(['success' => false, 'message' => 'This admin account is not active.'], $isJsonRequest, 403);
    }

    completeSessionLogin($adminAccount, true, 'password', '', '');
    clearLoginFailures($conn, $loginThrottleKey);

    $adminUpdate = $conn->prepare('UPDATE admin_accounts SET last_login_at = NOW() WHERE id = ?');
    if ($adminUpdate) {
        $adminUpdate->bind_param('i', $adminAccount['id']);
        $adminUpdate->execute();
        $adminUpdate->close();
    }

    loginResponse([
        'success' => true,
        'message' => 'Admin login successful.',
        'redirect' => appUrl('admin/dashboard.php'),
        'user' => [
            'id' => (int) $adminAccount['id'],
            'name' => $adminAccount['name'],
            'email' => $adminAccount['email'],
            'role' => $adminAccount['role'] ?? 'reviewer',
        ],
    ], $isJsonRequest);
}

$email = normalizeEmail((string) ($payload['email'] ?? ''));
$password = (string) ($payload['password'] ?? '');

if ($email === '' || $password === '') {
    recordLoginFailure($conn, $loginThrottleKey, $email);
    loginResponse(['success' => false, 'message' => 'Email and password are required.'], $isJsonRequest, 422);
}

$stmt = $conn->prepare('SELECT id, fullName, email, password, role, profile_image, provider, firebase_uid FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc() ?: null;
$stmt->close();

if ($user === null || !password_verify($password, (string) $user['password'])) {
    recordLoginFailure($conn, $loginThrottleKey, $email);
    loginResponse(['success' => false, 'message' => 'Invalid email or password.'], $isJsonRequest, 401);
}

completeSessionLogin($user, false, (string) ($user['provider'] ?? 'password'), (string) ($user['profile_image'] ?? ''), (string) ($user['firebase_uid'] ?? ''));

clearLoginFailures($conn, $loginThrottleKey);

if ($rememberMe) {
    revokeRememberMeToken($conn);
    issueRememberMeToken($conn, (int) $user['id']);
} else {
    revokeRememberMeToken($conn);
}

$loginUpdate = $conn->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?');
$userId = (int) $user['id'];
$loginUpdate->bind_param('i', $userId);
$loginUpdate->execute();
$loginUpdate->close();

loginResponse([
    'success' => true,
    'message' => 'Login successful.',
    'redirect' => appUrl(roleDashboardPath($user['role'] ?? 'developer')),
    'user' => [
        'id' => (int) $user['id'],
        'name' => $user['fullName'] ?? '',
        'email' => $user['email'] ?? $email,
        'photo' => $user['profile_image'] ?? '',
        'role' => $user['role'] ?? 'developer',
    ],
], $isJsonRequest);