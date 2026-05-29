<?php

require_once '../config/db.php';
require_once __DIR__ . '/middleware.php';
requireCompany();

$page_title = 'Company Applicants - DevHire';
$css_path = appUrl('assets/css/style.css');
$js_path = appUrl('assets/js/main.js');

$companyId = currentCompanyId();
$page = max(1, isset($_GET['page']) ? (int) $_GET['page'] : 1);
$perPage = 10;
$offset = ($page - 1) * $perPage;
$jobIdFilter = isset($_GET['job_id']) ? (int) $_GET['job_id'] : 0;

$allowedJobsStmt = $conn->prepare('SELECT id, title FROM jobs WHERE company_id = ? ORDER BY created_at DESC');
$allowedJobsStmt->bind_param('i', $companyId);
$allowedJobsStmt->execute();
$allowedJobs = $allowedJobsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$allowedJobsStmt->close();

if ($jobIdFilter > 0) {
	requireCompanyJobOwnership($conn, $jobIdFilter, $companyId);
}

// Build base query with job filter
$sql = 'SELECT a.id, a.full_name, a.email, a.phone, a.experience, a.tech_stack, a.job_position, a.resume_path, a.status, a.feedback, a.created_at, j.id AS job_id, j.title AS job_title FROM applications a INNER JOIN jobs j ON j.id = a.job_id WHERE j.company_id = ?';
$params = [$companyId];
$types = 'i';

if ($jobIdFilter > 0) {
	$sql .= ' AND j.id = ?';
	$params[] = $jobIdFilter;
	$types .= 'i';
}

// Get total count for pagination
$countSql = 'SELECT COUNT(*) AS total FROM applications a INNER JOIN jobs j ON j.id = a.job_id WHERE j.company_id = ?';
$countParams = [$companyId];
$countTypes = 'i';

if ($jobIdFilter > 0) {
	$countSql .= ' AND j.id = ?';
	$countParams[] = $jobIdFilter;
	$countTypes .= 'i';
}

$countStmt = $conn->prepare($countSql);
$countStmt->bind_param($countTypes, ...$countParams);
$countStmt->execute();
$totalFiltered = (int) ($countStmt->get_result()->fetch_assoc()['total'] ?? 0);
$countStmt->close();

$totalPages = max(1, (int) ceil($totalFiltered / $perPage));
if ($page > $totalPages) {
	$page = $totalPages;
	$offset = ($page - 1) * $perPage;
}

// Get total applicants count (for stats)
$statsStmt = $conn->prepare('SELECT COUNT(*) AS total FROM applications a INNER JOIN jobs j ON j.id = a.job_id WHERE j.company_id = ?');
$statsStmt->bind_param('i', $companyId);
$statsStmt->execute();
$totalApplicants = (int) ($statsStmt->get_result()->fetch_assoc()['total'] ?? 0);
$statsStmt->close();

// Get paginated results
$sql .= ' ORDER BY a.created_at DESC LIMIT ? OFFSET ?';
$params[] = $perPage;
$params[] = $offset;
$types .= 'ii';

