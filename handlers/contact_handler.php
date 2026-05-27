<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');

    // Validate
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        header('Location: ../pages/contact.php?error=Please fill all fields');
        exit;
    }

    if (!validateEmail($email)) {
        header('Location: ../pages/contact.php?error=Invalid email address');
        exit;
    }

    // In production, send email or save to database
    // For now, just redirect with success message
    header('Location: ../pages/contact.php?success=Thank you for contacting us');
    exit;
}

header('Location: ../pages/contact.php');
exit;
?>
