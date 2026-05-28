<?php

require_once '../config/db.php';
requireAdmin();

$page_title = 'Admin Jobs - DevHire';
$css_path = appUrl('assets/css/style.css');
$js_path = appUrl('assets/js/main.js');

$jobsStmt = $conn->prepare('SELECT j.id, j.title, j.status, j.job_type, j.work_mode, j.location, j.applications_count, j.created_at, COALESCE(u.company_name, u.fullName, u.email, "Company") AS company_name FROM jobs j LEFT JOIN users u ON u.id = j.company_id ORDER BY j.created_at DESC LIMIT 100');
$jobsStmt->execute();
$jobs = $jobsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$jobsStmt->close();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<section class="admin-shell">
	<div class="admin-hero">
		<div>
			<span class="eyebrow">Admin Panel</span>
			<h1>Jobs</h1>
			<p>Review the platform job inventory across all company accounts.</p>
		</div>
		<div class="admin-hero-actions">
			<div class="admin-user-summary">
				<div class="admin-user-avatar"><?= htmlspecialchars(strtoupper(substr(currentUserName(), 0, 1)), ENT_QUOTES, 'UTF-8') ?></div>
				<div>
					<strong><?= htmlspecialchars(currentUserName(), ENT_QUOTES, 'UTF-8') ?></strong>
					<span><?= htmlspecialchars(currentUserEmail(), ENT_QUOTES, 'UTF-8') ?></span>
				</div>
			</div>
			<?= renderLogoutForm('Logout', 'btn-secondary btn-inline') ?>
		</div>
	</div>

	<div class="panel">
		<div class="panel-header">
			<div>
				<span class="eyebrow">Jobs</span>
				<h2>Recent postings</h2>
			</div>
		</div>

		<div class="profile-list">
			<?php foreach ($jobs as $job): ?>
				<article class="profile-item-card">
					<div>
						<strong><?= htmlspecialchars($job['title'] ?? 'Untitled job', ENT_QUOTES, 'UTF-8') ?></strong>
						<p><?= htmlspecialchars($job['company_name'] ?? 'Company', ENT_QUOTES, 'UTF-8') ?> &middot; <?= htmlspecialchars($job['location'] ?? 'Remote', ENT_QUOTES, 'UTF-8') ?></p>
					</div>
					<div class="profile-item-meta">
						<span class="status-badge status-<?= htmlspecialchars($job['status'] ?? 'active', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ucfirst($job['status'] ?? 'active'), ENT_QUOTES, 'UTF-8') ?></span>
						<span><?= htmlspecialchars(ucfirst($job['job_type'] ?? 'full-time'), ENT_QUOTES, 'UTF-8') ?></span>
						<span><?= number_format((int) ($job['applications_count'] ?? 0)) ?> applications</span>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php include '../includes/footer.php'; ?>