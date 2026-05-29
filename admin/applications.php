<?php
$page_title = 'Application Management';
require_once 'includes/admin_header.php';

requireAdminPermission($conn, 'view_applications');

// Search and filter parameters
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$dateFilter = $_GET['date_range'] ?? '';
$jobFilter = $_GET['job_id'] ?? '';
$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = 25;
$offset = ($page - 1) * $limit;

// Build query with filters
$where = ['1=1'];
$params = [];
$types = '';

if (!empty($search)) {
    $where[] = '(full_name LIKE ? OR email LIKE ? OR job_position LIKE ?)';
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

if (!empty($jobFilter)) {
    $where[] = 'job_id = ?';
    $params[] = $jobFilter;
    $types .= 'i';
}

if (!empty($dateFilter)) {
    switch ($dateFilter) {
        case 'today':
            $where[] = 'DATE(created_at) = CURDATE()';
            break;
        case 'week':
            $where[] = 'created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
            break;
        case 'month':
            $where[] = 'created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)';
            break;
        case 'quarter':
            $where[] = 'created_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH)';
            break;
    }
}

$whereClause = implode(' AND ', $where);

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM applications WHERE $whereClause";
$stmt = $conn->prepare($countQuery);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$totalApplications = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Get applications with pagination
$query = "SELECT a.*, u.fullName as user_name, u.email as user_email, u.role as user_role, j.title as job_title, j.company_id 
          FROM applications a 
          LEFT JOIN users u ON a.user_id = u.id 
          LEFT JOIN jobs j ON a.job_id = j.id 
          WHERE $whereClause 
          ORDER BY a.created_at DESC 
          LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$applications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC) ?: [];
$stmt->close();

// Get unique statuses and jobs for filters
$statuses = $conn->query("SELECT DISTINCT status FROM applications")->fetch_all(MYSQLI_ASSOC);
$jobs = $conn->query("SELECT id, title FROM jobs ORDER BY title")->fetch_all(MYSQLI_ASSOC);

// Get status counts
$statusCounts = [];
foreach (['pending', 'approved', 'rejected', 'interview', 'reviewed', 'shortlisted'] as $status) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM applications WHERE status = ?");
    $stmt->bind_param('s', $status);
    $stmt->execute();
    $statusCounts[$status] = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
    $stmt->close();
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-content">
        <h1>Application Management</h1>
        <p>Review, manage, and track all job applications</p>
    </div>
    <div class="page-header-actions">
        <a href="<?= appUrl('admin/applications_export.php') ?>" class="btn btn-outline">
            <i class="fas fa-download"></i> Export CSV
        </a>
        <button class="btn btn-primary" onclick="openModal('exportModal')">
            <i class="fas fa-file-export"></i> Export Options
        </button>
    </div>
</div>

