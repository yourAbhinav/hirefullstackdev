<?php

require_once '../config/db.php';

$page_title = 'Jobs - DevHire';
$css_path = appUrl('assets/css/style.css');
$js_path = appUrl('assets/js/main.js');

$searchTerm = trim((string) ($_GET['q'] ?? $_GET['title'] ?? ''));
$experienceLevel = trim((string) ($_GET['experience_level'] ?? ''));
$workMode = trim((string) ($_GET['work_mode'] ?? ''));
$salaryRange = trim((string) ($_GET['salary_range'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;
$canSaveJobs = isDeveloper();

$whereParts = ['j.status = ?'];
$params = ['active'];
$types = 's';

if ($searchTerm !== '') {
    // Performance: avoid LIKE against LONGTEXT descriptions (forces full scans).
    // Use FULLTEXT index (title, description) for description searching and keep LIKE
    // for the remaining smaller columns to preserve existing search behavior.
    $whereParts[] = '(MATCH(j.title, j.description) AGAINST (? IN NATURAL LANGUAGE MODE) OR j.title LIKE ? OR j.tech_stack LIKE ? OR COALESCE(u.company_name, u.fullName, \'\') LIKE ?)';
    $like = '%' . $searchTerm . '%';
    array_push($params, $searchTerm, $like, $like, $like);
    $types .= 'ssss';
}

if ($experienceLevel !== '') {
    $whereParts[] = 'j.experience_level = ?';
    $params[] = $experienceLevel;
    $types .= 's';
}

if ($workMode !== '') {
    $whereParts[] = 'j.work_mode = ?';
    $params[] = $workMode;
    $types .= 's';
}

if ($salaryRange !== '') {
    if ($salaryRange === '0-100') {
        $whereParts[] = '(COALESCE(j.salary_min, 0) <= 100000 AND (j.salary_max IS NULL OR j.salary_max >= 0))';
    } elseif ($salaryRange === '100-150') {
        $whereParts[] = '(COALESCE(j.salary_min, 0) <= 150000 AND COALESCE(j.salary_max, 0) >= 100000)';
    } elseif ($salaryRange === '150-200') {
        $whereParts[] = '(COALESCE(j.salary_min, 0) <= 200000 AND COALESCE(j.salary_max, 0) >= 150000)';
    } elseif ($salaryRange === '200+') {
        $whereParts[] = '(COALESCE(j.salary_min, 0) >= 200000 OR COALESCE(j.salary_max, 0) >= 200000)';
    }
}

$whereSql = 'WHERE ' . implode(' AND ', $whereParts);

$countStmt = $conn->prepare('SELECT COUNT(*) AS total FROM jobs j LEFT JOIN users u ON u.id = j.company_id ' . $whereSql);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalJobs = (int) ($countStmt->get_result()->fetch_assoc()['total'] ?? 0);
$countStmt->close();

$totalPages = max(1, (int) ceil($totalJobs / $limit));
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
}

$dataSql = 'SELECT j.id, j.title, j.description, j.requirements, j.salary_min, j.salary_max, j.experience_level, j.job_type, j.work_mode, j.location, j.tech_stack, j.featured, j.created_at, COALESCE(u.company_name, u.fullName, \'Company\') AS company_name, COALESCE(u.profile_image, \'\') AS company_photo FROM jobs j LEFT JOIN users u ON u.id = j.company_id ' . $whereSql . ' ORDER BY j.featured DESC, j.created_at DESC LIMIT ? OFFSET ?';
$dataStmt = $conn->prepare($dataSql);
$dataParams = $params;
$dataTypes = $types . 'ii';
$dataParams[] = $limit;
$dataParams[] = $offset;
if (!empty($dataParams)) {
    $dataStmt->bind_param($dataTypes, ...$dataParams);
}
$dataStmt->execute();
$jobs = $dataStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$dataStmt->close();

include '../includes/header.php';
include '../includes/navbar.php';
?>

    <section class="jobs-hero">
        <div class="jobs-hero-inner">
            <h1>Job Opportunities</h1>
            <p class="quick-apply-subtitle">Find your next amazing opportunity at top companies</p>
        </div>
    </section>

    <section class="featured-jobs">
        <form method="GET" action="<?= appUrl('pages/jobs.php') ?>" class="jobs-filter-panel">
            <div class="jobs-filter-grid">
                <div class="form-group">
                    <label for="jobSearch">Search Position</label>
                    <input type="text" name="q" id="jobSearch" placeholder="e.g. Frontend Developer" value="<?= htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label for="experienceLevel">Experience Level</label>
                    <select id="experienceLevel" name="experience_level">
                        <option value="">All Levels</option>
                        <option value="0-1" <?= $experienceLevel === '0-1' ? 'selected' : '' ?>>0-1 Years</option>
                        <option value="1-3" <?= $experienceLevel === '1-3' ? 'selected' : '' ?>>1-3 Years</option>
                        <option value="3-5" <?= $experienceLevel === '3-5' ? 'selected' : '' ?>>3-5 Years</option>
                        <option value="5-10" <?= $experienceLevel === '5-10' ? 'selected' : '' ?>>5-10 Years</option>
                        <option value="10+" <?= $experienceLevel === '10+' ? 'selected' : '' ?>>10+ Years</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="workMode">Work Mode</label>
                    <select id="workMode" name="work_mode">
                        <option value="">All Types</option>
                        <option value="remote" <?= $workMode === 'remote' ? 'selected' : '' ?>>Remote</option>
                        <option value="hybrid" <?= $workMode === 'hybrid' ? 'selected' : '' ?>>Hybrid</option>
                        <option value="on-site" <?= $workMode === 'on-site' ? 'selected' : '' ?>>On-site</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="salaryRange">Salary Range</label>
                    <select id="salaryRange" name="salary_range">
                        <option value="">Any</option>
                        <option value="0-100" <?= $salaryRange === '0-100' ? 'selected' : '' ?>>$0 - $100k</option>
                        <option value="100-150" <?= $salaryRange === '100-150' ? 'selected' : '' ?>>$100k - $150k</option>
                        <option value="150-200" <?= $salaryRange === '150-200' ? 'selected' : '' ?>>$150k - $200k</option>
                        <option value="200+" <?= $salaryRange === '200+' ? 'selected' : '' ?>>$200k+</option>
                    </select>
                </div>
            </div>
            <div class="jobs-filter-actions">
                <button type="submit" class="btn-primary jobs-filter-button">Filter Jobs</button>
                <a href="<?= appUrl('pages/jobs.php') ?>" class="btn-secondary jobs-filter-button jobs-filter-reset">Reset</a>
            </div>
        </form>

        <div class="jobs-grid">
            <?php if (!empty($jobs)): ?>
                <?php foreach ($jobs as $job): ?>
                    <div class="job-card">
                        <div class="job-header">
                            <h3 class="job-title"><?= htmlspecialchars($job['title'] ?? 'Untitled role', ENT_QUOTES, 'UTF-8') ?></h3>
                            <p class="job-company"><?= htmlspecialchars($job['company_name'] ?? 'Company', ENT_QUOTES, 'UTF-8') ?></p>
                        </div>

                        <div class="job-details">
                            <div class="job-meta">
                                <span class="meta-item">
                                    <i class="fas fa-briefcase"></i> <?= htmlspecialchars($job['experience_level'] ?? 'Not specified', ENT_QUOTES, 'UTF-8') ?>
                                </span>
                                <span class="meta-item">
                                    <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars(ucfirst((string) ($job['work_mode'] ?? 'remote')), ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </div>

                            <div class="job-tags">
                                <?php foreach (array_slice(array_filter(array_map('trim', explode(',', (string) ($job['tech_stack'] ?? '')))), 0, 3) as $tech): ?>
                                    <span class="tag"><?= htmlspecialchars($tech, ENT_QUOTES, 'UTF-8') ?></span>
                                <?php endforeach; ?>
                            </div>

                            <div class="job-salary">
                                <?= !empty($job['salary_min']) || !empty($job['salary_max']) ? '$' . number_format((int) ($job['salary_min'] ?? 0)) . ' - $' . number_format((int) ($job['salary_max'] ?? $job['salary_min'] ?? 0)) . ' /yr' : 'Salary hidden' ?>
                            </div>
                        </div>

                        <div class="job-footer">
                            <a href="<?= appUrl('pages/apply.php?job_id=' . (int) $job['id']) ?>" class="btn-apply-job">Apply</a>
                            <?php if ($canSaveJobs): ?>
                                <button type="button" class="save-job" data-save-job="<?= (int) $job['id'] ?>" aria-label="Save job">
                                    <i class="far fa-bookmark"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">No active jobs match your filters right now.</div>
            <?php endif; ?>
        </div>
    </section>

    <div class="jobs-pagination-wrap">
        <?php if ($totalPages > 1): ?>
            <div class="jobs-pagination-actions">
                <?php if ($page > 1): ?>
                    <a class="btn-secondary jobs-pagination-btn jobs-filter-reset" href="<?= appUrl('pages/jobs.php?' . http_build_query(array_merge($_GET, ['page' => $page - 1]))) ?>">Previous</a>
                <?php endif; ?>
                <span class="btn-primary jobs-pagination-btn"><?= (int) $page ?> of <?= (int) $totalPages ?></span>
                <?php if ($page < $totalPages): ?>
                    <a class="btn-secondary jobs-pagination-btn jobs-filter-reset" href="<?= appUrl('pages/jobs.php?' . http_build_query(array_merge($_GET, ['page' => $page + 1]))) ?>">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

<?php include '../includes/footer.php'; ?>
