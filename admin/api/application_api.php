<?php
require_once '../../config/db.php';
require_once '../../includes/admin_helpers.php';
require_once '../../includes/helpers.php';

header('Content-Type: application/json');

requireAdminLogin();
requireAdminPermission($conn, 'view_applications');

function requireApiCsrf(): void {
    $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
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

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

switch ($action) {
    case 'get_application':
        $applicationId = (int) ($_GET['id'] ?? 0);
        
        if ($applicationId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid application ID']);
            exit;
        }
        
        $stmt = $conn->prepare('SELECT a.*, u.fullName, u.email as user_email, u.role as user_role, j.title as job_title 
                           FROM applications a 
                           LEFT JOIN users u ON a.user_id = u.id 
                           LEFT JOIN jobs j ON a.job_id = j.id 
                           WHERE a.id = ? LIMIT 1');
        $stmt->bind_param('i', $applicationId);
        $stmt->execute();
        $application = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($application) {
            echo json_encode(['success' => true, 'application' => $application]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Application not found']);
        }
        break;
        
    case 'update_status':
        requireAdminPermission($conn, 'edit_applications');
        requireApiCsrf();
        
        $applicationId = (int) ($_POST['application_id'] ?? 0);
        $newStatus = $_POST['status'] ?? '';
        $adminNotes = trim($_POST['admin_notes'] ?? '');
        $interviewDate = $_POST['interview_date'] ?? null;
        
        if ($applicationId <= 0 || empty($newStatus)) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            exit;
        }
        
        // Get current application data for audit
        $stmt = $conn->prepare('SELECT status, full_name, email FROM applications WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $applicationId);
        $stmt->execute();
        $application = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$application) {
            echo json_encode(['success' => false, 'message' => 'Application not found']);
            exit;
        }
        
        // Update application status
        if ($newStatus === 'interview' && !empty($interviewDate)) {
            $stmt = $conn->prepare('UPDATE applications SET status = ?, admin_notes = ?, interview_date = ?, updated_at = NOW() WHERE id = ?');
            $stmt->bind_param('sssi', $newStatus, $adminNotes, $interviewDate, $applicationId);
        } else {
            $stmt = $conn->prepare('UPDATE applications SET status = ?, admin_notes = ?, updated_at = NOW() WHERE id = ?');
            $stmt->bind_param('ssi', $newStatus, $adminNotes, $applicationId);
        }
        
        if ($stmt->execute()) {
            logAdminAction($conn, $admin['id'], 'update_application_status', 'application', $applicationId, 
                ['status' => $application['status']], ['status' => $newStatus, 'notes' => $adminNotes]);
            
            // Create notification
            createAdminNotification($conn, null, 'info', 'Application Status Updated', 
                "Application for {$application['job_title']} status changed to $newStatus", 
                'admin/applications.php');
            
            echo json_encode(['success' => true, 'message' => 'Application status updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update status']);
        }
        $stmt->close();
        break;
        
    case 'add_note':
        requireAdminPermission($conn, 'edit_applications');
        requireApiCsrf();
        
        $applicationId = (int) ($_POST['application_id'] ?? 0);
        $note = trim($_POST['note'] ?? '');
        
        if ($applicationId <= 0 || empty($note)) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            exit;
        }
        
        // Append note to existing admin_notes
        $stmt = $conn->prepare('SELECT admin_notes FROM applications WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $applicationId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$result) {
            echo json_encode(['success' => false, 'message' => 'Application not found']);
            exit;
        }
        
        $existingNotes = $result['admin_notes'] ?? '';
        $newNotes = $existingNotes ? $existingNotes . "\n\n" . $note : $note;
        $timestamp = date('Y-m-d H:i:s');
        $formattedNote = "[$timestamp - {$admin['name']}]: $newNotes";
        
        $stmt = $conn->prepare('UPDATE applications SET admin_notes = ?, updated_at = NOW() WHERE id = ?');
        $stmt->bind_param('si', $formattedNote, $applicationId);
        
        if ($stmt->execute()) {
            logAdminAction($conn, $admin['id'], 'add_application_note', 'application', $applicationId);
            echo json_encode(['success' => true, 'message' => 'Note added']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add note']);
        }
        $stmt->close();
        break;
        
    case 'delete':
        requireAdminPermission($conn, 'delete_applications');
        requireApiCsrf();
        
        $applicationId = (int) ($_POST['application_id'] ?? 0);
        
        if ($applicationId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid application ID']);
            exit;
        }
        
        // Get application data for audit
        $stmt = $conn->prepare('SELECT full_name, job_position FROM applications WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $applicationId);
        $stmt->execute();
        $application = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$application) {
            echo json_encode(['success' => false, 'message' => 'Application not found']);
            exit;
        }
        
        $stmt = $conn->prepare('DELETE FROM applications WHERE id = ?');
        $stmt->bind_param('i', $applicationId);
        
        if ($stmt->execute()) {
            logAdminAction($conn, $admin['id'], 'delete_application', 'application', $applicationId, $application);
            createAdminNotification($conn, null, 'warning', 'Application Deleted', 
                "Application from {$application['full_name']} for {$application['job_position']} was deleted", null);
            echo json_encode(['success' => true, 'message' => 'Application deleted']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete application']);
        }
        $stmt->close();
        break;
        
    case 'bulk_approve':
    case 'bulk_reject':
    case 'bulk_interview':
    case 'bulk_review':
    case 'bulk_shortlist':
    case 'bulk_delete':
        requireAdminPermission($conn, 'edit_applications');
        if (str_contains($action, 'delete')) {
            requireAdminPermission($conn, 'delete_applications');
        }
        requireApiCsrf();
        
        $applicationIds = $_POST['application_ids'] ?? [];
        if (empty($applicationIds) || !is_array($applicationIds)) {
            echo json_encode(['success' => false, 'message' => 'No applications selected']);
            exit;
        }
        
        $applicationIds = array_map('intval', $applicationIds);
        $placeholders = str_repeat('?,', count($applicationIds) - 1) . '?';
        $baseAction = str_replace('bulk_', '', $action);
        
        if ($baseAction === 'delete') {
            $stmt = $conn->prepare("DELETE FROM applications WHERE id IN ($placeholders)");
        } else {
            $stmt = $conn->prepare("UPDATE applications SET status = ?, updated_at = NOW() WHERE id IN ($placeholders)");
            array_unshift($applicationIds, $baseAction);
        }
        
        $stmt->bind_param(str_repeat('i', count($applicationIds)), ...$applicationIds);
        
        if ($stmt->execute()) {
            $affected = $stmt->affected_rows;
            logAdminAction($conn, $admin['id'], $action, 'application', null, null, ['affected_count' => $affected]);
            echo json_encode(['success' => true, 'message' => "$affected applications " . ($baseAction === 'delete' ? 'deleted' : 'updated')]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Bulk action failed']);
        }
        $stmt->close();
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
