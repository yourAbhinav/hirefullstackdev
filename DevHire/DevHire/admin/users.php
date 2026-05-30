<?php
$page_title = 'User Management';
require_once 'includes/admin_header.php';

requireAdminPermission($conn, 'view_users');

// Search and filter parameters
$search = $_GET['search'] ?? '';
$roleFilter = $_GET['role'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$providerFilter = $_GET['provider'] ?? '';
$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query with filters
$where = ['1=1'];
$params = [];
$types = '';

if (!empty($search)) {
    $where[] = '(fullName LIKE ? OR email LIKE ?)';
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'ss';
}

if (!empty($roleFilter)) {
    $where[] = 'role = ?';
    $params[] = $roleFilter;
    $types .= 's';
}

if (!empty($statusFilter)) {
    $where[] = 'verified = ?';
    $params[] = $statusFilter === 'verified' ? 1 : 0;
    $types .= 'i';
}

if (!empty($providerFilter)) {
    $where[] = 'provider = ?';
    $params[] = $providerFilter;
    $types .= 's';
}

$whereClause = implode(' AND ', $where);

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM users WHERE $whereClause";
$stmt = $conn->prepare($countQuery);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$totalUsers = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Get users with pagination
$query = "SELECT id, fullName, email, role, provider, verified, profile_image, phone, experience, techStack, last_login_at, created_at 
          FROM users 
          WHERE $whereClause 
          ORDER BY created_at DESC 
          LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC) ?: [];
$stmt->close();

// Get unique roles and providers for filters
$roles = $conn->query("SELECT DISTINCT role FROM users")->fetch_all(MYSQLI_ASSOC);
$providers = $conn->query("SELECT DISTINCT provider FROM users")->fetch_all(MYSQLI_ASSOC);
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-content">
        <h1>User Management</h1>
        <p>Manage all registered users, their roles, and account status</p>
    </div>
    <div class="page-header-actions">
        <a href="<?= appUrl('admin/users_export.php') ?><?= !empty($_GET) ? '?' . http_build_query(array_intersect_key($_GET, array_flip(['search', 'role', 'status']))) : '' ?>" class="btn btn-outline">
            <i class="fas fa-download"></i> Export Users
        </a>
        <button type="button" class="btn btn-primary" onclick="openAddUserModal()">
            <i class="fas fa-plus"></i> Add User
        </button>
    </div>
</div>

