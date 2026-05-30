<?php

require_once '../config/db.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'getJobs':
        getJobs();
        break;
    case 'getDevelopers':
        getDevelopers();
        break;
    case 'getApplications':
        getApplications();
        break;
    case 'submitApplication':
        submitApplication();
        break;
    case 'saveJob':
        saveJob();
        break;
    case 'getSavedJobs':
        getSavedJobs();
        break;
    case 'removeSavedJob':
        removeSavedJob();
        break;
    case 'sendMessage':
        sendMessage();
        break;
    case 'getMessages':
        getMessages();
        break;
    default:
        jsonResponse(['success' => false, 'message' => 'Invalid action.'], 400);
}

function jsonResponse(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

function requireLoginApi(): void
{
    if (!isLoggedIn()) {
        jsonResponse(['success' => false, 'message' => 'Unauthorized.'], 401);
    }
}

function requireAdminApi(): void
{
    if (!isAdmin()) {
        jsonResponse(['success' => false, 'message' => 'Forbidden.'], 403);
    }
}

function requireDeveloperApi(): void
{
    if (!isDeveloper()) {
        jsonResponse(['success' => false, 'message' => 'Forbidden.'], 403);
    }
}

function apiRequestPayload(): array
{
    static $payload = null;

    if (is_array($payload)) {
        return $payload;
    }

    $requestBody = json_decode((string) file_get_contents('php://input'), true);
    $payload = is_array($requestBody) ? $requestBody : $_POST;

    return $payload;
}

function requireApiCsrf(): void
{
    $payload = apiRequestPayload();
    $token = $payload['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);

    if (!verifyCsrf(is_string($token) ? $token : null)) {
        jsonResponse(['success' => false, 'message' => 'Your session expired. Please reload and try again.'], 403);
    }
}

function getJobs(): void
{
    global $conn;

    $page = max(1, (int) ($_GET['page'] ?? 1));
    $limit = 10;
    $offset = ($page - 1) * $limit;

    // select explicit columns to avoid accidentally exposing new fields
    $stmt = $conn->prepare('SELECT id, title, company_id, location, job_type, work_mode, salary_min, salary_max, experience_level, tech_stack, applications_count, status, featured, created_at, updated_at FROM jobs WHERE status = ? ORDER BY featured DESC, created_at DESC LIMIT ? OFFSET ?');
    $status = 'active';
    $stmt->bind_param('sii', $status, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $jobs = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();

    jsonResponse(['success' => true, 'jobs' => $jobs]);
}

function getDevelopers(): void
{
    global $conn;

    $page = max(1, (int) ($_GET['page'] ?? 1));
    $limit = 12;
    $offset = ($page - 1) * $limit;

    $stmt = $conn->prepare('SELECT id, fullName, experience, techStack, portfolio_url FROM users WHERE role = ? AND verified = 1 ORDER BY created_at DESC LIMIT ? OFFSET ?');
    $role = 'developer';
    $stmt->bind_param('sii', $role, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $developers = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();

    jsonResponse(['success' => true, 'developers' => $developers]);
}

function getApplications(): void
{
    requireLoginApi();
    global $conn;

    if (isAdmin()) {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = (int) ($_GET['limit'] ?? 200);
        $limit = max(1, min(200, $limit));
        $offset = ($page - 1) * $limit;

        // Admin view: select explicit columns but avoid exposing sensitive fields like resume_path, phone, email, message, feedback
        $stmt = $conn->prepare('SELECT id, full_name, job_position, job_id, user_id, status, rating, created_at, updated_at FROM applications ORDER BY created_at DESC LIMIT ? OFFSET ?');
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $applications = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();

        jsonResponse([
            'success' => true,
            'applications' => $applications,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'count' => count($applications),
            ],
        ]);
    }

    // Only developer accounts should access personal application history.
    requireDeveloperApi();

    $userId = (int) currentUserId();
    $stmt = $conn->prepare('SELECT id, full_name, email, phone, experience, tech_stack, job_position, portfolio_url, message, resume_path, job_id, user_id, status, rating, feedback, created_at, updated_at FROM applications WHERE user_id = ? ORDER BY created_at DESC');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $applications = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();

    jsonResponse(['success' => true, 'applications' => $applications]);
}

function storeResumeUpload(array $file, string &$error): string
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

    $fileName = bin2hex(random_bytes(16)) . '.' . $extension;
    if (!is_uploaded_file((string) $file['tmp_name']) || !move_uploaded_file((string) $file['tmp_name'], $uploadDir . $fileName)) {
        $error = 'Unable to save resume file.';
        return '';
    }

    return 'uploads/resumes/' . $fileName;
}

function findActiveJob(mysqli $conn, int $jobId): ?array
{
    $stmt = $conn->prepare('SELECT id, title, status FROM jobs WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $jobId);
    $stmt->execute();
    $job = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();

    if (empty($job) || ($job['status'] ?? '') !== 'active') {
        return null;
    }

    return $job;
}

function submitApplication(): void
{
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
    }

    requireLoginApi();
    requireDeveloperApi();
    requireApiCsrf();

    $payload = apiRequestPayload();

    $fullName = sanitize($payload['full_name'] ?? '');
    $email = normalizeEmail((string) ($payload['email'] ?? ''));
    $phone = sanitize($payload['phone'] ?? '');
    $experience = sanitize($payload['experience'] ?? '');
    $techStack = sanitize($payload['tech_stack'] ?? '');
    $jobPosition = sanitize($payload['job_position'] ?? '');
    $portfolio = sanitize($payload['portfolio_url'] ?? '');
    $message = sanitize($payload['message'] ?? '');
    $userId = (int) currentUserId();
    $jobId = !empty($payload['job_id']) ? (int) $payload['job_id'] : null;

    if ($jobId !== null) {
        $job = findActiveJob($conn, $jobId);
        if ($job === null) {
            jsonResponse(['success' => false, 'message' => 'The selected job is not available.'], 422);
        }

        $jobPosition = (string) ($job['title'] ?? $jobPosition);
    }

    if ($fullName === '' || $email === '' || !validateEmail($email) || $phone === '' || $experience === '' || $techStack === '' || $jobPosition === '') {
        jsonResponse(['success' => false, 'message' => 'Missing required fields.'], 422);
    }

    $duplicateSql = 'SELECT id FROM applications WHERE user_id = ?';
    $duplicateParams = [$userId];
    $duplicateTypes = 'i';

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
    $duplicateStmt->bind_param($duplicateTypes, ...$duplicateParams);
    $duplicateStmt->execute();
    $duplicateExists = $duplicateStmt->get_result()->num_rows > 0;
    $duplicateStmt->close();

    if ($duplicateExists) {
        jsonResponse(['success' => false, 'message' => 'You already submitted this application.'], 409);
    }

    $resumeError = '';
    $resumePath = !empty($payload['resume']) ? '' : '';
    $resumePath = !empty($_FILES['resume']['name'] ?? '') ? storeResumeUpload($_FILES['resume'], $resumeError) : '';
    if ($resumeError !== '') {
        jsonResponse(['success' => false, 'message' => $resumeError], 422);
    }

    $status = 'pending';
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
        'user_id',
        'status',
    ];

    $placeholders = '?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?';
    $types = 'sssssssssis';
    $params = [
        $fullName,
        $email,
        $phone,
        $experience,
        $techStack,
        $jobPosition,
        $portfolio,
        $message,
        $resumePath,
        $userId,
        $status,
    ];

    if ($jobId !== null && dbColumnExists($conn, 'applications', 'job_id')) {
        $columns[] = 'job_id';
        $placeholders = '?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?';
        $types = 'sssssssssisi';
        $params[] = $jobId;
    }

    $sql = 'INSERT INTO applications (' . implode(', ', $columns) . ') VALUES (' . $placeholders . ')';
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        if (!empty($resumePath)) {
            @unlink(__DIR__ . '/../' . $resumePath);
        }
        jsonResponse(['success' => false, 'message' => 'Error submitting application.'], 500);
    }

    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        $stmt->close();

        // increment applications_count for the associated job, if any
        if ($jobId !== null && $jobId > 0) {
            $inc = $conn->prepare('UPDATE jobs SET applications_count = applications_count + 1 WHERE id = ?');
            if ($inc) {
                $inc->bind_param('i', $jobId);
                $inc->execute();
                $inc->close();
            }
        }

        jsonResponse(['success' => true, 'message' => 'Application submitted successfully.']);
    }

    if (!empty($resumePath)) {
        @unlink(__DIR__ . '/../' . $resumePath);
    }

    $stmt->close();
    jsonResponse(['success' => false, 'message' => 'Error submitting application.'], 500);
}

