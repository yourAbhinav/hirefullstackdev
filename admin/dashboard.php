<?php

require_once '../config/db.php';
requireAdmin();

$page_title = 'Admin Dashboard - DevHire';
$css_path = appUrl('assets/css/style.css');
$js_path = appUrl('assets/js/main.js');

$nameColumn = dbColumnExists($conn, 'applications', 'full_name') ? 'a.full_name' : 'a.fullName';
$techColumn = dbColumnExists($conn, 'applications', 'tech_stack') ? 'a.tech_stack' : 'a.techStack';
$allowedStatuses = ['pending', 'reviewing', 'shortlisted', 'rejected', 'hired'];

$dashboardNotice = $_SESSION['dashboard_notice'] ?? '';
$dashboardError = $_SESSION['dashboard_error'] ?? '';
unset($_SESSION['dashboard_notice'], $_SESSION['dashboard_error']);

$currentQuery = [
    'q' => trim((string) ($_GET['q'] ?? '')),
    'status' => trim((string) ($_GET['status'] ?? '')),
    'page' => max(1, (int) ($_GET['page'] ?? 1)),
    'view' => (int) ($_GET['view'] ?? 0),
];

function dashboardRedirect(array $query = []): void
{
    $url = appUrl('admin/dashboard.php');
    $filtered = array_filter($query, static function ($value) {
        return $value !== '' && $value !== null;
    });

    if (!empty($filtered)) {
        $url .= '?' . http_build_query($filtered);
    }

    header('Location: ' . $url);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        $_SESSION['dashboard_error'] = 'Your session expired. Please reload and try again.';
        dashboardRedirect($currentQuery);
    }

    $action = $_POST['action'] ?? '';
    $applicationId = (int) ($_POST['application_id'] ?? 0);
    $returnQuery = [
        'q' => trim((string) ($_POST['return_q'] ?? '')),
        'status' => trim((string) ($_POST['return_status'] ?? '')),
        'page' => max(1, (int) ($_POST['return_page'] ?? 1)),
        'view' => (int) ($_POST['return_view'] ?? 0),
    ];

    if ($action === 'update_application') {
        $status = sanitize($_POST['status'] ?? 'pending');
        $feedback = sanitize($_POST['feedback'] ?? '');

        if ($applicationId <= 0) {
            $_SESSION['dashboard_error'] = 'Invalid application selected.';
            dashboardRedirect($returnQuery);
        }

        if (!in_array($status, $allowedStatuses, true)) {
            $_SESSION['dashboard_error'] = 'Invalid application status.';
            dashboardRedirect($returnQuery);
        }

        $stmt = $conn->prepare('UPDATE applications SET status = ?, feedback = ?, updated_at = NOW() WHERE id = ?');
        $stmt->bind_param('ssi', $status, $feedback, $applicationId);
        $stmt->execute();
        $stmt->close();

        $_SESSION['dashboard_notice'] = 'Application updated successfully.';
        dashboardRedirect($returnQuery);
    }

    if ($action === 'delete_application') {
        if ($applicationId <= 0) {
            $_SESSION['dashboard_error'] = 'Invalid application selected.';
            dashboardRedirect($returnQuery);
        }

        $stmt = $conn->prepare('DELETE FROM applications WHERE id = ?');
        $stmt->bind_param('i', $applicationId);
        $stmt->execute();
        $stmt->close();

        $_SESSION['dashboard_notice'] = 'Application deleted successfully.';
        dashboardRedirect($returnQuery);
    }

    $_SESSION['dashboard_error'] = 'Unsupported action.';
    dashboardRedirect($returnQuery);
}

$search = $currentQuery['q'];
$statusFilter = $currentQuery['status'];
$page = $currentQuery['page'];
$perPage = 10;
$offset = ($page - 1) * $perPage;

$whereParts = [];
$params = [];
$types = '';

if ($statusFilter !== '' && in_array($statusFilter, $allowedStatuses, true)) {
    $whereParts[] = 'a.status = ?';
    $params[] = $statusFilter;
    $types .= 's';
}

if ($search !== '') {
    $whereParts[] = '(' . implode(' OR ', [
        $nameColumn . ' LIKE ?',
        'a.email LIKE ?',
        'a.phone LIKE ?',
        'a.jobPosition LIKE ?',
        $techColumn . ' LIKE ?',
        'COALESCE(au.name, u.fullName, \'\') LIKE ?',
    ]) . ')';

    $like = '%' . $search . '%';
    for ($i = 0; $i < 6; $i++) {
        $params[] = $like;
        $types .= 's';
    }
}

