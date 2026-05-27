<?php

require_once '../config/db.php';

$page_title = 'Apply Now - DevHire';
$css_path = appUrl('assets/css/style.css');
$js_path = appUrl('assets/js/main.js');

requireLogin();

$currentUserId = currentUserId();
$currentFullName = $_SESSION['admin_name'] ?? $_SESSION['fullName'] ?? '';
$currentEmail = $_SESSION['admin_email'] ?? $_SESSION['email'] ?? '';
$values = [
    'full_name' => $currentFullName,
    'email' => $currentEmail,
    'phone' => '',
    'experience' => '',
    'tech_stack' => '',
    'jobPosition' => '',
    'portfolio' => '',
    'message' => '',
    'job_id' => isset($_GET['job_id']) ? (int) $_GET['job_id'] : null,
];
$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please reload and try again.';
    }

    $values['full_name'] = sanitize($_POST['full_name'] ?? '');
    $values['email'] = sanitize($_POST['email'] ?? '');
    $values['phone'] = sanitize($_POST['phone'] ?? '');
    $values['experience'] = sanitize($_POST['experience'] ?? '');
    $values['tech_stack'] = sanitize($_POST['tech_stack'] ?? '');
    $values['jobPosition'] = sanitize($_POST['jobPosition'] ?? '');
    $values['portfolio'] = sanitize($_POST['portfolio'] ?? '');
    $values['message'] = sanitize($_POST['message'] ?? '');
    $values['job_id'] = !empty($_POST['job_id']) ? (int) $_POST['job_id'] : null;

    if ($values['full_name'] === '') {
        $errors[] = 'Full name is required.';
    }

    if ($values['email'] === '' || !validateEmail($values['email'])) {
        $errors[] = 'A valid email address is required.';
    }

    if ($values['phone'] === '') {
        $errors[] = 'Phone number is required.';
    }

    if ($values['experience'] === '') {
        $errors[] = 'Experience level is required.';
    }

    if ($values['tech_stack'] === '') {
        $errors[] = 'Tech stack is required.';
    }

    if ($values['jobPosition'] === '') {
        $errors[] = 'Job position is required.';
    }

    $resumeName = '';

    if (!empty($_FILES['resume']['name']) && ($_FILES['resume']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Resume upload failed. Please try again.';
        } else {
            $maxSize = 5 * 1024 * 1024;
            if (($_FILES['resume']['size'] ?? 0) > $maxSize) {
                $errors[] = 'Resume must be 5 MB or smaller.';
            }

            $allowedExtensions = ['pdf', 'doc', 'docx'];
            $allowedMimeTypes = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ];
            $extension = strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION));
            $mimeType = '';

            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                if ($finfo) {
                    $mimeType = (string) finfo_file($finfo, $_FILES['resume']['tmp_name']);
                    finfo_close($finfo);
                }
            }

            if (!in_array($extension, $allowedExtensions, true) || ($mimeType !== '' && !in_array($mimeType, $allowedMimeTypes, true))) {
                $errors[] = 'Only PDF, DOC, or DOCX files are allowed.';
            }

            if (empty($errors)) {
                $uploadDir = __DIR__ . '/../uploads/resumes/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $resumeName = bin2hex(random_bytes(12)) . '.' . $extension;
                $destination = $uploadDir . $resumeName;

                if (!move_uploaded_file($_FILES['resume']['tmp_name'], $destination)) {
                    $errors[] = 'Unable to save resume file.';
                    $resumeName = '';
                }
            }
        }
    }

    if (empty($errors)) {
        $duplicateSql = 'SELECT id FROM applications WHERE email = ?';
        $duplicateParams = [$values['email']];
        $duplicateTypes = 's';

        if (!empty($currentUserId)) {
            if (!empty($values['job_id'])) {
                $duplicateSql .= ' AND user_id = ? AND job_id = ?';
                $duplicateParams[] = (int) $currentUserId;
                $duplicateParams[] = (int) $values['job_id'];
                $duplicateTypes .= 'ii';
            } else {
                $duplicateSql .= ' AND user_id = ? AND jobPosition = ?';
                $duplicateParams[] = (int) $currentUserId;
                $duplicateParams[] = $values['jobPosition'];
                $duplicateTypes .= 'is';
            }
        } else {
            $duplicateSql .= ' AND jobPosition = ?';
            $duplicateParams[] = $values['jobPosition'];
            $duplicateTypes .= 's';
        }

        $duplicateSql .= ' LIMIT 1';
        $duplicateStmt = $conn->prepare($duplicateSql);
        $duplicateStmt->bind_param($duplicateTypes, ...$duplicateParams);
        $duplicateStmt->execute();
        $duplicateResult = $duplicateStmt->get_result();

        if ($duplicateResult && $duplicateResult->num_rows > 0) {
            $errors[] = 'You have already submitted an application for this position.';
        }

        $duplicateStmt->close();
    }

    if (empty($errors)) {
        $fullNameColumn = dbColumnExists($conn, 'applications', 'full_name') ? 'full_name' : 'fullName';
        $techStackColumn = dbColumnExists($conn, 'applications', 'tech_stack') ? 'tech_stack' : 'techStack';
        $status = 'pending';

        if (!empty($values['job_id'])) {
            $sql = 'INSERT INTO applications (' . implode(', ', [
                $fullNameColumn,
                'email',
                'phone',
                'experience',
                $techStackColumn,
                'jobPosition',
                'portfolio',
                'message',
                'resume',
                'job_id',
                'user_id',
                'status',
            ]) . ') VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

            $stmt = $conn->prepare($sql);
            $userIdValue = (int) $currentUserId;
            $jobIdValue = (int) $values['job_id'];
            $stmt->bind_param(
                'sssssssssiis',
                $values['full_name'],
                $values['email'],
                $values['phone'],
                $values['experience'],
                $values['tech_stack'],
                $values['jobPosition'],
                $values['portfolio'],
                $values['message'],
                $resumeName,
                $jobIdValue,
                $userIdValue,
                $status
            );
        } else {
            $sql = 'INSERT INTO applications (' . implode(', ', [
                $fullNameColumn,
                'email',
                'phone',
                'experience',
                $techStackColumn,
                'jobPosition',
                'portfolio',
                'message',
                'resume',
                'user_id',
                'status',
            ]) . ') VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

            $stmt = $conn->prepare($sql);
            $userIdValue = (int) $currentUserId;
            $stmt->bind_param(
                'sssssssssis',
                $values['full_name'],
                $values['email'],
                $values['phone'],
                $values['experience'],
                $values['tech_stack'],
                $values['jobPosition'],
                $values['portfolio'],
                $values['message'],
                $resumeName,
                $userIdValue,
                $status
            );
        }

        if ($stmt->execute()) {
            $successMessage = 'Your application has been submitted successfully.';
            $values = [
                'full_name' => $currentFullName,
                'email' => $currentEmail,
                'phone' => '',
                'experience' => '',
                'tech_stack' => '',
                'jobPosition' => '',
                'portfolio' => '',
                'message' => '',
                'job_id' => $values['job_id'],
            ];
        } else {
            $errors[] = 'Unable to save your application right now.';
            if (!empty($resumeName)) {
                @unlink(__DIR__ . '/../uploads/resumes/' . $resumeName);
            }
        }

        $stmt->close();
    }
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

    <!-- Page Header -->
    <section class="page-hero">
        <div class="page-hero-inner">
            <span class="eyebrow">Application Portal</span>
            <h1>Apply Now</h1>
            <p class="quick-apply-subtitle">Submit your application and let us connect you with amazing companies.</p>
        </div>
    </section>

    <!-- Application Form Section -->
    <section class="featured-jobs" style="padding: 4rem 2rem;">
        <div class="apply-layout-single">
            <?php if (!empty($successMessage)): ?>
                <div class="notice notice-success">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Application submitted</strong>
                        <p><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="notice notice-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <strong>Review the following</strong>
                        <?php foreach ($errors as $error): ?>
                            <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="apply-form apply-form--stacked" novalidate>
                <?= csrfField() ?>
                <input type="hidden" name="user_id" value="<?= htmlspecialchars((string) $currentUserId, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="job_id" value="<?= htmlspecialchars((string) ($values['job_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">

                <div class="form-grid form-grid-two">
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" placeholder="John Doe" value="<?= htmlspecialchars($values['full_name'], ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" placeholder="john@example.com" value="<?= htmlspecialchars($values['email'], ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="tel" id="phone" name="phone" placeholder="+1 (555) 123-4567" value="<?= htmlspecialchars($values['phone'], ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="experience">Years of Experience *</label>
                        <select id="experience" name="experience" required>
                            <option value="">Select experience level</option>
                            <option value="0-1" <?= $values['experience'] === '0-1' ? 'selected' : '' ?>>0-1 Years</option>
                            <option value="1-3" <?= $values['experience'] === '1-3' ? 'selected' : '' ?>>1-3 Years</option>
                            <option value="3-5" <?= $values['experience'] === '3-5' ? 'selected' : '' ?>>3-5 Years</option>
                            <option value="5-10" <?= $values['experience'] === '5-10' ? 'selected' : '' ?>>5-10 Years</option>
                            <option value="10+" <?= $values['experience'] === '10+' ? 'selected' : '' ?>>10+ Years</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="tech_stack">Primary Tech Stack *</label>
                        <select id="tech_stack" name="tech_stack" required>
                            <option value="">Select tech stack</option>
                            <option value="javascript" <?= $values['tech_stack'] === 'javascript' ? 'selected' : '' ?>>JavaScript / Node.js</option>
                            <option value="react" <?= $values['tech_stack'] === 'react' ? 'selected' : '' ?>>React / Frontend</option>
                            <option value="python" <?= $values['tech_stack'] === 'python' ? 'selected' : '' ?>>Python / Backend</option>
                            <option value="java" <?= $values['tech_stack'] === 'java' ? 'selected' : '' ?>>Java / Spring</option>
                            <option value="devops" <?= $values['tech_stack'] === 'devops' ? 'selected' : '' ?>>DevOps / Cloud</option>
                            <option value="full-stack" <?= $values['tech_stack'] === 'full-stack' ? 'selected' : '' ?>>Full Stack</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="jobPosition">Desired Position *</label>
                        <select id="jobPosition" name="jobPosition" required>
                            <option value="">Select position</option>
                            <option value="full-stack-developer" <?= $values['jobPosition'] === 'full-stack-developer' ? 'selected' : '' ?>>Full Stack Developer</option>
                            <option value="frontend-developer" <?= $values['jobPosition'] === 'frontend-developer' ? 'selected' : '' ?>>Frontend Developer</option>
                            <option value="backend-developer" <?= $values['jobPosition'] === 'backend-developer' ? 'selected' : '' ?>>Backend Developer</option>
                            <option value="devops-engineer" <?= $values['jobPosition'] === 'devops-engineer' ? 'selected' : '' ?>>DevOps Engineer</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid form-grid-two">
                    <div class="form-group">
                        <label for="portfolio">Portfolio URL</label>
                        <input type="url" id="portfolio" name="portfolio" placeholder="https://yourportfolio.com" value="<?= htmlspecialchars($values['portfolio'], ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="form-group">
                        <label for="resume">Upload Resume</label>
                        <input type="file" id="resume" name="resume" accept=".pdf,.doc,.docx">
                    </div>
                </div>

                <div class="form-group">
                    <label for="message">Additional Message</label>
                    <textarea id="message" name="message" placeholder="Tell us about yourself and why you're a great fit..." rows="6"><?= htmlspecialchars($values['message'], ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <button type="submit" class="btn-submit btn-block">
                    <i class="fas fa-paper-plane"></i> Submit Application
                </button>
            </form>
        </div>
    </section>

<?php include '../includes/footer.php'; ?>
