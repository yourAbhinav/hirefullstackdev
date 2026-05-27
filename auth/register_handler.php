<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = sanitize($_POST['fullName'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = sanitize($_POST['password'] ?? '');
    $confirmPassword = sanitize($_POST['confirmPassword'] ?? '');
    $accountType = sanitize($_POST['accountType'] ?? '');

    // Validate
    $errors = [];
    if (empty($fullName)) $errors[] = 'Full name is required';
    if (empty($email) || !validateEmail($email)) $errors[] = 'Valid email is required';
    if (empty($password) || strlen($password) < 6) $errors[] = 'Password must be at least 6 characters';
    if ($password !== $confirmPassword) $errors[] = 'Passwords do not match';
    if (empty($accountType) || !in_array($accountType, ['developer', 'company'])) $errors[] = 'Valid account type is required';

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header('Location: ../pages/register.php');
        exit;
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $_SESSION['error'] = 'Email already registered';
        header('Location: ../pages/register.php');
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
        header('Location: ../pages/login.php');
        exit;
    } else {
        $_SESSION['error'] = 'Error creating account. Please try again.';
        logError('Registration error', $stmt->error);
        header('Location: ../pages/register.php');
        exit;
    }
}

header('Location: ../pages/register.php');
exit;
?>
