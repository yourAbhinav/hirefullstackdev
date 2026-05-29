<?php
$page_title = 'Audit Logs';
require_once 'includes/admin_header.php';

requireAdminPermission($conn, 'view_logs');

// Get search and filter parameters
$search = trim($_GET['search'] ?? '');
$actionFilter = $_GET['action'] ?? 'all';
$adminFilter = $_GET['admin'] ?? 'all';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 50;
$offset = ($page - 1) * $perPage;

// Build query
$whereConditions = ['1=1'];
$params = [];
$types = '';

if (!empty($search)) {
    $whereConditions[] = '(al.action LIKE ? OR al.entity_type LIKE ? OR a.name LIKE ?)';
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'sss';
}

if ($actionFilter !== 'all') {
    $whereConditions[] = 'al.action = ?';
    $params[] = $actionFilter;
    $types .= 's';
}

if ($adminFilter !== 'all') {
    $whereConditions[] = 'al.admin_id = ?';
    $params[] = $adminFilter;
    $types .= 'i';
}

if (!empty($dateFrom)) {
    $whereConditions[] = 'al.created_at >= ?';
    $params[] = $dateFrom . ' 00:00:00';
    $types .= 's';
}

if (!empty($dateTo)) {
    $whereConditions[] = 'al.created_at <= ?';
    $params[] = $dateTo . ' 23:59:59';
    $types .= 's';
}

$whereClause = implode(' AND ', $whereConditions);

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM admin_audit_logs al LEFT JOIN admin_accounts a ON al.admin_id = a.id WHERE $whereClause";
$stmt = $conn->prepare($countQuery);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$totalLogs = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Get logs with pagination
$query = "SELECT al.*, a.name as admin_name, a.email as admin_email 
          FROM admin_audit_logs al 
          LEFT JOIN admin_accounts a ON al.admin_id = a.id 
          WHERE $whereClause
          ORDER BY al.created_at DESC
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
$logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get admin list for filter
$admins = $conn->query("SELECT id, name, email FROM admin_accounts ORDER BY name")->fetch_all(MYSQLI_ASSOC) ?: [];

// Get action list for filter
$actions = $conn->query("SELECT DISTINCT action FROM admin_audit_logs ORDER BY action")->fetch_all(MYSQLI_ASSOC) ?: [];

// Calculate pagination
$totalPages = ceil($totalLogs / $perPage);
?>

<div class="content-area">
    <div class="page-header">
        <div class="page-header-left">
            <h1>Audit Logs</h1>
            <p>Track all admin actions and system events</p>
        </div>
        <div class="page-header-right">
            <button class="btn btn-primary" onclick="exportLogs()">
                <i class="fas fa-download"></i> Export Logs
            </button>
            <button class="btn btn-outline" onclick="clearOldLogs()">
                <i class="fas fa-trash"></i> Clear Old Logs
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <div class="filters-row">
            <div class="filter-group">
                <input type="text" id="searchInput" class="form-control" placeholder="Search by action, entity, or admin..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="filter-group">
                <select id="actionFilter" class="form-control">
                    <option value="all" <?= $actionFilter === 'all' ? 'selected' : '' ?>>All Actions</option>
                    <?php foreach ($actions as $act): ?>
                        <option value="<?= htmlspecialchars($act['action']) ?>" <?= $actionFilter === $act['action'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($act['action']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <select id="adminFilter" class="form-control">
                    <option value="all" <?= $adminFilter === 'all' ? 'selected' : '' ?>>All Admins</option>
                    <?php foreach ($admins as $adm): ?>
                        <option value="<?= $adm['id'] ?>" <?= $adminFilter == $adm['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($adm['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <input type="date" id="dateFrom" class="form-control" placeholder="From" value="<?= $dateFrom ?>">
            </div>
            <div class="filter-group">
                <input type="date" id="dateTo" class="form-control" placeholder="To" value="<?= $dateTo ?>">
            </div>
            <button class="btn btn-secondary" onclick="applyFilters()">
                <i class="fas fa-filter"></i> Apply
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
                <i class="fas fa-history"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($totalLogs) ?></div>
                <div class="stat-label">Total Logs</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-calendar-day"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($conn->query("SELECT COUNT(*) as count FROM admin_audit_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)")->fetch_assoc()['count'] ?? 0) ?></div>
                <div class="stat-label">Last 24 Hours</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-calendar-week"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($conn->query("SELECT COUNT(*) as count FROM admin_audit_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_assoc()['count'] ?? 0) ?></div>
                <div class="stat-label">Last 7 Days</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format(count($admins)) ?></div>
                <div class="stat-label">Active Admins</div>
            </div>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="table-container">
        <div class="table-header">
            <h2>Audit Log Entries (<?= number_format($totalLogs) ?>)</h2>
        </div>
        
        <?php if (empty($logs)): ?>
            <div class="empty-state">
                <i class="fas fa-history"></i>
                <h3>No logs found</h3>
                <p>Try adjusting your search filters or check back later.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>Admin</th>
                            <th>Action</th>
                            <th>Entity Type</th>
                            <th>Entity ID</th>
                            <th>IP Address</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td>
                                    <div class="timestamp-cell">
                                        <div class="date"><?= date('M d, Y', strtotime($log['created_at'])) ?></div>
                                        <div class="time"><?= date('H:i:s', strtotime($log['created_at'])) ?></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="admin-cell">
                                        <div class="admin-avatar">
                                            <?= strtoupper(substr($log['admin_name'] ?? 'Unknown', 0, 1)) ?>
                                        </div>
                                        <div class="admin-info">
                                            <div class="admin-name"><?= htmlspecialchars($log['admin_name'] ?? 'Unknown') ?></div>
                                            <div class="admin-email small"><?= htmlspecialchars($log['admin_email'] ?? '') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="action-badge action-<?= $log['action'] ?>">
                                        <?= htmlspecialchars($log['action']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($log['entity_type'] ?? '-') ?></td>
                                <td><?= $log['entity_id'] ? htmlspecialchars($log['entity_id']) : '-' ?></td>
                                <td><?= htmlspecialchars($log['ip_address'] ?? '-') ?></td>
                                <td>
                                    <button class="btn-icon" onclick="viewLogDetails(<?= htmlspecialchars(json_encode($log)) ?>)" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
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

<!-- Log Details Modal -->
<div class="modal" id="logModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Log Details</h2>
            <button class="modal-close" onclick="closeModal('logModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div id="logDetails"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('logModal')">Close</button>
        </div>
    </div>
</div>

<script>
function applyFilters() {
    const search = document.getElementById('searchInput').value;
    const action = document.getElementById('actionFilter').value;
    const admin = document.getElementById('adminFilter').value;
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (action !== 'all') params.append('action', action);
    if (admin !== 'all') params.append('admin', admin);
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);
    
    window.location.href = 'logs.php?' + params.toString();
}

function resetFilters() {
    window.location.href = 'logs.php';
}

function viewLogDetails(log) {
    const detailsHtml = `
        <div class="log-detail-item">
            <label>Timestamp:</label>
            <span>${log.created_at}</span>
        </div>
        <div class="log-detail-item">
            <label>Admin:</label>
            <span>${log.admin_name || 'Unknown'} (${log.admin_email || ''})</span>
        </div>
        <div class="log-detail-item">
            <label>Action:</label>
            <span>${log.action}</span>
        </div>
        <div class="log-detail-item">
            <label>Entity Type:</label>
            <span>${log.entity_type || '-'}</span>
        </div>
        <div class="log-detail-item">
            <label>Entity ID:</label>
            <span>${log.entity_id || '-'}</span>
        </div>
        <div class="log-detail-item">
            <label>IP Address:</label>
            <span>${log.ip_address || '-'}</span>
        </div>
        <div class="log-detail-item">
            <label>User Agent:</label>
            <span>${log.user_agent || '-'}</span>
        </div>
        <div class="log-detail-item">
            <label>Old Values:</label>
            <pre>${log.old_values ? JSON.stringify(JSON.parse(log.old_values), null, 2) : '-'}</pre>
        </div>
        <div class="log-detail-item">
            <label>New Values:</label>
            <pre>${log.new_values ? JSON.stringify(JSON.parse(log.new_values), null, 2) : '-'}</pre>
        </div>
    `;
    
    document.getElementById('logDetails').innerHTML = detailsHtml;
    document.getElementById('logModal').classList.add('show');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('show');
}

function goToPage(page) {
    const search = document.getElementById('searchInput').value;
    const action = document.getElementById('actionFilter').value;
    const admin = document.getElementById('adminFilter').value;
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    
    const params = new URLSearchParams();
    params.append('page', page);
    if (search) params.append('search', search);
    if (action !== 'all') params.append('action', action);
    if (admin !== 'all') params.append('admin', admin);
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);
    
    window.location.href = 'logs.php?' + params.toString();
}

