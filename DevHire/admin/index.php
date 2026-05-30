<?php
require_once '../config/db.php';
require_once '../includes/admin_helpers.php';

if (isAdminLoggedIn()) {
    header('Location: ' . appUrl('admin/dashboard.php'));
    exit;
}

header('Location: ' . appUrl('admin/login.php'));
exit;