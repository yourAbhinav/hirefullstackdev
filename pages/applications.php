<?php
require_once '../includes/helpers.php';
startSecureSession();
header('Location: ' . appUrl('pages/profile.php'));
exit;
