<?php
require_once '../config/db.php';
require_once '../includes/admin_helpers.php';

startSecureSession();

$token = trim((string) ($_GET['token'] ?? $_POST['token'] ?? ''));
$pageTitle = 'Approve Admin Access - DevHire';
$message = '';
$isError = false;
$request = $token !== '' ? getAdminAccessRequestByToken($conn, $token) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token !== '') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        $message = 'Security check failed. Refresh the page and try again.';
        $isError = true;
    } elseif ($request === null) {
        $message = 'This approval link is invalid or expired.';
        $isError = true;
    } else {
        $action = (string) ($_POST['action'] ?? '');

        if ($action === 'reject') {
            $reject = $conn->prepare("UPDATE admin_access_requests SET status = 'rejected', reviewed_at = NOW() WHERE id = ? AND status = 'pending'");
            if ($reject) {
                $requestId = (int) $request['id'];
                $reject->bind_param('i', $requestId);
                $reject->execute();
                $reject->close();
            }
            $message = 'Admin access request rejected.';
            $request = null;
        } elseif ($action === 'approve') {
            $reviewerId = isAdminLoggedIn() ? (int) currentAdminId() : 0;
            if ($reviewerId === 0) {
                $fallback = $conn->query("SELECT id FROM admin_accounts WHERE role = 'super_admin' AND status = 'active' ORDER BY id ASC LIMIT 1");
                $row = $fallback ? $fallback->fetch_assoc() : null;
                $reviewerId = (int) ($row['id'] ?? 0);
            }

            $result = approveAdminAccessRequest($conn, $token, $reviewerId);
            $message = $result['message'];
            $isError = !$result['success'];
            if ($result['success']) {
                $request = null;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #0f172a; color: #f8fafc; min-height: 100vh; display: grid; place-items: center; padding: 24px; }
        .card { width: min(560px, 100%); background: #1e293b; border: 1px solid rgba(124, 58, 237, 0.35); border-radius: 16px; padding: 28px; }
        h1 { font-size: 1.5rem; margin-bottom: 8px; }
        p { color: #cbd5e1; line-height: 1.6; }
        .notice { margin: 16px 0; padding: 12px 14px; border-radius: 10px; }
        .notice-error { background: rgba(244, 63, 94, 0.15); color: #fecdd3; }
        .notice-success { background: rgba(6, 182, 212, 0.15); color: #a5f3fc; }
        .meta { margin: 16px 0; padding: 14px; border-radius: 10px; background: rgba(15, 23, 42, 0.55); }
        .meta div { margin-bottom: 8px; }
        .actions { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 20px; }
        .btn { border: 0; border-radius: 10px; padding: 12px 16px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
        .btn-approve { background: linear-gradient(135deg, #4F46E5, #7C3AED); color: #fff; }
        .btn-reject { background: rgba(244, 63, 94, 0.2); color: #fecdd3; border: 1px solid rgba(244, 63, 94, 0.45); }
        .btn-link { background: transparent; color: #a5b4fc; border: 1px solid rgba(148, 163, 184, 0.35); }
    </style>
</head>
<body>
    <div class="card">
        <h1>Admin Access Approval</h1>

        <?php if ($message !== ''): ?>
            <div class="notice <?= $isError ? 'notice-error' : 'notice-success' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if ($request !== null): ?>
            <p>Review this admin access request carefully before approving.</p>
            <div class="meta">
                <div><strong>Name:</strong> <?= htmlspecialchars((string) $request['full_name']) ?></div>
                <div><strong>Email:</strong> <?= htmlspecialchars((string) $request['email']) ?></div>
                <div><strong>Requested IP:</strong> <?= htmlspecialchars((string) ($request['requested_ip'] ?? 'Unknown')) ?></div>
                <div><strong>Note:</strong> <?= htmlspecialchars((string) ($request['request_note'] !== '' ? $request['request_note'] : '(none)')) ?></div>
                <div><strong>Expires:</strong> <?= htmlspecialchars((string) $request['token_expires_at']) ?></div>
            </div>
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <div class="actions">
                    <button type="submit" name="action" value="approve" class="btn btn-approve">
                        <i class="fas fa-check"></i> Approve Admin
                    </button>
                    <button type="submit" name="action" value="reject" class="btn btn-reject">
                        <i class="fas fa-times"></i> Reject
                    </button>
                </div>
            </form>
        <?php elseif ($message === ''): ?>
            <p>This approval link is invalid, expired, or already processed.</p>
        <?php endif; ?>

        <div class="actions">
            <a href="<?= appUrl('admin/login.php') ?>" class="btn btn-link">Back to Admin Login</a>
            <?php if (isAdminLoggedIn()): ?>
                <a href="<?= appUrl('admin/dashboard.php') ?>" class="btn btn-link">Open Dashboard</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
