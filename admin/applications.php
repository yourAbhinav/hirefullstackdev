<?php

require_once '../config/db.php';
requireAdmin();

$page_title = 'Admin Applications - DevHire';
$css_path = appUrl('assets/css/style.css');
$js_path = appUrl('assets/js/main.js');

$applicationsStmt = $conn->prepare('SELECT a.id, a.full_name, a.email, a.job_position, a.status, a.created_at, j.title AS job_title, COALESCE(u.fullName, u.email, "Company") AS company_name FROM applications a LEFT JOIN jobs j ON j.id = a.job_id LEFT JOIN users u ON u.id = j.company_id ORDER BY a.created_at DESC LIMIT 100');
$applicationsStmt->execute();
$applications = $applicationsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$applicationsStmt->close();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<section class="admin-shell">
	<div class="admin-hero">
		<div>
			<span class="eyebrow">Admin Panel</span>
			<h1>Applications</h1>
			<p>View the latest candidate submissions across every job posting.</p>
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
				<span class="eyebrow">Applications</span>
				<h2>Latest submissions</h2>
			</div>
		</div>

		<div class="profile-list">
			<?php foreach ($applications as $application): ?>
				<article class="profile-item-card">
					<div>
						<strong><?= htmlspecialchars($application['full_name'] ?? 'Applicant', ENT_QUOTES, 'UTF-8') ?></strong>
						<p><?= htmlspecialchars($application['job_title'] ?? 'Application', ENT_QUOTES, 'UTF-8') ?> &middot; <?= htmlspecialchars($application['company_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
					</div>
					<div class="profile-item-meta">
						<span class="status-badge status-<?= htmlspecialchars($application['status'] ?? 'pending', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ucfirst($application['status'] ?? 'pending'), ENT_QUOTES, 'UTF-8') ?></span>
						<span><?= htmlspecialchars($application['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
						<span><?= htmlspecialchars(date('M j, Y', strtotime((string) $application['created_at'])), ENT_QUOTES, 'UTF-8') ?></span>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php include '../includes/footer.php'; ?>