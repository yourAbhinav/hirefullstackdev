<?php

require_once '../config/db.php';
require_once __DIR__ . '/middleware.php';
requireCompany();

$page_title = 'Company Dashboard - DevHire';
$css_path = appUrl('assets/css/style.css');
$js_path = appUrl('assets/js/main.js');

$companyId = currentCompanyId();
$companyName = currentUserName();
$companyEmail = currentUserEmail();

$companyProfileStmt = $conn->prepare('SELECT fullName, email, company_name, company_description FROM users WHERE id = ? LIMIT 1');
$companyProfileStmt->bind_param('i', $companyId);
$companyProfileStmt->execute();
$companyProfile = $companyProfileStmt->get_result()->fetch_assoc() ?: [];
$companyProfileStmt->close();

if (!empty($companyProfile)) {
    $companyName = (string) ($companyProfile['company_name'] ?? $companyProfile['fullName'] ?? $companyName);
    $companyEmail = (string) ($companyProfile['email'] ?? $companyEmail);
}

$totalJobsStmt = $conn->prepare('SELECT COUNT(*) AS total FROM jobs WHERE company_id = ?');
$totalJobsStmt->bind_param('i', $companyId);
$totalJobsStmt->execute();
$totalJobs = (int) ($totalJobsStmt->get_result()->fetch_assoc()['total'] ?? 0);
$totalJobsStmt->close();

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

$recentApplicationsStmt = $conn->prepare('SELECT a.id, a.full_name, a.email, a.job_position, a.status, a.created_at, j.title AS job_title FROM applications a INNER JOIN jobs j ON j.id = a.job_id WHERE j.company_id = ? ORDER BY a.created_at DESC LIMIT 8');
$recentApplicationsStmt->bind_param('i', $companyId);
$recentApplicationsStmt->execute();
$recentApplications = $recentApplicationsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$recentApplicationsStmt->close();

$recentJobsStmt = $conn->prepare('SELECT id, title, status, applications_count, location, work_mode, created_at FROM jobs WHERE company_id = ? ORDER BY created_at DESC LIMIT 6');
$recentJobsStmt->bind_param('i', $companyId);
$recentJobsStmt->execute();
$recentJobs = $recentJobsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$recentJobsStmt->close();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<section class="admin-shell">
    <div class="admin-hero">
        <div>
            <span class="eyebrow">Company Panel</span>
            <h1>Dashboard</h1>
            <p>Manage your hiring activity, review applications, and keep your jobs up to date.</p>
        </div>
        <div class="admin-hero-actions">
            <div class="admin-user-summary">
                <div class="admin-user-avatar"><?= htmlspecialchars(strtoupper(substr($companyName, 0, 1)), ENT_QUOTES, 'UTF-8') ?></div>
                <div>
                    <strong><?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8') ?></strong>
                    <span><?= htmlspecialchars($companyEmail, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </div>
            <?= renderLogoutForm('Logout', 'btn-secondary btn-inline') ?>
        </div>
    </div>

    <div class="dashboard-grid stats-grid-4">
        <article class="stat-card">
            <span>Total Jobs</span>
            <strong><?= number_format($totalJobs) ?></strong>
        </article>
        <article class="stat-card">
            <span>Active Jobs</span>
            <strong><?= number_format($activeJobs) ?></strong>
        </article>
        <article class="stat-card">
            <span>Total Applications</span>
            <strong><?= number_format($totalApplications) ?></strong>
        </article>
        <article class="stat-card">
            <span>Role</span>
            <strong>Company</strong>
        </article>
    </div>

    <div class="dashboard-grid stats-grid-2 mt-3">
        <article class="stat-card subtle">
            <span>Quick Action</span>
            <strong><a href="<?= appUrl('pages/jobs.php') ?>">Browse Jobs</a></strong>
        </article>
        <article class="stat-card subtle">
            <span>Quick Action</span>
            <strong><a href="<?= appUrl('pages/developers.php') ?>">Browse Developers</a></strong>
        </article>
        <article class="stat-card subtle">
            <span>Support</span>
            <strong><a href="<?= appUrl('pages/contact.php') ?>">Contact Team</a></strong>
        </article>
    </div>

    <div class="profile-panels panel-top-spacing">
        <section class="panel">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">Jobs</span>
                    <h2>Recent postings</h2>
                </div>
            </div>
            <?php if (!empty($recentJobs)): ?>
                <div class="profile-list">
                    <?php foreach ($recentJobs as $job): ?>
                        <article class="profile-item-card">
                            <div>
                                <strong><?= htmlspecialchars($job['title'] ?? 'Untitled job', ENT_QUOTES, 'UTF-8') ?></strong>
                                <p><?= htmlspecialchars($job['location'] ?? 'Remote', ENT_QUOTES, 'UTF-8') ?> &middot; <?= htmlspecialchars($job['work_mode'] ?? 'remote', ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                            <div class="profile-item-meta">
                                <span class="status-badge status-<?= htmlspecialchars($job['status'] ?? 'active', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ucfirst($job['status'] ?? 'active'), ENT_QUOTES, 'UTF-8') ?></span>
                                <span><?= number_format((int) ($job['applications_count'] ?? 0)) ?> applications</span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">No jobs have been posted yet.</div>
            <?php endif; ?>
        </section>

        <section class="panel">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">Applications</span>
                    <h2>Recent candidates</h2>
                </div>
            </div>
            <?php if (!empty($recentApplications)): ?>
                <div class="profile-list">
                    <?php foreach ($recentApplications as $application): ?>
                        <article class="profile-item-card">
                            <div>
                                <strong><?= htmlspecialchars($application['full_name'] ?? 'Applicant', ENT_QUOTES, 'UTF-8') ?></strong>
                                <p><?= htmlspecialchars($application['job_title'] ?? 'Job', ENT_QUOTES, 'UTF-8') ?> &middot; <?= htmlspecialchars($application['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                            <div class="profile-item-meta">
                                <span class="status-badge status-<?= htmlspecialchars($application['status'] ?? 'pending', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ucfirst($application['status'] ?? 'pending'), ENT_QUOTES, 'UTF-8') ?></span>
                                <span><?= htmlspecialchars(date('M j, Y', strtotime((string) $application['created_at'])), ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">No applications received yet.</div>
            <?php endif; ?>
        </section>
    </div>
</section>

<?php include '../includes/footer.php'; ?>