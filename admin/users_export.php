<?php
require_once '../config/db.php';
require_once '../includes/admin_helpers.php';
require_once '../includes/helpers.php';

requireAdminLogin();
requireAdminPermission($conn, 'view_users');

$search = $_GET['search'] ?? '';
$roleFilter = $_GET['role'] ?? '';
$statusFilter = $_GET['status'] ?? '';

$where = ['1=1'];
$params = [];
$types = '';

if ($search !== '') {
    $where[] = '(fullName LIKE ? OR email LIKE ?)';
    $searchParam = '%' . $search . '%';
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'ss';
}

if ($roleFilter !== '') {
    $where[] = 'role = ?';
    $params[] = $roleFilter;
    $types .= 's';
}

if ($statusFilter !== '') {
    $where[] = 'verified = ?';
    $params[] = $statusFilter === 'verified' ? 1 : 0;
    $types .= 'i';
}

$whereClause = implode(' AND ', $where);
$query = "SELECT id, fullName, email, role, provider, verified, phone, experience, techStack, created_at FROM users WHERE $whereClause ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
if ($params !== []) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC) ?: [];
$stmt->close();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="devhire-users-' . date('Y-m-d') . '.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['ID', 'Name', 'Email', 'Role', 'Provider', 'Verified', 'Phone', 'Experience', 'Tech Stack', 'Joined']);

foreach ($rows as $row) {
    fputcsv($out, [
        $row['id'],
        $row['fullName'],
        $row['email'],
        $row['role'],
        $row['provider'] ?? 'email',
        $row['verified'] ? 'yes' : 'no',
        $row['phone'] ?? '',
        $row['experience'] ?? '',
        $row['techStack'] ?? '',
        $row['created_at'],
    ]);
}

fclose($out);
exit;
