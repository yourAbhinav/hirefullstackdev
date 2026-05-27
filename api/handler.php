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
    if (!isAdminLoggedIn()) {
        jsonResponse(['success' => false, 'message' => 'Forbidden.'], 403);
    }
}

function getJobs(): void
{
    global $conn;

    $page = max(1, (int) ($_GET['page'] ?? 1));
    $limit = 10;
    $offset = ($page - 1) * $limit;

    $stmt = $conn->prepare('SELECT * FROM jobs WHERE status = ? ORDER BY featured DESC, created_at DESC LIMIT ? OFFSET ?');
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

    $userId = (int) currentUserId();
    $stmt = $conn->prepare('SELECT * FROM applications WHERE user_id = ? ORDER BY created_at DESC');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $applications = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();

    jsonResponse(['success' => true, 'applications' => $applications]);
}

function submitApplication(): void
{
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
    }

    requireLoginApi();

    $fullName = sanitize($_POST['full_name'] ?? $_POST['fullName'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $experience = sanitize($_POST['experience'] ?? '');
    $techStack = sanitize($_POST['tech_stack'] ?? $_POST['techStack'] ?? '');
    $jobPosition = sanitize($_POST['jobPosition'] ?? '');
    $portfolio = sanitize($_POST['portfolio'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    $userId = currentUserId();
    $jobId = !empty($_POST['job_id']) ? (int) $_POST['job_id'] : null;

    if ($fullName === '' || $email === '' || !validateEmail($email) || $experience === '' || $techStack === '' || $jobPosition === '') {
        jsonResponse(['success' => false, 'message' => 'Missing required fields.'], 422);
    }

    $duplicateSql = 'SELECT id FROM applications WHERE email = ?';
    $duplicateParams = [$email];
    $duplicateTypes = 's';

    if (!empty($userId)) {
        if (!empty($jobId)) {
            $duplicateSql .= ' AND user_id = ? AND job_id = ?';
            $duplicateParams[] = (int) $userId;
            $duplicateParams[] = (int) $jobId;
            $duplicateTypes .= 'ii';
        } else {
            $duplicateSql .= ' AND user_id = ? AND jobPosition = ?';
            $duplicateParams[] = (int) $userId;
            $duplicateParams[] = $jobPosition;
            $duplicateTypes .= 'is';
        }
    } else {
        $duplicateSql .= ' AND jobPosition = ?';
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

    $fullNameColumn = dbColumnExists($conn, 'applications', 'full_name') ? 'full_name' : 'fullName';
    $techColumn = dbColumnExists($conn, 'applications', 'tech_stack') ? 'tech_stack' : 'techStack';

    $resumeName = '';
    if (!empty($_FILES['resume']['name']) && ($_FILES['resume']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $resumeName = basename($_FILES['resume']['name']);
    }
    $status = 'pending';

    if (!empty($jobId)) {
        $sql = 'INSERT INTO applications (' . implode(', ', [
            $fullNameColumn,
            'email',
            'phone',
            'experience',
            $techColumn,
            'jobPosition',
            'portfolio',
            'message',
            'resume',
            'job_id',
            'user_id',
            'status',
        ]) . ') VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssssssssiis', $fullName, $email, $phone, $experience, $techStack, $jobPosition, $portfolio, $message, $resumeName, $jobId, $userId, $status);
    } else {
        $sql = 'INSERT INTO applications (' . implode(', ', [
            $fullNameColumn,
            'email',
            'phone',
            'experience',
            $techColumn,
            'jobPosition',
            'portfolio',
            'message',
            'resume',
            'user_id',
            'status',
        ]) . ') VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssssssssis', $fullName, $email, $phone, $experience, $techStack, $jobPosition, $portfolio, $message, $resumeName, $userId, $status);
    }

    if ($stmt->execute()) {
        jsonResponse(['success' => true, 'message' => 'Application submitted successfully.']);
    }

    jsonResponse(['success' => false, 'message' => 'Error submitting application.'], 500);
}

function saveJob(): void
{
    requireLoginApi();
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
    }

    $payload = json_decode(file_get_contents('php://input'), true);
    $jobId = (int) ($payload['job_id'] ?? $_POST['job_id'] ?? 0);
    $userId = (int) currentUserId();

    if ($jobId <= 0) {
        jsonResponse(['success' => false, 'message' => 'Invalid job selected.'], 422);
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
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
    }

    $payload = json_decode(file_get_contents('php://input'), true);
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
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
    }

    $payload = json_decode(file_get_contents('php://input'), true);
    $receiverId = (int) ($payload['receiver_id'] ?? $_POST['receiver_id'] ?? 0);
    $subject = sanitize($payload['subject'] ?? $_POST['subject'] ?? '');
    $message = sanitize($payload['message'] ?? $_POST['message'] ?? '');
    $senderId = (int) currentUserId();

    if ($receiverId <= 0 || $message === '') {
        jsonResponse(['success' => false, 'message' => 'Message and recipient are required.'], 422);
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
?>
