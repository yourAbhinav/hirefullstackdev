<?php
require_once '../config/db.php';
require_once '../includes/admin_helpers.php';

requireAdminLogin();

$admin = getCurrentAdmin($conn);
if ($admin === null) {
    adminLogout($conn);
    header('Location: ' . appUrl('admin/login.php'));
    exit;
}

$unreadCount = getUnreadNotificationCount($conn, $admin['id']);
$notifications = getAdminNotifications($conn, $admin['id'], 5);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Admin Dashboard') ?> - DevHire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.css">
    <link rel="stylesheet" href="<?= appUrl('admin/assets/css/admin-panel.css') ?>">
</head>
<body class="admin-panel">
    <div class="admin-container">
        <!-- Sidebar -->
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="<?= appUrl('admin/dashboard.php') ?>" class="logo">
                    <i class="fas fa-shield-alt"></i>
                    DevHire Admin
                </a>
            </div>
            
            <div class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Main Menu</div>
                    <a href="<?= appUrl('admin/dashboard.php') ?>" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">
                        <i class="fas fa-home"></i>
                        Dashboard
                    </a>
                    <a href="<?= appUrl('admin/users.php') ?>" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : '' ?>">
                        <i class="fas fa-users"></i>
                        Users
                    </a>
                    <a href="<?= appUrl('admin/applications.php') ?>" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'applications.php' ? 'active' : '' ?>">
                        <i class="fas fa-file-alt"></i>
                        Applications
                        <?php if (adminHasPermission($conn, 'view_applications')): ?>
                            <?php
                            $pendingApps = $conn->query("SELECT COUNT(*) as count FROM applications WHERE status = 'pending'")->fetch_assoc()['count'] ?? 0;
                            if ($pendingApps > 0): ?>
                                <span class="nav-badge"><?= $pendingApps ?></span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </a>
                    <a href="<?= appUrl('admin/jobs.php') ?>" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'jobs.php' ? 'active' : '' ?>">
                        <i class="fas fa-briefcase"></i>
                        Jobs
                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Management</div>
                    <?php if (adminHasPermission($conn, 'view_resumes')): ?>
                    <a href="<?= appUrl('admin/resumes.php') ?>" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'resumes.php' ? 'active' : '' ?>">
                        <i class="fas fa-file-pdf"></i>
                        Resumes
                    </a>
                    <?php endif; ?>
                    <?php if (adminHasPermission($conn, 'manage_admins')): ?>
                    <a href="<?= appUrl('admin/admins.php') ?>" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'admins.php' ? 'active' : '' ?>">
                        <i class="fas fa-user-shield"></i>
                        Admin Accounts
                    </a>
                    <?php endif; ?>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Analytics & Settings</div>
                    <?php if (adminHasPermission($conn, 'view_analytics')): ?>
                    <a href="<?= appUrl('admin/analytics.php') ?>" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'analytics.php' ? 'active' : '' ?>">
                        <i class="fas fa-chart-line"></i>
                        Analytics
                    </a>
                    <?php endif; ?>
                    <?php if (adminHasPermission($conn, 'view_logs')): ?>
                    <a href="<?= appUrl('admin/logs.php') ?>" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'logs.php' ? 'active' : '' ?>">
                        <i class="fas fa-history"></i>
                        Audit Logs
                    </a>
                    <?php endif; ?>
                    <?php if (adminHasPermission($conn, 'view_settings')): ?>
                    <a href="<?= appUrl('admin/settings.php') ?>" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : '' ?>">
                        <i class="fas fa-cog"></i>
                        Settings
                    </a>
                    <?php endif; ?>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Account</div>
                    <a href="<?= appUrl('admin/logout.php') ?>" class="nav-item">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="top-bar-left">
                    <button class="mobile-menu-btn" id="mobileMenuBtn">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title"><?= $page_title ?? 'Dashboard' ?></h1>
                </div>
                
                <div class="top-bar-right">
                    <!-- Notifications -->
                    <div class="notifications" id="notifications">
                        <button class="notifications-btn">
                            <i class="fas fa-bell"></i>
                            <span class="notifications-badge"><?= $unreadCount ?></span>
                        </button>
                        
                        <div class="notifications-dropdown" id="notificationsDropdown">
                            <div class="notifications-header">
                                <h3>Notifications</h3>
                                <button class="mark-read" onclick="markAllAsRead()">Mark all as read</button>
                            </div>
                            
                            <?php if (empty($notifications)): ?>
                                <div class="notifications-empty">
                                    <i class="fas fa-bell-slash"></i>
                                    <p>No notifications yet</p>
                                </div>
                            <?php else: ?>
                                <div class="notifications-list">
                                    <?php foreach ($notifications as $notification): ?>
                                        <?php
                                        $notifActionUrl = '';
                                        if (!empty($notification['action_url'])) {
                                            $rawUrl = $notification['action_url'];
                                            $notifActionUrl = (str_starts_with($rawUrl, 'http://') || str_starts_with($rawUrl, 'https://'))
                                                ? $rawUrl
                                                : appUrl(ltrim($rawUrl, '/'));
                                        }
                                        ?>
                                        <div class="notification-item <?= !$notification['is_read'] ? 'unread' : '' ?>" data-id="<?= (int) $notification['id'] ?>"<?= $notifActionUrl !== '' ? ' data-action-url="' . htmlspecialchars($notifActionUrl, ENT_QUOTES) . '"' : '' ?>>
                                            <div class="notification-item-header">
                                                <span class="notification-item-type <?= $notification['type'] ?>">
                                                    <?= $notification['type'] ?>
                                                </span>
                                                <span class="notification-item-time time-ago" data-ts="<?= htmlspecialchars($notification['created_at']) ?>">
                                                    <?= time_elapsed_string($notification['created_at']) ?>
                                                </span>
                                            </div>
                                            <div class="notification-item-content">
                                                <strong><?= htmlspecialchars($notification['title']) ?></strong>
                                                <?php if ($notification['message']): ?>
                                                    <p><?= htmlspecialchars(substr($notification['message'], 0, 100)) ?><?= strlen($notification['message']) > 100 ? '...' : '' ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Admin Menu -->
                    <div class="admin-menu" id="adminMenu">
                        <button class="admin-menu-btn">
                            <div class="admin-menu-avatar">
                                <?= strtoupper(substr($admin['name'], 0, 1)) ?>
                            </div>
                            <div class="admin-menu-info">
                                <div class="admin-menu-name"><?= htmlspecialchars($admin['name']) ?></div>
                                <div class="admin-menu-role"><?= getAdminRoleLabel($admin['role']) ?></div>
                            </div>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        
                        <div class="admin-menu-dropdown" id="adminMenuDropdown">
                            <a href="<?= appUrl('admin/settings.php') ?>" class="admin-menu-item">
                                <i class="fas fa-user"></i>
                                My Profile
                            </a>
                            <a href="<?= appUrl('admin/settings.php') ?>" class="admin-menu-item">
                                <i class="fas fa-cog"></i>
                                Settings
                            </a>
                            <a href="<?= appUrl('pages/login.php') ?>" class="admin-menu-item">
                                <i class="fas fa-home"></i>
                                View Website
                            </a>
                            <a href="<?= appUrl('admin/logout.php') ?>" class="admin-menu-item danger">
                                <i class="fas fa-sign-out-alt"></i>
                                Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Content Area -->
            <div class="content-area">
