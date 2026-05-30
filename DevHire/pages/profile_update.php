<?php

require_once '../config/db.php';
requireDeveloper();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . appUrl('pages/profile.php'));
    exit;
}

if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
    setFlash('error', 'Your session expired. Please try again.');
    header('Location: ' . appUrl('pages/profile.php'));
    exit;
}

$userId = (int) currentUserId();

$fullName = trim((string) ($_POST['fullName'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));
$experience = trim((string) ($_POST['experience'] ?? ''));
$techStack = trim((string) ($_POST['techStack'] ?? ''));
$portfolioUrl = trim((string) ($_POST['portfolio_url'] ?? ''));
$bio = trim((string) ($_POST['bio'] ?? ''));
$profileImage = trim((string) ($_POST['profile_image'] ?? ''));

$errors = [];

if ($fullName === '' || strlen($fullName) < 2) {
    $errors[] = 'Please enter your full name.';
}

if ($phone !== '' && strlen($phone) < 7) {
    $errors[] = 'Please enter a valid phone number.';
}

if ($portfolioUrl !== '' && filter_var($portfolioUrl, FILTER_VALIDATE_URL) === false) {
    $errors[] = 'Please enter a valid portfolio URL.';
}

if (!empty($errors)) {
    setFlash('error', implode(' ', $errors));
    header('Location: ' . appUrl('pages/profile.php'));
    exit;
}

$stmt = $conn->prepare('UPDATE users SET fullName = ?, phone = ?, experience = ?, techStack = ?, portfolio_url = ?, bio = ?, profile_image = COALESCE(NULLIF(?, \'\'), profile_image), updated_at = NOW() WHERE id = ? LIMIT 1');
if (!$stmt) {
    setFlash('error', 'Unable to update your profile right now.');
    header('Location: ' . appUrl('pages/profile.php'));
    exit;
}

$stmt->bind_param('sssssssi', $fullName, $phone, $experience, $techStack, $portfolioUrl, $bio, $profileImage, $userId);

if (!$stmt->execute()) {
    $stmt->close();
    setFlash('error', 'Unable to update your profile right now.');
    header('Location: ' . appUrl('pages/profile.php'));
    exit;
}

$stmt->close();

$_SESSION['user_name'] = $fullName;
$_SESSION['fullName'] = $fullName;
$_SESSION['user_phone'] = $phone;

setFlash('success', 'Profile updated successfully.');
header('Location: ' . appUrl('pages/profile.php'));
exit;