<?php
/**
 * Admin Panel Helper Functions
 * Provides authentication, permissions, and access control for admin panel
 */

require_once __DIR__ . '/helpers.php';

/**
 * Get current admin data from database
 */
function getCurrentAdmin(mysqli $conn): ?array
{
    $adminId = currentAdminId();
    if ($adminId === null) {
        return null;
    }

    $stmt = $conn->prepare('SELECT id, name, email, role, status, profile_image, last_login_at FROM admin_accounts WHERE id = ? AND status = ? LIMIT 1');
    $status = 'active';
    $stmt->bind_param('is', $adminId, $status);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();

    return $admin;
}

/**
 * Check if admin has specific permission
 */
/**
 * Require valid CSRF token on admin form POST requests.
 */
function requireAdminPostCsrf(): void
{
    $token = $_POST['csrf_token'] ?? null;
    if (!verifyCsrf(is_string($token) ? $token : null)) {
        http_response_code(403);
        die('CSRF validation failed');
    }
}

/**
 * Two-level admin RBAC (Super Admin, Admin).
 *
 * Super Admin: full access including admin management.
 * Admin: full product access but cannot manage administrators.
 */
function isSuperAdmin(?array $admin): bool
{
    return is_array($admin) && (($admin['role'] ?? '') === 'super_admin');
}

function isAdminRoleValid(string $role): bool
{
    return in_array($role, ['super_admin', 'admin'], true);
}

function isSuperAdminOnlyPermission(string $permission): bool
{
    // Administrator management is Super Admin only.
    return in_array($permission, ['manage_admins', 'manage_roles', 'edit_admin_permissions'], true);
}

function adminHasPermission(mysqli $conn, string $permission): bool
{
    $admin = getCurrentAdmin($conn);
    if ($admin === null) {
        return false;
    }

    // Super Admin has full access.
    if (isSuperAdmin($admin)) {
        return true;
    }

    // Enforce two-level hierarchy: Admins can do everything except manage administrators.
    if (($admin['role'] ?? '') === 'admin') {
        return !isSuperAdminOnlyPermission($permission);
    }

    // Any legacy/unknown roles are treated as non-privileged.
    // We keep the old permission table check only for backward compatibility when a legacy role exists.
    if (!isAdminRoleValid((string) ($admin['role'] ?? ''))) {
        return false;
    }

    $stmt = $conn->prepare('SELECT COUNT(*) as has_perm FROM admin_permissions WHERE admin_id = ? AND permission = ? LIMIT 1');
    $stmt->bind_param('is', $admin['id'], $permission);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return ($result['has_perm'] ?? 0) > 0;
}

/**
 * Require admin login - redirect to admin login if not logged in
 */
function requireAdminLogin(): void
{
    if (!isAdminLoggedIn()) {
        header('Location: ' . appUrl('admin/login.php'));
        exit;
    }
}

/**
 * Require specific admin permission
 */
function requireAdminPermission(mysqli $conn, string $permission): void
{
    requireAdminLogin();
    
    if (!adminHasPermission($conn, $permission)) {
        $_SESSION['admin_error'] = 'You do not have permission to access this resource.';
        header('Location: ' . appUrl('admin/dashboard.php'));
        exit;
    }
}

/**
 * Admin login - authenticate admin credentials
 */
