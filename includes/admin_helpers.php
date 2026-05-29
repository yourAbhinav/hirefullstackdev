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
function adminHasPermission(mysqli $conn, string $permission): bool
{
    $admin = getCurrentAdmin($conn);
    if ($admin === null) {
        return false;
    }

    // Super admins have all permissions
    if ($admin['role'] === 'super_admin') {
        return true;
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
    startSecureSession();
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

    startSecureSession();
    
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
 * Convert datetime to human-readable time elapsed string
 */
function time_elapsed_string($datetime, $full = false): string
{
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) {
        $string = array_slice($string, 0, 1);
    }
    
    return $string ? implode(', ', $string) . ' ago' : 'just now';
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
