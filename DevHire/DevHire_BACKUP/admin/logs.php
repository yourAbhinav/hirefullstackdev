<?php
$page_title = 'Audit Logs';
require_once '../config/db.php';
require_once '../includes/admin_helpers.php';

requireAdminLogin();
requireAdminPermission($conn, 'view_logs');

$admin = getCurrentAdmin($conn);

// Clear logs older than 90 days (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'clear_old') {
    requireAdminPostCsrf();
    $deleted = $conn->query('DELETE FROM admin_audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)');
    if ($admin !== null) {
        logAdminAction($conn, $admin['id'], 'clear_audit_logs', 'admin_audit_logs', null, null, ['older_than_days' => 90]);
    }
    header('Location: ' . appUrl('admin/logs.php?cleared=1'));
    exit;
}

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

// CSV export (before pagination mutates filter params)
if (isset($_GET['export']) && $_GET['export'] === '1') {
    $exportQuery = "SELECT al.created_at, a.name as admin_name, al.action, al.entity_type, al.entity_id, al.ip_address
                    FROM admin_audit_logs al
                    LEFT JOIN admin_accounts a ON al.admin_id = a.id
                    WHERE $whereClause
                    ORDER BY al.created_at DESC
                    LIMIT 5000";
    $exportStmt = $conn->prepare($exportQuery);
    if (!empty($params)) {
        $exportStmt->bind_param($types, ...$params);
    }
    $exportStmt->execute();
    $exportRows = $exportStmt->get_result()->fetch_all(MYSQLI_ASSOC) ?: [];
    $exportStmt->close();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="audit-logs-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Timestamp', 'Admin', 'Action', 'Target Type', 'Target ID', 'IP Address', 'Status']);
    foreach ($exportRows as $row) {
        fputcsv($out, [
            $row['created_at'],
            $row['admin_name'] ?? 'Unknown',
            $row['action'],
            $row['entity_type'] ?? '',
            $row['entity_id'] ?? '',
            $row['ip_address'] ?? '',
            'Recorded',
        ]);
    }
    fclose($out);
    exit;
}

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
$totalPages = max(1, (int) ceil($totalLogs / $perPage));

require_once 'includes/admin_header.php';
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Audit Logs</h1>
        <p>Track all admin actions and system events</p>
    </div>
    <div class="page-header-actions">
            <button class="btn btn-primary" onclick="exportLogs()">
                <i class="fas fa-download"></i> Export Logs
            </button>
            <button type="button" class="btn btn-outline" onclick="clearOldLogs()">
                <i class="fas fa-trash"></i> Clear Old Logs
            </button>
        </div>
</div>

