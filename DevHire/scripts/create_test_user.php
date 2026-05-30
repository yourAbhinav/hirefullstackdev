<?php
require_once __DIR__ . '/../config/db.php';

// Usage: php create_test_user.php email password [role]
$argv = $_SERVER['argv'];
if (php_sapi_name() !== 'cli' || count($argv) < 3) {
    echo "Usage: php create_test_user.php email password [role]\n";
    exit(1);
}

$email = strtolower(trim($argv[1]));
$password = $argv[2];
$role = $argv[3] ?? 'developer';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Invalid email\n");
    exit(2);
}

try {
    $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();

    if ($existing !== null) {
        echo "User already exists with id: " . $existing['id'] . "\n";
        exit(0);
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $fullName = explode('@', $email)[0];
    $stmt = $conn->prepare('INSERT INTO users (fullName, email, password, role, verified, created_at, updated_at) VALUES (?, ?, ?, ?, 1, NOW(), NOW())');
    $stmt->bind_param('ssss', $fullName, $email, $hash, $role);
    $stmt->execute();
    $insertId = $stmt->insert_id;
    $stmt->close();

    echo "Created user id: " . $insertId . "\n";
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, 'Error: ' . $e->getMessage() . "\n");
    exit(3);
}
