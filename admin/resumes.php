<?php
$page_title = 'Resume Management';
require_once 'includes/admin_header.php';

requireAdminPermission($conn, 'view_resumes');

// Get search and filter parameters
$search = trim($_GET['search'] ?? '');
$statusFilter = $_GET['status'] ?? 'all';
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Build query
$whereConditions = ['a.resume_path IS NOT NULL', 'a.resume_path != ""'];
$params = [];
$types = '';

if (!empty($search)) {
    $whereConditions[] = '(a.full_name LIKE ? OR a.email LIKE ? OR j.title LIKE ?)';
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'sss';
}

$whereClause = implode(' AND ', $whereConditions);

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM applications a LEFT JOIN jobs j ON a.job_id = j.id WHERE $whereClause";
$stmt = $conn->prepare($countQuery);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$totalResumes = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Get resumes with pagination
$query = "SELECT a.id, a.full_name, a.email, a.job_position, a.resume_path, a.created_at, 
                 j.title as job_title, a.status, u.fullName as user_name
          FROM applications a 
          LEFT JOIN jobs j ON a.job_id = j.id
          LEFT JOIN users u ON a.user_id = u.id
          WHERE $whereClause
          ORDER BY a.created_at DESC
          LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $params[] = $perPage;
    $params[] = $offset;
    $types .= 'ii';
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param('ii', $perPage, $offset);
}
$stmt->execute();
$resumes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calculate pagination
$totalPages = ceil($totalResumes / $perPage);
?>