$applicationsStmt = $conn->prepare($sql);
$applicationsStmt->bind_param($types, ...$params);
$applicationsStmt->execute();
$applications = $applicationsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$applicationsStmt->close();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<section class="admin-shell">
	<div class="admin-hero">
		<div>
			<span class="eyebrow">Company Panel</span>
			<h1>Applicants</h1>
			<p>Review all applications submitted to your jobs.</p>
		</div>
		<div class="admin-hero-actions">
			<a class="btn-secondary btn-inline" href="<?= appUrl('company/jobs.php') ?>">Manage Jobs</a>
			<a class="btn-secondary btn-inline" href="<?= appUrl('company/create-job.php') ?>">Create Job</a>
		</div>
	</div>

	<div class="dashboard-grid stats-grid-4">
		<article class="stat-card"><span>Total Applicants</span><strong><?= number_format($totalApplicants) ?></strong></article>
		<article class="stat-card"><span>Filtered Applicants</span><strong><?= number_format($totalFiltered) ?></strong></article>
		<article class="stat-card"><span>Selected Job</span><strong><?= $jobIdFilter > 0 ? number_format($jobIdFilter) : 'All' ?></strong></article>
		<article class="stat-card"><span>Role</span><strong>Company</strong></article>
	</div>

	<form class="dashboard-filters panel-top-spacing" method="GET">
		<div class="form-group">
			<label for="job_id">Filter by Job</label>
			<select id="job_id" name="job_id">
				<option value="">All jobs</option>
				<?php foreach ($allowedJobs as $job): ?>
					<option value="<?= (int) $job['id'] ?>" <?= $jobIdFilter === (int) $job['id'] ? 'selected' : '' ?>><?= htmlspecialchars($job['title'] ?? 'Untitled job', ENT_QUOTES, 'UTF-8') ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="dashboard-filter-actions">
			<button type="submit" class="btn-primary btn-inline">Filter</button>
			<a href="<?= appUrl('company/applicants.php') ?>" class="btn-secondary btn-inline">Reset</a>
		</div>
	</form>

	<section class="panel panel-top-spacing">
		<div class="panel-header">
			<div>
				<span class="eyebrow">Applications</span>
				<h2>Candidate list</h2>
			</div>
			<?php if ($totalPages > 1): ?>
				<div class="pagination-info">
					<span>Page <?= $page ?> of <?= $totalPages ?></span>
				</div>
			<?php endif; ?>
		</div>

		<?php if (!empty($applications)): ?>
			<div class="profile-list">
				<?php foreach ($applications as $application): ?>
					<article class="profile-item-card">
						<div>
							<strong><?= htmlspecialchars($application['full_name'] ?? 'Applicant', ENT_QUOTES, 'UTF-8') ?></strong>
							<p><?= htmlspecialchars($application['job_title'] ?? 'Job', ENT_QUOTES, 'UTF-8') ?> &middot; <?= htmlspecialchars($application['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
						</div>
						<div class="profile-item-meta">
							<span class="status-badge status-<?= htmlspecialchars($application['status'] ?? 'pending', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ucfirst($application['status'] ?? 'pending'), ENT_QUOTES, 'UTF-8') ?></span>
							<span><?= htmlspecialchars(date('M j, Y', strtotime((string) $application['created_at'])), ENT_QUOTES, 'UTF-8') ?></span>
						</div>
						<div class="profile-item-meta profile-item-meta-offset">
							<span><?= htmlspecialchars($application['phone'] ?: 'No phone', ENT_QUOTES, 'UTF-8') ?></span>
							<span><?= htmlspecialchars($application['experience'] ?: 'Experience not set', ENT_QUOTES, 'UTF-8') ?></span>
						</div>
						<?php if (!empty($application['resume_path'])): ?>
							<p class="profile-note"><a href="<?= appUrl($application['resume_path']) ?>" target="_blank" rel="noopener noreferrer">Download resume</a></p>
						<?php endif; ?>
						<?php if (!empty($application['feedback'])): ?>
							<p class="profile-note">Feedback: <?= htmlspecialchars($application['feedback'], ENT_QUOTES, 'UTF-8') ?></p>
						<?php endif; ?>
					</article>
				<?php endforeach; ?>
			</div>

			<?php if ($totalPages > 1): ?>
				<div class="pagination">
					<?php if ($page > 1): ?>
						<a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="btn-secondary btn-inline">Previous</a>
					<?php endif; ?>
					
					<?php for ($i = 1; $i <= $totalPages; $i++): ?>
						<?php if ($i === $page): ?>
							<span class="pagination-current"><?= $i ?></span>
						<?php else: ?>
							<a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="btn-secondary btn-inline"><?= $i ?></a>
						<?php endif; ?>
					<?php endfor; ?>
					
					<?php if ($page < $totalPages): ?>
						<a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="btn-secondary btn-inline">Next</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		<?php else: ?>
			<div class="empty-state">No applicants found for this company.</div>
		<?php endif; ?>
	</section>
</section>

<?php include '../includes/footer.php'; ?>