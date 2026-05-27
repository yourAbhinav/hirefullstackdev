<?php

require_once '../config/db.php';
requireLogin();

$page_title = 'My Profile - DevHire';
$css_path = appUrl('assets/css/style.css');
$js_path = appUrl('assets/js/main.js');

$userId = (int) currentUserId();

$profileStmt = $conn->prepare('SELECT au.id, au.firebase_uid, au.name, au.email, au.photo, au.provider, au.created_at AS admin_created_at, u.fullName, u.phone, u.experience, u.techStack, u.portfolio_url, u.bio, u.profile_image, u.role, u.verified, u.created_at AS user_created_at FROM admin_users au LEFT JOIN users u ON u.id = au.id WHERE au.id = ? LIMIT 1');
$profileStmt->bind_param('i', $userId);
$profileStmt->execute();
$profile = $profileStmt->get_result()->fetch_assoc() ?: [];
$profileStmt->close();

$applicationStmt = $conn->prepare('SELECT id, email, phone, experience, jobPosition, portfolio, message, resume, job_id, status, feedback, rating, created_at FROM applications WHERE user_id = ? ORDER BY created_at DESC LIMIT 12');
$applicationStmt->bind_param('i', $userId);
$applicationStmt->execute();
$applications = $applicationStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$applicationStmt->close();

$savedJobsStmt = $conn->prepare('SELECT sj.id AS saved_id, sj.job_id, sj.created_at AS saved_at, j.title, j.company_id, j.location, j.job_type, j.work_mode, j.salary_min, j.salary_max, j.experience_level, j.tech_stack, j.status FROM saved_jobs sj LEFT JOIN jobs j ON j.id = sj.job_id WHERE sj.user_id = ? ORDER BY sj.created_at DESC LIMIT 12');
$savedJobsStmt->bind_param('i', $userId);
$savedJobsStmt->execute();
$savedJobs = $savedJobsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$savedJobsStmt->close();

$messageStmt = $conn->prepare('SELECT m.id, m.subject, m.message, m.read_status, m.created_at, s.fullName AS sender_name, s.email AS sender_email, r.fullName AS receiver_name, r.email AS receiver_email FROM messages m LEFT JOIN users s ON s.id = m.sender_id LEFT JOIN users r ON r.id = m.receiver_id WHERE m.sender_id = ? OR m.receiver_id = ? ORDER BY m.created_at DESC LIMIT 8');
$messageStmt->bind_param('ii', $userId, $userId);
$messageStmt->execute();
$messages = $messageStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$messageStmt->close();

$totalApplications = count($applications);
$totalSavedJobs = count($savedJobs);
$totalMessages = count($messages);

$profileName = $profile['name'] ?? $profile['fullName'] ?? currentUserName();
$profileEmail = $profile['email'] ?? currentUserEmail();
$profilePhoto = $profile['photo'] ?? $profile['profile_image'] ?? ($_SESSION['admin_photo'] ?? '');
$profileRole = $profile['role'] ?? ($_SESSION['role'] ?? 'admin');
$profileProvider = $profile['provider'] ?? ($_SESSION['auth_provider'] ?? 'google');

include '../includes/header.php';
include '../includes/navbar.php';
?>