<!-- Filters -->
<div class="filters-section">
    <form method="GET" class="filters-form">
        <div class="filter-group">
            <label>Search</label>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name or email...">
        </div>
        
        <div class="filter-group">
            <label>Role</label>
            <select name="role">
                <option value="">All Roles</option>
                <?php foreach ($roles as $r): ?>
                    <option value="<?= $r['role'] ?>" <?= $roleFilter === $r['role'] ? 'selected' : '' ?>>
                        <?= ucfirst($r['role']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="filter-group">
            <label>Status</label>
            <select name="status">
                <option value="">All Status</option>
                <option value="verified" <?= $statusFilter === 'verified' ? 'selected' : '' ?>>Verified</option>
                <option value="unverified" <?= $statusFilter === 'unverified' ? 'selected' : '' ?>>Unverified</option>
            </select>
        </div>
        
        <div class="filter-group">
            <label>Provider</label>
            <select name="provider">
                <option value="">All Providers</option>
                <?php foreach ($providers as $p): ?>
                    <option value="<?= $p['provider'] ?>" <?= $providerFilter === $p['provider'] ? 'selected' : '' ?>>
                        <?= ucfirst($p['provider']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search"></i> Filter
        </button>
        
        <?php if (!empty($search) || !empty($roleFilter) || !empty($statusFilter) || !empty($providerFilter)): ?>
            <a href="<?= appUrl('admin/users.php') ?>" class="btn btn-outline">
                <i class="fas fa-times"></i> Clear
            </a>
        <?php endif; ?>
    </form>
</div>

<!-- Users Table -->
<div class="table-container">
    <div class="table-header">
        <div class="table-info">
            <span><?= number_format($totalUsers) ?> total users</span>
        </div>
        <div class="table-actions">
            <select class="bulk-action">
                <option value="">Bulk Actions</option>
                <option value="verify">Verify Selected</option>
                <option value="unverify">Unverify Selected</option>
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
                    <th><input type="checkbox" id="selectAll" onchange="toggleSelectAll()"></th>
                    <th>User</th>
                    <th>Role</th>
                    <th>Provider</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="8" class="text-center">
                            <div class="empty-state">
                                <i class="fas fa-users"></i>
                                <p>No users found</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><input type="checkbox" class="user-checkbox" value="<?= $user['id'] ?>"></td>
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar">
                                        <?php if ($user['profile_image']): ?>
                                            <img src="<?= htmlspecialchars($user['profile_image']) ?>" alt="<?= htmlspecialchars($user['fullName']) ?>">
                                        <?php else: ?>
                                            <?= strtoupper(substr($user['fullName'], 0, 1)) ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="user-info">
                                        <div class="user-name"><?= htmlspecialchars($user['fullName']) ?></div>
                                        <div class="user-email"><?= htmlspecialchars($user['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php 
                                $role = preg_replace('/[^a-z0-9_-]/i', '', $user['role']);
                                ?>
                                <span class="badge badge-<?= htmlspecialchars($role) ?>">
                                    <?= htmlspecialchars(ucfirst($user['role'])) ?>
                                </span>
                            </td>
                            <td>
                                <span class="provider-badge">
                                    <?php if ($user['provider'] === 'google'): ?>
                                        <i class="fab fa-google"></i> Google
                                    <?php elseif ($user['provider'] === 'github'): ?>
                                        <i class="fab fa-github"></i> GitHub
                                    <?php else: ?>
                                        <i class="fas fa-envelope"></i> Email
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($user['verified']): ?>
                                    <span class="status-badge status-verified">
                                        <i class="fas fa-check-circle"></i> Verified
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge status-unverified">
                                        <i class="fas fa-clock"></i> Unverified
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['last_login_at']): ?>
                                    <span class="time-ago" data-ts="<?= htmlspecialchars($user['last_login_at']) ?>"><?= time_elapsed_string($user['last_login_at']) ?></span>
                                <?php else: ?>
                                    Never
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="time-ago" data-ts="<?= htmlspecialchars($user['created_at']) ?>"><?= time_elapsed_string($user['created_at']) ?></span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon" onclick="viewUser(<?= $user['id'] ?>)" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-icon" onclick="editUser(<?= $user['id'] ?>)" title="Edit User">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon" onclick="toggleUserStatus(<?= $user['id'] ?>, '<?= $user['verified'] ? 'unverify' : 'verify' ?>')" title="<?= $user['verified'] ? 'Unverify' : 'Verify' ?>">
                                        <i class="fas <?= $user['verified'] ? 'fa-times-circle' : 'fa-check-circle' ?>"></i>
                                    </button>
                                    <button class="btn-icon btn-icon-danger" onclick="deleteUser(<?= $user['id'] ?>)" title="Delete User">
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
    <?php if ($totalUsers > $limit): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="pagination-link">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
            <?php endif; ?>
            
            <span class="pagination-info">
                Page <?= $page ?> of <?= ceil($totalUsers / $limit) ?>
            </span>
            
            <?php if ($page < ceil($totalUsers / $limit)): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="pagination-link">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- User Details Modal -->
<div id="userModal" class="modal">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h3>User Details</h3>
            <button class="modal-close" onclick="closeModal('userModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="userModalBody">
            <!-- Content loaded dynamically -->
        </div>
    </div>
</div>

<!-- Add/Edit User Modal -->
<div id="addUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="userFormModalTitle">Add New User</h3>
            <button type="button" class="modal-close" data-modal-close="addUserModal" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="addUserForm">
                <input type="hidden" name="user_id" id="editUserId" value="">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="fullName" id="userFullName" required>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" id="userEmail" required>
                </div>
                <div class="form-group" id="passwordFieldGroup">
                    <label id="passwordFieldLabel">Password *</label>
                    <input type="password" name="password" id="userPassword">
                    <small id="passwordHint" style="display:none;color:#64748b;font-size:12px;">Leave blank to keep current password when editing.</small>
                </div>
                </div>
                <div class="form-group">
                    <label>Role *</label>
                    <select name="role" required>
                        <option value="developer">Developer</option>
                        <option value="company">Company</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="tel" name="phone">
                </div>
                <div class="form-group">
                    <label>Experience Level</label>
                    <select name="experience">
                        <option value="">Select Experience</option>
                        <option value="entry">Entry Level (0-2 years)</option>
                        <option value="mid">Mid Level (2-5 years)</option>
                        <option value="senior">Senior Level (5-10 years)</option>
                        <option value="lead">Lead/Principal (10+ years)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tech Stack</label>
                    <input type="text" name="techStack" placeholder="e.g., React, Node.js, Python">
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addUserModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="userFormSubmitBtn">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Page Header */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 20px;
}

.page-header-content h1 {
    font-size: 28px;
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 8px;
}

.page-header-content p {
    color: #666;
    font-size: 14px;
}

.page-header-actions {
    display: flex;
    gap: 10px;
}

/* Filters */
.filters-section {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.filters-form {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    align-items: flex-end;
}

.filter-group {
    flex: 1;
    min-width: 150px;
}

.filter-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #333;
    margin-bottom: 6px;
}

.filter-group input,
.filter-group select {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
}

/* Buttons */
.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}

.btn-primary {
    background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(79, 70, 229, 0.3);
}

.btn-secondary {
    background: #f3f4f6;
    color: #333;
}

.btn-secondary:hover {
    background: #e5e7eb;
}

.btn-outline {
    background: transparent;
    border: 1px solid #e5e7eb;
    color: #666;
}

.btn-outline:hover {
    border-color: #4F46E5;
    color: #4F46E5;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 13px;
}

/* Table */
.table-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    overflow: hidden;
}

