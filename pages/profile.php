<?php

require_once '../config/db.php';
requireDeveloper();

$page_title = 'My Profile - DevHire';
$css_path = appUrl('assets/css/style.css');
$js_path = appUrl('assets/js/main.js');

function profileImageUrl(string $value): string
{
    if ($value === '') {
        return '';
    }

    if (preg_match('#^https?://#i', $value)) {
        return $value;
    }

    return appUrl(ltrim($value, '/'));
}

$userId = (int) currentUserId();

$profileStmt = $conn->prepare('SELECT u.id, u.fullName, u.email, u.phone, u.experience, u.techStack, u.portfolio_url, u.bio, u.profile_image, u.role, u.provider, u.firebase_uid, u.company_name, u.company_description, u.verified, u.created_at AS user_created_at, u.updated_at AS user_updated_at FROM users u WHERE u.id = ? LIMIT 1');
$profileStmt->bind_param('i', $userId);
$profileStmt->execute();
$profile = $profileStmt->get_result()->fetch_assoc() ?: [];
$profileStmt->close();

if (empty($profile)) {
    $profile = [
        'id' => $userId,
        'fullName' => currentUserName(),
        'email' => currentUserEmail(),
        'phone' => '',
        'experience' => '',
        'techStack' => '',
        'portfolio_url' => '',
        'bio' => '',
        'profile_image' => '',
        'role' => currentUserRole(),
        'provider' => $_SESSION['user_provider'] ?? '',
        'verified' => 0,
    ];
}

$applicationStmt = $conn->prepare('SELECT id, full_name, email, phone, experience, tech_stack, job_position, portfolio_url, message, resume_path, job_id, status, feedback, rating, created_at FROM applications WHERE user_id = ? ORDER BY created_at DESC LIMIT 12');
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

$profileName = (string) ($profile['fullName'] ?? currentUserName());
$profileEmail = (string) ($profile['email'] ?? currentUserEmail());
$profilePhone = (string) ($profile['phone'] ?? '');
$profileExperience = (string) ($profile['experience'] ?? '');
$profileTechStack = (string) ($profile['techStack'] ?? '');
$profileProvider = (string) ($profile['provider'] ?? ($_SESSION['user_provider'] ?? 'password'));
$profileRole = strtolower((string) ($profile['role'] ?? currentUserRole()));
$profilePhoto = (string) ($profile['profile_image'] ?? currentUserPhoto());
$profilePhotoUrl = profileImageUrl($profilePhoto);
$profilePortfolio = (string) ($profile['portfolio_url'] ?? '');
$profileBio = (string) ($profile['bio'] ?? '');

include '../includes/header.php';
include '../includes/navbar.php';
?>

<section class="admin-shell profile-shell">
    <div class="admin-hero profile-hero">
        <div>
            <span class="eyebrow">Account</span>
            <h1>My Profile</h1>
            <p>Track your saved jobs, application history, and recent messages in one place.</p>
        </div>
        <div class="profile-hero-card">
            <?php if (!empty($profilePhotoUrl)): ?>
                <img src="<?= escape($profilePhotoUrl) ?>" alt="Profile photo" class="profile-avatar">
            <?php else: ?>
                <div class="profile-avatar profile-avatar-fallback"><?= escape(strtoupper(substr($profileName, 0, 1))) ?></div>
            <?php endif; ?>
            <div>
                <strong><?= escape($profileName) ?></strong>
                <span><?= escape($profileEmail) ?></span>
                <span class="profile-meta"><?= escape(roleLabel($profileRole)) ?> &middot; <?= escape(ucfirst($profileProvider ?: 'password')) ?></span>
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
            <span>Role</span>
            <strong><?= escape(roleLabel($profileRole)) ?></strong>
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
                <div><span>Full Name</span><strong><?= escape($profileName) ?></strong></div>
                <div><span>Email</span><strong><?= escape($profileEmail) ?></strong></div>
                <div><span>Phone</span><strong><?= escape($profilePhone !== '' ? $profilePhone : 'Not set') ?></strong></div>
                <div><span>Experience</span><strong><?= escape($profileExperience !== '' ? $profileExperience : 'Not set') ?></strong></div>
                <div><span>Tech Stack</span><strong><?= escape($profileTechStack !== '' ? $profileTechStack : 'Not set') ?></strong></div>
                <div><span>Portfolio</span>
                    <strong>
                        <?php if (!empty($profilePortfolio)): ?>
                            <a href="<?= escape($profilePortfolio) ?>" target="_blank" rel="noopener noreferrer">View portfolio</a>
                        <?php else: ?>
                            Not set
                        <?php endif; ?>
                    </strong>
                </div>
                <div><span>Provider</span><strong><?= escape(ucfirst($profileProvider !== '' ? $profileProvider : 'password')) ?></strong></div>
                <div><span>Role</span><strong><?= escape(roleLabel($profileRole)) ?></strong></div>
            </div>

            <?php if (!empty($profileBio)): ?>
                <div class="profile-bio-block">
                    <span class="eyebrow">Bio</span>
                    <p class="profile-bio-copy">
                        <?= escape($profileBio) ?>
                    </p>
                </div>
            <?php endif; ?>
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
                                <strong><?= escape($savedJob['title'] ?? 'Untitled role') ?></strong>
                                <p><?= escape($savedJob['location'] ?? 'Remote') ?> &middot; <?= escape($savedJob['work_mode'] ?? 'remote') ?></p>
                            </div>
                            <div class="profile-item-meta">
                                <span><?= escape($savedJob['job_type'] ?? 'full-time') ?></span>
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
                                <strong><?= escape($application['job_position'] ?? 'Application') ?></strong>
                                <p><?= escape($application['email'] ?? '') ?></p>
                            </div>
                            <div class="profile-item-meta">
                                <span class="status-badge status-<?= escape($application['status'] ?? 'pending') ?>"><?= escape(ucfirst($application['status'] ?? 'pending')) ?></span>
                                <span><?= escape(date('M j, Y', strtotime((string) $application['created_at']))) ?></span>
                            </div>
                            <?php if (!empty($application['feedback'])): ?>
                                <p class="profile-note">Feedback: <?= escape($application['feedback']) ?></p>
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
                                <strong><?= escape($message['subject'] ?: 'Message') ?></strong>
                                <p><?= escape($message['sender_name'] ?? $message['receiver_name'] ?? 'Conversation') ?></p>
                            </div>
                            <div class="profile-item-meta">
                                <span><?= escape(date('M j, Y', strtotime((string) $message['created_at']))) ?></span>
                                <span><?= !empty($message['read_status']) ? 'Read' : 'Unread' ?></span>
                            </div>
                            <p class="profile-note"><?= escape(strlen((string) ($message['message'] ?? '')) > 160 ? substr((string) $message['message'], 0, 160) . '...' : (string) ($message['message'] ?? '')) ?></p>
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