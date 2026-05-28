<?php

require_once '../config/db.php';
startSecureSession();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        $_SESSION['error'] = 'Your session expired. Please reload and try again.';
        header('Location: ' . appUrl('pages/register.php'));
        exit;
    }

    $fullName = trim((string) ($_POST['fullName'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirmPassword'] ?? '');
    $accountType = strtolower(trim((string) ($_POST['accountType'] ?? '')));

    // Validate
    $errors = [];
    if (empty($fullName)) $errors[] = 'Full name is required';
    if (empty($email) || !validateEmail($email)) $errors[] = 'Valid email is required';
    if (empty($password) || strlen($password) < 6) $errors[] = 'Password must be at least 6 characters';
    if ($password !== $confirmPassword) $errors[] = 'Passwords do not match';
    if (empty($accountType) || !in_array($accountType, ['developer', 'company'])) $errors[] = 'Valid account type is required';

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header('Location: ' . appUrl('pages/register.php'));
        exit;
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $_SESSION['error'] = 'Email already registered';
        header('Location: ' . appUrl('pages/register.php'));
        exit;
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (fullName, email, password, role) VALUES (?, ?, ?, ?)");
    $role = ($accountType === 'developer') ? 'developer' : 'company';
    $stmt->bind_param("ssss", $fullName, $email, $hashedPassword, $role);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Account created successfully! Please login.';
        header('Location: ' . appUrl('pages/login.php'));
        exit;
    } else {
        $_SESSION['error'] = 'Error creating account. Please try again.';
        logError('Registration error', $stmt->error);
        header('Location: ' . appUrl('pages/register.php'));
        exit;
    }
}

header('Location: ' . appUrl('pages/register.php'));
exit;
?>
