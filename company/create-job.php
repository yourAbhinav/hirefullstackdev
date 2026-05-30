<?php

require_once '../config/db.php';
require_once __DIR__ . '/middleware.php';
requireCompany();

$page_title = 'Create Job - DevHire';
$css_path = appUrl('assets/css/style.css');
$js_path = appUrl('assets/js/main.js');

$companyId = currentCompanyId();
$jobId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$job = null;
$allowedJobTypes = companyJobOptions();
$allowedWorkModes = companyWorkModeOptions();
$allowedStatuses = companyStatusOptions();

if ($jobId > 0) {
	$job = requireCompanyJobOwnership($conn, $jobId, $companyId);
}

$formValues = [
	'title' => (string) ($job['title'] ?? ''),
	'description' => (string) ($job['description'] ?? ''),
	'requirements' => (string) ($job['requirements'] ?? ''),
	'salary_min' => (string) ($job['salary_min'] ?? ''),
	'salary_max' => (string) ($job['salary_max'] ?? ''),
	'experience_level' => (string) ($job['experience_level'] ?? ''),
	'job_type' => (string) ($job['job_type'] ?? 'full-time'),
	'work_mode' => (string) ($job['work_mode'] ?? 'remote'),
	'location' => (string) ($job['location'] ?? ''),
	'tech_stack' => (string) ($job['tech_stack'] ?? ''),
	'status' => (string) ($job['status'] ?? 'active'),
	'featured' => !empty($job['featured']) ? '1' : '0',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
		setFlash('error', 'Your session expired. Please reload and try again.');
		companyRedirect($jobId > 0 ? 'company/create-job.php?id=' . $jobId : 'company/create-job.php');
	}

	$formValues = [
		'title' => sanitize($_POST['title'] ?? ''),
		'description' => sanitize($_POST['description'] ?? ''),
		'requirements' => sanitize($_POST['requirements'] ?? ''),
		'salary_min' => sanitize($_POST['salary_min'] ?? ''),
		'salary_max' => sanitize($_POST['salary_max'] ?? ''),
		'experience_level' => sanitize($_POST['experience_level'] ?? ''),
		'job_type' => sanitize($_POST['job_type'] ?? 'full-time'),
		'work_mode' => sanitize($_POST['work_mode'] ?? 'remote'),
		'location' => sanitize($_POST['location'] ?? ''),
		'tech_stack' => sanitize($_POST['tech_stack'] ?? ''),
		'status' => sanitize($_POST['status'] ?? 'active'),
		'featured' => !empty($_POST['featured']) ? '1' : '0',
	];

	$errors = [];

	if ($formValues['title'] === '') {
		$errors[] = 'Job title is required.';
	}

	if ($formValues['description'] === '') {
		$errors[] = 'Job description is required.';
	}

	if ($formValues['requirements'] === '') {
		$errors[] = 'Job requirements are required.';
	}

	if (!array_key_exists($formValues['job_type'], $allowedJobTypes)) {
		$errors[] = 'Please choose a valid job type.';
	}

	if (!array_key_exists($formValues['work_mode'], $allowedWorkModes)) {
		$errors[] = 'Please choose a valid work mode.';
	}

	if (!array_key_exists($formValues['status'], $allowedStatuses)) {
		$errors[] = 'Please choose a valid job status.';
	}

	$salaryMin = $formValues['salary_min'] !== '' ? (int) $formValues['salary_min'] : null;
	$salaryMax = $formValues['salary_max'] !== '' ? (int) $formValues['salary_max'] : null;

	if ($salaryMin !== null && $salaryMax !== null && $salaryMin > $salaryMax) {
		$errors[] = 'Minimum salary cannot be greater than maximum salary.';
	}

	if (!empty($errors)) {
		setFlash('error', implode(' ', $errors));
	} else {
		if ($jobId > 0) {
			$stmt = $conn->prepare('UPDATE jobs SET title = ?, description = ?, requirements = ?, salary_min = ?, salary_max = ?, experience_level = ?, job_type = ?, work_mode = ?, location = ?, tech_stack = ?, status = ?, featured = ?, updated_at = NOW() WHERE id = ? AND company_id = ?');
			$featured = (int) ($formValues['featured'] === '1');
			// types: title(s), description(s), requirements(s), salary_min(i), salary_max(i), experience_level(s), job_type(s), work_mode(s), location(s), tech_stack(s), status(s), featured(i), jobId(i), companyId(i)
			$stmt->bind_param('sssiissssssiii', $formValues['title'], $formValues['description'], $formValues['requirements'], $salaryMin, $salaryMax, $formValues['experience_level'], $formValues['job_type'], $formValues['work_mode'], $formValues['location'], $formValues['tech_stack'], $formValues['status'], $featured, $jobId, $companyId);
			$stmt->execute();
			$stmt->close();
			setFlash('success', 'Job updated successfully.');
			companyRedirect('company/jobs.php');
		}

		$stmt = $conn->prepare('INSERT INTO jobs (title, company_id, description, requirements, salary_min, salary_max, experience_level, job_type, work_mode, location, tech_stack, status, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
		$featured = (int) ($formValues['featured'] === '1');
		// types: title(s), company_id(i), description(s), requirements(s), salary_min(i), salary_max(i), experience_level(s), job_type(s), work_mode(s), location(s), tech_stack(s), status(s), featured(i)
		$stmt->bind_param('sissiissssssi', $formValues['title'], $companyId, $formValues['description'], $formValues['requirements'], $salaryMin, $salaryMax, $formValues['experience_level'], $formValues['job_type'], $formValues['work_mode'], $formValues['location'], $formValues['tech_stack'], $formValues['status'], $featured);
		$stmt->execute();
		$stmt->close();
		setFlash('success', 'Job created successfully.');
		companyRedirect('company/jobs.php');
	}
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<section class="page-hero">
	<div class="page-hero-inner">
		<span class="eyebrow">Company Panel</span>
		<h1><?= $jobId > 0 ? 'Edit Job' : 'Create Job' ?></h1>
		<p class="quick-apply-subtitle">Publish a role and manage it from your company workspace.</p>
	</div>
</section>

<section class="featured-jobs company-create-job-section">
	<div class="apply-layout-single">
		<?php if (!empty($successMessage = getFlash('success'))): ?>
			<div class="notice notice-success"><i class="fas fa-check-circle"></i><div><strong>Success</strong><p><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></p></div></div>
		<?php endif; ?>

		<?php if (!empty($errorMessage = getFlash('error'))): ?>
			<div class="notice notice-error"><i class="fas fa-exclamation-circle"></i><div><strong>Review the form</strong><p><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p></div></div>
		<?php endif; ?>

		<form method="POST" class="apply-form apply-form--stacked" novalidate>
			<?= csrfField() ?>

			<div class="form-grid form-grid-two">
				<div class="form-group">
					<label for="title">Job Title *</label>
					<input type="text" id="title" name="title" value="<?= htmlspecialchars($formValues['title'], ENT_QUOTES, 'UTF-8') ?>" required>
				</div>
				<div class="form-group">
					<label for="location">Location</label>
					<input type="text" id="location" name="location" value="<?= htmlspecialchars($formValues['location'], ENT_QUOTES, 'UTF-8') ?>">
				</div>
				<div class="form-group">
					<label for="job_type">Job Type</label>
					<select id="job_type" name="job_type">
						<?php foreach ($allowedJobTypes as $value => $label): ?>
							<option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>" <?= $formValues['job_type'] === $value ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="form-group">
					<label for="work_mode">Work Mode</label>
					<select id="work_mode" name="work_mode">
						<?php foreach ($allowedWorkModes as $value => $label): ?>
							<option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>" <?= $formValues['work_mode'] === $value ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="form-group">
					<label for="salary_min">Minimum Salary</label>
					<input type="number" id="salary_min" name="salary_min" value="<?= htmlspecialchars($formValues['salary_min'], ENT_QUOTES, 'UTF-8') ?>" min="0">
				</div>
				<div class="form-group">
					<label for="salary_max">Maximum Salary</label>
					<input type="number" id="salary_max" name="salary_max" value="<?= htmlspecialchars($formValues['salary_max'], ENT_QUOTES, 'UTF-8') ?>" min="0">
				</div>
				<div class="form-group">
					<label for="experience_level">Experience Level</label>
					<input type="text" id="experience_level" name="experience_level" value="<?= htmlspecialchars($formValues['experience_level'], ENT_QUOTES, 'UTF-8') ?>" placeholder="3-5 Years">
				</div>
				<div class="form-group">
					<label for="status">Status</label>
					<select id="status" name="status">
						<?php foreach ($allowedStatuses as $value => $label): ?>
							<option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>" <?= $formValues['status'] === $value ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="form-group featured-toggle-group">
					<label class="featured-toggle-label">
						<input type="checkbox" name="featured" value="1" <?= $formValues['featured'] === '1' ? 'checked' : '' ?>> Featured listing
					</label>
				</div>
			</div>

			<div class="form-group">
				<label for="tech_stack">Tech Stack</label>
				<input type="text" id="tech_stack" name="tech_stack" value="<?= htmlspecialchars($formValues['tech_stack'], ENT_QUOTES, 'UTF-8') ?>" placeholder="React, Node.js, MySQL">
			</div>

			<div class="form-group">
				<label for="description">Description *</label>
				<textarea id="description" name="description" rows="7" required><?= htmlspecialchars($formValues['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
			</div>

			<div class="form-group">
				<label for="requirements">Requirements *</label>
				<textarea id="requirements" name="requirements" rows="7" required><?= htmlspecialchars($formValues['requirements'], ENT_QUOTES, 'UTF-8') ?></textarea>
			</div>

			<button type="submit" class="btn-submit btn-block">
				<i class="fas fa-save"></i> <?= $jobId > 0 ? 'Update Job' : 'Create Job' ?>
			</button>
		</form>
	</div>
</section>

<?php include '../includes/footer.php'; ?>
