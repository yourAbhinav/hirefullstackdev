<?php
require_once '../config/db.php';
startSecureSession();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        setFlash('error', 'Your session expired. Please reload and try again.');
        header('Location: ' . appUrl('pages/contact.php'));
        exit;
    }

    $name = sanitize($_POST['name'] ?? '');
    $email = normalizeEmail((string) ($_POST['email'] ?? ''));
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    $userId = (int) (currentUserId() ?? 0);

    if ($name === '' || $email === '' || $subject === '' || $message === '') {
        setFlash('error', 'Please fill all fields.');
        header('Location: ' . appUrl('pages/contact.php'));
        exit;
    }

    if (!validateEmail($email)) {
        setFlash('error', 'Invalid email address.');
        header('Location: ' . appUrl('pages/contact.php'));
        exit;
    }

    $status = 'new';
    $columns = ['full_name', 'email', 'subject', 'message', 'status'];
    $types = 'sssss';
    $params = [$name, $email, $subject, $message, $status];

    if ($userId > 0) {
        $columns[] = 'user_id';
        $types .= 'i';
        $params[] = $userId;
    }

    $stmt = $conn->prepare('INSERT INTO contact_messages (' . implode(', ', $columns) . ') VALUES (' . implode(', ', array_fill(0, count($columns), '?')) . ')');
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        $stmt->close();
        setFlash('success', 'Thank you for contacting us. We will get back to you shortly.');
        header('Location: ' . appUrl('pages/contact.php'));
        exit;
    }

    $stmt->close();
    logError('Contact form submission failed', $conn->error ?? '');
    setFlash('error', 'We could not save your message right now.');
    header('Location: ' . appUrl('pages/contact.php'));
    exit;
}

header('Location: ' . appUrl('pages/contact.php'));
exit;

