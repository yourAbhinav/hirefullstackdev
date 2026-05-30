<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/site.php';

$siteName = SITE_COMPANY_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?> - Maintenance</title>
    <style>
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #0f172a, #111827); color: #e5e7eb; padding: 24px; }
        .card { width: min(640px, 100%); background: rgba(15, 23, 42, 0.88); border: 1px solid rgba(148, 163, 184, 0.25); border-radius: 18px; padding: 32px; box-shadow: 0 24px 80px rgba(0, 0, 0, 0.35); }
        .eyebrow { display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 999px; background: rgba(124, 58, 237, 0.15); color: #c4b5fd; font-weight: 700; font-size: 12px; letter-spacing: 0.08em; text-transform: uppercase; }
        h1 { margin: 18px 0 10px; font-size: clamp(2rem, 4vw, 3rem); line-height: 1.05; color: #fff; }
        p { margin: 0 0 12px; line-height: 1.7; color: #cbd5e1; }
        .actions { margin-top: 22px; display: flex; gap: 12px; flex-wrap: wrap; }
        a { color: inherit; text-decoration: none; }
        .btn { display: inline-flex; align-items: center; justify-content: center; padding: 12px 16px; border-radius: 12px; font-weight: 600; border: 1px solid transparent; }
        .btn-primary { background: linear-gradient(135deg, #4F46E5, #7C3AED); color: #fff; }
        .btn-secondary { background: rgba(255, 255, 255, 0.04); border-color: rgba(148, 163, 184, 0.25); color: #e2e8f0; }
    </style>
</head>
<body>
    <main class="card">
        <div class="eyebrow">Maintenance Mode</div>
        <h1><?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?> is temporarily unavailable</h1>
        <p>We are making updates to improve the platform. The public site is currently paused, but administrators can still sign in.</p>
        <p>Please check back soon.</p>
        <div class="actions">
            <a class="btn btn-primary" href="<?= appUrl('admin/login.php') ?>">Admin Login</a>
        </div>
    </main>
</body>
</html>