<section class="admin-shell profile-shell">
    <div class="admin-hero profile-hero">
        <div>
            <span class="eyebrow">Account</span>
            <h1>My Profile</h1>
            <p>Track saved jobs, application history, and messages from one place.</p>
        </div>
        <div class="profile-hero-card">
            <?php if (!empty($profilePhoto)): ?>
                <img src="<?= htmlspecialchars($profilePhoto, ENT_QUOTES, 'UTF-8') ?>" alt="Profile photo" class="profile-avatar">
            <?php else: ?>
                <div class="profile-avatar profile-avatar-fallback"><?= htmlspecialchars(strtoupper(substr($profileName, 0, 1)), ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <div>
                <strong><?= htmlspecialchars($profileName, ENT_QUOTES, 'UTF-8') ?></strong>
                <span><?= htmlspecialchars($profileEmail, ENT_QUOTES, 'UTF-8') ?></span>
                <span class="profile-meta"><?= htmlspecialchars(ucfirst($profileRole), ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars(ucfirst($profileProvider), ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        </div>
    </div>

    <div class="dashboard-grid stats-grid-4">
        <article class="stat-card">
            <span>Applications</span>
            <strong><?= number_format($totalApplications) ?></strong>
        </article>
        <article class="stat-card">
            <span>Saved Jobs</span>
            <strong><?= number_format($totalSavedJobs) ?></strong>
        </article>
        <article class="stat-card">
            <span>Messages</span>
            <strong><?= number_format($totalMessages) ?></strong>
        </article>
        <article class="stat-card">
            <span>Account</span>
            <strong><?= htmlspecialchars(strtoupper(substr($profileName, 0, 1)), ENT_QUOTES, 'UTF-8') ?></strong>
        </article>
    </div>

    <div class="profile-panels">
        <section class="panel profile-summary-panel">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">Profile</span>
                    <h2>Details</h2>
                </div>
            </div>
            <div class="details-grid profile-details-grid">
                <div><span>Full Name</span><strong><?= htmlspecialchars($profileName, ENT_QUOTES, 'UTF-8') ?></strong></div>
                <div><span>Email</span><strong><?= htmlspecialchars($profileEmail, ENT_QUOTES, 'UTF-8') ?></strong></div>
                <div><span>Phone</span><strong><?= htmlspecialchars($profile['phone'] ?? 'Not set', ENT_QUOTES, 'UTF-8') ?></strong></div>
                <div><span>Experience</span><strong><?= htmlspecialchars($profile['experience'] ?? 'Not set', ENT_QUOTES, 'UTF-8') ?></strong></div>
                <div><span>Tech Stack</span><strong><?= htmlspecialchars($profile['techStack'] ?? 'Not set', ENT_QUOTES, 'UTF-8') ?></strong></div>
                <div><span>Portfolio</span><strong><?= !empty($profile['portfolio_url']) ? 'Available' : 'Not set' ?></strong></div>
            </div>
        </section>

        <section class="panel">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">Saved</span>
                    <h2>Jobs</h2>
                </div>
            </div>
            <?php if (!empty($savedJobs)): ?>
                <div class="profile-list">
                    <?php foreach ($savedJobs as $savedJob): ?>
                        <article class="profile-item-card">
                            <div>
                                <strong><?= htmlspecialchars($savedJob['title'] ?? 'Untitled role', ENT_QUOTES, 'UTF-8') ?></strong>
                                <p><?= htmlspecialchars(trim(($savedJob['location'] ?? 'Remote') . ' · ' . ($savedJob['work_mode'] ?? 'remote')), ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                            <div class="profile-item-meta">
                                <span><?= htmlspecialchars($savedJob['job_type'] ?? 'full-time', ENT_QUOTES, 'UTF-8') ?></span>
                                <span><?= !empty($savedJob['salary_min']) && !empty($savedJob['salary_max']) ? '$' . number_format((int) $savedJob['salary_min']) . ' - $' . number_format((int) $savedJob['salary_max']) : 'Salary hidden' ?></span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">No saved jobs yet. Browse open positions and save a few.</div>
            <?php endif; ?>
        </section>

        <section class="panel">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">History</span>
                    <h2>Applications</h2>
                </div>
            </div>
            <?php if (!empty($applications)): ?>
                <div class="profile-list">
                    <?php foreach ($applications as $application): ?>
                        <article class="profile-item-card">
                            <div>
                                <strong><?= htmlspecialchars($application['jobPosition'] ?? 'Application', ENT_QUOTES, 'UTF-8') ?></strong>
                                <p><?= htmlspecialchars($application['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                            <div class="profile-item-meta">
                                <span class="status-badge status-<?= htmlspecialchars($application['status'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ucfirst($application['status']), ENT_QUOTES, 'UTF-8') ?></span>
                                <span><?= htmlspecialchars(date('M j, Y', strtotime($application['created_at'])), ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <?php if (!empty($application['feedback'])): ?>
                                <p class="profile-note">Feedback: <?= htmlspecialchars($application['feedback'], ENT_QUOTES, 'UTF-8') ?></p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">No applications submitted yet.</div>
            <?php endif; ?>
        </section>

        <section class="panel">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">Messages</span>
                    <h2>Recent inbox</h2>
                </div>
            </div>
            <?php if (!empty($messages)): ?>
                <div class="profile-list">
                    <?php foreach ($messages as $message): ?>
                        <article class="profile-item-card">
                            <div>
                                <strong><?= htmlspecialchars($message['subject'] ?: 'Message', ENT_QUOTES, 'UTF-8') ?></strong>
                                <p><?= htmlspecialchars($message['sender_name'] ?? $message['receiver_name'] ?? 'Conversation', ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                            <div class="profile-item-meta">
                                <span><?= htmlspecialchars(date('M j, Y', strtotime($message['created_at'])), ENT_QUOTES, 'UTF-8') ?></span>
                                <span><?= !empty($message['read_status']) ? 'Read' : 'Unread' ?></span>
                            </div>
                            <p class="profile-note"><?= htmlspecialchars(strlen($message['message'] ?? '') > 160 ? substr($message['message'] ?? '', 0, 160) . '...' : ($message['message'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">No messages yet.</div>
            <?php endif; ?>
        </section>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
