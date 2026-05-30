<?php

require_once '../config/db.php';
require_once __DIR__ . '/middleware.php';
requireCompany();

$page_title = 'Create Job - DevHire';
$page_description = 'Create and publish developer jobs from your company dashboard with a polished, guided job editor.';
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

<style>
	.company-job-shell {
		width: min(1240px, calc(100% - 32px));
		margin: 0 auto;
		padding: 0 0 72px;
	}

	.company-job-layout {
		display: grid;
		grid-template-columns: minmax(280px, 360px) minmax(0, 1fr);
		gap: 22px;
		align-items: start;
	}

	.company-job-rail,
	.company-job-card {
		border-radius: 24px;
		background: rgba(255, 255, 255, 0.9);
		border: 1px solid rgba(148, 163, 184, 0.22);
		box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
		backdrop-filter: blur(10px);
	}

	.company-job-rail {
		padding: 24px;
		position: sticky;
		top: 110px;
	}

	.company-job-card {
		padding: 24px;
	}

	.company-job-kicker {
		display: inline-flex;
		align-items: center;
		gap: 8px;
		padding: 8px 12px;
		border-radius: 999px;
		background: #eff6ff;
		color: #1d4ed8;
		font-size: 12px;
		font-weight: 700;
		letter-spacing: 0.08em;
		text-transform: uppercase;
		margin-bottom: 16px;
	}

	.company-job-rail h2 {
		color: #0f172a;
		margin: 0 0 10px;
		font-size: 1.8rem;
		letter-spacing: -0.03em;
	}

	.company-job-rail p,
	.company-job-rail li,
	.company-job-rail .rail-note {
		color: #475569;
		line-height: 1.7;
	}

	.rail-metrics {
		display: grid;
		gap: 12px;
		margin: 20px 0;
	}

	.rail-metric {
		padding: 14px 16px;
		border-radius: 16px;
		background: linear-gradient(135deg, #f8fafc, #eef2ff);
		border: 1px solid rgba(79, 70, 229, 0.08);
	}

	.rail-metric strong {
		display: block;
		color: #0f172a;
		font-size: 1rem;
		margin-bottom: 4px;
	}

	.rail-metric span {
		color: #64748b;
		font-size: 0.92rem;
	}

	.rail-list {
		margin: 18px 0 0;
		padding: 0;
		list-style: none;
		display: grid;
		gap: 10px;
	}

	.rail-list li {
		display: flex;
		align-items: flex-start;
		gap: 10px;
	}

	.rail-list i {
		color: #4f46e5;
		margin-top: 4px;
	}

	.company-job-form .form-grid-two {
		margin-bottom: 18px;
	}

	.company-job-form .form-group label {
		font-weight: 700;
		color: #0f172a;
	}

	.company-job-form input,
	.company-job-form select,
	.company-job-form textarea {
		border-radius: 14px;
		border: 1px solid #cbd5e1;
		background: #fff;
		box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
	}

	.company-job-form input:focus,
	.company-job-form select:focus,
	.company-job-form textarea:focus {
		outline: none;
		border-color: #4f46e5;
		box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.12);
	}

	.company-job-actions {
		display: flex;
		justify-content: flex-end;
		margin-top: 6px;
	}

	@media (max-width: 960px) {
		.company-job-layout {
			grid-template-columns: 1fr;
		}

		.company-job-rail {
			position: static;
		}
	}
</style>

<section class="page-hero">
	<div class="page-hero-inner">
		<span class="eyebrow">Company Panel</span>
		<h1><?= $jobId > 0 ? 'Edit Job' : 'Create Job' ?></h1>
		<p class="quick-apply-subtitle">Publish a role that is easier to discover, easier to trust, and easier to apply to.</p>
	</div>
</section>

<section class="company-job-shell">
	<div class="company-job-layout">
		<aside class="company-job-rail">
			<span class="company-job-kicker">Job publishing guide</span>
			<h2>Build a stronger job post</h2>
			<p class="rail-note">Use a precise title, a clear stack, and a concise description that explains who the role is for and what the developer will ship.</p>

			<div class="rail-metrics">
				<div class="rail-metric">
					<strong>Search visibility</strong>
					<span>Job titles and stack terms help candidates discover the role.</span>
				</div>
				<div class="rail-metric">
					<strong>Candidate trust</strong>
					<span>Clear requirements improve application quality and reduce drop-off.</span>
				</div>
				<div class="rail-metric">
					<strong>Hiring speed</strong>
					<span>Well-structured roles get reviewed and approved faster.</span>
				</div>
			</div>

			<ul class="rail-list">
				<li><i class="fas fa-check-circle"></i><span>Use the technologies the team actually needs.</span></li>
				<li><i class="fas fa-check-circle"></i><span>Describe the outcomes, not only the responsibilities.</span></li>
				<li><i class="fas fa-check-circle"></i><span>Keep the salary range and work mode explicit.</span></li>
			</ul>
		</aside>

		<div class="company-job-card">
		<?php if (!empty($successMessage = getFlash('success'))): ?>
			<div class="notice notice-success"><i class="fas fa-check-circle"></i><div><strong>Success</strong><p><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></p></div></div>
		<?php endif; ?>

		<?php if (!empty($errorMessage = getFlash('error'))): ?>
			<div class="notice notice-error"><i class="fas fa-exclamation-circle"></i><div><strong>Review the form</strong><p><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p></div></div>
		<?php endif; ?>

		<form method="POST" action="<?= appUrl('company/create-job.php' . ($jobId > 0 ? '?id=' . $jobId : '')) ?>" class="apply-form apply-form--stacked company-job-form" novalidate>
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

			<div class="company-job-actions">
				<button type="submit" class="btn-submit btn-block">
					<i class="fas fa-save"></i> <?= $jobId > 0 ? 'Update Job' : 'Create Job' ?>
				</button>
			</div>
		</form>
		</div>
	</div>
</section>

<?php include '../includes/footer.php'; ?>
