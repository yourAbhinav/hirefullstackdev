<?php
require_once '../../config/db.php';
require_once '../../includes/admin_helpers.php';
require_once '../../includes/helpers.php';

header('Content-Type: application/json');

requireAdminLogin();
requireAdminPermission($conn, 'view_jobs');

$jsonPayload = json_decode((string) file_get_contents('php://input'), true);
if (is_array($jsonPayload)) {
    $_POST = array_merge($_POST, $jsonPayload);
}

function requireApiCsrf(): void {
    $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
    if (!verifyCsrf(is_string($token) ? $token : null)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'CSRF validation failed']);
        exit;
    }
}

$admin = getCurrentAdmin($conn);
if ($admin === null) {
    echo json_encode(['success' => false, 'message' => 'Admin not found']);
    exit;
}

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

switch ($action) {
    case 'get_job':
        $jobId = (int) ($_GET['id'] ?? 0);
        
        if ($jobId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid job ID']);
            exit;
        }
        
        $stmt = $conn->prepare('SELECT j.*, COUNT(a.id) as application_count 
                           FROM jobs j 
                           LEFT JOIN applications a ON j.id = a.job_id 
                           WHERE j.id = ? 
                           GROUP BY j.id 
                           LIMIT 1');
        $stmt->bind_param('i', $jobId);
        $stmt->execute();
        $job = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($job) {
            echo json_encode(['success' => true, 'job' => $job]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Job not found']);
        }
        break;
        
    case 'create':
        requireAdminPermission($conn, 'create_jobs');
        requireApiCsrf();
        
        $title = trim($_POST['title'] ?? '');
        $companyId = (int) ($_POST['company_id'] ?? 0);
        $location = trim($_POST['location'] ?? '');
        $jobType = $_POST['job_type'] ?? 'full-time';
        $workMode = $_POST['work_mode'] ?? 'on-site';
        $experienceLevel = $_POST['experience_level'] ?? 'entry';
        $salaryMin = (int) ($_POST['salary_min'] ?? 0);
        $salaryMax = (int) ($_POST['salary_max'] ?? 0);
        $techStack = trim($_POST['tech_stack'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $requirements = trim($_POST['requirements'] ?? '');
        $benefits = trim($_POST['benefits'] ?? '');
        $status = $_POST['status'] ?? 'active';
        $featured = isset($_POST['featured']) ? 1 : 0;
        
        if (empty($title) || $companyId <= 0 || empty($location) || empty($description)) {
            echo json_encode(['success' => false, 'message' => 'Required fields are missing']);
            exit;
        }
        
        $stmt = $conn->prepare('INSERT INTO jobs (title, company_id, location, job_type, work_mode, experience_level, salary_min, salary_max, tech_stack, description, requirements, benefits, status, featured, created_at, updated_at) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
        $stmt->bind_param('sisssiissssssi', $title, $companyId, $location, $jobType, $workMode, $experienceLevel, $salaryMin, $salaryMax, $techStack, $description, $requirements, $benefits, $status, $featured);
        
        if ($stmt->execute()) {
            $newJobId = $stmt->insert_id;
            logAdminAction($conn, $admin['id'], 'create_job', 'job', $newJobId, null, ['title' => $title]);
            echo json_encode(['success' => true, 'message' => 'Job created successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create job']);
        }
        $stmt->close();
        break;
        
    case 'update':
        requireAdminPermission($conn, 'edit_jobs');
        requireApiCsrf();
        
        $jobId = (int) ($_POST['job_id'] ?? 0);
        
        if ($jobId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid job ID']);
            exit;
        }
        
        // Get current job data for audit
        $stmt = $conn->prepare('SELECT id, title, company_id, location, job_type, work_mode, experience_level, salary_min, salary_max, tech_stack, description, requirements, benefits, status, featured FROM jobs WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $jobId);
        $stmt->execute();
        $currentJob = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$currentJob) {
            echo json_encode(['success' => false, 'message' => 'Job not found']);
            exit;
        }
        
        $title = trim($_POST['title'] ?? $currentJob['title']);
        $companyId = (int) ($_POST['company_id'] ?? $currentJob['company_id']);
        $location = trim($_POST['location'] ?? $currentJob['location']);
        $jobType = $_POST['job_type'] ?? $currentJob['job_type'];
        $workMode = $_POST['work_mode'] ?? $currentJob['work_mode'];
        $experienceLevel = $_POST['experience_level'] ?? $currentJob['experience_level'];
        $salaryMin = (int) ($_POST['salary_min'] ?? $currentJob['salary_min']);
        $salaryMax = (int) ($_POST['salary_max'] ?? $currentJob['salary_max']);
        $techStack = trim($_POST['tech_stack'] ?? $currentJob['tech_stack']);
        $description = trim($_POST['description'] ?? $currentJob['description']);
        $requirements = trim($_POST['requirements'] ?? $currentJob['requirements']);
        $benefits = trim($_POST['benefits'] ?? $currentJob['benefits']);
        $status = $_POST['status'] ?? $currentJob['status'];
        $featured = isset($_POST['featured']) ? 1 : 0;
        
        $stmt = $conn->prepare('UPDATE jobs SET title = ?, company_id = ?, location = ?, job_type = ?, work_mode = ?, experience_level = ?, salary_min = ?, salary_max = ?, tech_stack = ?, description = ?, requirements = ?, benefits = ?, status = ?, featured = ?, updated_at = NOW() WHERE id = ?');
        $stmt->bind_param('sisssiissssssii', $title, $companyId, $location, $jobType, $workMode, $experienceLevel, $salaryMin, $salaryMax, $techStack, $description, $requirements, $benefits, $status, $featured, $jobId);
        
        if ($stmt->execute()) {
            $oldValues = [
                'title' => $currentJob['title'],
                'status' => $currentJob['status'],
                'featured' => $currentJob['featured']
            ];
            $newValues = [
                'title' => $title,
                'status' => $status,
                'featured' => $featured
            ];
            
            logAdminAction($conn, $admin['id'], 'update_job', 'job', $jobId, $oldValues, $newValues);
            echo json_encode(['success' => true, 'message' => 'Job updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update job']);
        }
        $stmt->close();
        break;
        
    case 'toggle_featured':
        requireAdminPermission($conn, 'edit_jobs');
        requireApiCsrf();
        
        $jobId = (int) ($_POST['job_id'] ?? 0);
        $isFeatured = (int) ($_POST['featured'] ?? 0);
        
        if ($jobId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid job ID']);
            exit;
        }
        
        $stmt = $conn->prepare('UPDATE jobs SET featured = ?, updated_at = NOW() WHERE id = ?');
        $stmt->bind_param('ii', $isFeatured, $jobId);
        
        if ($stmt->execute()) {
            logAdminAction($conn, $admin['id'], 'toggle_job_featured', 'job', $jobId, null, ['featured' => $isFeatured]);
            echo json_encode(['success' => true, 'message' => 'Job featured status updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update featured status']);
        }
        $stmt->close();
        break;
        
    case 'activate':
    case 'close':
        requireAdminPermission($conn, 'edit_jobs');
        requireApiCsrf();
        
        $jobId = (int) ($_POST['job_id'] ?? 0);
        
        if ($jobId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid job ID']);
            exit;
        }
        
        $newStatus = $action === 'activate' ? 'active' : 'closed';
        
        $stmt = $conn->prepare('UPDATE jobs SET status = ?, updated_at = NOW() WHERE id = ?');
        $stmt->bind_param('si', $newStatus, $jobId);
        
        if ($stmt->execute()) {
            logAdminAction($conn, $admin['id'], $action . '_job', 'job', $jobId, null, ['status' => $newStatus]);
            createAdminNotification($conn, null, 'info', 'Job Status Changed', "Job was " . ($action === 'activate' ? 'activated' : 'closed'), 'admin/jobs.php');
            echo json_encode(['success' => true, 'message' => 'Job status updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update job status']);
        }
        $stmt->close();
        break;
        
    case 'delete':
        requireAdminPermission($conn, 'delete_jobs');
        requireApiCsrf();
        
        $jobId = (int) ($_POST['job_id'] ?? 0);
        
        if ($jobId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid job ID']);
            exit;
        }
        
        // Get job data for audit
        $stmt = $conn->prepare('SELECT title, company_id FROM jobs WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $jobId);
        $stmt->execute();
        $job = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$job) {
            echo json_encode(['success' => false, 'message' => 'Job not found']);
            exit;
        }
        
        $stmt = $conn->prepare('DELETE FROM jobs WHERE id = ?');
        $stmt->bind_param('i', $jobId);
        
        if ($stmt->execute()) {
            logAdminAction($conn, $admin['id'], 'delete_job', 'job', $jobId, $job, null);
            createAdminNotification($conn, null, 'warning', 'Job Deleted', "Job '{$job['title']}' was deleted by admin", null);
            echo json_encode(['success' => true, 'message' => 'Job deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete job']);
        }
        $stmt->close();
        break;
        
    case 'bulk_activate':
    case 'bulk_close':
    case 'bulk_feature':
    case 'bulk_unfeature':
    case 'bulk_delete':
        requireAdminPermission($conn, 'edit_jobs');
        if (str_contains($action, 'delete')) {
            requireAdminPermission($conn, 'delete_jobs');
        }
        requireApiCsrf();
        
        $jobIds = $_POST['job_ids'] ?? [];
        if (empty($jobIds) || !is_array($jobIds)) {
            echo json_encode(['success' => false, 'message' => 'No jobs selected']);
            exit;
        }
        
        $jobIds = array_map('intval', $jobIds);
        $placeholders = str_repeat('?,', count($jobIds) - 1) . '?';
        $baseAction = str_replace('bulk_', '', $action);
        
        if ($baseAction === 'delete') {
            $stmt = $conn->prepare("DELETE FROM jobs WHERE id IN ($placeholders)");
        } elseif ($baseAction === 'feature') {
            $stmt = $conn->prepare("UPDATE jobs SET featured = 1, updated_at = NOW() WHERE id IN ($placeholders)");
        } elseif ($baseAction === 'unfeature') {
            $stmt = $conn->prepare("UPDATE jobs SET featured = 0, updated_at = NOW() WHERE id IN ($placeholders)");
        } else {
            $newStatus = $baseAction === 'activate' ? 'active' : 'closed';
            $stmt = $conn->prepare("UPDATE jobs SET status = ?, updated_at = NOW() WHERE id IN ($placeholders)");
            array_unshift($jobIds, $newStatus);
        }
        
        $stmt->bind_param(str_repeat('i', count($jobIds)), ...$jobIds);
        
        if ($stmt->execute()) {
            $affected = $stmt->affected_rows;
            logAdminAction($conn, $admin['id'], $action, 'job', null, null, ['affected_count' => $affected]);
            echo json_encode(['success' => true, 'message' => "$affected jobs " . ($baseAction === 'delete' ? 'deleted' : 'updated')]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Bulk action failed']);
        }
        $stmt->close();
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