.table-header {
    padding: 20px 25px;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.table-info {
    font-size: 14px;
    color: #666;
}

.table-actions {
    display: flex;
    gap: 10px;
}

.bulk-action {
    padding: 8px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 13px;
}

.table-responsive {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table thead {
    background: #f8fafc;
}

.data-table th {
    padding: 15px 20px;
    text-align: left;
    font-size: 13px;
    font-weight: 600;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.data-table td {
    padding: 15px 20px;
    border-bottom: 1px solid #f3f4f6;
}

.data-table tbody tr:hover {
    background: #f8fafc;
}

/* User Cell */
.user-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
    overflow: hidden;
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-name {
    font-weight: 600;
    color: #1a1a2e;
    margin-bottom: 4px;
}

.user-email {
    font-size: 13px;
    color: #666;
}

/* Badges */
.badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.badge-developer {
    background: #DBEAFE;
    color: #1D4ED8;
}

.badge-company {
    background: #D1FAE5;
    color: #059669;
}

.provider-badge {
    font-size: 13px;
    color: #666;
    display: flex;
    align-items: center;
    gap: 6px;
}

.status-badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
}

.status-verified {
    background: #D1FAE5;
    color: #059669;
}

.status-unverified {
    background: #FEE2E2;
    color: #DC2626;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 8px;
}

.btn-icon {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 6px;
    background: #f3f4f6;
    color: #666;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.btn-icon:hover {
    background: #4F46E5;
    color: white;
}

.btn-icon-danger:hover {
    background: #DC2626;
}

/* Pagination */
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
    gap: 15px;
}

.pagination-link {
    padding: 8px 16px;
    background: #f3f4f6;
    color: #333;
    text-decoration: none;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.2s;
}

.pagination-link:hover {
    background: #4F46E5;
    color: white;
}

.pagination-info {
    font-size: 14px;
    color: #666;
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 2000;
    align-items: center;
    justify-content: center;
}

.modal.show {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 16px;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-lg {
    max-width: 800px;
}

.modal-header {
    padding: 20px 25px;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    font-size: 18px;
    font-weight: 600;
    color: #1a1a2e;
}

.modal-close {
    background: none;
    border: none;
    font-size: 20px;
    color: #666;
    cursor: pointer;
    padding: 5px;
}

.modal-close:hover {
    color: #1a1a2e;
}

.modal-body {
    padding: 25px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #4F46E5;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 30px;
}

/* Empty State */
.empty-state {
    padding: 40px 20px;
    text-align: center;
    color: #666;
}

.empty-state i {
    font-size: 48px;
    color: #d1d5db;
    margin-bottom: 15px;
}

.empty-state p {
    margin: 0;
}

.text-center {
    text-align: center;
}

/* Responsive */
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
    }
    
    .page-header-actions {
        width: 100%;
    }
    
    .filters-form {
        flex-direction: column;
    }
    
    .filter-group {
        width: 100%;
    }
    
    .table-header {
        flex-direction: column;
        gap: 15px;
    }
    
    .action-buttons {
        flex-wrap: wrap;
    }
}
</style>

