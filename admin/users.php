<?php

require_once '../config/db.php';
requireAdmin();

$page_title = 'Admin Users - DevHire';
$css_path = appUrl('assets/css/style.css');
$js_path = appUrl('assets/js/main.js');

$isSuperAdmin = currentAdminRole() === 'super_admin';

$adminAccountsStmt = $conn->prepare('SELECT id, name, email, role, status, last_login_at, created_at FROM admin_accounts ORDER BY created_at DESC');
$adminAccountsStmt->execute();
$adminAccounts = $adminAccountsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$adminAccountsStmt->close();

$permissionsStmt = $conn->prepare('SELECT ap.admin_id, GROUP_CONCAT(ap.permission ORDER BY ap.permission SEPARATOR ", ") AS permissions FROM admin_permissions ap GROUP BY ap.admin_id');
$permissionsStmt->execute();
$permissionRows = $permissionsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$permissionsStmt->close();

$permissionMap = [];
foreach ($permissionRows as $permissionRow) {
	$permissionMap[(int) $permissionRow['admin_id']] = (string) ($permissionRow['permissions'] ?? '');
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<section class="admin-shell">
	<div class="admin-hero">
		<div>
			<span class="eyebrow">Admin Panel</span>
			<h1>Users</h1>
			<p>Manage admin accounts, roles, and permissions from the standalone admin account store.</p>
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
				<span class="eyebrow">Admin Accounts</span>
				<h2>Directory</h2>
			</div>
		</div>

		<?php if (!$isSuperAdmin): ?>
			<div class="notice notice-info panel-bottom-spacing">Only super_admin accounts can create or promote admin accounts.</div>
		<?php endif; ?>

		<div class="profile-list">
			<?php foreach ($adminAccounts as $account): ?>
				<article class="profile-item-card">
					<div>
						<strong><?= htmlspecialchars($account['name'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></strong>
						<p><?= htmlspecialchars($account['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
					</div>
					<div class="profile-item-meta">
						<span class="status-badge status-<?= htmlspecialchars($account['status'] ?? 'inactive', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ucfirst($account['status'] ?? 'inactive'), ENT_QUOTES, 'UTF-8') ?></span>
						<span><?= htmlspecialchars(ucfirst($account['role'] ?? 'reviewer'), ENT_QUOTES, 'UTF-8') ?></span>
						<span><?= htmlspecialchars((string) ($permissionMap[(int) $account['id']] ?? 'No permissions assigned'), ENT_QUOTES, 'UTF-8') ?></span>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php include '../includes/footer.php'; ?>