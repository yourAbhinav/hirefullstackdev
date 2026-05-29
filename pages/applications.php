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

$statusClasses = [
	'pending' => 'application-status-pending',
	'approved' => 'application-status-approved',
	'interview' => 'application-status-interview',
	'reviewed' => 'application-status-reviewed',
	'reviewing' => 'application-status-reviewed',
	'shortlisted' => 'application-status-shortlisted',
	'rejected' => 'application-status-rejected',
	'hired' => 'application-status-approved',
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
							<div class="application-status <?= htmlspecialchars($statusClasses[$app['status']] ?? 'application-status-default', ENT_QUOTES, 'UTF-8') ?>">
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

<?php include '../includes/footer.php'; ?>

