<?php
require_once '../config/db.php';
require_once '../includes/admin_helpers.php';
require_once '../includes/helpers.php';

requireAdminLogin();
requireAdminPermission($conn, 'view_applications');

$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$dateFilter = $_GET['date_range'] ?? '';
$jobFilter = $_GET['job_id'] ?? '';
$exportAll = !empty($_GET['all']);

$search = htmlspecialchars(strip_tags(trim($search)), ENT_QUOTES, 'UTF-8');
$statusFilter = htmlspecialchars(strip_tags(trim($statusFilter)), ENT_QUOTES, 'UTF-8');
$dateFilter = htmlspecialchars(strip_tags(trim($dateFilter)), ENT_QUOTES, 'UTF-8');
$jobFilter = (int) $jobFilter;

$where = ['1=1'];
$params = [];
$types = '';

if (!$exportAll && $search !== '') {
    $where[] = '(a.full_name LIKE ? OR a.email LIKE ? OR a.job_position LIKE ?)';
    $searchParam = '%' . $search . '%';
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'sss';
}

if (!$exportAll && $statusFilter !== '') {
    $where[] = 'a.status = ?';
    $params[] = $statusFilter;
    $types .= 's';
}

if (!$exportAll && $jobFilter > 0) {
    $where[] = 'a.job_id = ?';
    $params[] = $jobFilter;
    $types .= 'i';
}

if (!$exportAll && $dateFilter !== '') {
    switch ($dateFilter) {
        case 'today':
            $where[] = 'DATE(a.created_at) = CURDATE()';
            break;
        case 'week':
            $where[] = 'a.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
            break;
        case 'month':
            $where[] = 'a.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)';
            break;
        case 'quarter':
            $where[] = 'a.created_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH)';
            break;
    }
}

$whereClause = implode(' AND ', $where);

$query = "SELECT a.id, a.full_name, a.email, a.phone, a.experience, a.tech_stack, a.job_position, a.portfolio_url, a.message, a.resume_path, a.status, a.admin_notes, a.interview_date, a.rating, a.feedback, a.created_at, a.updated_at, u.fullName AS user_name, u.email AS user_email, j.title AS job_title, j.company_id AS company_id
          FROM applications a
          LEFT JOIN users u ON a.user_id = u.id
          LEFT JOIN jobs j ON a.job_id = j.id
          WHERE $whereClause
          ORDER BY a.created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$rows = $result->fetch_all(MYSQLI_ASSOC) ?: [];
$stmt->close();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="devhire-applications-' . date('Y-m-d') . '.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, [
    'ID',
    'Applicant Name',
    'Applicant Email',
    'Phone',
    'Experience',
    'Tech Stack',
    'Position',
    'Job Title',
    'Status',
    'Portfolio URL',
    'Resume Path',
    'Admin Notes',
    'Interview Date',
    'Rating',
    'Feedback',
    'Applied At',
]);

foreach ($rows as $row) {
    fputcsv($out, [
        $row['id'],
        $row['full_name'],
        $row['email'],
        $row['phone'] ?? '',
        $row['experience'] ?? '',
        $row['tech_stack'] ?? '',
        $row['job_position'] ?? '',
        $row['job_title'] ?? '',
        $row['status'] ?? '',
        $row['portfolio_url'] ?? '',
        $row['resume_path'] ?? '',
        $row['admin_notes'] ?? '',
        $row['interview_date'] ?? '',
        $row['rating'] ?? '',
        $row['feedback'] ?? '',
        $row['created_at'] ?? '',
    ]);
}

fclose($out);
exit;
