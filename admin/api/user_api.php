<?php
require_once '../../config/db.php';
require_once '../../includes/admin_helpers.php';
require_once '../../includes/helpers.php';

header('Content-Type: application/json');

requireAdminLogin();
requireAdminPermission($conn, 'view_users');

function getAdminJsonInput(): array
{
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (str_contains($contentType, 'application/json')) {
        $decoded = json_decode(file_get_contents('php://input') ?: '', true);
        return is_array($decoded) ? $decoded : [];
    }
    return $_POST;
}

function requireApiCsrfFromInput(array $input): void
{
    $token = $input['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
    if (!verifyCsrf(is_string($token) ? $token : null)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'CSRF validation failed']);
        exit;
    }
}

$admin = getCurrentAdmin($conn);
if ($admin === null) {
    echo json_encode(['success' => false, 'message' => 'Admin not found']);
    exit;
}

$jsonInput = getAdminJsonInput();
$action = $_GET['action'] ?? ($jsonInput['action'] ?? ($_POST['action'] ?? ''));

switch ($action) {
    case 'get_user':
        $userId = (int) ($_GET['id'] ?? 0);

        if ($userId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
            exit;
        }

        $stmt = $conn->prepare('SELECT id, fullName, email, role, phone, experience, techStack, verified, provider, firebase_uid, profile_image, last_login_at, created_at, updated_at FROM users WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user) {
            echo json_encode(['success' => true, 'user' => $user]);
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found']);
        }
        break;

    case 'create':
        requireAdminPermission($conn, 'edit_users');
        requireApiCsrfFromInput($jsonInput);

        $fullName = trim($jsonInput['fullName'] ?? '');
        $email = normalizeEmail($jsonInput['email'] ?? '');
        $password = $jsonInput['password'] ?? '';
        $role = $jsonInput['role'] ?? 'developer';
        $phone = trim($jsonInput['phone'] ?? '');
        $experience = $jsonInput['experience'] ?? '';
        $techStack = trim($jsonInput['techStack'] ?? '');

        if (empty($fullName) || empty($email) || empty($password) || empty($role)) {
            echo json_encode(['success' => false, 'message' => 'Required fields are missing']);
            exit;
        }

        if (!validateEmail($email)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email format']);
            exit;
        }

        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            exit;
        }
        $stmt->close();

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('INSERT INTO users (fullName, email, password, role, phone, experience, techStack, verified, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())');
        $stmt->bind_param('sssssss', $fullName, $email, $hashedPassword, $role, $phone, $experience, $techStack);

        if ($stmt->execute()) {
            $newUserId = $stmt->insert_id;
            logAdminAction($conn, $admin['id'], 'create_user', 'user', $newUserId, null, ['email' => $email, 'role' => $role]);
            echo json_encode(['success' => true, 'message' => 'User created successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create user']);
        }
        $stmt->close();
        break;

    case 'update':
        requireAdminPermission($conn, 'edit_users');
        requireApiCsrfFromInput($jsonInput);

        $userId = (int) ($jsonInput['user_id'] ?? 0);
        $fullName = trim($jsonInput['fullName'] ?? '');
        $email = normalizeEmail($jsonInput['email'] ?? '');
        $role = $jsonInput['role'] ?? 'developer';
        $phone = trim($jsonInput['phone'] ?? '');
        $experience = $jsonInput['experience'] ?? '';
        $techStack = trim($jsonInput['techStack'] ?? '');
        $password = $jsonInput['password'] ?? '';

        if ($userId <= 0 || empty($fullName) || empty($email) || empty($role)) {
            echo json_encode(['success' => false, 'message' => 'Required fields are missing']);
            exit;
        }

        if (!validateEmail($email)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email format']);
            exit;
        }

        $stmt = $conn->prepare('SELECT id, email FROM users WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$existing) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            exit;
        }

        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1');
        $stmt->bind_param('si', $email, $userId);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Email already in use']);
            exit;
        }
        $stmt->close();

        if ($password !== '') {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('UPDATE users SET fullName = ?, email = ?, role = ?, phone = ?, experience = ?, techStack = ?, password = ?, updated_at = NOW() WHERE id = ?');
            $stmt->bind_param('sssssssi', $fullName, $email, $role, $phone, $experience, $techStack, $hashedPassword, $userId);
        } else {
            $stmt = $conn->prepare('UPDATE users SET fullName = ?, email = ?, role = ?, phone = ?, experience = ?, techStack = ?, updated_at = NOW() WHERE id = ?');
            $stmt->bind_param('ssssssi', $fullName, $email, $role, $phone, $experience, $techStack, $userId);
        }

        if ($stmt->execute()) {
            logAdminAction($conn, $admin['id'], 'update_user', 'user', $userId, ['email' => $existing['email']], ['email' => $email, 'role' => $role]);
            echo json_encode(['success' => true, 'message' => 'User updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update user']);
        }
        $stmt->close();
        break;

    case 'verify':
    case 'unverify':
        requireAdminPermission($conn, 'edit_users');
        requireApiCsrfFromInput($jsonInput);

        $userId = (int) ($jsonInput['user_id'] ?? 0);
        if ($userId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
            exit;
        }

        $newStatus = $action === 'verify' ? 1 : 0;

        $stmt = $conn->prepare('SELECT verified, email FROM users WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            exit;
        }

        $stmt = $conn->prepare('UPDATE users SET verified = ? WHERE id = ?');
        $stmt->bind_param('ii', $newStatus, $userId);

        if ($stmt->execute()) {
            logAdminAction($conn, $admin['id'], $action . '_user', 'user', $userId, ['verified' => $user['verified']], ['verified' => $newStatus]);
            createAdminNotification($conn, null, 'info', 'User Status Changed', "User {$user['email']} was " . ($action === 'verify' ? 'verified' : 'unverified'), 'admin/users.php');
            echo json_encode(['success' => true, 'message' => 'User status updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update user status']);
        }
        $stmt->close();
        break;

    case 'delete':
        requireAdminPermission($conn, 'delete_users');
        requireApiCsrfFromInput($jsonInput);

        $userId = (int) ($jsonInput['user_id'] ?? 0);
        if ($userId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
            exit;
        }

        $stmt = $conn->prepare('SELECT email, role FROM users WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            exit;
        }

        $stmt = $conn->prepare('DELETE FROM users WHERE id = ?');
        $stmt->bind_param('i', $userId);

        if ($stmt->execute()) {
            logAdminAction($conn, $admin['id'], 'delete_user', 'user', $userId, $user, null);
            createAdminNotification($conn, null, 'warning', 'User Deleted', "User {$user['email']} was deleted by admin", null);
            echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
        }
        $stmt->close();
        break;

    case 'bulk_verify':
    case 'bulk_unverify':
    case 'bulk_delete':
        requireAdminPermission($conn, 'edit_users');
        if ($action === 'bulk_delete') {
            requireAdminPermission($conn, 'delete_users');
        }
        requireApiCsrfFromInput($jsonInput);

        $userIds = $jsonInput['user_ids'] ?? [];
        if (empty($userIds) || !is_array($userIds)) {
            echo json_encode(['success' => false, 'message' => 'No users selected']);
            exit;
        }

        $userIds = array_map('intval', $userIds);
        $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
        $baseAction = str_replace('bulk_', '', $action);

        if ($baseAction === 'delete') {
            $stmt = $conn->prepare("DELETE FROM users WHERE id IN ($placeholders)");
            $stmt->bind_param(str_repeat('i', count($userIds)), ...$userIds);
        } else {
            $newStatus = $baseAction === 'verify' ? 1 : 0;
            $stmt = $conn->prepare("UPDATE users SET verified = ? WHERE id IN ($placeholders)");
            $params = array_merge([$newStatus], $userIds);
            $stmt->bind_param(str_repeat('i', count($params)), ...$params);
        }

        if ($stmt->execute()) {
            $affected = $stmt->affected_rows;
            logAdminAction($conn, $admin['id'], $action, 'user', null, null, ['affected_count' => $affected]);
            echo json_encode(['success' => true, 'message' => "$affected users " . ($baseAction === 'delete' ? 'deleted' : ($baseAction === 'verify' ? 'verified' : 'unverified'))]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Bulk action failed']);
        }
        $stmt->close();
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