<script>
// Modal functions
// Select all checkbox
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
}

// View user details
function viewUser(userId) {
    fetch(`<?= appUrl('admin/api/user_api.php') ?>?action=get_user&id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const user = data.user;
                document.getElementById('userModalBody').innerHTML = `
                    <div class="user-detail-header">
                        <div class="user-detail-avatar">
                            ${user.profile_image ? `<img src="${user.profile_image}" alt="${user.fullName}">` : user.fullName.charAt(0).toUpperCase()}
                        </div>
                        <div class="user-detail-info">
                            <h4>${user.fullName}</h4>
                            <p>${user.email}</p>
                            <span class="badge badge-${user.role}">${user.role}</span>
                        </div>
                    </div>
                    <div class="user-detail-grid">
                        <div class="detail-item">
                            <label>Phone:</label>
                            <span>${user.phone || 'Not provided'}</span>
                        </div>
                        <div class="detail-item">
                            <label>Experience:</label>
                            <span>${user.experience || 'Not specified'}</span>
                        </div>
                        <div class="detail-item">
                            <label>Tech Stack:</label>
                            <span>${user.techStack || 'Not specified'}</span>
                        </div>
                        <div class="detail-item">
                            <label>Provider:</label>
                            <span>${user.provider || 'Email'}</span>
                        </div>
                        <div class="detail-item">
                            <label>Status:</label>
                            <span>${user.verified ? 'Verified' : 'Unverified'}</span>
                        </div>
                        <div class="detail-item">
                            <label>Joined:</label>
                            <span>${new Date(user.created_at).toLocaleDateString()}</span>
                        </div>
                        <div class="detail-item">
                            <label>Last Login:</label>
                            <span>${user.last_login_at ? new Date(user.last_login_at).toLocaleString() : 'Never'}</span>
                        </div>
                        <div class="detail-item">
                            <label>Profile Image:</label>
                            <span>${user.profile_image ? 'Yes' : 'No'}</span>
                        </div>
                    </div>
                    ${user.bio ? `<div class="user-detail-bio"><label>Bio:</label><p>${user.bio}</p></div>` : ''}
                `;
                openModal('userModal');
            }
        });
}

function resetUserForm() {
    document.getElementById('userFormModalTitle').textContent = 'Add New User';
    document.getElementById('userFormSubmitBtn').textContent = 'Add User';
    document.getElementById('editUserId').value = '';
    document.getElementById('addUserForm').reset();
    document.getElementById('userPassword').required = true;
    document.getElementById('passwordHint').style.display = 'none';
    document.getElementById('passwordFieldLabel').textContent = 'Password *';
}

function openAddUserModal() {
    resetUserForm();
    openModal('addUserModal');
}

// Edit user — load into modal
function editUser(userId) {
    const api = (window.AdminPanel && AdminPanel.config.userApi) || '<?= appUrl('admin/api/user_api.php') ?>';
    fetch(`${api}?action=get_user&id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert(data.message || 'Could not load user');
                return;
            }
            const u = data.user;
            document.getElementById('userFormModalTitle').textContent = 'Edit User';
            document.getElementById('userFormSubmitBtn').textContent = 'Save Changes';
            document.getElementById('editUserId').value = u.id;
            document.getElementById('userFullName').value = u.fullName || '';
            document.getElementById('userEmail').value = u.email || '';
            document.querySelector('[name="role"]').value = u.role || 'developer';
            document.querySelector('[name="phone"]').value = u.phone || '';
            document.querySelector('[name="experience"]').value = u.experience || '';
            document.querySelector('[name="techStack"]').value = u.techStack || '';
            document.getElementById('userPassword').value = '';
            document.getElementById('userPassword').required = false;
            document.getElementById('passwordHint').style.display = 'block';
            document.getElementById('passwordFieldLabel').textContent = 'New Password';
            openModal('addUserModal');
        });
}

