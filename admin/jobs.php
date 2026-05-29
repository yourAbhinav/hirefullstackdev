<?php
$page_title = 'Job Management';
require_once 'includes/admin_header.php';

requireAdminPermission($conn, 'view_jobs');

// Search and filter parameters
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$featuredFilter = $_GET['featured'] ?? '';
$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query with filters
$where = ['1=1'];
$params = [];
$types = '';

if (!empty($search)) {
    $where[] = '(title LIKE ? OR location LIKE ? OR tech_stack LIKE ?)';
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'sss';
}

if (!empty($statusFilter)) {
    $where[] = 'status = ?';
    $params[] = $statusFilter;
    $types .= 's';
}

if (!empty($featuredFilter)) {
    $where[] = 'featured = ?';
    $params[] = $featuredFilter === 'yes' ? 1 : 0;
    $types .= 'i';
}

$whereClause = implode(' AND ', $where);

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM jobs WHERE $whereClause";
$stmt = $conn->prepare($countQuery);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$totalJobs = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Get jobs with pagination
$query = "SELECT j.*, COUNT(a.id) as application_count 
          FROM jobs j 
          LEFT JOIN applications a ON j.id = a.job_id 
          WHERE $whereClause 
          GROUP BY j.id 
          ORDER BY j.created_at DESC 
          LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$jobs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC) ?: [];
$stmt->close();

// Get unique statuses
$statuses = $conn->query("SELECT DISTINCT status FROM jobs")->fetch_all(MYSQLI_ASSOC);

// Get statistics
$activeJobs = $conn->query("SELECT COUNT(*) as count FROM jobs WHERE status = 'active'")->fetch_assoc()['count'] ?? 0;
$closedJobs = $conn->query("SELECT COUNT(*) as count FROM jobs WHERE status = 'closed'")->fetch_assoc()['count'] ?? 0;
$featuredJobs = $conn->query("SELECT COUNT(*) as count FROM jobs WHERE featured = 1")->fetch_assoc()['count'] ?? 0;
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-content">
        <h1>Job Management</h1>
        <p>Create, edit, and manage job listings on the platform</p>
    </div>
    <div class="page-header-actions">
        <a href="<?= appUrl('admin/jobs_export.php') ?>" class="btn btn-outline">
            <i class="fas fa-download"></i> Export Jobs
        </a>
        <button class="btn btn-primary" onclick="openModal('jobModal')">
            <i class="fas fa-plus"></i> Create Job
        </button>
    </div>
</div>

