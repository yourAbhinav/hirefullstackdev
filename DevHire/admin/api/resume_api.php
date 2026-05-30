<?php
// Start session immediately before any other operations
require_once '../../includes/helpers.php';
startSecureSession();

require_once '../../config/db.php';
require_once '../../includes/admin_helpers.php';

header('Content-Type: application/json');

requireAdminLogin();
requireAdminPermission($conn, 'view_resumes');

$admin = getCurrentAdmin($conn);
if ($admin === null) {
    echo json_encode(['success' => false, 'message' => 'Admin not found']);
    exit;
}

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

switch ($action) {
    case 'view':
        $applicationId = (int) ($_GET['application_id'] ?? 0);
        
        if ($applicationId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid application ID']);
            exit;
        }
        
        // Get application details
        $stmt = $conn->prepare('SELECT id, full_name, resume_path FROM applications WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $applicationId);
        $stmt->execute();
        $application = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$application || empty($application['resume_path'])) {
            echo json_encode(['success' => false, 'message' => 'Resume not found']);
            exit;
        }
        
        // Log resume view action
        logAdminAction($conn, $admin['id'], 'view_resume', 'application', $applicationId, 
            null, ['applicant' => $application['full_name']]);
        
        $resumePath = resolveApplicationResumePath($application['resume_path']);
        if ($resumePath === null) {
            echo json_encode(['success' => false, 'message' => 'Invalid resume path']);
            exit;
        }
        
        if (!file_exists($resumePath)) {
            echo json_encode(['success' => false, 'message' => 'Resume file not found on server']);
            exit;
        }
        
        $fileInfo = pathinfo($resumePath);
        $fileSize = filesize($resumePath);
        $fileSizeFormatted = formatFileSize($fileSize);
        $fileExtension = strtolower($fileInfo['extension']);
        
        // Return file info for direct download without tokens
        echo json_encode([
            'success' => true,
            'filename' => $fileInfo['basename'],
            'file_size' => $fileSizeFormatted,
            'file_extension' => $fileExtension,
            'application_id' => $applicationId
        ]);
        break;
        
    case 'download':
        // This is handled by resume_download.php
        echo json_encode(['success' => false, 'message' => 'Use resume_download.php for downloads']);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function formatFileSize($bytes): string {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 0) {
        return $bytes . ' bytes';
    } else {
        return '0 bytes';
    }
}