$whereSql = !empty($whereParts) ? 'WHERE ' . implode(' AND ', $whereParts) : '';

$countSql = 'SELECT COUNT(*) AS total FROM applications a LEFT JOIN admin_users au ON au.id = a.user_id LEFT JOIN users u ON u.id = a.user_id ' . $whereSql;
$countStmt = $conn->prepare($countSql);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalFiltered = (int) ($countStmt->get_result()->fetch_assoc()['total'] ?? 0);
$countStmt->close();

$totalPages = max(1, (int) ceil($totalFiltered / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}

$dataSql = 'SELECT a.id, a.email, a.phone, a.experience, a.jobPosition, a.portfolio, a.message, a.resume, a.job_id, a.user_id, a.status, a.feedback, a.rating, a.created_at, ' .
    $nameColumn . ' AS applicant_name, ' .
    $techColumn . ' AS tech_stack_value, ' .
    'COALESCE(au.name, u.fullName, \'\') AS account_name, ' .
    'COALESCE(au.email, u.email, a.email) AS account_email, ' .
    'COALESCE(au.photo, u.profile_image, \'\') AS account_photo ' .
    'FROM applications a LEFT JOIN admin_users au ON au.id = a.user_id LEFT JOIN users u ON u.id = a.user_id ' .
    $whereSql . ' ORDER BY a.created_at DESC LIMIT ? OFFSET ?';

$dataStmt = $conn->prepare($dataSql);
$dataParams = $params;
$dataTypes = $types . 'ii';
$dataParams[] = $perPage;
$dataParams[] = $offset;
if (!empty($dataParams)) {
    $dataStmt->bind_param($dataTypes, ...$dataParams);
}
$dataStmt->execute();
$applicationsResult = $dataStmt->get_result();
$applications = $applicationsResult ? $applicationsResult->fetch_all(MYSQLI_ASSOC) : [];
$dataStmt->close();

$selectedApplication = null;
if ($currentQuery['view'] > 0) {
    $detailStmt = $conn->prepare('SELECT a.id, a.email, a.phone, a.experience, a.jobPosition, a.portfolio, a.message, a.resume, a.job_id, a.user_id, a.status, a.feedback, a.rating, a.created_at, ' . $nameColumn . ' AS applicant_name, ' . $techColumn . ' AS tech_stack_value, COALESCE(au.name, u.fullName, \'\') AS account_name, COALESCE(au.email, u.email, a.email) AS account_email, COALESCE(au.photo, u.profile_image, \'\') AS account_photo FROM applications a LEFT JOIN admin_users au ON au.id = a.user_id LEFT JOIN users u ON u.id = a.user_id WHERE a.id = ? LIMIT 1');
    $detailId = $currentQuery['view'];
    $detailStmt->bind_param('i', $detailId);
    $detailStmt->execute();
    $selectedApplication = $detailStmt->get_result()->fetch_assoc();
    $detailStmt->close();
}

$totalApplicationsStmt = $conn->query('SELECT COUNT(*) AS total FROM applications');
$totalApplications = (int) ($totalApplicationsStmt->fetch_assoc()['total'] ?? 0);
$pendingApplications = (int) ($conn->query("SELECT COUNT(*) AS total FROM applications WHERE status = 'pending'")->fetch_assoc()['total'] ?? 0);
$reviewingApplications = (int) ($conn->query("SELECT COUNT(*) AS total FROM applications WHERE status = 'reviewing'")->fetch_assoc()['total'] ?? 0);
$shortlistedApplications = (int) ($conn->query("SELECT COUNT(*) AS total FROM applications WHERE status = 'shortlisted'")->fetch_assoc()['total'] ?? 0);
$totalSavedJobs = (int) ($conn->query('SELECT COUNT(*) AS total FROM saved_jobs')->fetch_assoc()['total'] ?? 0);
$totalMessages = (int) ($conn->query('SELECT COUNT(*) AS total FROM messages')->fetch_assoc()['total'] ?? 0);

include '../includes/header.php';
include '../includes/navbar.php';
?>

<section class="admin-shell">
    <div class="admin-hero">
        <div>
            <span class="eyebrow">Admin Panel</span>
            <h1>Dashboard</h1>
            <p>Review applications, update hiring status, and keep the platform moving.</p>
        </div>
        <div class="admin-hero-actions">
            <div class="admin-user-summary">
                <div class="admin-user-avatar">
                    <?= htmlspecialchars(strtoupper(substr(currentUserName(), 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div>
                    <strong><?= htmlspecialchars(currentUserName(), ENT_QUOTES, 'UTF-8') ?></strong>
                    <span><?= htmlspecialchars(currentUserEmail(), ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </div>
            <a class="btn-secondary btn-inline" href="<?= appUrl('auth/logout.php') ?>">Logout</a>
        </div>
    </div>

    <?php if (!empty($dashboardNotice)): ?>
        <div class="notice notice-success"><i class="fas fa-check-circle"></i><p><?= htmlspecialchars($dashboardNotice, ENT_QUOTES, 'UTF-8') ?></p></div>
    <?php endif; ?>

    <?php if (!empty($dashboardError)): ?>
        <div class="notice notice-error"><i class="fas fa-exclamation-circle"></i><p><?= htmlspecialchars($dashboardError, ENT_QUOTES, 'UTF-8') ?></p></div>
    <?php endif; ?>

    <div class="dashboard-grid stats-grid-4">
        <article class="stat-card">
            <span>Total Applications</span>
            <strong><?= number_format($totalApplications) ?></strong>
        </article>
        <article class="stat-card">
            <span>Pending</span>
            <strong><?= number_format($pendingApplications) ?></strong>
        </article>
        <article class="stat-card">
            <span>Reviewing</span>
            <strong><?= number_format($reviewingApplications) ?></strong>
        </article>
        <article class="stat-card">
            <span>Saved Jobs</span>
            <strong><?= number_format($totalSavedJobs) ?></strong>
        </article>
    </div>

    <div class="dashboard-grid stats-grid-2 mt-3">
        <article class="stat-card subtle">
            <span>Shortlisted</span>
            <strong><?= number_format($shortlistedApplications) ?></strong>
        </article>
        <article class="stat-card subtle">
            <span>Messages</span>
            <strong><?= number_format($totalMessages) ?></strong>
        </article>
    </div>

    <form class="dashboard-filters" method="GET">
        <div class="form-group">
            <label for="q">Search</label>
            <input type="search" id="q" name="q" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Name, email, phone, position, tech stack">
        </div>
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="">All statuses</option>
                <?php foreach ($allowedStatuses as $statusOption): ?>
                    <option value="<?= htmlspecialchars($statusOption, ENT_QUOTES, 'UTF-8') ?>" <?= $statusFilter === $statusOption ? 'selected' : '' ?>><?= ucfirst($statusOption) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="dashboard-filter-actions">
            <button type="submit" class="btn-primary btn-inline">Filter</button>
            <a href="<?= appUrl('admin/dashboard.php') ?>" class="btn-secondary btn-inline">Reset</a>
        </div>
    </form>

    <?php if (!empty($selectedApplication)): ?>
        <section class="panel details-panel">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">Selected application</span>
                    <h2><?= htmlspecialchars($selectedApplication['applicant_name'] ?: $selectedApplication['account_name'] ?: $selectedApplication['email'], ENT_QUOTES, 'UTF-8') ?></h2>
                </div>
                <a class="btn-secondary btn-inline" href="<?= appUrl('admin/dashboard.php') ?>">Close</a>
            </div>
            <div class="details-grid">
                <div><span>Email</span><strong><?= htmlspecialchars($selectedApplication['email'], ENT_QUOTES, 'UTF-8') ?></strong></div>
                <div><span>Phone</span><strong><?= htmlspecialchars($selectedApplication['phone'] ?? '-', ENT_QUOTES, 'UTF-8') ?></strong></div>
                <div><span>Position</span><strong><?= htmlspecialchars($selectedApplication['jobPosition'] ?? '-', ENT_QUOTES, 'UTF-8') ?></strong></div>
                <div><span>Tech Stack</span><strong><?= htmlspecialchars($selectedApplication['tech_stack_value'] ?? '-', ENT_QUOTES, 'UTF-8') ?></strong></div>
                <div><span>Experience</span><strong><?= htmlspecialchars($selectedApplication['experience'] ?? '-', ENT_QUOTES, 'UTF-8') ?></strong></div>
                <div><span>Status</span><strong class="status-badge status-<?= htmlspecialchars($selectedApplication['status'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ucfirst($selectedApplication['status']), ENT_QUOTES, 'UTF-8') ?></strong></div>
            </div>
            <div class="details-copy">
                <div>
                    <span>Portfolio</span>
                    <?php if (!empty($selectedApplication['portfolio'])): ?>
                        <p><a href="<?= htmlspecialchars($selectedApplication['portfolio'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Open portfolio</a></p>
                    <?php else: ?>
                        <p>Not provided</p>
                    <?php endif; ?>
                </div>
                <div>
                    <span>Message</span>
                    <p><?= nl2br(htmlspecialchars($selectedApplication['message'] ?? '', ENT_QUOTES, 'UTF-8')) ?></p>
                </div>
                <div>
                    <span>Feedback</span>
                    <p><?= nl2br(htmlspecialchars($selectedApplication['feedback'] ?? '', ENT_QUOTES, 'UTF-8')) ?></p>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section class="panel">
        <div class="panel-header">
            <div>
                <span class="eyebrow">Applications</span>
                <h2>Latest submissions</h2>
            </div>
            <p><?= number_format($totalFiltered) ?> result<?= $totalFiltered === 1 ? '' : 's' ?></p>
        </div>

        <div class="table-responsive">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>Applicant</th>
                        <th>Contact</th>
                        <th>Position</th>
                        <th>Experience</th>
                        <th>Status</th>
                        <th>Feedback</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($applications)): ?>
                        <?php foreach ($applications as $application): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($application['applicant_name'] ?: $application['account_name'] ?: $application['email'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    <span class="table-subtext">#<?= (int) $application['id'] ?> · <?= htmlspecialchars(date('M j, Y', strtotime($application['created_at'])), ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td>
                                    <div><?= htmlspecialchars($application['email'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <span class="table-subtext"><?= htmlspecialchars($application['phone'] ?? '-', ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td>
                                    <div><?= htmlspecialchars($application['jobPosition'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
                                    <span class="table-subtext"><?= htmlspecialchars($application['tech_stack_value'] ?? '-', ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td><?= htmlspecialchars($application['experience'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <span class="status-badge status-<?= htmlspecialchars($application['status'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ucfirst($application['status']), ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td>
                                    <form method="POST" class="action-form action-form-stack">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="update_application">
                                        <input type="hidden" name="application_id" value="<?= (int) $application['id'] ?>">
                                        <input type="hidden" name="return_q" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="return_status" value="<?= htmlspecialchars($statusFilter, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="return_page" value="<?= (int) $page ?>">
                                        <input type="hidden" name="return_view" value="<?= (int) $currentQuery['view'] ?>">
                                        <select name="status" class="status-select">
                                            <?php foreach ($allowedStatuses as $statusOption): ?>
                                                <option value="<?= htmlspecialchars($statusOption, ENT_QUOTES, 'UTF-8') ?>" <?= $application['status'] === $statusOption ? 'selected' : '' ?>><?= ucfirst($statusOption) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <textarea name="feedback" rows="2" placeholder="Feedback"><?= htmlspecialchars($application['feedback'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                                        <button type="submit" class="btn-primary btn-inline">Save</button>
                                    </form>
                                </td>
                                <td>
                                    <div class="row-actions">
                                        <a class="btn-secondary btn-inline" href="<?= appUrl('admin/dashboard.php?' . http_build_query(array_merge($currentQuery, ['view' => (int) $application['id']])) ) ?>">View</a>
                                        <form method="POST" onsubmit="return confirm('Delete this application?');">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="action" value="delete_application">
                                            <input type="hidden" name="application_id" value="<?= (int) $application['id'] ?>">
                                            <input type="hidden" name="return_q" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="return_status" value="<?= htmlspecialchars($statusFilter, ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="return_page" value="<?= (int) $page ?>">
                                            <input type="hidden" name="return_view" value="<?= (int) $currentQuery['view'] ?>">
                                            <button type="submit" class="btn-danger btn-inline">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="empty-state">
                                No matching applications found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="pagination-bar">
            <span>Page <?= (int) $page ?> of <?= (int) $totalPages ?></span>
            <div class="pagination-actions">
                <?php if ($page > 1): ?>
                    <a class="btn-secondary btn-inline" href="<?= appUrl('admin/dashboard.php?' . http_build_query(array_merge($currentQuery, ['page' => $page - 1]))) ?>">Previous</a>
                <?php endif; ?>
                <?php if ($page < $totalPages): ?>
                    <a class="btn-secondary btn-inline" href="<?= appUrl('admin/dashboard.php?' . http_build_query(array_merge($currentQuery, ['page' => $page + 1]))) ?>">Next</a>
                <?php endif; ?>
            </div>
        </div>
    </section>
</section>

<?php include '../includes/footer.php'; ?>
