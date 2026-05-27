<?php

require_once '../config/db.php';

$requestBody = json_decode(file_get_contents('php://input'), true);
$payload = is_array($requestBody) ? $requestBody : $_POST;
$isJsonRequest = is_array($requestBody) || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));

function respondLogin(array $payload, bool $isJsonRequest): void
{
    if ($isJsonRequest) {
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }

    header('Location: ' . appUrl('admin/dashboard.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . appUrl('pages/login.php'));
    exit;
}

if (!empty($payload['uid']) || ($payload['mode'] ?? '') === 'firebase') {
    $firebaseUid = sanitize($payload['uid'] ?? '');
    $name = sanitize($payload['name'] ?? '');
    $email = sanitize($payload['email'] ?? '');
    $photo = sanitize($payload['photo'] ?? '');
    $provider = sanitize($payload['provider'] ?? 'google');

    if (empty($firebaseUid) || empty($email)) {
        $_SESSION['error'] = 'Google sign-in failed. Please try again.';
        respondLogin(['success' => false, 'message' => $_SESSION['error']], $isJsonRequest);
    }

    $select = $conn->prepare('SELECT id FROM admin_users WHERE firebase_uid = ? OR email = ? LIMIT 1');
    $select->bind_param('ss', $firebaseUid, $email);
    $select->execute();
    $existing = $select->get_result()->fetch_assoc();
    $select->close();

    if ($existing) {
        $adminId = (int) $existing['id'];
        $update = $conn->prepare('UPDATE admin_users SET firebase_uid = ?, name = ?, email = ?, photo = ?, provider = ? WHERE id = ?');
        $update->bind_param('sssssi', $firebaseUid, $name, $email, $photo, $provider, $adminId);
        $update->execute();
        $update->close();
    } else {
        $insert = $conn->prepare('INSERT INTO admin_users (firebase_uid, name, email, photo, provider, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $insert->bind_param('sssss', $firebaseUid, $name, $email, $photo, $provider);
        $insert->execute();
        $adminId = (int) $insert->insert_id;
        $insert->close();
    }

    $shadowPassword = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
    $shadowUser = $conn->prepare(
        'INSERT INTO users (id, fullName, email, password, role, verified) VALUES (?, ?, ?, ?, "admin", 1) '
        . 'ON DUPLICATE KEY UPDATE fullName = VALUES(fullName), email = VALUES(email), role = VALUES(role)'
    );
    $shadowUser->bind_param('isss', $adminId, $name, $email, $shadowPassword);
    $shadowUser->execute();
    $shadowUser->close();

    session_regenerate_id(true);
    $_SESSION['admin_user_id'] = $adminId;
    $_SESSION['admin_name'] = $name;
    $_SESSION['admin_email'] = $email;
    $_SESSION['admin_photo'] = $photo;
    $_SESSION['auth_provider'] = $provider;
    $_SESSION['admin'] = $email;
    $_SESSION['user_id'] = $adminId;
    $_SESSION['user_name'] = $name;
    $_SESSION['email'] = $email;
    $_SESSION['role'] = 'admin';
    $_SESSION['fullName'] = $name;
    unset($_SESSION['error']);

    respondLogin([
        'success' => true,
        'message' => 'Login successful.',
        'redirect' => appUrl('admin/dashboard.php'),
        'user' => [
            'id' => $adminId,
            'name' => $name,
            'email' => $email,
            'photo' => $photo,
        ],
    ], $isJsonRequest);
}

$email = sanitize($payload['email'] ?? '');
$password = (string) ($payload['password'] ?? '');

if (empty($email) || empty($password)) {
    $_SESSION['error'] = 'Email and password are required.';
    header('Location: ' . appUrl('pages/login.php'));
    exit;
}

$stmt = $conn->prepare('SELECT id, email, password, role, fullName FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['fullName'] = $user['fullName'];

        if ($user['role'] === 'admin') {
            $_SESSION['admin_user_id'] = (int) $user['id'];
            $_SESSION['admin_name'] = $user['fullName'];
            $_SESSION['admin_email'] = $user['email'];
        }

        unset($_SESSION['error']);
        header('Location: ' . appUrl($user['role'] === 'admin' ? 'admin/dashboard.php' : 'index.php'));
        exit;
    }

    $_SESSION['error'] = 'Invalid password.';
} else {
    $_SESSION['error'] = 'User not found.';
}

header('Location: ' . appUrl('pages/login.php'));
exit;
?>