<div class="content-area">
    <div class="page-header">
        <div class="page-header-left">
            <h1>Resume Management</h1>
            <p>View and manage all uploaded resumes</p>
        </div>
        <div class="page-header-right">
            <button class="btn btn-primary" onclick="exportResumes()">
                <i class="fas fa-download"></i> Export CSV
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <div class="filters-row">
            <div class="filter-group">
                <input type="text" id="searchInput" class="form-control" placeholder="Search by name, email, or job title..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="filter-group">
                <select id="statusFilter" class="form-control">
                    <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>All Status</option>
                    <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="approved" <?= $statusFilter === 'approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    <option value="interview" <?= $statusFilter === 'interview' ? 'selected' : '' ?>>Interview</option>
                </select>
            </div>
            <button class="btn btn-secondary" onclick="applyFilters()">
                <i class="fas fa-filter"></i> Apply Filters
            </button>
            <button class="btn btn-outline" onclick="resetFilters()">
                <i class="fas fa-redo"></i> Reset
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-file-pdf"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($totalResumes) ?></div>
                <div class="stat-label">Total Resumes</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($conn->query("SELECT COUNT(*) as count FROM applications WHERE resume_path IS NOT NULL AND resume_path != '' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_assoc()['count'] ?? 0) ?></div>
                <div class="stat-label">This Week</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-hdd"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= formatBytes(getTotalResumeSize($conn)) ?></div>
                <div class="stat-label">Total Size</div>
            </div>
        </div>
    </div>

    <!-- Resumes Table -->
    <div class="table-container">
        <div class="table-header">
            <h2>Uploaded Resumes (<?= number_format($totalResumes) ?>)</h2>
        </div>
        
        <?php if (empty($resumes)): ?>
            <div class="empty-state">
                <i class="fas fa-file-pdf"></i>
                <h3>No resumes found</h3>
                <p>Try adjusting your search filters or check back later.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                            </th>
                            <th>Applicant</th>
                            <th>Email</th>
                            <th>Job Position</th>
                            <th>File Name</th>
                            <th>File Size</th>
                            <th>Upload Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resumes as $resume): ?>
                            <?php
                            $projectRoot = realpath(__DIR__ . '/../');
                            $resumePath = $projectRoot . '/' . ltrim($resume['resume_path'], '/');
                            $fileExists = file_exists($resumePath);
                            $fileSize = $fileExists ? filesize($resumePath) : 0;
                            $fileInfo = pathinfo($resumePath);
                            $fileName = $fileInfo['basename'] ?? basename($resume['resume_path']);
                            ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="resume-checkbox" value="<?= $resume['id'] ?>">
                                </td>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar">
                                            <?= strtoupper(substr($resume['full_name'], 0, 1)) ?>
                                        </div>
                                        <div class="user-info">
                                            <div class="user-name"><?= htmlspecialchars($resume['full_name']) ?></div>
                                            <?php if ($resume['user_name']): ?>
                                                <div class="user-email small"><?= htmlspecialchars($resume['user_name']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($resume['email']) ?></td>
                                <td><?= htmlspecialchars($resume['job_position'] ?? $resume['job_title']) ?></td>
                                <td>
                                    <div class="file-name">
                                        <i class="fas fa-file-<?= getFileIcon($fileName) ?>"></i>
                                        <?= htmlspecialchars($fileName) ?>
                                    </div>
                                </td>
                                <td><?= formatBytes($fileSize) ?></td>
                                <td><?= date('M d, Y', strtotime($resume['created_at'])) ?></td>
                                <td>
                                    <span class="badge badge-<?= $resume['status'] ?>">
                                        <?= ucfirst($resume['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($fileExists): ?>
                                            <button class="btn-icon" onclick="viewResume(<?= $resume['id'] ?>)" title="View Resume">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn-icon" onclick="downloadResume(<?= $resume['id'] ?>)" title="Download">
                                                <i class="fas fa-download"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn-icon disabled" title="File not found">
                                                <i class="fas fa-exclamation-triangle"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn-icon" onclick="viewApplication(<?= $resume['id'] ?>)" title="View Application">
                                            <i class="fas fa-file-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <button class="pagination-btn" onclick="goToPage(<?= $page - 1 ?>)">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                    <?php endif; ?>
                    
                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    
                    if ($startPage > 1) {
                        echo '<button class="pagination-btn" onclick="goToPage(1)">1</button>';
                        if ($startPage > 2) {
                            echo '<span class="pagination-ellipsis">...</span>';
                        }
                    }
                    
                    for ($i = $startPage; $i <= $endPage; $i++) {
                        $activeClass = $i === $page ? 'active' : '';
                        echo "<button class='pagination-btn $activeClass' onclick='goToPage($i)'>$i</button>";
                    }
                    
                    if ($endPage < $totalPages) {
                        if ($endPage < $totalPages - 1) {
                            echo '<span class="pagination-ellipsis">...</span>';
                        }
                        echo "<button class='pagination-btn' onclick='goToPage($totalPages)'>$totalPages</button>";
                    }
                    ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <button class="pagination-btn" onclick="goToPage(<?= $page + 1 ?>)">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Resume View Modal -->
<div class="modal" id="resumeModal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h2>Resume Preview</h2>
            <button class="modal-close" onclick="closeModal('resumeModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div id="resumePreview">
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading resume...</p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('resumeModal')">Close</button>
            <button class="btn btn-primary" id="downloadResumeBtn">
                <i class="fas fa-download"></i> Download
            </button>
        </div>
    </div>
</div>

<script>
function applyFilters() {
    const search = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;
    window.location.href = `resumes.php?search=${encodeURIComponent(search)}&status=${status}`;
}

function resetFilters() {
    window.location.href = 'resumes.php';
}

function viewResume(applicationId) {
    // Get download token first
    fetch('api/resume_api.php?action=view&application_id=' + applicationId, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Open preview in new tab for PDF
            if (data.preview_url) {
                window.open(data.preview_url, '_blank');
            } else {
                // For non-PDF files, download directly
                window.open(data.download_url, '_blank');
            }
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to load resume');
    });
}

function downloadResume(applicationId) {
    fetch('api/resume_api.php?action=view&application_id=' + applicationId, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.open(data.download_url, '_blank');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to download resume');
    });
}

function viewApplication(applicationId) {
    window.location.href = 'applications.php';
}

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.resume-checkbox');
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
}

function exportResumes() {
    const selected = Array.from(document.querySelectorAll('.resume-checkbox:checked')).map(cb => cb.value);
    if (selected.length === 0) {
        alert('Please select at least one resume to export');
        return;
    }
    
    // Create export request
    const params = new URLSearchParams();
    params.append('action', 'export');
    selected.forEach(id => params.append('resume_ids[]', id));
    
    window.location.href = 'api/resume_api.php?' + params.toString();
}

function goToPage(page) {
    const search = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;
    window.location.href = `resumes.php?search=${encodeURIComponent(search)}&status=${status}&page=${page}`;
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('show');
}

function getFileIcon(filename) {
    const ext = filename.split('.').pop().toLowerCase();
    const icons = {
        'pdf': 'pdf',
        'doc': 'word',
        'docx': 'word',
        'txt': 'alt',
        'rtf': 'alt'
    };
    return icons[ext] || 'alt';
}
</script>

<?php require_once 'includes/admin_footer.php'; ?>