function adminLogin(mysqli $conn, string $email, string $password, bool $remember = false): array
{
    $email = normalizeEmail($email);
    
    $stmt = $conn->prepare('SELECT id, name, email, password, role, status, firebase_uid, profile_image, failed_login_attempts, locked_until FROM admin_accounts WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($admin === false) {
        return ['success' => false, 'message' => 'Invalid credentials'];
    }

    // Check if account is locked
    if ($admin['locked_until'] !== null && strtotime($admin['locked_until']) > time()) {
        return ['success' => false, 'message' => 'Account is temporarily locked due to multiple failed login attempts'];
    }

    // Check if account is active
    if ($admin['status'] !== 'active') {
        return ['success' => false, 'message' => 'Account is not active'];
    }

    // Verify password
    if (!password_verify($password, $admin['password'])) {
        // Increment failed login attempts
        $failedAttempts = (int) $admin['failed_login_attempts'] + 1;
        $maxAttempts = 5;
        
        if ($failedAttempts >= $maxAttempts) {
            // Lock account for 30 minutes
            $lockUntil = date('Y-m-d H:i:s', time() + 1800);
            $stmt = $conn->prepare('UPDATE admin_accounts SET failed_login_attempts = ?, locked_until = ? WHERE id = ?');
            $stmt->bind_param('isi', $failedAttempts, $lockUntil, $admin['id']);
            $stmt->execute();
            $stmt->close();
            
            logAdminAction($conn, $admin['id'], 'login_failed', 'admin_account', $admin['id'], null, ['reason' => 'account_locked']);
        } else {
            $stmt = $conn->prepare('UPDATE admin_accounts SET failed_login_attempts = ? WHERE id = ?');
            $stmt->bind_param('ii', $failedAttempts, $admin['id']);
            $stmt->execute();
            $stmt->close();
        }
        
        logAdminAction($conn, $admin['id'], 'login_failed', 'admin_account', $admin['id'], null, ['email' => $email]);
        return ['success' => false, 'message' => 'Invalid credentials'];
    }

    // Successful login - reset failed attempts and update last login
    $stmt = $conn->prepare('UPDATE admin_accounts SET failed_login_attempts = 0, locked_until = NULL, last_login_at = NOW(), last_login_ip = ? WHERE id = ?');
    $ip = requestClientIp();
    $stmt->bind_param('si', $ip, $admin['id']);
    $stmt->execute();
    $stmt->close();

    // Set admin session
    startAdminSecureSession();
    session_regenerate_id(true);
    
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_name'] = $admin['name'];
    $_SESSION['admin_email'] = $admin['email'];
    $_SESSION['admin_role'] = $admin['role'];
    $_SESSION['admin_photo'] = $admin['profile_image'];
    $_SESSION['admin_firebase_uid'] = $admin['firebase_uid'];

    // Clear user session if exists
    unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email'], $_SESSION['user_role']);

    // Log successful login
    logAdminAction($conn, $admin['id'], 'login', 'admin_account', $admin['id']);

    return ['success' => true, 'message' => 'Login successful'];
}

/**
 * Admin logout
 */
function adminLogout(mysqli $conn = null): void
{
    $adminId = currentAdminId();
    
    if ($conn !== null && $adminId !== null) {
        logAdminAction($conn, $adminId, 'logout', 'admin_account', $adminId);
    }

    startAdminSecureSession();
    
    $_SESSION = [];
    
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}

/**
 * Log admin action for audit trail
 */
