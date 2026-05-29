<?php
require_once '../../config/db.php';
require_once '../../includes/admin_helpers.php';

header('Content-Type: application/json');

requireAdminLogin();
requireAdminPermission($conn, 'view_resumes');

$admin = getCurrentAdmin($conn);
if ($admin === null) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$token = $_GET['token'] ?? '';
$preview = isset($_GET['preview']) && $_GET['preview'] === '1';

if (empty($token)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid token']);
    exit;
}

// Validate token from session
if (!isset($_SESSION['resume_download_tokens'][$token])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid or expired token']);
    exit;
}

$tokenData = $_SESSION['resume_download_tokens'][$token];

// Check token expiration
if (strtotime($tokenData['expires_at']) < time()) {
    unset($_SESSION['resume_download_tokens'][$token]);
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token expired']);
    exit;
}

// Verify admin ID matches
if ($tokenData['admin_id'] !== $admin['id']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token does not belong to current admin']);
    exit;
}

// Get application details
$applicationId = $tokenData['application_id'];
$stmt = $conn->prepare('SELECT id, full_name, resume_path FROM applications WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $applicationId);
$stmt->execute();
$application = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$application || empty($application['resume_path'])) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Resume not found']);
    exit;
}

// Get file path using project root instead of DOCUMENT_ROOT
$projectRoot = realpath(__DIR__ . '/../../');
$resumePath = $projectRoot . '/' . ltrim($application['resume_path'], '/');

// Ensure path is within uploads directory to prevent traversal
$realPath = realpath($resumePath);
$uploadsDir = $projectRoot . '/uploads/resumes';
if ($realPath === false || strpos($realPath, $uploadsDir) !== 0) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid resume path']);
    exit;
}

if (!file_exists($resumePath)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'File not found on server']);
    exit;
}

// Log download
logAdminAction($conn, $admin['id'], 'download_resume', 'application', $applicationId, 
    null, ['applicant' => $application['full_name'], 'preview' => $preview]);

// Clean up token after use
unset($_SESSION['resume_download_tokens'][$token]);

$fileInfo = pathinfo($resumePath);
$mimeType = getMimeType($resumePath);
$fileName = $fileInfo['basename'];

// Set headers for file download/preview
if ($preview && $mimeType === 'application/pdf') {
    // For PDF preview, serve inline
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $fileName . '"');
    header('Content-Length: ' . filesize($resumePath));
    header('Cache-Control: private, max-age=3600');
    header('Pragma: private');
    header('Expires: ' . date('D, d M Y H:i:s', time() + 3600) . ' GMT');
} else {
    // For download
    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Content-Length: ' . filesize($resumePath));
    header('Cache-Control: private, max-age=3600');
    header('Pragma: private');
    header('Expires: ' . date('D, d M Y H:i:s', time() + 3600) . ' GMT');
}

// Output file
readfile($resumePath);
exit;

function getMimeType($filePath): string {
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    
    $mimeTypes = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'txt' => 'text/plain',
        'rtf' => 'application/rtf'
    ];
    
    return $mimeTypes[$extension] ?? 'application/octet-stream';
}
