<?php

require_once '../config/db.php';
requireDeveloper();

$page_title = 'Apply Now - DevHire';
$css_path = appUrl('assets/css/style.css');
$js_path = appUrl('assets/js/main.js');

function applicationImageUrl(string $value): string
{
    if ($value === '') {
        return '';
    }

    if (preg_match('#^https?://#i', $value)) {
        return $value;
    }

    return appUrl(ltrim($value, '/'));
}

$selectedJob = null;
$jobId = isset($_GET['job_id']) ? (int) $_GET['job_id'] : null;
$currentName = currentUserName();
$currentEmail = currentUserEmail();
$currentPhone = '';
$currentExperience = '';
$currentTechStack = '';
$currentPortfolio = '';
$currentBio = '';

if ($jobId !== null && $jobId > 0) {
    $jobLookup = $conn->prepare('SELECT id, title, company_id, location, job_type, work_mode, status FROM jobs WHERE id = ? LIMIT 1');
    $jobLookup->bind_param('i', $jobId);
    $jobLookup->execute();
    $selectedJob = $jobLookup->get_result()->fetch_assoc() ?: null;
    $jobLookup->close();

    if (empty($selectedJob) || ($selectedJob['status'] ?? '') !== 'active') {
        $selectedJob = null;
        $jobId = null;
    }
}

$profileUserId = (int) currentUserId();
$profileStmt = $conn->prepare('SELECT fullName, email, phone, experience, techStack, portfolio_url, bio FROM users WHERE id = ? LIMIT 1');
$profileStmt->bind_param('i', $profileUserId);
$profileStmt->execute();
$profile = $profileStmt->get_result()->fetch_assoc() ?: [];
$profileStmt->close();

if (!empty($profile)) {
    $currentName = (string) ($profile['fullName'] ?? $currentName);
    $currentEmail = (string) ($profile['email'] ?? $currentEmail);
    $currentPhone = (string) ($profile['phone'] ?? $currentPhone);
    $currentExperience = (string) ($profile['experience'] ?? $currentExperience);
    $currentTechStack = (string) ($profile['techStack'] ?? $currentTechStack);
    $currentPortfolio = (string) ($profile['portfolio_url'] ?? $currentPortfolio);
    $currentBio = (string) ($profile['bio'] ?? $currentBio);
}

$successMessage = getFlash('success');
$errorMessage = getFlash('error');

if ($selectedJob !== null) {
    $currentJobPosition = (string) ($selectedJob['title'] ?? '');
} else {
    $currentJobPosition = 'General Application';
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<section class="page-hero">
    <div class="page-hero-inner">
        <span class="eyebrow">Application Portal</span>
        <h1>Apply Now</h1>
        <p class="quick-apply-subtitle">Submit your application and let us connect you with amazing companies.</p>
    </div>
</section>

<section class="featured-jobs" style="padding: 4rem 2rem;">
    <div class="apply-layout-single">
        <?php if (!empty($successMessage)): ?>
            <div class="notice notice-success">
                <i class="fas fa-check-circle"></i>
                <div>
                    <strong>Application submitted</strong>
                    <p><?= escape($successMessage) ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>
            <div class="notice notice-error">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    <strong>Review the following</strong>
                    <p><?= escape($errorMessage) ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($selectedJob !== null): ?>
            <div class="notice notice-info" style="margin-bottom: 1.5rem;">
                <i class="fas fa-briefcase"></i>
                <div>
                    <strong>Applying for a specific role</strong>
                    <p><?= escape($selectedJob['title'] ?? 'Selected job') ?><?= !empty($selectedJob['location']) ? ' &middot; ' . escape($selectedJob['location']) : '' ?></p>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= appUrl('handlers/apply.php') ?>" enctype="multipart/form-data" class="apply-form apply-form--stacked" novalidate>
            <?= csrfField() ?>
            <input type="hidden" name="redirect_to" value="<?= escape(isset($jobId) && $jobId !== null ? 'pages/apply.php?job_id=' . (int) $jobId : 'pages/apply.php') ?>">
            <?php if ($jobId !== null): ?>
                <input type="hidden" name="job_id" value="<?= (int) $jobId ?>">
            <?php endif; ?>

            <div class="form-grid form-grid-two">
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" placeholder="John Doe" value="<?= escape($currentName) ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" placeholder="john@example.com" value="<?= escape($currentEmail) ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="tel" id="phone" name="phone" placeholder="+1 (555) 123-4567" value="<?= escape($currentPhone) ?>" required>
                </div>

                <div class="form-group">
                    <label for="experience">Years of Experience *</label>
                    <select id="experience" name="experience" required>
                        <option value="">Select experience level</option>
                        <option value="0-1" <?= $currentExperience === '0-1' ? 'selected' : '' ?>>0-1 Years</option>
                        <option value="1-3" <?= $currentExperience === '1-3' ? 'selected' : '' ?>>1-3 Years</option>
                        <option value="3-5" <?= $currentExperience === '3-5' ? 'selected' : '' ?>>3-5 Years</option>
                        <option value="5-10" <?= $currentExperience === '5-10' ? 'selected' : '' ?>>5-10 Years</option>
                        <option value="10+" <?= $currentExperience === '10+' ? 'selected' : '' ?>>10+ Years</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="tech_stack">Primary Tech Stack *</label>
                    <select id="tech_stack" name="tech_stack" required>
                        <option value="">Select tech stack</option>
                        <option value="javascript" <?= $currentTechStack === 'javascript' ? 'selected' : '' ?>>JavaScript / Node.js</option>
                        <option value="react" <?= $currentTechStack === 'react' ? 'selected' : '' ?>>React / Frontend</option>
                        <option value="python" <?= $currentTechStack === 'python' ? 'selected' : '' ?>>Python / Backend</option>
                        <option value="java" <?= $currentTechStack === 'java' ? 'selected' : '' ?>>Java / Spring</option>
                        <option value="devops" <?= $currentTechStack === 'devops' ? 'selected' : '' ?>>DevOps / Cloud</option>
                        <option value="full-stack" <?= $currentTechStack === 'full-stack' ? 'selected' : '' ?>>Full Stack</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="job_position">Desired Position *</label>
                    <input type="text" id="job_position" name="job_position" placeholder="Full Stack Developer" value="<?= escape($currentJobPosition) ?>" required>
                </div>
            </div>

            <div class="form-grid form-grid-two">
                <div class="form-group">
                    <label for="portfolio_url">Portfolio URL</label>
                    <input type="url" id="portfolio_url" name="portfolio_url" placeholder="https://yourportfolio.com" value="<?= escape($currentPortfolio) ?>">
                </div>

                <div class="form-group">
                    <label for="resume">Upload Resume</label>
                    <input type="file" id="resume" name="resume" accept=".pdf,.doc,.docx">
                </div>
            </div>

            <div class="form-group">
                <label for="message">Additional Message</label>
                <textarea id="message" name="message" placeholder="Tell us about yourself and why you're a great fit..." rows="6"><?= escape($currentBio) ?></textarea>
            </div>

            <button type="submit" class="btn-submit btn-block">
                <i class="fas fa-paper-plane"></i> Submit Application
            </button>
        </form>
    </div>
</section>

<?php include '../includes/footer.php'; ?>