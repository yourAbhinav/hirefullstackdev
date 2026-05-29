<?php
$page_title = 'Platform Settings';
require_once 'includes/admin_header.php';

requireAdminPermission($conn, 'view_settings');

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    requireAdminPermission($conn, 'edit_settings');
    requireAdminPostCsrf();
    
    if ($_POST['action'] === 'update_general') {
        $siteName = trim($_POST['site_name'] ?? '');
        $siteDescription = trim($_POST['site_description'] ?? '');
        $contactEmail = trim($_POST['contact_email'] ?? '');
        $maintenanceMode = isset($_POST['maintenance_mode']) ? 1 : 0;
        
        // Update settings in database
        $stmt = $conn->prepare("INSERT INTO platform_settings (setting_key, setting_value) VALUES ('site_name', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param('ss', $siteName, $siteName);
        $stmt->execute();
        
        $stmt = $conn->prepare("INSERT INTO platform_settings (setting_key, setting_value) VALUES ('site_description', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param('ss', $siteDescription, $siteDescription);
        $stmt->execute();
        
        $stmt = $conn->prepare("INSERT INTO platform_settings (setting_key, setting_value) VALUES ('contact_email', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param('ss', $contactEmail, $contactEmail);
        $stmt->execute();
        
        $stmt = $conn->prepare("INSERT INTO platform_settings (setting_key, setting_value) VALUES ('maintenance_mode', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param('ii', $maintenanceMode, $maintenanceMode);
        $stmt->execute();
        
        logAdminAction($conn, $admin['id'], 'update_settings', 'platform_settings', null, null, ['section' => 'general']);
        $successMessage = 'General settings updated successfully';
    }
    
    if ($_POST['action'] === 'update_security') {
        $minPasswordLength = (int) ($_POST['min_password_length'] ?? 8);
        $requireUppercase = isset($_POST['require_uppercase']) ? 1 : 0;
        $requireLowercase = isset($_POST['require_lowercase']) ? 1 : 0;
        $requireNumbers = isset($_POST['require_numbers']) ? 1 : 0;
        $requireSpecialChars = isset($_POST['require_special_chars']) ? 1 : 0;
        $sessionTimeout = (int) ($_POST['session_timeout'] ?? 3600);
        $maxLoginAttempts = (int) ($_POST['max_login_attempts'] ?? 5);
        
        $stmt = $conn->prepare("INSERT INTO platform_settings (setting_key, setting_value) VALUES ('min_password_length', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param('ii', $minPasswordLength, $minPasswordLength);
        $stmt->execute();
        
        $stmt = $conn->prepare("INSERT INTO platform_settings (setting_key, setting_value) VALUES ('require_uppercase', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param('ii', $requireUppercase, $requireUppercase);
        $stmt->execute();
        
        $stmt = $conn->prepare("INSERT INTO platform_settings (setting_key, setting_value) VALUES ('require_lowercase', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param('ii', $requireLowercase, $requireLowercase);
        $stmt->execute();
        
        $stmt = $conn->prepare("INSERT INTO platform_settings (setting_key, setting_value) VALUES ('require_numbers', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param('ii', $requireNumbers, $requireNumbers);
        $stmt->execute();
        
        $stmt = $conn->prepare("INSERT INTO platform_settings (setting_key, setting_value) VALUES ('require_special_chars', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param('ii', $requireSpecialChars, $requireSpecialChars);
        $stmt->execute();
        
        $stmt = $conn->prepare("INSERT INTO platform_settings (setting_key, setting_value) VALUES ('session_timeout', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param('ii', $sessionTimeout, $sessionTimeout);
        $stmt->execute();
        
        $stmt = $conn->prepare("INSERT INTO platform_settings (setting_key, setting_value) VALUES ('max_login_attempts', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param('ii', $maxLoginAttempts, $maxLoginAttempts);
        $stmt->execute();
        
        logAdminAction($conn, $admin['id'], 'update_settings', 'platform_settings', null, null, ['section' => 'security']);
        $successMessage = 'Security settings updated successfully';
    }
    
    if ($_POST['action'] === 'update_notifications') {
        $emailNotifications = isset($_POST['email_notifications']) ? 1 : 0;
        $newUserAlerts = isset($_POST['new_user_alerts']) ? 1 : 0;
        $applicationAlerts = isset($_POST['application_alerts']) ? 1 : 0;
        $securityAlerts = isset($_POST['security_alerts']) ? 1 : 0;
        
        $stmt = $conn->prepare("INSERT INTO platform_settings (setting_key, setting_value) VALUES ('email_notifications', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param('ii', $emailNotifications, $emailNotifications);
        $stmt->execute();
        
        $stmt = $conn->prepare("INSERT INTO platform_settings (setting_key, setting_value) VALUES ('new_user_alerts', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param('ii', $newUserAlerts, $newUserAlerts);
        $stmt->execute();
        
        $stmt = $conn->prepare("INSERT INTO platform_settings (setting_key, setting_value) VALUES ('application_alerts', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param('ii', $applicationAlerts, $applicationAlerts);
        $stmt->execute();
        
        $stmt = $conn->prepare("INSERT INTO platform_settings (setting_key, setting_value) VALUES ('security_alerts', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param('ii', $securityAlerts, $securityAlerts);
        $stmt->execute();
        
        logAdminAction($conn, $admin['id'], 'update_settings', 'platform_settings', null, null, ['section' => 'notifications']);
        $successMessage = 'Notification settings updated successfully';
    }
}

// Get current settings
function getSetting($conn, $key, $default = '') {
    $stmt = $conn->prepare("SELECT setting_value FROM platform_settings WHERE setting_key = ?");
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result['setting_value'] ?? $default;
}

$settings = [
    'site_name' => getSetting($conn, 'site_name', 'DevHire'),
    'site_description' => getSetting($conn, 'site_description', 'Hiring Full Stack Developers'),
    'contact_email' => getSetting($conn, 'contact_email', 'admin@devhire.com'),
    'maintenance_mode' => getSetting($conn, 'maintenance_mode', 0),
    'min_password_length' => getSetting($conn, 'min_password_length', 8),
    'require_uppercase' => getSetting($conn, 'require_uppercase', 1),
    'require_lowercase' => getSetting($conn, 'require_lowercase', 1),
    'require_numbers' => getSetting($conn, 'require_numbers', 1),
    'require_special_chars' => getSetting($conn, 'require_special_chars', 0),
    'session_timeout' => getSetting($conn, 'session_timeout', 3600),
    'max_login_attempts' => getSetting($conn, 'max_login_attempts', 5),
    'email_notifications' => getSetting($conn, 'email_notifications', 1),
    'new_user_alerts' => getSetting($conn, 'new_user_alerts', 1),
    'application_alerts' => getSetting($conn, 'application_alerts', 1),
    'security_alerts' => getSetting($conn, 'security_alerts', 1)
];
?>

<div class="page-header">
        <div class="page-header-left">
            <h1>Platform Settings</h1>
            <p>Configure your platform settings and preferences</p>
        </div>
    </div>

    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($successMessage) ?>
        </div>
    <?php endif; ?>

    <!-- Settings Tabs -->
    <div class="settings-tabs">
        <button class="tab-btn active" data-tab="general">
            <i class="fas fa-cog"></i> General
        </button>
        <button class="tab-btn" data-tab="security">
            <i class="fas fa-shield-alt"></i> Security
        </button>
        <button class="tab-btn" data-tab="notifications">
            <i class="fas fa-bell"></i> Notifications
        </button>
    </div>

    <!-- General Settings -->
    <div class="settings-content" id="general-tab">
        <div class="settings-section">
            <div class="settings-header">
                <h2>General Settings</h2>
                <p>Basic platform configuration</p>
            </div>
            
            <form method="POST" action="settings.php">
                <input type="hidden" name="action" value="update_general">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                
                <div class="form-group">
                    <label for="site_name">Site Name</label>
                    <input type="text" id="site_name" name="site_name" class="form-control" value="<?= htmlspecialchars($settings['site_name']) ?>" required>
                    <small>The name of your platform</small>
                </div>
                
                <div class="form-group">
                    <label for="site_description">Site Description</label>
                    <textarea id="site_description" name="site_description" class="form-control" rows="3"><?= htmlspecialchars($settings['site_description']) ?></textarea>
                    <small>Brief description of your platform</small>
                </div>
                
                <div class="form-group">
                    <label for="contact_email">Contact Email</label>
                    <input type="email" id="contact_email" name="contact_email" class="form-control" value="<?= htmlspecialchars($settings['contact_email']) ?>" required>
                    <small>Email address for support inquiries</small>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="maintenance_mode" <?= $settings['maintenance_mode'] ? 'checked' : '' ?>>
                        <span>Maintenance Mode</span>
                    </label>
                    <small>When enabled, only admins can access the platform</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Security Settings -->
    <div class="settings-content" id="security-tab" style="display: none;">
        <div class="settings-section">
            <div class="settings-header">
                <h2>Security Settings</h2>
                <p>Configure password policies and security options</p>
            </div>
            
            <form method="POST" action="settings.php">
                <input type="hidden" name="action" value="update_security">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                
                <div class="form-group">
                    <label for="min_password_length">Minimum Password Length</label>
                    <input type="number" id="min_password_length" name="min_password_length" class="form-control" value="<?= $settings['min_password_length'] ?>" min="6" max="32" required>
                    <small>Minimum number of characters required for passwords</small>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="require_uppercase" <?= $settings['require_uppercase'] ? 'checked' : '' ?>>
                        <span>Require Uppercase Letters</span>
                    </label>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="require_lowercase" <?= $settings['require_lowercase'] ? 'checked' : '' ?>>
                        <span>Require Lowercase Letters</span>
                    </label>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="require_numbers" <?= $settings['require_numbers'] ? 'checked' : '' ?>>
                        <span>Require Numbers</span>
                    </label>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="require_special_chars" <?= $settings['require_special_chars'] ? 'checked' : '' ?>>
                        <span>Require Special Characters</span>
                    </label>
                </div>
                
                <div class="form-group">
                    <label for="session_timeout">Session Timeout (seconds)</label>
                    <input type="number" id="session_timeout" name="session_timeout" class="form-control" value="<?= $settings['session_timeout'] ?>" min="300" max="86400" required>
                    <small>Auto-logout after inactivity (300-86400 seconds)</small>
                </div>
                
                <div class="form-group">
                    <label for="max_login_attempts">Maximum Login Attempts</label>
                    <input type="number" id="max_login_attempts" name="max_login_attempts" class="form-control" value="<?= $settings['max_login_attempts'] ?>" min="3" max="10" required>
                    <small>Number of failed login attempts before lockout</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Notification Settings -->
    <div class="settings-content" id="notifications-tab" style="display: none;">
        <div class="settings-section">
            <div class="settings-header">
                <h2>Notification Settings</h2>
                <p>Configure email and in-app notifications</p>
            </div>
            
            <form method="POST" action="settings.php">
                <input type="hidden" name="action" value="update_notifications">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="email_notifications" <?= $settings['email_notifications'] ? 'checked' : '' ?>>
                        <span>Enable Email Notifications</span>
                    </label>
                    <small>Send email notifications for important events</small>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="new_user_alerts" <?= $settings['new_user_alerts'] ? 'checked' : '' ?>>
                        <span>New User Registrations</span>
                    </label>
                    <small>Notify when new users register</small>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="application_alerts" <?= $settings['application_alerts'] ? 'checked' : '' ?>>
                        <span>New Job Applications</span>
                    </label>
                    <small>Notify when new applications are submitted</small>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="security_alerts" <?= $settings['security_alerts'] ? 'checked' : '' ?>>
                        <span>Security Alerts</span>
                    </label>
                    <small>Notify about security events (failed logins, etc.)</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

<script>
// Tab switching
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        // Remove active class from all tabs
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.settings-content').forEach(c => c.style.display = 'none');
        
        // Add active class to clicked tab
        this.classList.add('active');
        const tabId = this.dataset.tab + '-tab';
        document.getElementById(tabId).style.display = 'block';
    });
});
</script>

<style>
.settings-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 30px;
    border-bottom: 2px solid #e5e7eb;
    padding-bottom: 10px;
}

.tab-btn {
    padding: 12px 24px;
    background: none;
    border: none;
    color: #6b7280;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.tab-btn:hover {
    color: #4F46E5;
}

.tab-btn.active {
    color: #4F46E5;
    border-bottom: 2px solid #4F46E5;
    margin-bottom: -12px;
}

.settings-content {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.settings-section {
    max-width: 600px;
}

.settings-header {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e5e7eb;
}

.settings-header h2 {
    font-size: 20px;
    font-weight: 600;
    color: #1a1a2e;
    margin-bottom: 5px;
}

.settings-header p {
    color: #6b7280;
    font-size: 14px;
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #374151;
    font-size: 14px;
}

.form-group small {
    display: block;
    margin-top: 5px;
    color: #6b7280;
    font-size: 13px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.checkbox-label span {
    font-weight: 500;
    color: #374151;
}

.form-actions {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e5e7eb;
}

.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-success {
    background: #D1FAE5;
    color: #059669;
    border: 1px solid #10B981;
}
</style>

<?php require_once 'includes/admin_footer.php'; ?>
