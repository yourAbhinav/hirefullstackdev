<?php
require_once '../config/db.php';
require_once '../includes/admin_helpers.php';

adminLogout($conn);

header('Location: ' . appUrl('admin/login.php'));
exit;