<!-- Stats Overview -->
<div class="stats-overview">
    <div class="stat-card stat-active">
        <div class="stat-icon">
            <i class="fas fa-briefcase"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $activeJobs ?></div>
            <div class="stat-label">Active Jobs</div>
        </div>
    </div>
    <div class="stat-card stat-closed">
        <div class="stat-icon">
            <i class="fas fa-archive"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $closedJobs ?></div>
            <div class="stat-label">Closed Jobs</div>
        </div>
    </div>
    <div class="stat-card stat-featured">
        <div class="stat-icon">
            <i class="fas fa-star"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $featuredJobs ?></div>
            <div class="stat-label">Featured Jobs</div>
        </div>
    </div>
    <div class="stat-card stat-total">
        <div class="stat-icon">
            <i class="fas fa-layer-group"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($totalJobs) ?></div>
            <div class="stat-label">Total Jobs</div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="filters-section">
    <form method="GET" class="filters-form">
        <div class="filter-group">
            <label>Search</label>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by title, location, tech stack...">
        </div>
        
        <div class="filter-group">
            <label>Status</label>
            <select name="status">
                <option value="">All Status</option>
                <?php foreach ($statuses as $s): ?>
                    <option value="<?= $s['status'] ?>" <?= $statusFilter === $s['status'] ? 'selected' : '' ?>>
                        <?= ucfirst($s['status']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="filter-group">
            <label>Featured</label>
            <select name="featured">
                <option value="">All Jobs</option>
                <option value="yes" <?= $featuredFilter === 'yes' ? 'selected' : '' ?>>Featured Only</option>
                <option value="no" <?= $featuredFilter === 'no' ? 'selected' : '' ?>>Non-Featured</option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search"></i> Filter
        </button>
        
        <?php if (!empty($search) || !empty($statusFilter) || !empty($featuredFilter)): ?>
            <a href="<?= appUrl('admin/jobs.php') ?>" class="btn btn-outline">
                <i class="fas fa-times"></i> Clear
            </a>
        <?php endif; ?>
    </form>
</div>

<!-- Jobs Table -->
<div class="table-container">
    <div class="table-header">
        <div class="table-info">
            <span><?= number_format($totalJobs) ?> total jobs</span>
        </div>
        <div class="table-actions">
            <select class="bulk-action" id="bulkAction">
                <option value="">Bulk Actions</option>
                <option value="activate">Activate Selected</option>
                <option value="close">Close Selected</option>
                <option value="feature">Feature Selected</option>
                <option value="unfeature">Unfeature Selected</option>
                <option value="delete">Delete Selected</option>
            </select>
            <button class="btn btn-sm btn-secondary" onclick="applyBulkAction()">
                Apply
            </button>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAllJobs" onchange="toggleSelectAllJobs()"></th>
                    <th>Job Title</th>
                    <th>Location</th>
                    <th>Salary Range</th>
                    <th>Applications</th>
                    <th>Status</th>
                    <th>Posted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($jobs)): ?>
                    <tr>
                        <td colspan="8" class="text-center">
                            <div class="empty-state">
                                <i class="fas fa-briefcase"></i>
                                <p>No jobs found</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($jobs as $job): ?>
                        <tr>
                            <td><input type="checkbox" class="job-checkbox" value="<?= $job['id'] ?>"></td>
                            <td>
                                <div class="job-title-cell">
                                    <?php if ($job['featured']): ?>
                                        <i class="fas fa-star featured-star" title="Featured"></i>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($job['title']) ?>
                                </div>
                            </td>
                            <td>
                                <div class="job-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?= htmlspecialchars($job['location']) ?>
                                </div>
                            </td>
                            <td>
                                <div class="salary-range">
                                    <?php if ($job['salary_min'] || $job['salary_max']): ?>
                                        $<?= number_format($job['salary_min']) ?> - $<?= number_format($job['salary_max']) ?>
                                    <?php else: ?>
                                        Not specified
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="application-count">
                                    <i class="fas fa-users"></i>
                                    <?= number_format($job['application_count']) ?>
                                </div>
                            </td>
                            <td>
                                <?php 
                                $status = preg_replace('/[^a-z0-9_-]/i', '', $job['status']);
                                ?>
                                <span class="job-status status-<?= htmlspecialchars($status) ?>">
                                    <?= htmlspecialchars(ucfirst($job['status'])) ?>
                                </span>
                            </td>
                            <td>
                                <span class="time-ago" data-ts="<?= htmlspecialchars($job['created_at']) ?>"><?= time_elapsed_string($job['created_at']) ?></span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon" onclick="viewJob(<?= $job['id'] ?>)" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-icon" onclick="editJob(<?= $job['id'] ?>)" title="Edit Job">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon" onclick="toggleFeatured(<?= $job['id'] ?>, <?= $job['featured'] ? 0 : 1 ?>)" title="<?= $job['featured'] ? 'Remove from Featured' : 'Add to Featured' ?>">
                                        <i class="fas fa-star"></i>
                                    </button>
                                    <button class="btn-icon" onclick="toggleStatus(<?= $job['id'] ?>, '<?= $job['status'] === 'active' ? 'close' : 'activate' ?>')" title="<?= $job['status'] === 'active' ? 'Close Job' : 'Activate Job' ?>">
                                        <i class="fas <?= $job['status'] === 'active' ? 'fa-archive' : 'fa-check-circle' ?>"></i>
                                    </button>
                                    <button class="btn-icon btn-icon-danger" onclick="deleteJob(<?= $job['id'] ?>)" title="Delete Job">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalJobs > $limit): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="pagination-link">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
            <?php endif; ?>
            
            <span class="pagination-info">
                Page <?= $page ?> of <?= ceil($totalJobs / $limit) ?>
            </span>
            
            <?php if ($page < ceil($totalJobs / $limit)): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="pagination-link">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Job Create/Edit Modal -->