// Toggle user verification status
function userApiRequest(payload) {
    const api = (window.AdminPanel && AdminPanel.config.userApi) || '<?= appUrl('admin/api/user_api.php') ?>';
    const body = Object.assign({}, payload, { csrf_token: (window.AdminPanel && AdminPanel.config.csrfToken) || '' });
    return fetch(api, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
    }).then(response => response.json());
}

function toggleUserStatus(userId, action) {
    if (!confirm(`Are you sure you want to ${action} this user?`)) return;
    
    userApiRequest({ action, user_id: userId })
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Action failed');
        }
    });
}

// Delete user
function deleteUser(userId) {
    if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) return;
    
    userApiRequest({ action: 'delete', user_id: userId })
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
    const action = document.querySelector('.bulk-action').value;
    const selected = Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.value);
    
    if (!action) {
        alert('Please select an action');
        return;
    }
    
    if (selected.length === 0) {
        alert('Please select at least one user');
        return;
    }
    
    if (action === 'delete' && !confirm(`Are you sure you want to delete ${selected.length} users?`)) return;
    
    userApiRequest({ action: `bulk_${action}`, user_ids: selected })
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Bulk action failed');
        }
    });
}

// Add user form
document.getElementById('addUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    const userId = data.user_id ? parseInt(data.user_id, 10) : 0;
    const action = userId > 0 ? 'update' : 'create';
    if (action === 'create' && !data.password) {
        alert('Password is required for new users');
        return;
    }
    const payload = {
        action,
        fullName: data.fullName,
        email: data.email,
        role: data.role,
        phone: data.phone,
        experience: data.experience,
        techStack: data.techStack
    };
    if (userId > 0) {
        payload.user_id = userId;
        if (data.password) payload.password = data.password;
    } else {
        payload.password = data.password;
    }
    const btn = document.getElementById('userFormSubmitBtn');
    if (window.AdminPanel) AdminPanel.setButtonLoading(btn, true);
    userApiRequest(payload)
    .then(result => {
        if (window.AdminPanel) AdminPanel.setButtonLoading(btn, false);
        if (result.success) {
            closeModal('addUserModal');
            location.reload();
        } else {
            alert(result.message || 'Failed to save user');
        }
    })
    .catch(() => {
        if (window.AdminPanel) AdminPanel.setButtonLoading(btn, false);
        alert('Request failed. Please try again.');
    });
});
</script>

<?php require_once 'includes/admin_footer.php'; ?>