function saveJob(): void
{
    requireLoginApi();
    requireDeveloperApi();
    requireApiCsrf();
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
    }

    $payload = apiRequestPayload();
    $jobId = (int) ($payload['job_id'] ?? $_POST['job_id'] ?? 0);
    $userId = (int) currentUserId();

    if ($jobId <= 0) {
        jsonResponse(['success' => false, 'message' => 'Invalid job selected.'], 422);
    }

    if (findActiveJob($conn, $jobId) === null) {
        jsonResponse(['success' => false, 'message' => 'Job not found or no longer active.'], 404);
    }

    $stmt = $conn->prepare('INSERT IGNORE INTO saved_jobs (user_id, job_id, created_at) VALUES (?, ?, NOW())');
    $stmt->bind_param('ii', $userId, $jobId);
    $stmt->execute();
    $stmt->close();

    jsonResponse(['success' => true, 'saved' => true, 'message' => 'Job saved successfully.']);
}

function getSavedJobs(): void
{
    requireLoginApi();
    requireDeveloperApi();
    global $conn;

    $userId = (int) currentUserId();
    $stmt = $conn->prepare('SELECT sj.id, sj.job_id, sj.created_at, j.title, j.company_id, j.location, j.job_type, j.work_mode, j.salary_min, j.salary_max, j.experience_level, j.tech_stack FROM saved_jobs sj LEFT JOIN jobs j ON j.id = sj.job_id WHERE sj.user_id = ? ORDER BY sj.created_at DESC');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $savedJobs = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();

    jsonResponse(['success' => true, 'saved_jobs' => $savedJobs]);
}