function exportLogs() {
    const search = document.getElementById('searchInput').value;
    const action = document.getElementById('actionFilter').value;
    const admin = document.getElementById('adminFilter').value;
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    
    const params = new URLSearchParams();
    params.append('action', 'export');
    if (search) params.append('search', search);
    if (action !== 'all') params.append('action', action);
    if (admin !== 'all') params.append('admin', admin);
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);
    
    window.location.href = 'api/log_api.php?' + params.toString();
}

function clearOldLogs() {
    if (confirm('Are you sure you want to delete logs older than 90 days? This action cannot be undone.')) {
        fetch('api/log_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'clear_old',
                csrf_token: '<?= getCsrfToken() ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Old logs cleared successfully');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to clear old logs');
        });
    }
}

function getCsrfToken() {
    return '<?= $_SESSION['csrf_token'] ?? '' ?>';
}
</script>

<style>
.timestamp-cell {
    display: flex;
    flex-direction: column;
}

.timestamp-cell .date {
    font-weight: 500;
    color: #1a1a2e;
}

.timestamp-cell .time {
    font-size: 12px;
    color: #6b7280;
}

.admin-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}

.admin-avatar {
    width: 32px;
    height: 32px;
    background: #4F46E5;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 12px;
}

.admin-info {
    display: flex;
    flex-direction: column;
}

.admin-name {
    font-weight: 500;
    color: #1a1a2e;
}

.admin-email {
    font-size: 12px;
    color: #6b7280;
}

.action-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
}

.action-create {
    background: #D1FAE5;
    color: #059669;
}

.action-update {
    background: #DBEAFE;
    color: #1D4ED8;
}

.action-delete {
    background: #FEE2E2;
    color: #DC2626;
}

.action-view {
    background: #F3F4F6;
    color: #374151;
}

.action-login {
    background: #FEF3C7;
    color: #D97706;
}

.action-logout {
    background: #E5E7EB;
    color: #4B5563;
}

.log-detail-item {
    display: flex;
    margin-bottom: 15px;
}

.log-detail-item label {
    width: 120px;
    font-weight: 500;
    color: #374151;
    flex-shrink: 0;
}

.log-detail-item span {
    color: #6b7280;
    flex: 1;
}

.log-detail-item pre {
    background: #f8fafc;
    padding: 10px;
    border-radius: 6px;
    font-size: 12px;
    overflow-x: auto;
    margin: 0;
}
</style>

<?php require_once 'includes/admin_footer.php'; ?>
