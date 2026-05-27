<?php
// handlers/apply.php  - handles the Quick Apply form submitted from index.php

require_once '../config/db.php';   // also loads helpers.php via db.php (ensure helpers included there)
                                   // include helpers explicitly in case db.php doesn't
require_once '../includes/helpers.php';

// Only handle POST; redirect back for anything else
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /hieringfullstackdeveloper/DevHire/index.php');
    exit;
}

/* Collect & sanitize */
$full_name  = sanitize($_POST['full_name']  ?? '');
$email      = sanitize($_POST['email']      ?? '');
$experience = sanitize($_POST['experience'] ?? '');
$tech_stack = sanitize($_POST['tech_stack'] ?? '');
$resume     = '';

/* Validate */
$errors = [];
if (empty($full_name))            $errors[] = 'Full name is required.';
if (empty($email))                $errors[] = 'Email is required.';
elseif (!validateEmail($email))   $errors[] = 'Invalid email address.';
if (empty($experience))           $errors[] = 'Please select your experience.';
if (empty($tech_stack))           $errors[] = 'Please select your tech stack.';

if (!empty($errors)) {
    // Redirect back with error message instead of die()
    $msg = urlencode(implode(' ', $errors));
    header("Location: /hieringfullstackdeveloper/DevHire/index.php?apply_error={$msg}#quick-apply");
    exit;
}

/* Resume upload */
if (
    isset($_FILES['resume']) &&
    $_FILES['resume']['error'] === UPLOAD_ERR_OK
) {
    $uploadDir = '../uploads/resumes/';

    // Use 0755 not 0777
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Whitelist allowed extensions
    $allowedExts = ['pdf', 'doc', 'docx'];
    $ext = strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExts)) {
        header('Location: /hieringfullstackdeveloper/DevHire/index.php?apply_error=' . urlencode('Only PDF, DOC, DOCX files allowed.') . '#quick-apply');
        exit;
    }

    $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['resume']['name']));
    if (move_uploaded_file($_FILES['resume']['tmp_name'], $uploadDir . $fileName)) {
        $resume = $fileName;
    }
}

/* Insert into DB */
$sql = "INSERT INTO applications
            (full_name, email, experience, tech_stack, resume, status)
        VALUES
            (?, ?, ?, ?, ?, 'Pending')";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    // Log the real error, show friendly message to user
    error_log('DB Prepare Error: ' . $conn->error);
    header('Location: /hieringfullstackdeveloper/DevHire/index.php?apply_error=' . urlencode('Database error. Please try again.') . '#quick-apply');
    exit;
}

$stmt->bind_param("sssss", $full_name, $email, $experience, $tech_stack, $resume);

if (!$stmt->execute()) {
    error_log('DB Execute Error: ' . $stmt->error);
    header('Location: /hieringfullstackdeveloper/DevHire/index.php?apply_error=' . urlencode('Could not save application. Please try again.') . '#quick-apply');
    exit;
}

$stmt->close();

/* Success: redirect back with success flag */
header('Location: /hieringfullstackdeveloper/DevHire/index.php?apply_success=1#quick-apply');
exit;