function logAdminAction(mysqli $conn, int $adminId, string $action, string $entityType, ?int $entityId = null, ?array $oldValues = null, ?array $newValues = null): bool
{
    $stmt = $conn->prepare('INSERT INTO admin_audit_logs (admin_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    
    $oldJson = $oldValues !== null ? json_encode($oldValues) : null;
    $newJson = $newValues !== null ? json_encode($newValues) : null;
    $ip = requestClientIp();
    $userAgent = requestUserAgent();
    
    $stmt->bind_param('ississss', $adminId, $action, $entityType, $entityId, $oldJson, $newJson, $ip, $userAgent);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

/**
 * Create admin notification
 */
function createAdminNotification(mysqli $conn, ?int $adminId, string $type, string $title, string $message = null, string $actionUrl = null): bool
{
    $stmt = $conn->prepare('INSERT INTO admin_notifications (admin_id, type, title, message, action_url) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('issss', $adminId, $type, $title, $message, $actionUrl);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

/**
 * Get unread notification count for admin
 */
function getUnreadNotificationCount(mysqli $conn, int $adminId): int
{
    $stmt = $conn->prepare('SELECT COUNT(*) as count FROM admin_notifications WHERE admin_id = ? AND is_read = FALSE');
    $stmt->bind_param('i', $adminId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    return (int) ($result['count'] ?? 0);
}

/**
 * Get admin notifications
 */
function getAdminNotifications(mysqli $conn, int $adminId, int $limit = 10): array
{
    $stmt = $conn->prepare('SELECT id, admin_id, type, title, message, action_url, is_read, created_at, read_at FROM admin_notifications WHERE admin_id = ? ORDER BY created_at DESC LIMIT ?');
    $stmt->bind_param('ii', $adminId, $limit);
    $stmt->execute();
    $notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC) ?: [];
    $stmt->close();
    
    return $notifications;
}

/**
 * Mark notification as read
 */
function markNotificationAsRead(mysqli $conn, int $notificationId, int $adminId): bool
{
    $stmt = $conn->prepare('UPDATE admin_notifications SET is_read = TRUE, read_at = NOW() WHERE id = ? AND admin_id = ?');
    $stmt->bind_param('ii', $notificationId, $adminId);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

/**
 * Mark all notifications as read for admin
 */
function markAllNotificationsAsRead(mysqli $conn, int $adminId): bool
{
    $stmt = $conn->prepare('UPDATE admin_notifications SET is_read = TRUE, read_at = NOW() WHERE admin_id = ? AND is_read = FALSE');
    $stmt->bind_param('i', $adminId);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

/**
 * Get platform setting
 */
function getPlatformSetting(mysqli $conn, string $key, $default = null)
{
    $stmt = $conn->prepare('SELECT setting_value, setting_type FROM platform_settings WHERE setting_key = ? LIMIT 1');
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($result === null) {
        return $default;
    }
    
    $value = $result['setting_value'];
    $type = $result['setting_type'];
    
    switch ($type) {
        case 'boolean':
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        case 'integer':
            return (int) $value;
        case 'json':
            return json_decode($value, true);
        default:
            return $value;
    }
}

/**
 * Set platform setting
 */
function setPlatformSetting(mysqli $conn, string $key, $value, string $type = 'string', string $category = 'general', int $updatedBy = null): bool
{
    $stmt = $conn->prepare('INSERT INTO platform_settings (setting_key, setting_value, setting_type, category, updated_by) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), setting_type = VALUES(setting_type), category = VALUES(category), updated_by = VALUES(updated_by), updated_at = NOW()');
    
    if (is_array($value)) {
        $value = json_encode($value);
    }
    
    $stmt->bind_param('ssssi', $key, $value, $type, $category, $updatedBy);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

/**
 * Get admin role label
 */
function getAdminRoleLabel(string $role): string
{
    $labels = [
        'super_admin' => 'Super Admin',
        'admin' => 'Admin',
        'recruiter' => 'Recruiter',
        'editor' => 'Editor',
        'viewer' => 'Viewer'
    ];
    
    return $labels[$role] ?? ucfirst($role);
}

/**
 * Check if maintenance mode is enabled
 */
function isMaintenanceMode(mysqli $conn): bool
{
    return (bool) getPlatformSetting($conn, 'maintenance_mode', false);
}

/**
 * Get dashboard statistics
 */
function getDashboardStats(mysqli $conn): array
{
    $stats = [];
    
    // Total users
    $stmt = $conn->prepare('SELECT COUNT(*) as total FROM users');
    $stmt->execute();
    $stats['total_users'] = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt->close();
    
    // Active users (logged in within last 30 days)
    $stmt = $conn->prepare('SELECT COUNT(*) as total FROM users WHERE last_login_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)');
    $stmt->execute();
    $stats['active_users'] = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt->close();
    
    // Total applications
    $stmt = $conn->prepare('SELECT COUNT(*) as total FROM applications');
    $stmt->execute();
    $stats['total_applications'] = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt->close();
    
    // Application status breakdown
    $stmt = $conn->prepare('SELECT status, COUNT(*) as count FROM applications GROUP BY status');
    $stmt->execute();
    $appStatus = $stmt->get_result()->fetch_all(MYSQLI_ASSOC) ?: [];
    $stmt->close();
    
    foreach ($appStatus as $status) {
        $stats['applications_' . strtolower($status['status'])] = $status['count'];
    }
    
    // Total jobs
    $stmt = $conn->prepare('SELECT COUNT(*) as total FROM jobs');
    $stmt->execute();
    $stats['total_jobs'] = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt->close();
    
    // Active jobs
    $stmt = $conn->prepare('SELECT COUNT(*) as total FROM jobs WHERE status = ?');
    $status = 'active';
    $stmt->bind_param('s', $status);
    $stmt->execute();
    $stats['active_jobs'] = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt->close();
    
    // Total resumes
    $stmt = $conn->prepare('SELECT COUNT(*) as total FROM applications WHERE resume_path IS NOT NULL AND resume_path != ?');
    $empty = '';
    $stmt->bind_param('s', $empty);
    $stmt->execute();
    $stats['total_resumes'] = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt->close();
    
    return $stats;
}

/**
 * Convert datetime to human-readable time elapsed string (PHP 8.2+ safe).
 */
function time_elapsed_string($datetime, $full = false): string
{
    if ($datetime === null || $datetime === '') {
        return 'just now';
    }

    $timestamp = strtotime((string) $datetime);
    if ($timestamp === false) {
        return 'just now';
    }

    $seconds = time() - $timestamp;
    if ($seconds < 0) {
        return 'just now';
    }
    if ($seconds < 45) {
        return 'just now';
    }

    $units = [
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
    ];

    $parts = [];
    foreach ($units as $unitSeconds => $label) {
        $count = (int) floor($seconds / $unitSeconds);
        if ($count < 1) {
            continue;
        }
        $parts[] = $count . ' ' . $label . ($count === 1 ? '' : 's');
        $seconds -= $count * $unitSeconds;
        if (!$full) {
            break;
        }
    }

    if ($parts === []) {
        return 'just now';
    }

    return ($full ? implode(', ', $parts) : $parts[0]) . ' ago';
}

/**
 * Whether an application row references a resume path.
 */
function applicationHasResume(?string $resumePath): bool
{
    return is_string($resumePath) && trim($resumePath) !== '';
}

/**
 * Whether the resume file exists on disk under the project root.
 */
function applicationResumeExistsOnDisk(?string $resumePath): bool
{
    if (!applicationHasResume($resumePath)) {
        return false;
    }

    $projectRoot = realpath(__DIR__ . '/..');
    if ($projectRoot === false) {
        return false;
    }

    $fullPath = resolveApplicationResumePath($resumePath, $projectRoot);

    return $fullPath !== null && is_file($fullPath);
}

/**
 * Resolve a resume path to an absolute filesystem path under uploads/resumes.
 */
function resolveApplicationResumePath(?string $resumePath, ?string $projectRoot = null): ?string
{
    if (!applicationHasResume($resumePath)) {
        return null;
    }

    $projectRoot = $projectRoot ?? realpath(__DIR__ . '/..');
    if ($projectRoot === false || $projectRoot === null) {
        return null;
    }

    $normalizedResumePath = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, ltrim((string) $resumePath, '\\/'));
    $candidatePath = $projectRoot . DIRECTORY_SEPARATOR . $normalizedResumePath;
    $realPath = realpath($candidatePath);

    if ($realPath === false) {
        return null;
    }

    $normalizedRealPath = str_replace('\\', '/', $realPath);
    $normalizedUploadsDir = str_replace('\\', '/', $projectRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'resumes');

    if (strpos($normalizedRealPath, $normalizedUploadsDir) !== 0) {
        return null;
    }

    return $realPath;
}

/**
 * HTML badge for resume upload status in admin UI.
 */
function renderResumeStatusBadge(?string $resumePath, bool $compact = false): string
{
    if (!applicationHasResume($resumePath)) {
        $label = $compact ? 'No Resume' : '✗ No Resume';
        return '<span class="resume-status resume-missing"><i class="fas fa-times-circle" aria-hidden="true"></i> ' . escape($label) . '</span>';
    }

    if (!applicationResumeExistsOnDisk($resumePath)) {
        $label = $compact ? 'File Missing' : '✗ File Missing';
        return '<span class="resume-status resume-missing"><i class="fas fa-exclamation-circle" aria-hidden="true"></i> ' . escape($label) . '</span>';
    }

    $label = $compact ? 'Uploaded' : '✓ Resume Uploaded';
    return '<span class="resume-status resume-ok"><i class="fas fa-check-circle" aria-hidden="true"></i> ' . escape($label) . '</span>';
}

/**
 * Human-readable application status label.
 */
function applicationStatusLabel(string $status): string
{
    return ucfirst(str_replace('_', ' ', $status));
}

/**
 * Format bytes to human-readable size
 */
function formatBytes(int $bytes, int $precision = 2): string
{
    if ($bytes === 0) {
        return '0 B';
    }
    
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $pow = floor(log($bytes) / log(1024));
    
    return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
}

/**
 * Get total resume size from database
 */
function getTotalResumeSize(mysqli $conn): int
{
    $stmt = $conn->prepare("SELECT SUM(LENGTH(resume_path)) as total FROM applications WHERE resume_path IS NOT NULL AND resume_path != ''");
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    return (int) ($result['total'] ?? 0);
}

/**
 * Get file icon based on file extension
 */
function getFileIcon(string $filename): string
{
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    $iconMap = [
        'pdf' => 'pdf',
        'doc' => 'word',
        'docx' => 'word',
        'txt' => 'alt',
        'jpg' => 'image',
        'jpeg' => 'image',
        'png' => 'image',
        'gif' => 'image',
        'zip' => 'archive',
        'rar' => 'archive',
    ];
    
    return $iconMap[$extension] ?? 'file';
}

/**
 * Ensure admin access request table exists.
 */
function ensureAdminAccessRequestTable(mysqli $conn): bool
{
    $sql = 'CREATE TABLE IF NOT EXISTS admin_access_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        request_note TEXT NULL,
        status ENUM("pending", "approved", "rejected") NOT NULL DEFAULT "pending",
        approval_token VARCHAR(128) NOT NULL,
        token_expires_at DATETIME NOT NULL,
        requested_ip VARCHAR(45) DEFAULT NULL,
        reviewed_by INT DEFAULT NULL,
        reviewed_at DATETIME DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_admin_access_email_pending (email, status),
        UNIQUE KEY uniq_admin_access_token (approval_token),
        INDEX idx_admin_access_status (status),
        INDEX idx_admin_access_expires (token_expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

    return $conn->query($sql) === true;
}

/**
 * Active admin emails that can approve new admin requests.
 */
function getAdminApproverAccounts(mysqli $conn): array
{
    $stmt = $conn->prepare("SELECT id, email FROM admin_accounts WHERE status = 'active' AND role IN ('super_admin', 'admin') ORDER BY role = 'super_admin' DESC, id ASC");
    if (!$stmt) {
        return [];
    }

    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC) ?: [];
    $stmt->close();

    $accounts = [];
    foreach ($rows as $row) {
        $email = normalizeEmail((string) ($row['email'] ?? ''));
        $adminId = (int) ($row['id'] ?? 0);
        if ($adminId > 0 && $email !== '' && validateEmail($email)) {
            $accounts[] = ['id' => $adminId, 'email' => $email];
        }
    }

    return $accounts;
}

/**
 * Active admin emails that can approve new admin requests.
 */
function getAdminApproverEmails(mysqli $conn): array
{
    $emails = [];
    foreach (getAdminApproverAccounts($conn) as $account) {
        $email = (string) ($account['email'] ?? '');
        if ($email !== '' && validateEmail($email)) {
            $emails[] = $email;
        }
    }

    return array_values(array_unique($emails));
}

/**
 * Submit a secure admin access request (pending approval).
 */
function submitAdminAccessRequest(mysqli $conn, string $fullName, string $email, string $password, string $note = ''): array
{
    if (!ensureAdminAccessRequestTable($conn)) {
        return ['success' => false, 'message' => 'Unable to initialize admin request storage.'];
    }

    $fullName = trim($fullName);
    $email = normalizeEmail($email);
    $note = trim($note);

    if ($fullName === '' || strlen($fullName) < 2) {
        return ['success' => false, 'message' => 'Please enter your full name.'];
    }

    if (!validateEmail($email)) {
        return ['success' => false, 'message' => 'Please enter a valid email address.'];
    }

    if (strlen($password) < 12) {
        return ['success' => false, 'message' => 'Password must be at least 12 characters.'];
    }

    if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        return ['success' => false, 'message' => 'Password must include uppercase, lowercase, and a number.'];
    }

    if (in_array($password, ['admin123', 'Admin@123', 'password123', 'Password123'], true)) {
        return ['success' => false, 'message' => 'Choose a stronger password than common defaults.'];
    }

    $existingAdmin = $conn->prepare('SELECT id FROM admin_accounts WHERE email = ? LIMIT 1');
    if ($existingAdmin) {
        $existingAdmin->bind_param('s', $email);
        $existingAdmin->execute();
        $hasAdmin = $existingAdmin->get_result()->num_rows > 0;
        $existingAdmin->close();
        if ($hasAdmin) {
            return ['success' => false, 'message' => 'An admin account with this email already exists.'];
        }
    }

    $pendingStmt = $conn->prepare("SELECT id FROM admin_access_requests WHERE email = ? AND status = 'pending' LIMIT 1");
    if ($pendingStmt) {
        $pendingStmt->bind_param('s', $email);
        $pendingStmt->execute();
        $hasPending = $pendingStmt->get_result()->num_rows > 0;
        $pendingStmt->close();
        if ($hasPending) {
            return ['success' => false, 'message' => 'A pending request already exists for this email.'];
        }
    }

    $approvers = getAdminApproverEmails($conn);
    if ($approvers === []) {
        return ['success' => false, 'message' => 'No active administrators are available to review requests yet.'];
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $approvalToken = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', time() + (72 * 3600));
    $requestedIp = requestClientIp();

    $insert = $conn->prepare('INSERT INTO admin_access_requests (full_name, email, password_hash, request_note, status, approval_token, token_expires_at, requested_ip) VALUES (?, ?, ?, ?, "pending", ?, ?, ?)');
    if (!$insert) {
        return ['success' => false, 'message' => 'Unable to save your request. Please try again.'];
    }

    $insert->bind_param('sssssss', $fullName, $email, $passwordHash, $note, $approvalToken, $expiresAt, $requestedIp);
    if (!$insert->execute()) {
        $insert->close();
        return ['success' => false, 'message' => 'Unable to save your request. Please try again.'];
    }
    $insert->close();

    $approvalUrl = appUrl('admin/approve_admin_access.php?token=' . urlencode($approvalToken));
    $subject = 'DevHire Admin Access Approval Required';
    $body = "A new admin access request was submitted.\n\n"
        . "Name: {$fullName}\n"
        . "Email: {$email}\n"
        . "IP: {$requestedIp}\n"
        . "Note: " . ($note !== '' ? $note : '(none)') . "\n\n"
        . "Review and approve securely:\n{$approvalUrl}\n\n"
        . "This link expires in 72 hours.";

    $headers = 'From: DevHire Security <noreply@devhire.local>' . "\r\n" . 'Content-Type: text/plain; charset=UTF-8';
    foreach (getAdminApproverAccounts($conn) as $approver) {
        $approverId = (int) ($approver['id'] ?? 0);
        $approverEmail = (string) ($approver['email'] ?? '');

        if ($approverId > 0) {
            createAdminNotification(
                $conn,
                $approverId,
                'warning',
                'Admin Access Request Pending',
                "{$fullName} ({$email}) requested admin access. Review the request in admin management.",
                $approvalUrl
            );
        }

        if ($approverEmail !== '') {
            @mail($approverEmail, $subject, $body, $headers);
        }
    }

    return [
        'success' => true,
        'message' => 'Your request was sent. An existing administrator must approve it before you can sign in.',
    ];
}

/**
 * Load a pending admin access request by token.
 */
function getAdminAccessRequestByToken(mysqli $conn, string $token): ?array
{
    if (!ensureAdminAccessRequestTable($conn)) {
        return null;
    }

    $token = trim($token);
    if ($token === '') {
        return null;
    }

    $stmt = $conn->prepare("SELECT * FROM admin_access_requests WHERE approval_token = ? AND status = 'pending' AND token_expires_at > NOW() LIMIT 1");
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('s', $token);
    $stmt->execute();
    $request = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();

    return $request;
}

/**
 * Approve a pending admin access request.
 */
function approveAdminAccessRequest(mysqli $conn, string $token, int $reviewerAdminId): array
{
    $request = getAdminAccessRequestByToken($conn, $token);
    if ($request === null) {
        return ['success' => false, 'message' => 'This approval link is invalid or expired.'];
    }

    $email = normalizeEmail((string) $request['email']);
    $existing = $conn->prepare('SELECT id FROM admin_accounts WHERE email = ? LIMIT 1');
    if ($existing) {
        $existing->bind_param('s', $email);
        $existing->execute();
        $exists = $existing->get_result()->num_rows > 0;
        $existing->close();
        if ($exists) {
            return ['success' => false, 'message' => 'An admin account with this email already exists.'];
        }
    }

    $insert = $conn->prepare("INSERT INTO admin_accounts (name, email, password, role, status, created_at) VALUES (?, ?, ?, 'admin', 'active', NOW())");
    if (!$insert) {
        return ['success' => false, 'message' => 'Unable to create the admin account.'];
    }

    $name = (string) $request['full_name'];
    $passwordHash = (string) $request['password_hash'];
    $insert->bind_param('sss', $name, $email, $passwordHash);
    if (!$insert->execute()) {
        $insert->close();
        return ['success' => false, 'message' => 'Unable to create the admin account.'];
    }

    $newAdminId = (int) $insert->insert_id;
    $insert->close();

    $update = $conn->prepare("UPDATE admin_access_requests SET status = 'approved', reviewed_by = ?, reviewed_at = NOW() WHERE id = ?");
    if ($update) {
        $requestId = (int) $request['id'];
        $update->bind_param('ii', $reviewerAdminId, $requestId);
        $update->execute();
        $update->close();
    }

    logAdminAction($conn, $reviewerAdminId, 'approve_admin_request', 'admin_access_request', (int) $request['id'], null, ['email' => $email, 'new_admin_id' => $newAdminId]);

    return ['success' => true, 'message' => 'Admin account approved and activated successfully.'];
}
