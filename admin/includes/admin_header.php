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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: #1a1a2e;
            color: white;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            overflow-y: auto;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            padding: 30px 25px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header .logo {
            font-size: 24px;
            font-weight: 700;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-header .logo i {
            color: #4F46E5;
        }

        .sidebar-nav {
            padding: 20px 0;
        }

        .nav-section {
            margin-bottom: 30px;
        }

        .nav-section-title {
            padding: 0 25px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255,255,255,0.5);
            margin-bottom: 15px;
        }

        .nav-item {
            padding: 12px 25px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s;
            font-size: 14px;
            border-left: 3px solid transparent;
        }

        .nav-item:hover,
        .nav-item.active {
            background: rgba(79, 70, 229, 0.2);
            color: white;
            border-left-color: #4F46E5;
        }

        .nav-item i {
            width: 18px;
            text-align: center;
        }

        .nav-badge {
            margin-left: auto;
            background: #4F46E5;
            color: white;
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 10px;
            font-weight: 600;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            display: flex;
            flex-direction: column;
        }

        /* Top Bar */
        .top-bar {
            background: white;
            padding: 20px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #e5e7eb;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .top-bar-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: #4F46E5;
            font-size: 24px;
            cursor: pointer;
        }

        .page-title {
            font-size: 24px;
            font-weight: 700;
            color: #1a1a2e;
        }

        .top-bar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .notifications {
            position: relative;
            cursor: pointer;
        }

        .notifications-btn {
            background: none;
            border: none;
            color: #4F46E5;
            font-size: 20px;
            cursor: pointer;
            position: relative;
        }

        .notifications-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #DC2626;
            color: white;
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: 600;
            display: <?= $unreadCount > 0 ? 'block' : 'none' ?>;
        }

        .notifications-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            width: 350px;
            margin-top: 15px;
            display: none;
            z-index: 1000;
        }

        .notifications-dropdown.show {
            display: block;
        }

        .notifications-header {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notifications-header h3 {
            font-size: 16px;
            font-weight: 600;
            color: #1a1a2e;
        }

        .notifications-header .mark-read {
            background: none;
            border: none;
            color: #4F46E5;
            font-size: 13px;
            cursor: pointer;
            font-weight: 500;
        }

        .notifications-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .notification-item {
            padding: 15px 20px;
            border-bottom: 1px solid #f3f4f6;
            cursor: pointer;
            transition: background 0.2s;
        }

        .notification-item:hover {
            background: #f8fafc;
        }

        .notification-item.unread {
            background: #fef3f7;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .notification-item-type {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            padding: 2px 8px;
            border-radius: 4px;
        }

        .notification-item-type.info {
            background: #DBEAFE;
            color: #1D4ED8;
        }

        .notification-item-type.success {
            background: #D1FAE5;
            color: #059669;
        }

        .notification-item-type.warning {
            background: #FEF3C7;
            color: #D97706;
        }

        .notification-item-type.error {
            background: #FEE2E2;
            color: #DC2626;
        }

        .notification-item-type.security {
            background: #F3E8FF;
            color: #7C3AED;
        }

        .notification-item-time {
            font-size: 12px;
            color: #666;
        }

        .notification-item-content {
            font-size: 14px;
            color: #333;
        }

        .notifications-empty {
            padding: 40px 20px;
            text-align: center;
            color: #666;
        }

        .notifications-empty i {
            font-size: 48px;
            color: #d1d5db;
            margin-bottom: 15px;
        }

        .admin-menu {
            position: relative;
        }

        .admin-menu-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 8px;
            transition: background 0.2s;
        }

        .admin-menu-btn:hover {
            background: #f3f4f6;
        }

        .admin-menu-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
        }

        .admin-menu-info {
            text-align: left;
            display: none;
        }

        @media (min-width: 1200px) {
            .admin-menu-info {
                display: block;
            }
        }

        .admin-menu-name {
            font-weight: 600;
            font-size: 14px;
            color: #1a1a2e;
        }

        .admin-menu-role {
            font-size: 12px;
            color: #666;
        }

        .admin-menu-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            width: 220px;
            margin-top: 15px;
            display: none;
            z-index: 1000;
        }

        .admin-menu-dropdown.show {
            display: block;
        }

        .admin-menu-item {
            padding: 12px 20px;
            color: #333;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            transition: background 0.2s;
        }

        .admin-menu-item:hover {
            background: #f8fafc;
        }

        .admin-menu-item.danger {
            color: #DC2626;
        }

        .admin-menu-item.danger:hover {
            background: #FEF2F2;
        }

        /* Content Area */
        .content-area {
            padding: 30px;
            flex: 1;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .mobile-menu-btn {
                display: block;
            }

            .content-area {
                padding: 20px;
            }
        }

        @media (max-width: 768px) {
            .top-bar {
                padding: 15px 20px;
            }

            .page-title {
                font-size: 20px;
            }

            .content-area {
                padding: 15px;
            }

            .notifications-dropdown {
                width: 300px;
                right: -50px;
            }
        }

        /* Overlay for mobile menu */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }

        .sidebar-overlay.show {
            display: block;
        }

        @media (max-width: 1200px) {
            .sidebar-overlay.show {
                display: block;
            }
        }
    </style>