<?php if (isset($_GET['cleared'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> Logs older than 90 days were removed.
    </div>
<?php endif; ?>

<!-- Filters -->
<div class="filters-section">
    <form method="GET" class="filters-form logs-filters" id="logsFilterForm">
        <div class="filter-group">
            <label for="searchInput">Search</label>
            <input type="text" id="searchInput" name="search" placeholder="Action, target, or admin…" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="filter-group">
            <label for="actionFilter">Action</label>
            <select id="actionFilter" name="action">
                <option value="all" <?= $actionFilter === 'all' ? 'selected' : '' ?>>All Actions</option>
                <?php foreach ($actions as $act): ?>
                    <option value="<?= htmlspecialchars($act['action']) ?>" <?= $actionFilter === $act['action'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($act['action']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label for="adminFilter">Admin</label>
            <select id="adminFilter" name="admin">
                <option value="all" <?= $adminFilter === 'all' ? 'selected' : '' ?>>All Admins</option>
                <?php foreach ($admins as $adm): ?>
                    <option value="<?= $adm['id'] ?>" <?= (string) $adminFilter === (string) $adm['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($adm['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label for="dateFrom">From</label>
            <input type="date" id="dateFrom" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>">
        </div>
        <div class="filter-group">
            <label for="dateTo">To</label>
            <input type="date" id="dateTo" name="date_to" value="<?= htmlspecialchars($dateTo) ?>">
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-filter"></i> Apply
        </button>
        <a href="<?= appUrl('admin/logs.php') ?>" class="btn btn-outline">
            <i class="fas fa-redo"></i> Reset
        </a>
    </form>
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
                            <th>Target</th>
                            <th>IP</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr data-log="<?= htmlspecialchars(json_encode($log, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>">
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
                                    <span class="action-badge action-<?= htmlspecialchars(preg_replace('/[^a-z0-9_-]/', '', strtolower($log['action']))) ?>">
                                        <?= htmlspecialchars($log['action']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="target-cell">
                                        <?= htmlspecialchars($log['entity_type'] ?? '—') ?>
                                        <?php if (!empty($log['entity_id'])): ?>
                                            <span class="target-id">#<?= (int) $log['entity_id'] ?></span>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td><code class="ip-cell"><?= htmlspecialchars($log['ip_address'] ?? '—') ?></code></td>
                                <td><span class="status-pill status-recorded">Recorded</span></td>
                                <td>
                                    <div class="action-dropdown">
                                        <button type="button" class="btn-icon action-dropdown-toggle" aria-label="Log actions" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="action-dropdown-menu" role="menu">
                                            <button type="button" class="action-dropdown-item" data-action="view" onclick="viewLogDetails(this)">
                                                <i class="fas fa-eye"></i> View Details
                                            </button>
                                        </div>
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
const logsBaseUrl = (window.AdminPanel && AdminPanel.config.logsUrl) || '<?= appUrl('admin/logs.php') ?>';

function parseLogRow(btn) {
    const row = btn.closest('tr');
    if (!row || !row.dataset.log) {
        return null;
    }
    try {
        return JSON.parse(row.dataset.log);
    } catch (e) {
        return null;
    }
}

function formatJsonBlock(value) {
    if (!value) {
        return '—';
    }
    try {
        const parsed = typeof value === 'string' ? JSON.parse(value) : value;
        return JSON.stringify(parsed, null, 2);
    } catch (e) {
        return String(value);
    }
}

function viewLogDetails(btn) {
    const log = parseLogRow(btn);
    if (!log) {
        return;
    }

    document.getElementById('logDetails').innerHTML = `
        <div class="log-detail-grid">
            <div class="log-detail-item"><label>Timestamp</label><span>${log.created_at || '—'}</span></div>
            <div class="log-detail-item"><label>Admin</label><span>${log.admin_name || 'Unknown'}${log.admin_email ? ' (' + log.admin_email + ')' : ''}</span></div>
            <div class="log-detail-item"><label>Action</label><span>${log.action || '—'}</span></div>
            <div class="log-detail-item"><label>Target</label><span>${log.entity_type || '—'}${log.entity_id ? ' #' + log.entity_id : ''}</span></div>
            <div class="log-detail-item"><label>IP</label><span>${log.ip_address || '—'}</span></div>
            <div class="log-detail-item"><label>Status</label><span>Recorded</span></div>
            <div class="log-detail-item full"><label>User Agent</label><span>${log.user_agent || '—'}</span></div>
            <div class="log-detail-item full"><label>Previous Values</label><pre>${formatJsonBlock(log.old_values)}</pre></div>
            <div class="log-detail-item full"><label>New Values</label><pre>${formatJsonBlock(log.new_values)}</pre></div>
        </div>
    `;
    openModal('logModal');
}

function buildLogsQuery(extra) {
    const form = document.getElementById('logsFilterForm');
    const params = new URLSearchParams(new FormData(form));
    if (extra) {
        Object.keys(extra).forEach(k => params.set(k, extra[k]));
    }
    return params.toString();
}

function goToPage(page) {
    window.location.href = logsBaseUrl + '?' + buildLogsQuery({ page: String(page) });
}

function exportLogs() {
    window.location.href = logsBaseUrl + '?' + buildLogsQuery({ export: '1' });
}

function clearOldLogs() {
    if (!confirm('Delete audit logs older than 90 days? This cannot be undone.')) {
        return;
    }
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = logsBaseUrl;
    const action = document.createElement('input');
    action.type = 'hidden';
    action.name = 'action';
    action.value = 'clear_old';
    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = 'csrf_token';
    csrf.value = (window.AdminPanel && AdminPanel.config.csrfToken) || '';
    form.appendChild(action);
    form.appendChild(csrf);
    document.body.appendChild(form);
    form.submit();
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
