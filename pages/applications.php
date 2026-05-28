<?php
require_once '../includes/helpers.php';
startSecureSession();

requireDeveloper();

require_once '../config/db.php';

$userId = (int) ($_SESSION['user_id'] ?? 0);
$page_title = 'My Applications - DevHire';

include '../includes/header.php';
include '../includes/navbar.php';

// Fetch user's applications
$applicationStmt = $conn->prepare(
	'SELECT a.id, a.full_name, a.email, a.job_position, a.status, a.message, 
	        a.created_at, a.feedback, a.rating, j.title AS job_title, 
	        COALESCE(c.company_name, "Company") AS company_name 
	 FROM applications a 
	 LEFT JOIN jobs j ON j.id = a.job_id 
	 LEFT JOIN users c ON c.id = j.company_id 
	 WHERE a.user_id = ? 
	 ORDER BY a.created_at DESC 
	 LIMIT 50'
);
$applicationStmt->bind_param('i', $userId);
$applicationStmt->execute();
$applications = $applicationStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$applicationStmt->close();

$statusColors = [
	'pending' => '#FF9800',
	'reviewing' => '#2196F3',
	'shortlisted' => '#4CAF50',
	'rejected' => '#F44336',
	'hired' => '#8BC34A'
];
?>

<section class="profile-shell">
	<div class="profile-container">
		<h1>My Applications</h1>
		<p>View and track your job applications</p>

		<?php if (empty($applications)): ?>
			<div class="notice notice-info">
				You haven't applied to any jobs yet. 
				<a href="<?= appUrl('pages/apply.php') ?>">Start applying now</a>
			</div>
		<?php else: ?>
			<div class="applications-list">
				<?php foreach ($applications as $app): ?>
					<div class="application-card">
						<div class="application-header">
							<div class="application-info">
								<h3><?= htmlspecialchars($app['job_title'] ?? $app['job_position'] ?? 'Job', ENT_QUOTES, 'UTF-8') ?></h3>
								<p class="company-name"><?= htmlspecialchars($app['company_name'] ?? 'Company', ENT_QUOTES, 'UTF-8') ?></p>
							</div>
							<div class="application-status" style="background-color: <?= htmlspecialchars($statusColors[$app['status']] ?? '#999', ENT_QUOTES, 'UTF-8') ?>;">
								<?= htmlspecialchars(ucfirst($app['status'] ?? 'unknown'), ENT_QUOTES, 'UTF-8') ?>
							</div>
						</div>

						<div class="application-details">
							<div class="detail-row">
								<span class="label">Applied on:</span>
								<span class="value"><?= htmlspecialchars(date('M d, Y', strtotime($app['created_at'])), ENT_QUOTES, 'UTF-8') ?></span>
							</div>
							<?php if (!empty($app['message'])): ?>
								<div class="detail-row">
									<span class="label">Your message:</span>
									<span class="value"><?= htmlspecialchars(substr($app['message'], 0, 100), ENT_QUOTES, 'UTF-8') ?>...</span>
								</div>
							<?php endif; ?>
							<?php if (!empty($app['feedback'])): ?>
								<div class="detail-row">
									<span class="label">Feedback:</span>
									<span class="value"><?= htmlspecialchars($app['feedback'], ENT_QUOTES, 'UTF-8') ?></span>
								</div>
							<?php endif; ?>
							<?php if (!empty($app['rating'])): ?>
								<div class="detail-row">
									<span class="label">Rating:</span>
									<span class="value">&#9733; <?= htmlspecialchars($app['rating'], ENT_QUOTES, 'UTF-8') ?>/5</span>
								</div>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<style>
.applications-list {
	display: grid;
	gap: 1.5rem;
	margin-top: 2rem;
}

.application-card {
	background: white;
	border: 1px solid #eee;
	border-radius: 8px;
	padding: 1.5rem;
	transition: box-shadow 0.2s ease;
}

.application-card:hover {
	box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.application-header {
	display: flex;
	justify-content: space-between;
	align-items: start;
	margin-bottom: 1rem;
	gap: 1rem;
}

.application-info h3 {
	margin: 0 0 0.5rem 0;
	font-size: 1.1rem;
	color: #333;
}

.company-name {
	margin: 0;
	font-size: 0.9rem;
	color: #666;
}

.application-status {
	padding: 0.4rem 0.8rem;
	border-radius: 20px;
	color: white;
	font-size: 0.85rem;
	font-weight: 600;
	white-space: nowrap;
}

.application-details {
	display: grid;
	gap: 0.75rem;
	font-size: 0.95rem;
}

.detail-row {
	display: flex;
	gap: 1rem;
}

.detail-row .label {
	color: #999;
	min-width: 120px;
	font-weight: 500;
}

.detail-row .value {
	color: #333;
}
</style>

<?php include '../includes/footer.php'; ?>

