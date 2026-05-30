<?php
require_once __DIR__ . '/../config/db.php';
$email = $argv[1] ?? 'testuser@example.com';
$stmt = $conn->prepare('SELECT id, email, role, last_login_at, provider FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc() ?: null;
print_r($row);
