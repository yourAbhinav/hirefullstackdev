<?php

require_once '../config/db.php';
require_once '../includes/admin_helpers.php';

startSecureSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . appUrl('admin/login.php'));
    exit;
}

$requestBody = json_decode((string) file_get_contents('php://input'), true);
$payload = is_array($requestBody) ? $requestBody : $_POST;
$isJsonRequest = is_array($requestBody) || str_contains((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json');

function adminGoogleLoginResponse(array $payload, bool $isJsonRequest, int $statusCode = 200, string $fallbackRedirect = 'admin/login.php'): void
{
    http_response_code($statusCode);

    if ($isJsonRequest) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    if ($statusCode >= 400) {
        $_SESSION['google_error'] = $payload['message'] ?? 'Google login failed.';
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

function firebaseCertCachePath(): string
{
    return __DIR__ . '/../logs/firebase_certs_cache.json';
}

function getFirebasePublicCerts(): array|false
{
    $cacheFile = firebaseCertCachePath();
    $cacheTtlSeconds = 3600; // 1 hour cache

    if (is_file($cacheFile)) {
        $age = time() - (int) @filemtime($cacheFile);
        if ($age >= 0 && $age < $cacheTtlSeconds) {
            $cached = @file_get_contents($cacheFile);
            if (is_string($cached) && $cached !== '') {
                $decoded = json_decode($cached, true);
                if (is_array($decoded) && !empty($decoded)) {
                    return $decoded;
                }
            }
        }
    }

    $certResponse = fetchRemoteContents('https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com');
    if ($certResponse === false) {
        if (is_file($cacheFile)) {
            $stale = @file_get_contents($cacheFile);
            if (is_string($stale) && $stale !== '') {
                $decoded = json_decode($stale, true);
                if (is_array($decoded) && !empty($decoded)) {
                    return $decoded;
                }
            }
        }
        return false;
    }

    $certs = json_decode($certResponse, true);
    if (!is_array($certs) || empty($certs)) {
        return false;
    }

    @file_put_contents($cacheFile, json_encode($certs, JSON_UNESCAPED_SLASHES), LOCK_EX);
    return $certs;
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

    $certs = getFirebasePublicCerts();
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

function findAdminByFirebaseUid(mysqli $conn, string $firebaseUid): ?array
{
    $stmt = $conn->prepare('SELECT id, name, email, role, status, profile_image FROM admin_accounts WHERE firebase_uid = ? LIMIT 1');
    $stmt->bind_param('s', $firebaseUid);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();

    return $admin;
}

function findAdminByEmail(mysqli $conn, string $email): ?array
{
    $stmt = $conn->prepare('SELECT id, name, email, role, status, profile_image, firebase_uid FROM admin_accounts WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();

    return $admin;
}

function linkGoogleToAdmin(mysqli $conn, int $adminId, string $firebaseUid, string $profileImage): bool
{
    $stmt = $conn->prepare('UPDATE admin_accounts SET firebase_uid = ?, profile_image = ? WHERE id = ?');
    $stmt->bind_param('ssi', $firebaseUid, $profileImage, $adminId);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

function adminFirebaseLogin(mysqli $conn, string $firebaseUid, string $email, string $name, string $photo): array
{
    $admin = findAdminByFirebaseUid($conn, $firebaseUid);

    // If admin found by Firebase UID, log them in
    if ($admin !== null) {
        if ($admin['status'] !== 'active') {
            return ['success' => false, 'message' => 'Account is not active'];
        }

        // Update last login
        $stmt = $conn->prepare('UPDATE admin_accounts SET last_login_at = NOW(), last_login_ip = ? WHERE id = ?');
        $ip = requestClientIp();
        $stmt->bind_param('si', $ip, $admin['id']);
        $stmt->execute();
        $stmt->close();

        // Set admin session
        startSecureSession();
        session_regenerate_id(true);
        
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['admin_photo'] = $admin['profile_image'];
        $_SESSION['admin_firebase_uid'] = $firebaseUid;

        // Clear user session if exists
        unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email'], $_SESSION['user_role']);

        logAdminAction($conn, $admin['id'], 'google_login', 'admin_account', $admin['id']);

        return ['success' => true, 'message' => 'Login successful'];
    }

    // Try to find admin by email and link Google account
    $admin = findAdminByEmail($conn, $email);
    if ($admin !== null) {
        if ($admin['status'] !== 'active') {
            return ['success' => false, 'message' => 'Account is not active'];
        }

        // Link Google account to existing admin
        if (empty($admin['firebase_uid'])) {
            linkGoogleToAdmin($conn, $admin['id'], $firebaseUid, $photo);
        }

        // Update last login
        $stmt = $conn->prepare('UPDATE admin_accounts SET last_login_at = NOW(), last_login_ip = ? WHERE id = ?');
        $ip = requestClientIp();
        $stmt->bind_param('si', $ip, $admin['id']);
        $stmt->execute();
        $stmt->close();

        // Set admin session
        startSecureSession();
        session_regenerate_id(true);
        
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['admin_photo'] = $photo ?: $admin['profile_image'];
        $_SESSION['admin_firebase_uid'] = $firebaseUid;

        // Clear user session if exists
        unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email'], $_SESSION['user_role']);

        logAdminAction($conn, $admin['id'], 'google_login_linked', 'admin_account', $admin['id']);

        return ['success' => true, 'message' => 'Google account linked successfully'];
    }

    // No admin account found
    return ['success' => false, 'message' => 'No admin account found with this email. Please contact your administrator.'];
}

// Handle Google login request
$idToken = $payload['idToken'] ?? '';

if (empty($idToken)) {
    adminGoogleLoginResponse(['success' => false, 'message' => 'Missing ID token'], $isJsonRequest, 400);
}

// Verify Firebase ID token
$firebaseUser = verifyFirebaseIdToken($idToken);
if ($firebaseUser === false) {
    adminGoogleLoginResponse(['success' => false, 'message' => 'Invalid ID token'], $isJsonRequest, 401);
}

// Perform admin Firebase login
$loginResult = adminFirebaseLogin(
    $conn,
    $firebaseUser['sub'],
    $firebaseUser['email'],
    $firebaseUser['name'] ?? '',
    $firebaseUser['picture'] ?? ''
);

if ($loginResult['success']) {
    adminGoogleLoginResponse(['success' => true, 'message' => $loginResult['message']], $isJsonRequest);
} else {
    adminGoogleLoginResponse(['success' => false, 'message' => $loginResult['message']], $isJsonRequest, 401);
}
?>