</head>
<body>
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
                                        <div class="notification-item <?= !$notification['is_read'] ? 'unread' : '' ?>" data-id="<?= $notification['id'] ?>">
                                            <div class="notification-item-header">
                                                <span class="notification-item-type <?= $notification['type'] ?>">
                                                    <?= $notification['type'] ?>
                                                </span>
                                                <span class="notification-item-time">
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
                            <a href="#" class="admin-menu-item">
                                <i class="fas fa-user"></i>
                                My Profile
                            </a>
                            <?php if ($admin['role'] === 'super_admin'): ?>
                            <a href="#" class="admin-menu-item">
                                <i class="fas fa-cog"></i>
                                Account Settings
                            </a>
                            <?php endif; ?>
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
                <script>
                // Mobile menu toggle
                const mobileMenuBtn = document.getElementById('mobileMenuBtn');
                const sidebar = document.getElementById('sidebar');
                const sidebarOverlay = document.createElement('div');
                sidebarOverlay.className = 'sidebar-overlay';
                document.body.appendChild(sidebarOverlay);

                mobileMenuBtn.addEventListener('click', () => {
                    sidebar.classList.toggle('open');
                    sidebarOverlay.classList.toggle('show');
                });

                sidebarOverlay.addEventListener('click', () => {
                    sidebar.classList.remove('open');
                    sidebarOverlay.classList.remove('show');
                });

                // Notifications dropdown
                const notifications = document.getElementById('notifications');
                const notificationsDropdown = document.getElementById('notificationsDropdown');

                notifications.addEventListener('click', (e) => {
                    e.stopPropagation();
                    notificationsDropdown.classList.toggle('show');
                });

                // Admin menu dropdown
                const adminMenu = document.getElementById('adminMenu');
                const adminMenuDropdown = document.getElementById('adminMenuDropdown');

                adminMenu.addEventListener('click', (e) => {
                    e.stopPropagation();
                    adminMenuDropdown.classList.toggle('show');
                });

                // Close dropdowns when clicking outside
                document.addEventListener('click', () => {
                    notificationsDropdown.classList.remove('show');
                    adminMenuDropdown.classList.remove('show');
                });

                // Mark notification as read
                document.querySelectorAll('.notification-item').forEach(item => {
                    item.addEventListener('click', function() {
                        const notificationId = this.dataset.id;
                        
                        fetch('<?= appUrl('admin/api/notification_api.php') ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                action: 'mark_read',
                                notification_id: notificationId
                            })
                        }).then(response => response.json())
                          .then(data => {
                              if (data.success) {
                                  this.classList.remove('unread');
                                  const badge = document.querySelector('.notifications-badge');
                                  const currentCount = parseInt(badge.textContent);
                                  badge.textContent = Math.max(0, currentCount - 1);
                                  badge.style.display = currentCount > 1 ? 'block' : 'none';
                              }
                          });
                        
                        // Navigate if action URL exists
                        const actionUrl = this.dataset.action;
                        if (actionUrl) {
                            window.location.href = actionUrl;
                        }
                    });
                });

                function markAllAsRead() {
                    fetch('<?= appUrl('admin/api/notification_api.php') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'mark_all_read'
                        })
                    }).then(response => response.json())
                      .then(data => {
                          if (data.success) {
                              document.querySelectorAll('.notification-item').forEach(item => {
                                  item.classList.remove('unread');
                              });
                              const badge = document.querySelector('.notifications-badge');
                              badge.textContent = '0';
                              badge.style.display = 'none';
                          }
                      });
                }

                // Time elapsed helper
                function time_elapsed_string($datetime) {
                    // This would be handled server-side
                }
                </script>
