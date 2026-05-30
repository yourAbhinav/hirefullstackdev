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

$stats = [
    'applications' => count($applications),
    'saved_jobs' => count($savedJobs),
    'messages' => count($messages),
    'profile_strength' => 0,
];

$completenessFields = [
    $profileName,
    $profileEmail,
    $profilePhone,
    $profileExperience,
    $profileTechStack,
    $profileBio,
    $profilePortfolio,
    $profilePhoto,
];

foreach ($completenessFields as $field) {
    if (trim((string) $field) !== '') {
        $stats['profile_strength'] += 12;
    }
}

$stats['profile_strength'] = min(100, $stats['profile_strength']);

include '../includes/header.php';
include '../includes/navbar.php';
?>

<section class="profile-shell">
    <style>
        .profile-shell {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
            padding: 34px 0 88px;
        }

        .profile-hero {
            position: relative;
            overflow: hidden;
            border-radius: 24px;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 62%, #334155 100%);
            color: #fff;
            padding: 28px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.14);
            margin-bottom: 24px;
        }

        .profile-hero::after {
            content: '';
            position: absolute;
            inset: auto -10% -30% auto;
            width: 360px;
            height: 360px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255,255,255,0.18), rgba(255,255,255,0));
            pointer-events: none;
        }

        .profile-hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.55fr) minmax(320px, 0.85fr);
            gap: 24px;
            align-items: stretch;
            position: relative;
            z-index: 1;
        }

        .profile-cover-copy h1 {
            font-size: clamp(1.9rem, 3vw, 3rem);
            line-height: 1.02;
            margin: 12px 0 10px;
            letter-spacing: -0.03em;
        }

        .profile-cover-copy p {
            max-width: 62ch;
            line-height: 1.7;
            color: rgba(255, 255, 255, 0.8);
        }

        .profile-badges {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .profile-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.14);
            color: #fff;
            font-size: 0.84rem;
        }

        .profile-card {
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.9), rgba(30, 41, 59, 0.92));
            border: 1px solid rgba(148, 163, 184, 0.18);
            backdrop-filter: blur(12px);
            border-radius: 20px;
            padding: 22px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 18px;
        }

        .profile-card-top {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .profile-avatar-wrap {
            position: relative;
            flex-shrink: 0;
        }

        .profile-avatar {
            width: 90px;
            height: 90px;
            border-radius: 22px;
            object-fit: cover;
            border: 4px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.35);
        }

        .profile-avatar-fallback {
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, #f59e0b, #ef4444);
            font-size: 2rem;
            font-weight: 800;
        }

        .profile-meta-stack strong {
            display: block;
            font-size: 1.02rem;
            margin-bottom: 4px;
        }

        .profile-meta-stack span {
            display: block;
            color: rgba(255, 255, 255, 0.78);
            margin-top: 4px;
        }

        .profile-edit-toggle {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            align-self: flex-start;
            border: 1px solid rgba(255, 255, 255, 0.16);
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
            border-radius: 999px;
            padding: 10px 14px;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.2s ease, border-color 0.2s ease;
        }

        .profile-edit-toggle:hover {
            background: rgba(255, 255, 255, 0.14);
            transform: translateY(-1px);
            border-color: rgba(255, 255, 255, 0.24);
        }

        .profile-edit-toggle i {
            font-size: 0.9rem;
        }

        .profile-score {
            border-radius: 16px;
            padding: 14px 16px;
            background: rgba(15, 23, 42, 0.28);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .profile-score-row {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: center;
            margin-bottom: 10px;
        }

        .profile-score-bar {
            height: 10px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
            overflow: hidden;
        }

        .profile-score-fill {
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #22c55e, #38bdf8, #a78bfa);
            width: <?= (int) $stats['profile_strength'] ?>%;
        }

        .profile-quick-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 18px;
        }

        .profile-quick-actions a {
            text-decoration: none;
            border-radius: 12px;
            padding: 11px 14px;
            font-weight: 650;
            font-size: 0.92rem;
        }

        .profile-quick-primary {
            background: #fff;
            color: #0f172a;
        }

        .profile-quick-secondary {
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.16);
        }

        .profile-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 24px;
        }

        .profile-stat {
            background: #fff;
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 16px;
            padding: 16px 18px;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.04);
        }

        .profile-stat span {
            display: block;
            color: #64748b;
            font-size: 0.82rem;
        }

        .profile-stat strong {
            display: block;
            margin-top: 6px;
            font-size: 1.25rem;
            color: #0f172a;
        }

        .profile-content-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(0, 0.85fr);
            gap: 22px;
        }

        .profile-editor {
            margin-bottom: 22px;
            background: linear-gradient(135deg, #0f172a 0%, #111827 58%, #1d4ed8 100%);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 18px;
            padding: 20px;
            box-shadow: 0 16px 44px rgba(15, 23, 42, 0.12);
            color: #e2e8f0;
        }

        .profile-editor.is-collapsed {
            display: none;
        }

        .profile-editor-header {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: start;
            margin-bottom: 16px;
        }

        .profile-editor-header h2 {
            margin: 4px 0 0;
            font-size: 1.05rem;
            color: #fff;
        }

        .profile-editor-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .profile-editor-grid .form-group {
            margin: 0;
        }

        .profile-editor-grid label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: rgba(226, 232, 240, 0.9);
            margin-bottom: 6px;
        }

        .profile-editor-grid input,
        .profile-editor-grid textarea,
        .profile-editor-grid select {
            width: 100%;
            border-radius: 12px;
            border: 1px solid rgba(148, 163, 184, 0.18);
            background: rgba(15, 23, 42, 0.55);
            color: #fff;
            padding: 11px 12px;
            font-size: 0.92rem;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .profile-editor-grid input:focus,
        .profile-editor-grid textarea:focus,
        .profile-editor-grid select:focus {
            border-color: #60a5fa;
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.14);
        }

        .profile-editor-grid textarea {
            min-height: 116px;
            resize: vertical;
        }

        .profile-editor-full {
            grid-column: 1 / -1;
        }

        .profile-editor-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 18px;
        }

        .profile-editor-actions .btn-primary,
        .profile-editor-actions .btn-secondary {
            border-radius: 12px;
            padding: 12px 18px;
            font-weight: 600;
        }

        .profile-editor-actions .btn-primary {
            background: linear-gradient(135deg, #0f172a, #2563eb);
            color: #fff;
        }

        .profile-editor-actions .btn-secondary {
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.14);
        }

        .profile-panel {
            background: linear-gradient(135deg, #0f172a 0%, #111827 58%, #1f2937 100%);
            border: 1px solid rgba(59, 130, 246, 0.16);
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 16px 44px rgba(15, 23, 42, 0.12);
            color: #e2e8f0;
        }

        .profile-panel-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            gap: 16px;
            margin-bottom: 20px;
        }

        .profile-panel-header h2 {
            margin: 4px 0 0;
            font-size: 1.1rem;
            color: #fff;
        }

        .profile-details-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .profile-detail {
            border-radius: 14px;
            padding: 14px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .profile-detail span {
            display: block;
            color: rgba(226, 232, 240, 0.7);
            font-size: 0.78rem;
            margin-bottom: 6px;
        }

        .profile-detail strong {
            color: #fff;
            font-size: 0.9rem;
            line-height: 1.55;
            word-break: break-word;
        }

        .profile-bio {
            margin-top: 16px;
            padding: 16px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .profile-bio p {
            margin: 8px 0 0;
            color: rgba(226, 232, 240, 0.82);
            line-height: 1.75;
        }

        .profile-list {
            display: grid;
            gap: 14px;
        }

        .profile-item {
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.05);
            padding: 14px;
        }

        .profile-item-top {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: start;
        }

        .profile-item-title {
            margin: 0;
            font-size: 0.95rem;
            color: #fff;
        }

        .profile-item-subtitle {
            margin: 6px 0 0;
            color: rgba(226, 232, 240, 0.72);
            line-height: 1.5;
            font-size: 0.87rem;
        }

        .profile-item-meta {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 12px;
        }

        .profile-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            padding: 6px 9px;
            background: rgba(96, 165, 250, 0.16);
            color: #dbeafe;
            font-size: 0.76rem;
            font-weight: 600;
        }

        .profile-side-stack {
            display: grid;
            gap: 18px;
        }

        .profile-mini-list {
            display: grid;
            gap: 12px;
        }

        .profile-mini-item {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: start;
            padding: 12px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .profile-mini-item strong {
            display: block;
            color: #fff;
            margin-bottom: 5px;
        }

        .profile-mini-item span,
        .profile-muted {
            color: rgba(226, 232, 240, 0.72);
            font-size: 0.84rem;
            line-height: 1.5;
        }

        .profile-empty {
            border-radius: 14px;
            border: 1px dashed rgba(255, 255, 255, 0.12);
            background: rgba(255, 255, 255, 0.04);
            padding: 16px;
            color: rgba(226, 232, 240, 0.72);
            font-size: 0.88rem;
        }

        .profile-section-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        @media (max-width: 1024px) {
            .profile-hero-grid,
            .profile-content-grid {
                grid-template-columns: 1fr;
            }

            .profile-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .profile-shell {
                width: min(100% - 20px, 1180px);
                padding-top: 20px;
            }

            .profile-hero,
            .profile-panel {
                border-radius: 20px;
                padding: 20px;
            }

            .profile-stats,
            .profile-details-grid {
                grid-template-columns: 1fr;
            }

            .profile-card-top {
                align-items: flex-start;
            }

            .profile-avatar {
                width: 84px;
                height: 84px;
                border-radius: 22px;
            }
        }
    </style>

    <div class="profile-hero">
        <div class="profile-hero-grid">
            <div class="profile-cover-copy">
                <span class="eyebrow">Account Hub</span>
                <h1><?= escape($profileName) ?></h1>
                <p><?= escape($profileBio !== '' ? $profileBio : 'Build your career profile, track your applications, and keep your saved opportunities organized in one polished workspace.') ?></p>

                <div class="profile-badges">
                    <span class="profile-badge"><i class="fas fa-user-circle"></i> <?= escape(roleLabel($profileRole)) ?></span>
                    <span class="profile-badge"><i class="fas fa-envelope"></i> <?= escape($profileProvider !== '' ? ucfirst($profileProvider) : 'Password') ?></span>
                    <span class="profile-badge"><i class="fas fa-shield-alt"></i> <?= !empty($profile['verified']) ? 'Verified' : 'Not verified' ?></span>
                </div>

                <div class="profile-quick-actions">
                    <a href="<?= appUrl('pages/applications.php') ?>" class="profile-quick-primary">My Applications</a>
                    <a href="<?= appUrl('pages/jobs.php') ?>" class="profile-quick-secondary">Browse Jobs</a>
                    <a href="<?= appUrl('pages/contact.php') ?>" class="profile-quick-secondary">Support</a>
                </div>
            </div>

            <div class="profile-card">
                <div class="profile-card-top">
                    <div class="profile-avatar-wrap">
                        <?php if (!empty($profilePhotoUrl)): ?>
                            <img src="<?= escape($profilePhotoUrl) ?>" alt="Profile photo" class="profile-avatar">
                        <?php else: ?>
                            <div class="profile-avatar profile-avatar-fallback"><?= escape(strtoupper(substr($profileName, 0, 1))) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="profile-meta-stack">
                        <strong><?= escape($profileName) ?></strong>
                        <span><?= escape($profileEmail) ?></span>
                        <span><?= escape($profilePhone !== '' ? $profilePhone : 'Phone not set') ?></span>
                    </div>
                </div>

                <div class="profile-score">
                    <div class="profile-score-row">
                        <strong>Profile strength</strong>
                        <span><?= (int) $stats['profile_strength'] ?>%</span>
                    </div>
                    <div class="profile-score-bar">
                        <div class="profile-score-fill"></div>
                    </div>
                </div>

                <button type="button" class="profile-edit-toggle" id="profileEditToggle">
                    <i class="fas fa-pen"></i>
                    Edit profile
                </button>

                <div class="profile-muted">
                    Keep filling out your profile to improve visibility to recruiters and make your account feel complete.
                </div>
            </div>
        </div>
    </div>

    <div class="profile-stats">
        <article class="profile-stat">
            <span>Applications</span>
            <strong><?= number_format($stats['applications']) ?></strong>
        </article>
        <article class="profile-stat">
            <span>Saved Jobs</span>
            <strong><?= number_format($stats['saved_jobs']) ?></strong>
        </article>
        <article class="profile-stat">
            <span>Messages</span>
            <strong><?= number_format($stats['messages']) ?></strong>
        </article>
        <article class="profile-stat">
            <span>Strength</span>
            <strong><?= (int) $stats['profile_strength'] ?>%</strong>
        </article>
    </div>

    <section class="profile-editor is-collapsed" id="profileEditor">
        <div class="profile-editor-header">
            <div>
                <span class="eyebrow">Edit Profile</span>
                <h2>Update your basics</h2>
            </div>
            <span class="profile-muted">You control your name, phone, experience, skills, portfolio, bio, and photo.</span>
        </div>

        <form method="POST" action="<?= appUrl('pages/profile_update.php') ?>">
            <?= csrfField() ?>
            <div class="profile-editor-grid">
                <div class="form-group">
                    <label for="profileFullName">Full Name</label>
                    <input type="text" id="profileFullName" name="fullName" value="<?= escape($profileName) ?>" maxlength="120" required>
                </div>
                <div class="form-group">
                    <label for="profilePhone">Phone Number</label>
                    <input type="tel" id="profilePhone" name="phone" value="<?= escape($profilePhone) ?>" placeholder="+1 (555) 123-4567">
                </div>
                <div class="form-group">
                    <label for="profileExperience">Experience</label>
                    <input type="text" id="profileExperience" name="experience" value="<?= escape($profileExperience) ?>" placeholder="3-5 Years">
                </div>
                <div class="form-group">
                    <label for="profileTechStack">Tech Stack</label>
                    <input type="text" id="profileTechStack" name="techStack" value="<?= escape($profileTechStack) ?>" placeholder="React, Node.js, MySQL">
                </div>
                <div class="form-group profile-editor-full">
                    <label for="profilePortfolio">Portfolio URL</label>
                    <input type="url" id="profilePortfolio" name="portfolio_url" value="<?= escape($profilePortfolio) ?>" placeholder="https://yourportfolio.com">
                </div>
                <div class="form-group profile-editor-full">
                    <label for="profileBio">Bio</label>
                    <textarea id="profileBio" name="bio" placeholder="Tell recruiters what you do best..."><?= escape($profileBio) ?></textarea>
                </div>
                <div class="form-group profile-editor-full">
                    <label for="profileImage">Profile Photo URL</label>
                    <input type="url" id="profileImage" name="profile_image" value="<?= escape($profilePhoto) ?>" placeholder="https://example.com/photo.jpg">
                </div>
            </div>

            <div class="profile-editor-actions">
                <button type="submit" class="btn-primary">Save Profile</button>
                <a href="<?= appUrl('pages/profile.php') ?>" class="btn-secondary">Reset</a>
            </div>
        </form>
    </section>

    <div class="profile-content-grid">
        <section class="profile-panel">
            <div class="profile-panel-header">
                <div>
                    <span class="eyebrow">About</span>
                    <h2>Profile details</h2>
                </div>
            </div>

            <div class="profile-details-grid">
                <div class="profile-detail"><span>Full Name</span><strong><?= escape($profileName) ?></strong></div>
                <div class="profile-detail"><span>Email</span><strong><?= escape($profileEmail) ?></strong></div>
                <div class="profile-detail"><span>Phone</span><strong><?= escape($profilePhone !== '' ? $profilePhone : 'Not set') ?></strong></div>
                <div class="profile-detail"><span>Experience</span><strong><?= escape($profileExperience !== '' ? $profileExperience : 'Not set') ?></strong></div>
                <div class="profile-detail"><span>Tech Stack</span><strong><?= escape($profileTechStack !== '' ? $profileTechStack : 'Not set') ?></strong></div>
                <div class="profile-detail"><span>Portfolio</span><strong><?= !empty($profilePortfolio) ? '<a href="' . escape($profilePortfolio) . '" target="_blank" rel="noopener noreferrer">Open portfolio</a>' : 'Not set' ?></strong></div>
            </div>

            <?php if (!empty($profileBio)): ?>
                <div class="profile-bio">
                    <span class="eyebrow">Bio</span>
                    <p><?= escape($profileBio) ?></p>
                </div>
            <?php endif; ?>
        </section>

        <div class="profile-side-stack">
            <section class="profile-panel">
                <div class="profile-panel-header">
                    <div>
                        <span class="eyebrow">Activity</span>
                        <h2>Recent applications</h2>
                    </div>
                    <a href="<?= appUrl('pages/applications.php') ?>">View all</a>
                </div>

                <?php if (!empty($applications)): ?>
                    <div class="profile-list">
                        <?php foreach ($applications as $application): ?>
                            <article class="profile-item">
                                <div class="profile-item-top">
                                    <div>
                                        <h3 class="profile-item-title"><?= escape($application['job_position'] ?? 'Application') ?></h3>
                                        <p class="profile-item-subtitle"><?= escape($application['job_id'] ? 'Job ID ' . (int) $application['job_id'] : 'Submitted application') ?></p>
                                    </div>
                                    <span class="profile-pill"><?= escape(ucfirst($application['status'] ?? 'pending')) ?></span>
                                </div>
                                <div class="profile-item-meta">
                                    <span class="profile-pill"><i class="fas fa-calendar-alt"></i> <?= escape(date('M j, Y', strtotime((string) $application['created_at']))) ?></span>
                                    <?php if (!empty($application['rating'])): ?>
                                        <span class="profile-pill"><i class="fas fa-star"></i> <?= escape((string) $application['rating']) ?>/5</span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($application['feedback'])): ?>
                                    <p class="profile-item-subtitle">Feedback: <?= escape($application['feedback']) ?></p>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="profile-empty">You have not submitted any applications yet.</div>
                <?php endif; ?>
            </section>

            <section class="profile-panel">
                <div class="profile-panel-header">
                    <div>
                        <span class="eyebrow">Saved & Inbox</span>
                        <h2>Shortlist and messages</h2>
                    </div>
                </div>

                <div class="profile-section-title">
                    <strong>Saved jobs</strong>
                </div>
                <?php if (!empty($savedJobs)): ?>
                    <div class="profile-mini-list" style="margin-bottom: 18px;">
                        <?php foreach ($savedJobs as $savedJob): ?>
                            <article class="profile-mini-item">
                                <div>
                                    <strong><?= escape($savedJob['title'] ?? 'Untitled role') ?></strong>
                                    <span><?= escape(($savedJob['location'] ?? 'Remote') . ' · ' . ($savedJob['work_mode'] ?? 'remote')) ?></span>
                                </div>
                                <span class="profile-pill"><?= escape($savedJob['status'] ?? 'active') ?></span>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="profile-empty" style="margin-bottom: 18px;">No saved jobs yet. Save roles to build a shortlist.</div>
                <?php endif; ?>

                <div class="profile-section-title">
                    <strong>Recent messages</strong>
                </div>
                <?php if (!empty($messages)): ?>
                    <div class="profile-mini-list">
                        <?php foreach ($messages as $message): ?>
                            <article class="profile-mini-item">
                                <div>
                                    <strong><?= escape($message['subject'] ?: 'Message') ?></strong>
                                    <span><?= escape($message['sender_name'] ?? $message['receiver_name'] ?? 'Conversation') ?></span>
                                    <span><?= escape(strlen((string) ($message['message'] ?? '')) > 90 ? substr((string) $message['message'], 0, 90) . '...' : (string) ($message['message'] ?? '')) ?></span>
                                </div>
                                <span class="profile-pill"><?= !empty($message['read_status']) ? 'Read' : 'Unread' ?></span>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="profile-empty">No messages yet. Recruiters and teammates will appear here.</div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</section>

<script>
    const profileEditToggle = document.getElementById('profileEditToggle');
    const profileEditor = document.getElementById('profileEditor');

    if (profileEditToggle && profileEditor) {
        profileEditToggle.addEventListener('click', () => {
            profileEditor.classList.toggle('is-collapsed');
            if (!profileEditor.classList.contains('is-collapsed')) {
                profileEditor.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }
</script>

<?php include '../includes/footer.php'; ?>