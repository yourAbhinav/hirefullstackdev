<?php

require_once '../config/db.php';
startSecureSession();
requireDeveloper();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . appUrl('index.php'));
    exit;
}

if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
    setFlash('error', 'Your session expired. Please reload and try again.');
    header('Location: ' . appUrl('index.php#quick-apply'));
    exit;
}

function applicationUploadResume(array $file, string &$error): string
{
    $error = '';

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return '';
    }

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $error = 'Resume upload failed. Please try again.';
        return '';
    }

    $maxSize = 5 * 1024 * 1024;
    $allowedExtensions = ['pdf', 'doc', 'docx'];
    $allowedMimeTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
    $mimeType = '';

    if (($file['size'] ?? 0) > $maxSize) {
        $error = 'Resume must be 5 MB or smaller.';
        return '';
    }

    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mimeType = (string) finfo_file($finfo, (string) $file['tmp_name']);
            finfo_close($finfo);
        }
    }

    if (!in_array($extension, $allowedExtensions, true) || ($mimeType !== '' && !in_array($mimeType, $allowedMimeTypes, true))) {
        $error = 'Only PDF, DOC, or DOCX files are allowed.';
        return '';
    }

    $uploadDir = __DIR__ . '/../uploads/resumes/';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        $error = 'Unable to prepare the resume upload directory.';
        return '';
    }

    $storedFileName = bin2hex(random_bytes(16)) . '.' . $extension;
    $destination = $uploadDir . $storedFileName;

    if (!is_uploaded_file((string) $file['tmp_name']) || !move_uploaded_file((string) $file['tmp_name'], $destination)) {
        $error = 'Unable to save resume file.';
        return '';
    }

    return 'uploads/resumes/' . $storedFileName;
}

function applicationRedirect(string $messageKey, string $message, string $redirectTo = 'index.php#quick-apply'): void
{
    setFlash($messageKey, $message);

    if (!preg_match('#^(index\.php|pages/apply\.php)([?#][A-Za-z0-9_=&%./-]*)?$#', $redirectTo)) {
        $redirectTo = 'index.php#quick-apply';
    }

    header('Location: ' . appUrl($redirectTo));
    exit;
}

$currentUserId = (int) (currentUserId() ?? 0);
$currentName = currentUserName();
$currentEmail = currentUserEmail();
$redirectTo = trim((string) ($_POST['redirect_to'] ?? 'index.php#quick-apply'));

$fullName = sanitize($_POST['full_name'] ?? '');
$email = normalizeEmail((string) ($_POST['email'] ?? ''));
$phone = sanitize($_POST['phone'] ?? '');
$experience = sanitize($_POST['experience'] ?? '');
$techStack = sanitize($_POST['tech_stack'] ?? '');
$jobPosition = sanitize($_POST['job_position'] ?? '');
$portfolioUrl = sanitize($_POST['portfolio_url'] ?? '');
$message = sanitize($_POST['message'] ?? '');
$jobId = !empty($_POST['job_id']) ? (int) $_POST['job_id'] : null;
$resumePath = '';
$errors = [];

if ($fullName === '') {
    $errors[] = 'Full name is required.';
}

if ($email === '' || !validateEmail($email)) {
    $errors[] = 'A valid email address is required.';
}

if ($phone === '') {
    $errors[] = 'Phone number is required.';
}

if ($experience === '') {
    $errors[] = 'Experience level is required.';
}

if ($techStack === '') {
    $errors[] = 'Tech stack is required.';
}

$selectedJob = null;
if ($jobId !== null && $jobId > 0) {
    $jobStmt = $conn->prepare('SELECT id, title, status FROM jobs WHERE id = ? LIMIT 1');
    $jobStmt->bind_param('i', $jobId);
    $jobStmt->execute();
    $selectedJob = $jobStmt->get_result()->fetch_assoc() ?: null;
    $jobStmt->close();

    if (empty($selectedJob) || ($selectedJob['status'] ?? '') !== 'active') {
        $errors[] = 'The selected job is not available for applications.';
        $jobId = null;
    } else {
        $jobPosition = (string) ($selectedJob['title'] ?? $jobPosition);
    }
}

