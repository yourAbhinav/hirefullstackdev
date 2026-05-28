<?php

require_once __DIR__ . '/../includes/helpers.php';

function companyRedirect(string $path = 'company/dashboard.php'): void
{
	header('Location: ' . appUrl($path));
	exit;
}

function companyJobById(mysqli $conn, int $jobId, ?int $companyId = null): ?array
{
	$companyId = $companyId ?? currentCompanyId();
	$stmt = $conn->prepare('SELECT id, title, company_id, description, requirements, salary_min, salary_max, experience_level, job_type, work_mode, location, tech_stack, applications_count, status, featured, created_at, updated_at FROM jobs WHERE id = ? AND company_id = ? LIMIT 1');
	$stmt->bind_param('ii', $jobId, $companyId);
	$stmt->execute();
	$job = $stmt->get_result()->fetch_assoc() ?: null;
	$stmt->close();

	return $job;
}

function requireCompanyJobOwnership(mysqli $conn, int $jobId, ?int $companyId = null): array
{
	$job = companyJobById($conn, $jobId, $companyId);

	if ($job === null) {
		setFlash('error', 'You can only manage jobs that belong to your company.');
		companyRedirect('company/jobs.php');
	}

	return $job;
}

function companyJobOptions(): array
{
	return [
		'full-time' => 'Full Time',
		'part-time' => 'Part Time',
		'contract' => 'Contract',
		'freelance' => 'Freelance',
	];
}

function companyWorkModeOptions(): array
{
	return [
		'remote' => 'Remote',
		'hybrid' => 'Hybrid',
		'on-site' => 'On Site',
	];
}

function companyStatusOptions(): array
{
	return [
		'active' => 'Active',
		'inactive' => 'Inactive',
		'closed' => 'Closed',
	];
}
