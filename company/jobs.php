<?php

require_once '../config/db.php';
require_once __DIR__ . '/middleware.php';
requireCompany();

$page_title = 'Company Jobs - DevHire';
$css_path = appUrl('assets/css/style.css');
$js_path = appUrl('assets/js/main.js');

$companyId = currentCompanyId();
$companyName = currentUserName();
$companyEmail = currentUserEmail();
$statusFilter = strtolower(trim((string) ($_GET['status'] ?? '')));
$allowedStatuses = array_keys(companyStatusOptions());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
		setFlash('error', 'Your session expired. Please reload and try again.');
		companyRedirect('company/jobs.php');
	}

	$action = (string) ($_POST['action'] ?? '');
	$jobId = (int) ($_POST['job_id'] ?? 0);

	if ($action === 'delete_job' && $jobId > 0) {
		requireCompanyJobOwnership($conn, $jobId, $companyId);

		$stmt = $conn->prepare('DELETE FROM jobs WHERE id = ? AND company_id = ?');
		$stmt->bind_param('ii', $jobId, $companyId);
		$stmt->execute();
		$stmt->close();

		setFlash('success', 'Job deleted successfully.');
		companyRedirect('company/jobs.php');
	}

	setFlash('error', 'Unsupported action.');
	companyRedirect('company/jobs.php');
}

$whereSql = '';
$params = [$companyId];
$types = 'i';

if ($statusFilter !== '' && in_array($statusFilter, $allowedStatuses, true)) {
	$whereSql = ' AND status = ?';
	$params[] = $statusFilter;
	$types .= 's';
}

$statsStmt = $conn->prepare('SELECT COUNT(*) AS total FROM jobs WHERE company_id = ?');
$statsStmt->bind_param('i', $companyId);
$statsStmt->execute();
$totalJobs = (int) ($statsStmt->get_result()->fetch_assoc()['total'] ?? 0);
$statsStmt->close();

$activeJobsStmt = $conn->prepare("SELECT COUNT(*) AS total FROM jobs WHERE company_id = ? AND status = 'active'");
$activeJobsStmt->bind_param('i', $companyId);
$activeJobsStmt->execute();
$activeJobs = (int) ($activeJobsStmt->get_result()->fetch_assoc()['total'] ?? 0);
$activeJobsStmt->close();

$applicationsStmt = $conn->prepare('SELECT COUNT(*) AS total FROM applications a INNER JOIN jobs j ON j.id = a.job_id WHERE j.company_id = ?');
$applicationsStmt->bind_param('i', $companyId);
$applicationsStmt->execute();
$totalApplications = (int) ($applicationsStmt->get_result()->fetch_assoc()['total'] ?? 0);
$applicationsStmt->close();

$jobsSql = 'SELECT id, title, location, job_type, work_mode, status, featured, applications_count, created_at FROM jobs WHERE company_id = ?' . $whereSql . ' ORDER BY created_at DESC';
$jobsStmt = $conn->prepare($jobsSql);
$jobsStmt->bind_param($types, ...$params);
$jobsStmt->execute();
$jobs = $jobsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$jobsStmt->close();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<section class="admin-shell">
	<div class="admin-hero">
		<div>
			<span class="eyebrow">Company Panel</span>
			<h1>Jobs</h1>
			<p>Create, update, and manage your company openings.</p>
		</div>
		<div class="admin-hero-actions">
			<div class="admin-user-summary">
				<div class="admin-user-avatar"><?= htmlspecialchars(strtoupper(substr($companyName, 0, 1)), ENT_QUOTES, 'UTF-8') ?></div>
				<div>
					<strong><?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8') ?></strong>
					<span><?= htmlspecialchars($companyEmail, ENT_QUOTES, 'UTF-8') ?></span>
				</div>
			</div>
			<a class="btn-primary btn-inline" href="<?= appUrl('company/create-job.php') ?>">Create Job</a>
		</div>
	</div>

	<?php if (!empty($successMessage = getFlash('success'))): ?>
		<div class="notice notice-success"><i class="fas fa-check-circle"></i><p><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></p></div>
	<?php endif; ?>

	<?php if (!empty($errorMessage = getFlash('error'))): ?>
		<div class="notice notice-error"><i class="fas fa-exclamation-circle"></i><p><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p></div>
	<?php endif; ?>

	<div class="dashboard-grid stats-grid-4">
		<article class="stat-card"><span>Total Jobs</span><strong><?= number_format($totalJobs) ?></strong></article>
		<article class="stat-card"><span>Active Jobs</span><strong><?= number_format($activeJobs) ?></strong></article>
		<article class="stat-card"><span>Applications</span><strong><?= number_format($totalApplications) ?></strong></article>
		<article class="stat-card"><span>Role</span><strong>Company</strong></article>
	</div>

	<form class="dashboard-filters panel-top-spacing" method="GET">
		<div class="form-group">
			<label for="status">Filter by Status</label>
			<select id="status" name="status">
				<option value="">All statuses</option>
				<?php foreach (companyStatusOptions() as $value => $label): ?>
					<option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>" <?= $statusFilter === $value ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="dashboard-filter-actions">
			<button type="submit" class="btn-primary btn-inline">Filter</button>
			<a href="<?= appUrl('company/jobs.php') ?>" class="btn-secondary btn-inline">Reset</a>
		</div>
	</form>

	<section class="panel panel-top-spacing">
		<div class="panel-header">
			<div>
				<span class="eyebrow">Managed Jobs</span>
				<h2>Job listings</h2>
			</div>
			<a href="<?= appUrl('company/create-job.php') ?>" class="btn-secondary btn-inline">New Job</a>
		</div>

		<?php if (!empty($jobs)): ?>
			<div class="profile-list">
				<?php foreach ($jobs as $job): ?>
					<article class="profile-item-card">
						<div>
							<strong><?= htmlspecialchars($job['title'] ?? 'Untitled job', ENT_QUOTES, 'UTF-8') ?></strong>
							<p><?= htmlspecialchars($job['location'] ?? 'Remote', ENT_QUOTES, 'UTF-8') ?> &middot; <?= htmlspecialchars($job['work_mode'] ?? 'remote', ENT_QUOTES, 'UTF-8') ?></p>
						</div>
						<div class="profile-item-meta">
							<span class="status-badge status-<?= htmlspecialchars($job['status'] ?? 'active', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ucfirst($job['status'] ?? 'active'), ENT_QUOTES, 'UTF-8') ?></span>
							<span><?= number_format((int) ($job['applications_count'] ?? 0)) ?> applicants</span>
						</div>
						<div class="row-actions row-actions-spaced">
							<a href="<?= appUrl('company/create-job.php?id=' . (int) $job['id']) ?>" class="btn-secondary btn-inline">Edit</a>
							<a href="<?= appUrl('company/applicants.php?job_id=' . (int) $job['id']) ?>" class="btn-secondary btn-inline">Applicants</a>
							<form method="POST" class="inline-form" onsubmit="return confirm('Delete this job?');">
								<?= csrfField() ?>
								<input type="hidden" name="action" value="delete_job">
								<input type="hidden" name="job_id" value="<?= (int) $job['id'] ?>">
								<button type="submit" class="btn-secondary btn-inline btn-inline-danger">Delete</button>
							</form>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		<?php else: ?>
			<div class="empty-state">No jobs match this filter yet.</div>
		<?php endif; ?>
	</section>
</section>

<?php include '../includes/footer.php'; ?>