<div id="jobModal" class="modal">
    <div class="modal-content modal-xl">
        <div class="modal-header">
            <h3 id="jobModalTitle">Create New Job</h3>
            <button class="modal-close" onclick="closeModal('jobModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="jobForm">
                <input type="hidden" name="job_id" id="jobId">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Job Title *</label>
                        <input type="text" name="title" id="jobTitle" required placeholder="e.g., Senior Full Stack Developer">
                    </div>
                    <div class="form-group">
                        <label>Company ID *</label>
                        <input type="number" name="company_id" id="companyId" required placeholder="Company ID">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Location *</label>
                        <input type="text" name="location" id="jobLocation" required placeholder="e.g., San Francisco, CA or Remote">
                    </div>
                    <div class="form-group">
                        <label>Job Type *</label>
                        <select name="job_type" id="jobType" required>
                            <option value="full-time">Full-time</option>
                            <option value="part-time">Part-time</option>
                            <option value="contract">Contract</option>
                            <option value="internship">Internship</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Work Mode *</label>
                        <select name="work_mode" id="workMode" required>
                            <option value="on-site">On-site</option>
                            <option value="remote">Remote</option>
                            <option value="hybrid">Hybrid</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Experience Level *</label>
                        <select name="experience_level" id="experienceLevel" required>
                            <option value="entry">Entry Level (0-2 years)</option>
                            <option value="mid">Mid Level (2-5 years)</option>
                            <option value="senior">Senior Level (5-10 years)</option>
                            <option value="lead">Lead/Principal (10+ years)</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Salary Min</label>
                        <input type="number" name="salary_min" id="salaryMin" placeholder="Minimum annual salary">
                    </div>
                    <div class="form-group">
                        <label>Salary Max</label>
                        <input type="number" name="salary_max" id="salaryMax" placeholder="Maximum annual salary">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Tech Stack</label>
                    <input type="text" name="tech_stack" id="techStack" placeholder="e.g., React, Node.js, Python, AWS">
                </div>
                
                <div class="form-group">
                    <label>Description *</label>
                    <textarea name="description" id="jobDescription" rows="6" required placeholder="Job description..."></textarea>
                </div>
                
                <div class="form-group">
                    <label>Requirements</label>
                    <textarea name="requirements" id="requirements" rows="4" placeholder="Job requirements..."></textarea>
                </div>
                
                <div class="form-group">
                    <label>Benefits</label>
                    <textarea name="benefits" id="benefits" rows="4" placeholder="Job benefits..."></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Status *</label>
                        <select name="status" id="jobStatus" required>
                            <option value="active">Active</option>
                            <option value="closed">Closed</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Featured</label>
                        <div class="toggle-switch">
                            <input type="checkbox" name="featured" id="featured">
                            <label for="featured" class="toggle-label"></label>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('jobModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="jobSubmitBtn">Create Job</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Job Details Modal -->
<div id="jobDetailModal" class="modal">
    <div class="modal-content modal-xl">
        <div class="modal-header">
            <h3>Job Details</h3>
            <button class="modal-close" onclick="closeModal('jobDetailModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="jobDetailBody">
            <!-- Content loaded dynamically -->
        </div>
    </div>
</div>

<style>
/* Stats Overview */
.stats-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-active .stat-icon { background: linear-gradient(135deg, #10B981 0%, #059669 100%); }
.stat-closed .stat-icon { background: linear-gradient(135deg, #6B7280 0%, #4B5563 100%); }
.stat-featured .stat-icon { background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%); }
.stat-total .stat-icon { background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%); }

/* Job Title Cell */
.job-title-cell {
    font-weight: 600;
    color: #1a1a2e;
}

.featured-star {
    color: #F59E0B;
    margin-right: 8px;
}

/* Job Location */
.job-location {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    color: #666;
}

/* Salary Range */
.salary-range {
    font-size: 14px;
    color: #333;
    font-weight: 500;
}

/* Application Count */
.application-count {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    color: #666;
}

/* Job Status */
.job-status {
    padding: 6px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-active { background: #D1FAE5; color: #059669; }
.status-closed { background: #FEE2E2; color: #DC2626; }
.status-draft { background: #DBEAFE; color: #1D4ED8; }

/* Form Row */
.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

/* Toggle Switch */
.toggle-switch {
    position: relative;
    display: inline-block;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-label {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 26px;
    background-color: #d1d5db;
    border-radius: 13px;
    transition: background-color 0.3s;
    cursor: pointer;
}

.toggle-label::after {
    content: '';
    position: absolute;
    top: 3px;
    left: 3px;
    width: 20px;
    height: 20px;
    background-color: white;
    border-radius: 50%;
    transition: transform 0.3s;
}

.toggle-switch input:checked + .toggle-label {
    background-color: #4F46E5;
}

.toggle-switch input:checked + .toggle-label::after {
    transform: translateX(24px);
}

@media (max-width: 768px) {
    .stats-overview {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Modal functions
function openModal(modalId) {
    document.getElementById(modalId).classList.add('show');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('show');
}

// Select all checkboxes
function toggleSelectAllJobs() {
    const selectAll = document.getElementById('selectAllJobs');
    const checkboxes = document.querySelectorAll('.job-checkbox');
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
}

// Open create job modal
function openJobModal() {
    document.getElementById('jobForm').reset();
    document.getElementById('jobId').value = '';
    document.getElementById('jobModalTitle').textContent = 'Create New Job';
    document.getElementById('jobSubmitBtn').textContent = 'Create Job';
    openModal('jobModal');
}

// Edit job
function editJob(jobId) {
    fetch(`<?= appUrl('admin/api/job_api.php') ?>?action=get_job&id=${jobId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const job = data.job;
                document.getElementById('jobId').value = job.id;
                document.getElementById('jobTitle').value = job.title;
                document.getElementById('companyId').value = job.company_id;
                document.getElementById('jobLocation').value = job.location;
                document.getElementById('jobType').value = job.job_type;
                document.getElementById('workMode').value = job.work_mode;
                document.getElementById('experienceLevel').value = job.experience_level;
                document.getElementById('salaryMin').value = job.salary_min || '';
                document.getElementById('salaryMax').value = job.salary_max || '';
                document.getElementById('techStack').value = job.tech_stack || '';
                document.getElementById('jobDescription').value = job.description || '';
                document.getElementById('requirements').value = job.requirements || '';
                document.getElementById('benefits').value = job.benefits || '';
                document.getElementById('jobStatus').value = job.status;
                document.getElementById('featured').checked = job.featured == 1;
                
                document.getElementById('jobModalTitle').textContent = 'Edit Job';
                document.getElementById('jobSubmitBtn').textContent = 'Update Job';
                openModal('jobModal');
            }
        });
}

// View job details
function viewJob(jobId) {
    fetch(`<?= appUrl('admin/api/job_api.php') ?>?action=get_job&id=${jobId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const job = data.job;
                document.getElementById('jobDetailBody').innerHTML = `
                    <div class="job-detail-header">
                        <div>
                            ${job.featured ? '<span class="badge badge-featured"><i class="fas fa-star"></i> Featured</span>' : ''}
                            <h4>${job.title}</h4>
                            <p>${job.location}</p>
                        </div>
                        <span class="job-status status-${job.status}">${job.status}</span>
                    </div>
                    <div class="job-detail-grid">
                        <div class="detail-section">
                            <h5>Job Information</h5>
                            <div class="detail-row">
                                <label>Type:</label>
                                <span>${job.job_type}</span>
                            </div>
                            <div class="detail-row">
                                <label>Work Mode:</label>
                                <span>${job.work_mode}</span>
                            </div>
                            <div class="detail-row">
                                <label>Experience:</label>
                                <span>${job.experience_level}</span>
                            </div>
                            <div class="detail-row">
                                <label>Salary:</label>
                                <span>$${job.salary_min ? number_format(job.salary_min) : 'Not specified'} - $${job.salary_max ? number_format(job.salary_max) : 'Not specified'}</span>
                            </div>
                            <div class="detail-row">
                                <label>Applications:</label>
                                <span>${job.application_count}</span>
                            </div>
                        </div>
                        <div class="detail-section">
                            <h5>Tech Stack</h5>
                            <p>${job.tech_stack || 'Not specified'}</p>
                        </div>
                        <div class="detail-section full-width">
                            <h5>Description</h5>
                            <p>${job.description || 'No description provided'}</p>
                        </div>
                        ${job.requirements ? `
                        <div class="detail-section full-width">
                            <h5>Requirements</h5>
                            <p>${job.requirements}</p>
                        </div>
                        ` : ''}
                        ${job.benefits ? `
                        <div class="detail-section full-width">
                            <h5>Benefits</h5>
                            <p>${job.benefits}</p>
                        </div>
                        ` : ''}
                        <div class="detail-section">
                            <h5>Meta Information</h5>
                            <div class="detail-row">
                                <label>Posted:</label>
                                <span>${new Date(job.created_at).toLocaleString()}</span>
                            </div>
                            <div class="detail-row">
                                <label>Updated:</label>
                                <span>${new Date(job.updated_at).toLocaleString()}</span>
                            </div>
                        </div>
                    </div>
                `;
                openModal('jobDetailModal');
            }
        });
}

// Toggle featured status
function toggleFeatured(jobId, isFeatured) {
    if (!confirm(`Are you sure you want to ${isFeatured ? 'feature' : 'unfeature'} this job?`)) return;
    
    fetch('<?= appUrl('admin/api/job_api.php') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'toggle_featured', job_id: jobId, featured: isFeatured })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Action failed');
        }
    });
}

// Toggle job status
function toggleStatus(jobId, action) {
    if (!confirm(`Are you sure you want to ${action} this job?`)) return;
    
    fetch('<?= appUrl('admin/api/job_api.php') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: action === 'activate' ? 'activate' : 'close', job_id: jobId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Action failed');
        }
    });
}

// Delete job
function deleteJob(jobId) {
    if (!confirm('Are you sure you want to delete this job? This action cannot be undone.')) return;
    
    fetch('<?= appUrl('admin/api/job_api.php') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'delete', job_id: jobId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Delete failed');
        }
    });
}

// Bulk actions
function applyBulkAction() {
    const action = document.getElementById('bulkAction').value;
    const selected = Array.from(document.querySelectorAll('.job-checkbox:checked')).map(cb => cb.value);
    
    if (!action) {
        alert('Please select an action');
        return;
    }
    
    if (selected.length === 0) {
        alert('Please select at least one job');
        return;
    }
    
    if (action === 'delete' && !confirm(`Delete ${selected.length} jobs?`)) return;
    
    fetch('<?= appUrl('admin/api/job_api.php') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: `bulk_${action}`, job_ids: selected })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Bulk action failed');
        }
    });
}

// Job form submission
document.getElementById('jobForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    const jobId = data.job_id;
    
    data.featured = document.getElementById('featured').checked ? 1 : 0;
    data.action = jobId ? 'update' : 'create';
    
    fetch('<?= appUrl('admin/api/job_api.php') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            closeModal('jobModal');
            location.reload();
        } else {
            alert(result.message || 'Operation failed');
        }
    });
});
</script>

<?php require_once 'includes/admin_footer.php'; ?>
