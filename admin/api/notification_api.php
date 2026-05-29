<?php
require_once '../../config/db.php';
require_once '../../includes/admin_helpers.php';
require_once '../../includes/helpers.php';

header('Content-Type: application/json');

requireAdminLogin();

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

$input = getAdminJsonInput();
$action = $input['action'] ?? '';

switch ($action) {
    case 'mark_read':
        requireApiCsrfFromInput($input);
        $notificationId = (int) ($input['notification_id'] ?? 0);

        if ($notificationId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid notification ID']);
            exit;
        }

        $result = markNotificationAsRead($conn, $notificationId, $admin['id']);

        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to mark notification as read']);
        }
        break;

    case 'mark_all_read':
        requireApiCsrfFromInput($input);
        $result = markAllNotificationsAsRead($conn, $admin['id']);

        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to mark all notifications as read']);
        }
        break;

    case 'get_count':
        $count = getUnreadNotificationCount($conn, $admin['id']);
        echo json_encode(['success' => true, 'count' => $count]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