<!-- Status Overview Cards -->
<div class="status-overview">
    <div class="status-card status-pending">
        <div class="status-card-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div class="status-card-content">
            <div class="status-card-value"><?= $statusCounts['pending'] ?></div>
            <div class="status-card-label">Pending</div>
        </div>
    </div>
    <div class="status-card status-approved">
        <div class="status-card-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="status-card-content">
            <div class="status-card-value"><?= $statusCounts['approved'] ?></div>
            <div class="status-card-label">Approved</div>
        </div>
    </div>
    <div class="status-card status-rejected">
        <div class="status-card-icon">
            <i class="fas fa-times-circle"></i>
        </div>
        <div class="status-card-content">
            <div class="status-card-value"><?= $statusCounts['rejected'] ?></div>
            <div class="status-card-label">Rejected</div>
        </div>
    </div>
    <div class="status-card status-interview">
        <div class="status-card-icon">
            <i class="fas fa-calendar"></i>
        </div>
        <div class="status-card-content">
            <div class="status-card-value"><?= $statusCounts['interview'] ?></div>
            <div class="status-card-label">Interview</div>
        </div>
    </div>
    <div class="status-card status-reviewed">
        <div class="status-card-icon">
            <i class="fas fa-eye"></i>
        </div>
        <div class="status-card-content">
            <div class="status-card-value"><?= $statusCounts['reviewed'] ?></div>
            <div class="status-card-label">Reviewed</div>
        </div>
    </div>
    <div class="status-card status-shortlisted">
        <div class="status-card-icon">
            <i class="fas fa-star"></i>
        </div>
        <div class="status-card-content">
            <div class="status-card-value"><?= $statusCounts['shortlisted'] ?></div>
            <div class="status-card-label">Shortlisted</div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="filters-section">
    <form method="GET" class="filters-form">
        <div class="filter-group">
            <label>Search</label>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name, email, job...">
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
            <label>Job</label>
            <select name="job_id">
                <option value="">All Jobs</option>
                <?php foreach ($jobs as $job): ?>
                    <option value="<?= $job['id'] ?>" <?= $jobFilter == $job['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($job['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="filter-group">
            <label>Date Range</label>
            <select name="date_range">
                <option value="">All Time</option>
                <option value="today" <?= $dateFilter === 'today' ? 'selected' : '' ?>>Today</option>
                <option value="week" <?= $dateFilter === 'week' ? 'selected' : '' ?>>Last 7 Days</option>
                <option value="month" <?= $dateFilter === 'month' ? 'selected' : '' ?>>Last 30 Days</option>
                <option value="quarter" <?= $dateFilter === 'quarter' ? 'selected' : '' ?>>Last 3 Months</option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search"></i> Filter
        </button>
        
        <?php if (!empty($search) || !empty($statusFilter) || !empty($jobFilter) || !empty($dateFilter)): ?>
            <a href="<?= appUrl('admin/applications.php') ?>" class="btn btn-outline">
                <i class="fas fa-times"></i> Clear
            </a>
        <?php endif; ?>
    </form>
</div>

<!-- Applications Table -->
<div class="table-container">
    <div class="table-header">
        <div class="table-info">
            <span><?= number_format($totalApplications) ?> total applications</span>
        </div>
        <div class="table-actions">
            <select class="bulk-action" id="bulkAction">
                <option value="">Bulk Actions</option>
                <option value="approve">Approve Selected</option>
                <option value="reject">Reject Selected</option>
                <option value="interview">Schedule Interview</option>
                <option value="reviewed">Mark as Reviewed</option>
                <option value="shortlist">Add to Shortlist</option>
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
                    <th><input type="checkbox" id="selectAllApps" onchange="toggleSelectAllApps()"></th>
                    <th>Applicant</th>
                    <th>Position</th>
                    <th>Applied</th>
                    <th>Time Ago</th>
                    <th>Resume</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($applications)): ?>
                    <tr>
                        <td colspan="8" class="text-center">
                            <div class="empty-state">
                                <i class="fas fa-file-alt"></i>
                                <p>No applications found</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($applications as $app): ?>
                        <tr>
                            <td><input type="checkbox" class="app-checkbox" value="<?= $app['id'] ?>"></td>
                            <td>
                                <div class="applicant-cell">
                                    <div class="applicant-avatar">
                                        <?= strtoupper(substr($app['full_name'], 0, 1)) ?>
                                    </div>
                                    <div class="applicant-info">
                                        <div class="applicant-name"><?= htmlspecialchars($app['full_name']) ?></div>
                                        <div class="applicant-email"><?= htmlspecialchars($app['user_email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="job-title"><?= htmlspecialchars($app['job_title'] ?: ($app['job_position'] ?? 'General Application')) ?></div>
                            </td>
                            <td>
                                <span class="date-cell"><?= date('M j, Y', strtotime($app['created_at'])) ?></span>
                            </td>
                            <td>
                                <span class="time-ago"><?= time_elapsed_string($app['created_at']) ?></span>
                            </td>
                            <td>
                                <?php if (applicationResumeExistsOnDisk($app['resume_path'] ?? null)): ?>
                                    <div class="action-buttons">
                                        <button type="button" class="btn-icon" onclick="viewResume(<?= (int) $app['id'] ?>)" title="View Resume">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn-icon" onclick="downloadResume(<?= (int) $app['id'] ?>)" title="Download Resume">
                                            <i class="fas fa-download"></i>
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <?= renderResumeStatusBadge($app['resume_path'] ?? null, true) ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($app['status'])): ?>
                                    <span class="status-pill status-<?= htmlspecialchars($app['status']) ?>">
                                        <?= applicationStatusLabel($app['status']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="status-pill status-pending">
                                        Pending
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-dropdown">
                                    <button type="button" class="btn-icon action-dropdown-toggle" aria-label="Actions" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="action-dropdown-menu" role="menu">
                                        <button type="button" class="action-dropdown-item" data-action="view" onclick="viewApplication(<?= (int) $app['id'] ?>)">
                                            <i class="fas fa-eye"></i> View Details
                                        </button>
                                        <button type="button" class="action-dropdown-item" data-action="status" onclick="openStatusModal(<?= (int) $app['id'] ?>)">
                                            <i class="fas fa-edit"></i> Change Status
                                        </button>
                                        <?php if (applicationResumeExistsOnDisk($app['resume_path'] ?? null)): ?>
                                        <button type="button" class="action-dropdown-item" data-action="resume" onclick="viewResume(<?= (int) $app['id'] ?>)">
                                            <i class="fas fa-file-pdf"></i> View Resume
                                        </button>
                                        <?php endif; ?>
                                        <button type="button" class="action-dropdown-item" data-action="note" onclick="addNote(<?= (int) $app['id'] ?>)">
                                            <i class="fas fa-sticky-note"></i> Add Note
                                        </button>
                                        <button type="button" class="action-dropdown-item danger" data-action="delete" onclick="deleteApplication(<?= (int) $app['id'] ?>)">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalApplications > $limit): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="pagination-link">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
            <?php endif; ?>
            
            <span class="pagination-info">
                Page <?= $page ?> of <?= ceil($totalApplications / $limit) ?>
            </span>
            
            <?php if ($page < ceil($totalApplications / $limit)): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="pagination-link">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Application Details Modal -->
<div id="applicationModal" class="modal">
    <div class="modal-content modal-xl">
        <div class="modal-header">
            <h3>Application Details</h3>
            <button class="modal-close" onclick="closeModal('applicationModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="applicationModalBody">
            <!-- Content loaded dynamically -->
        </div>
    </div>
</div>

<!-- Status Change Modal -->
<div id="statusModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Change Application Status</h3>
            <button class="modal-close" onclick="closeModal('statusModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="statusForm">
                <input type="hidden" name="application_id" id="applicationId">
                <input type="hidden" name="csrf_token" id="statusCsrfToken" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                
                <div class="form-group">
                    <label>New Status *</label>
                    <select name="status" required>
                        <option value="">-- Select Status --</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="interview">Interview</option>
                        <option value="reviewed">Reviewed</option>
                        <option value="shortlisted">Shortlisted</option>
                    </select>
                </div>
                
                <div class="form-group" id="interviewDateGroup" style="display: none;">
                    <label>Interview Date *</label>
                    <input type="datetime-local" name="interview_date" id="interviewDate">
                </div>
                
                <div class="form-group">
                    <label>Admin Notes</label>
                    <textarea name="admin_notes" rows="4" placeholder="Add any notes about this decision..."></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('statusModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Resume View Modal -->
<div id="resumeModal" class="modal">
    <div class="modal-content modal-xl">
        <div class="modal-header">
            <h3>Resume View</h3>
            <button class="modal-close" onclick="closeModal('resumeModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="resumeModalBody">
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading resume...</p>
            </div>
        </div>
    </div>
</div>

<style>
/* Status Overview */
.status-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.status-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    transition: all 0.3s;
}

.status-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.status-card-icon {
    width: 45px;
    height: 45px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: white;
}

.status-pending .status-card-icon { background: #F59E0B; }
.status-approved .status-card-icon { background: #10B981; }
.status-rejected .status-card-icon { background: #DC2626; }
.status-interview .status-card-icon { background: #3B82F6; }
.status-reviewed .status-card-icon { background: #8B5CF6; }
.status-shortlisted .status-card-icon { background: #EC4899; }

.status-card-value {
    font-size: 28px;
    font-weight: 700;
    color: #1a1a2e;
    line-height: 1;
}

.status-card-label {
    font-size: 13px;
    color: #666;
    margin-top: 5px;
}

/* Applicant Cell */
.applicant-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.applicant-avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
}

.applicant-name {
    font-weight: 600;
    color: #1a1a2e;
    margin-bottom: 4px;
}

.applicant-email {
    font-size: 13px;
    color: #666;
}

/* Job Info */
.job-info {
    font-size: 14px;
}

.job-title {
    font-weight: 600;
    color: #1a1a2e;
    margin-bottom: 4px;
}

.job-role {
    font-size: 12px;
    color: #666;
}

/* Date Info */
.date-info {
    font-size: 14px;
}

.date-info .time-ago {
    font-size: 12px;
    color: #666;
    margin-top: 4px;
}

/* Application Status */
.application-status {
    padding: 6px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending { background: #FEF3C7; color: #D97706; }
.status-approved { background: #D1FAE5; color: #059669; }
.status-rejected { background: #FEE2E2; color: #DC2626; }
.status-interview { background: #DBEAFE; color: #1D4ED8; }
.status-reviewed { background: #F3E8FF; color: #7C3AED; }
.status-shortlisted { background: #FCE7F3; color: #EC4899; }

/* No Resume */
.no-resume {
    font-size: 13px;
    color: #999;
    font-style: italic;
}

/* Button Icon Small */
.btn-icon-sm {
    width: 28px;
    height: 28px;
    font-size: 12px;
}

/* Modal XL */
.modal-xl {
    max-width: 900px;
}

/* Loading Spinner */
.loading-spinner {
    text-align: center;
    padding: 40px;
    color: #666;
}

.loading-spinner i {
    font-size: 48px;
    margin-bottom: 15px;
}

@media (max-width: 768px) {
    .status-overview {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .filters-form {
        flex-direction: column;
    }
}
</style>

<script>
function appApiRequest(payload) {
    if (window.AdminPanel && AdminPanel.applicationApiRequest) {
        return AdminPanel.applicationApiRequest(payload);
    }
    const api = '<?= appUrl('admin/api/application_api.php') ?>';
    return fetch(api, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(Object.assign({}, payload, { csrf_token: (window.AdminPanel && AdminPanel.config.csrfToken) || '' }))
    }).then(r => r.json());
}

// Select all checkboxes
function toggleSelectAllApps() {
    const selectAll = document.getElementById('selectAllApps');
    const checkboxes = document.querySelectorAll('.app-checkbox');
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
}

// View application details
function viewApplication(appId) {
    fetch(`<?= appUrl('admin/api/application_api.php') ?>?action=get_application&id=${appId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const app = data.application;
                document.getElementById('applicationModalBody').innerHTML = `
                    <div class="application-detail-header">
                        <div>
                            <h4>${app.full_name}</h4>
                            <p>${app.user_email}</p>
                        </div>
                        <span class="application-status status-${app.status}">${app.status}</span>
                    </div>
                    <div class="application-detail-grid">
                        <div class="detail-section">
                            <h5>Application Information</h5>
                            <div class="detail-row">
                                <label>Position:</label>
                                <span>${app.job_title || 'N/A'}</span>
                            </div>
                            <div class="detail-row">
                                <label>Applied:</label>
                                <span>${new Date(app.created_at).toLocaleString()}</span>
                            </div>
                            <div class="detail-row">
                                <label>Phone:</label>
                                <span>${app.phone || 'Not provided'}</span>
                            </div>
                            <div class="detail-row">
                                <label>Experience:</label>
                                <span>${app.experience || 'Not specified'}</span>
                            </div>
                        </div>
                        <div class="detail-section">
                            <h5>Skills & Portfolio</h5>
                            <div class="detail-row">
                                <label>Tech Stack:</label>
                                <span>${app.tech_stack || 'Not specified'}</span>
                            </div>
                            <div class="detail-row">
                                <label>Portfolio:</label>
                                ${app.portfolio_url ? `<a href="${app.portfolio_url}" target="_blank">${app.portfolio_url}</a>` : 'Not provided'}
                            </div>
                        </div>
                        <div class="detail-section">
                            <h5>Cover Letter</h5>
                            <p>${app.message || 'No cover letter provided'}</p>
                        </div>
                        ${app.admin_notes ? `
                        <div class="detail-section">
                            <h5>Admin Notes</h5>
                            <p>${app.admin_notes}</p>
                        </div>
                        ` : ''}
                        ${app.resume_path ? `
                        <div class="detail-section">
                            <h5>Resume</h5>
                            <button class="btn btn-primary" onclick="viewResume(${app.id})">
                                <i class="fas fa-file-pdf"></i> View Resume
                            </button>
                        </div>
                        ` : ''}
                    </div>
                `;
                openModal('applicationModal');
            }
        });
}

// View resume
function viewResume(appId) {
    openModal('resumeModal');
    document.getElementById('resumeModalBody').innerHTML = `
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Loading resume...</p>
        </div>
    `;
    
    fetch(`<?= appUrl('admin/api/resume_api.php') ?>?action=view&application_id=${appId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.preview_url) {
                    document.getElementById('resumeModalBody').innerHTML = `
                        <iframe src="${data.preview_url}" width="100%" height="600px" style="border: none;"></iframe>
                        <div class="modal-footer-actions">
                            <a href="${data.download_url}" class="btn btn-primary" target="_blank">
                                <i class="fas fa-download"></i> Download Resume
                            </a>
                        </div>
                    `;
                } else {
                    document.getElementById('resumeModalBody').innerHTML = `
                        <div class="resume-download-option">
                            <i class="fas fa-file-pdf"></i>
                            <h4>Resume Available for Download</h4>
                            <p>File: ${data.filename}</p>
                            <p>Size: ${data.file_size}</p>
                            <a href="${data.download_url}" class="btn btn-primary" target="_blank">
                                <i class="fas fa-download"></i> Download Resume
                            </a>
                        </div>
                    `;
                }
            } else {
                document.getElementById('resumeModalBody').innerHTML = `
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <p>${data.message || 'Failed to load resume'}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            document.getElementById('resumeModalBody').innerHTML = `
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>Error loading resume: ${error.message}</p>
                </div>
            `;
        });
}

// Download resume
function downloadResume(appId) {
    window.open(`<?= appUrl('admin/api/resume_api.php') ?>?action=view&application_id=${appId}`, '_blank');
}

// Open status modal
function openStatusModal(appId) {
    document.getElementById('applicationId').value = appId;
    // Don't reset form completely, just set default values
    document.querySelector('select[name="status"]').value = '';
    document.querySelector('textarea[name="admin_notes"]').value = '';
    document.getElementById('interviewDateGroup').style.display = 'none';
    // Update CSRF token
    document.getElementById('statusCsrfToken').value = window.AdminPanel && AdminPanel.config.csrfToken ? AdminPanel.config.csrfToken : '';
    openModal('statusModal');
}

// Handle status change
document.getElementById('statusForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    // Validate status is not empty
    if (!data.status || data.status === '') {
        alert('Please select a status');
        return;
    }
    
    appApiRequest({ action: 'update_status', ...data })
    .then(result => {
        if (result.success) {
            closeModal('statusModal');
            location.reload();
        } else {
            alert(result.message || 'Failed to update status');
        }
    })
    .catch(error => {
        console.error('Status update error:', error);
        alert('Failed to update status. Please try again.');
    });
});

// Status dropdown change
document.querySelector('select[name="status"]').addEventListener('change', function() {
    const interviewGroup = document.getElementById('interviewDateGroup');
    interviewGroup.style.display = this.value === 'interview' ? 'block' : 'none';
});

// Add note
function addNote(appId) {
    const note = prompt('Enter admin note:');
    if (note) {
        appApiRequest({ action: 'add_note', application_id: appId, note })
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to add note');
            }
        });
    }
}

// Delete application
function deleteApplication(appId) {
    if (!confirm('Are you sure you want to delete this application?')) return;
    
    appApiRequest({ action: 'delete', application_id: appId })
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
    const selected = Array.from(document.querySelectorAll('.app-checkbox:checked')).map(cb => cb.value);
    
    if (!action) {
        alert('Please select an action');
        return;
    }
    
    if (selected.length === 0) {
        alert('Please select at least one application');
        return;
    }
    
    if (action === 'delete' && !confirm(`Delete ${selected.length} applications?`)) return;
    
    const actionText = action === 'delete' ? 'delete' : 'update status for';
    if (!confirm(`Are you sure you want to ${actionText} ${selected.length} application(s)?`)) return;
    
    appApiRequest({ action: `bulk_${action}`, application_ids: selected })
    .then(data => {
        if (data.success) {
            const message = data.message || 'Bulk action completed successfully';
            alert(message);
            // Force page reload to show updated status
            setTimeout(() => {
                location.reload();
            }, 500);
        } else {
            alert(data.message || 'Bulk action failed');
        }
    })
    .catch(error => {
        console.error('Bulk action error:', error);
        alert('Failed to perform bulk action. Please try again.');
    });
}
</script>

<?php require_once 'includes/admin_footer.php'; ?>