function removeSavedJob(): void
{
    requireLoginApi();
    requireDeveloperApi();
    requireApiCsrf();
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
    }

    $payload = apiRequestPayload();
    $jobId = (int) ($payload['job_id'] ?? $_POST['job_id'] ?? 0);
    $userId = (int) currentUserId();

    if ($jobId <= 0) {
        jsonResponse(['success' => false, 'message' => 'Invalid job selected.'], 422);
    }

    $stmt = $conn->prepare('DELETE FROM saved_jobs WHERE user_id = ? AND job_id = ?');
    $stmt->bind_param('ii', $userId, $jobId);
    $stmt->execute();
    $stmt->close();

    jsonResponse(['success' => true, 'saved' => false, 'message' => 'Job removed from saved list.']);
}

function sendMessage(): void
{
    requireLoginApi();
    requireDeveloperApi();
    requireApiCsrf();
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
    }

    $payload = apiRequestPayload();
    $receiverId = (int) ($payload['receiver_id'] ?? $_POST['receiver_id'] ?? 0);
    $subject = sanitize($payload['subject'] ?? $_POST['subject'] ?? '');
    $message = sanitize($payload['message'] ?? $_POST['message'] ?? '');
    $senderId = (int) currentUserId();

    if ($receiverId <= 0 || $message === '') {
        jsonResponse(['success' => false, 'message' => 'Message and recipient are required.'], 422);
    }

    // Messages are stored against user IDs only; receiver must be a valid user account.
    $receiverStmt = $conn->prepare('SELECT id FROM users WHERE id = ? LIMIT 1');
    $receiverStmt->bind_param('i', $receiverId);
    $receiverStmt->execute();
    $receiverExists = $receiverStmt->get_result()->num_rows > 0;
    $receiverStmt->close();

    if (!$receiverExists) {
        jsonResponse(['success' => false, 'message' => 'Invalid message recipient.'], 422);
    }

    $stmt = $conn->prepare('INSERT INTO messages (sender_id, receiver_id, subject, message, read_status, created_at) VALUES (?, ?, ?, ?, 0, NOW())');
    $stmt->bind_param('iiss', $senderId, $receiverId, $subject, $message);
    $stmt->execute();
    $stmt->close();

    jsonResponse(['success' => true, 'message' => 'Message sent successfully.']);
}

function getMessages(): void
{
    requireAdminApi();
    global $conn;

    $stmt = $conn->prepare('SELECT m.id, m.subject, m.message, m.read_status, m.created_at, s.fullName AS sender_name, s.email AS sender_email, r.fullName AS receiver_name, r.email AS receiver_email FROM messages m LEFT JOIN users s ON s.id = m.sender_id LEFT JOIN users r ON r.id = m.receiver_id ORDER BY m.created_at DESC LIMIT 100');
    $stmt->execute();
    $result = $stmt->get_result();
    $messages = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();

    jsonResponse(['success' => true, 'messages' => $messages]);
}
