<?php

require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
		setFlash('error', 'Your session expired. Please reload and try again.');
		header('Location: ' . appUrl('pages/login.php'));
		exit;
	}

	logoutCurrentUser($conn);
	header('Location: ' . appUrl('index.php'));
	exit;
}

if (isLoggedIn()) {
	$page_title = 'Confirm Logout - DevHire';
	include '../includes/header.php';
?>
<section class="page-hero">
	<div class="page-hero-inner logout-hero-inner">
		<span class="eyebrow">Secure Session</span>
		<h1>Confirm logout</h1>
		<p class="quick-apply-subtitle">Use the button below to end this session and revoke the current remember-me token.</p>
		<div class="logout-actions">
			<?= renderLogoutForm('Logout Now', 'btn-primary') ?>
			<a href="<?= appUrl(roleDashboardPath()) ?>" class="btn-secondary">Back to dashboard</a>
		</div>
	</div>
</section>
<?php
	include '../includes/footer.php';
	exit;
}

header('Location: ' . appUrl('index.php'));
exit;
?>