if ($jobPosition === '') {
    $jobPosition = 'General Application';
}

if (!empty($_FILES['resume']['name']) && ($_FILES['resume']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    $resumeUploadError = '';
    $resumePath = applicationUploadResume($_FILES['resume'], $resumeUploadError);
    if ($resumeUploadError !== '') {
        $errors[] = $resumeUploadError;
    }
}

if (!empty($errors)) {
    applicationRedirect('error', implode(' ', $errors), $redirectTo);
}

$duplicateSql = 'SELECT id FROM applications WHERE ';
$duplicateParams = [];
$duplicateTypes = '';

if ($currentUserId > 0) {
    $duplicateSql .= 'user_id = ?';
    $duplicateParams[] = $currentUserId;
    $duplicateTypes .= 'i';
} else {
    $duplicateSql .= 'email = ?';
    $duplicateParams[] = $email;
    $duplicateTypes .= 's';
}

if ($jobId !== null) {
    $duplicateSql .= ' AND job_id = ?';
    $duplicateParams[] = $jobId;
    $duplicateTypes .= 'i';
} else {
    $duplicateSql .= ' AND job_position = ?';
    $duplicateParams[] = $jobPosition;
    $duplicateTypes .= 's';
}

$duplicateSql .= ' LIMIT 1';
$duplicateStmt = $conn->prepare($duplicateSql);
if (!empty($duplicateParams)) {
    $duplicateStmt->bind_param($duplicateTypes, ...$duplicateParams);
}
$duplicateStmt->execute();
$duplicateExists = $duplicateStmt->get_result()->num_rows > 0;
$duplicateStmt->close();

if ($duplicateExists) {
    applicationRedirect('error', 'You have already submitted an application for this position.', $redirectTo);
}

$status = 'pending';
$userIdValue = $currentUserId > 0 ? $currentUserId : null;

$columns = [
    'full_name',
    'email',
    'phone',
    'experience',
    'tech_stack',
    'job_position',
    'portfolio_url',
    'message',
    'resume_path',
    'status',
];
$types = 'ssssssssss';
$params = [
    $fullName,
    $email,
    $phone,
    $experience,
    $techStack,
    $jobPosition,
    $portfolioUrl,
    $message,
    $resumePath,
    $status,
];

if ($jobId !== null) {
    $columns[] = 'job_id';
    $types .= 'i';
    $params[] = $jobId;
}

if ($userIdValue !== null) {
    $columns[] = 'user_id';
    $types .= 'i';
    $params[] = $userIdValue;
}

$sql = 'INSERT INTO applications (' . implode(', ', $columns) . ') VALUES (' . implode(', ', array_fill(0, count($columns), '?')) . ')';
$stmt = $conn->prepare($sql);

if (!$stmt) {
    if (!empty($resumePath)) {
        @unlink(__DIR__ . '/../' . $resumePath);
    }
    applicationRedirect('error', 'Database error. Please try again.', $redirectTo);
}

$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    $stmt->close();

    // increment job applications count when associated with a job
    if ($jobId !== null && $jobId > 0) {
        $incStmt = $conn->prepare('UPDATE jobs SET applications_count = applications_count + 1 WHERE id = ?');
        if ($incStmt) {
            $incStmt->bind_param('i', $jobId);
            $incStmt->execute();
            $incStmt->close();
        }
    }

    applicationRedirect('success', 'Your application has been submitted successfully.', $redirectTo);
}

if (!empty($resumePath)) {
    @unlink(__DIR__ . '/../' . $resumePath);
}

$stmt->close();
applicationRedirect('error', 'Could not save application. Please try again.', $redirectTo);