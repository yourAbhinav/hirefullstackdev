<?php
$page_title = 'Admin Accounts';
require_once 'includes/admin_header.php';

requireAdminPermission($conn, 'manage_admins');

// Enforce two-level model: only Super Admin can manage administrators.
if (!isSuperAdmin($admin ?? null)) {
    $_SESSION['admin_error'] = 'Only Super Admin can manage administrators.';
    header('Location: ' . appUrl('admin/dashboard.php'));
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    requireAdminPostCsrf();
    
    if ($_POST['action'] === 'create') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        // New admins are always role=admin (two-level system).
        $role = 'admin';
        $status = $_POST['status'] ?? 'active';
        
        if (empty($name) || empty($email)) {
            $error = 'Name and email are required';
        } else {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM admin_accounts WHERE email = ?");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $error = 'Email already exists';
            } else {
                // Generate a strong random password to satisfy schema.
                // Admins can still log in using Google (email allowlist) if configured.
                $randomPassword = bin2hex(random_bytes(24));
                $hashedPassword = password_hash($randomPassword, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO admin_accounts (name, email, password, role, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param('sssss', $name, $email, $hashedPassword, $role, $status);
                if ($stmt->execute()) {
                    logAdminAction($conn, $admin['id'], 'create_admin', 'admin_account', $stmt->insert_id, null, ['email' => $email, 'role' => $role]);
                    $success = 'Admin account created successfully';
                } else {
                    $error = 'Failed to create admin account';
                }
                $stmt->close();
            }
            $stmt->close();
        }
    }
    
    if ($_POST['action'] === 'update') {
        $adminId = (int) ($_POST['admin_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $status = $_POST['status'] ?? 'active';
        
        if ($adminId <= 0 || empty($name) || empty($email)) {
            $error = 'Invalid parameters';
        } else {
            // Get old values for audit
            $stmt = $conn->prepare("SELECT name, email, role, status FROM admin_accounts WHERE id = ?");
            $stmt->bind_param('i', $adminId);
            $stmt->execute();
            $oldAdmin = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if (!$oldAdmin) {
                $error = 'Admin account not found';
            } else {
                // Do not allow modifying Super Admin role (two-level system protection).
                if (($oldAdmin['role'] ?? '') === 'super_admin') {
                    // Super Admin profile updates are allowed (name/email/status), but role stays super_admin.
                    $stmt = $conn->prepare("UPDATE admin_accounts SET name = ?, email = ?, status = ? WHERE id = ?");
                    $stmt->bind_param('sssi', $name, $email, $status, $adminId);
                } else {
                    // All non-super admins are fixed role=admin.
                    $fixedRole = 'admin';
                    $stmt = $conn->prepare("UPDATE admin_accounts SET name = ?, email = ?, role = ?, status = ? WHERE id = ?");
                    $stmt->bind_param('ssssi', $name, $email, $fixedRole, $status, $adminId);
                }
                if ($stmt->execute()) {
                    $newValues = ['name' => $name, 'email' => $email, 'status' => $status];
                    if (($oldAdmin['role'] ?? '') !== 'super_admin') {
                        $newValues['role'] = 'admin';
                    }
                    logAdminAction($conn, $admin['id'], 'update_admin', 'admin_account', $adminId, $oldAdmin, $newValues);
                    $success = 'Admin account updated successfully';
                } else {
                    $error = 'Failed to update admin account';
                }
                $stmt->close();
            }
        }
    }
    
    if ($_POST['action'] === 'delete') {
        $adminId = (int) ($_POST['admin_id'] ?? 0);
        
        if ($adminId <= 0) {
            $error = 'Invalid admin ID';
        } elseif ($adminId === $admin['id']) {
            $error = 'You cannot delete your own account';
        } else {
            // Get admin data for audit
            $stmt = $conn->prepare("SELECT name, email FROM admin_accounts WHERE id = ?");
            $stmt->bind_param('i', $adminId);
            $stmt->execute();
            $adminToDelete = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if (!$adminToDelete) {
                $error = 'Admin account not found';
            } else {
                $stmt = $conn->prepare("DELETE FROM admin_accounts WHERE id = ?");
                $stmt->bind_param('i', $adminId);
                if ($stmt->execute()) {
                    logAdminAction($conn, $admin['id'], 'delete_admin', 'admin_account', $adminId, $adminToDelete, null);
                    $success = 'Admin account deleted successfully';
                } else {
                    $error = 'Failed to delete admin account';
                }
                $stmt->close();
            }
        }
    }
    
    if ($_POST['action'] === 'change_password') {
        $adminId = (int) ($_POST['admin_id'] ?? 0);
        $newPassword = $_POST['new_password'] ?? '';
        
        if ($adminId <= 0 || empty($newPassword)) {
            $error = 'Invalid parameters';
        } elseif (strlen($newPassword) < 8) {
            $error = 'Password must be at least 8 characters';
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE admin_accounts SET password = ? WHERE id = ?");
            $stmt->bind_param('si', $hashedPassword, $adminId);
            if ($stmt->execute()) {
                logAdminAction($conn, $admin['id'], 'change_admin_password', 'admin_account', $adminId, null, null);
                $success = 'Password changed successfully';
            } else {
                $error = 'Failed to change password';
            }
            $stmt->close();
        }
    }
}

// Get all admin accounts
$admins = $conn->query("SELECT * FROM admin_accounts ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC) ?: [];

// Admin statistics (two levels only)
$totalAdmins = count($admins);
$activeAdmins = count(array_filter($admins, fn($a) => ($a['status'] ?? '') === 'active'));
$inactiveAdmins = count(array_filter($admins, fn($a) => ($a['status'] ?? '') !== 'active'));
$lastAddedAdmin = $admins[0] ?? null;

// Recent admin activity (latest actions touching admin accounts)
$recentAdminActivity = $conn
    ->query("SELECT al.action, al.entity_id, al.created_at, a.name as actor_name 
             FROM admin_audit_logs al 
             LEFT JOIN admin_accounts a ON al.admin_id = a.id
             WHERE al.entity_type = 'admin_account'
             ORDER BY al.created_at DESC
             LIMIT 6")
    ->fetch_all(MYSQLI_ASSOC) ?: [];

$pendingAdminRequests = $conn
    ->query("SELECT id, full_name, email, request_note, requested_ip, token_expires_at, created_at, approval_token FROM admin_access_requests WHERE status = 'pending' ORDER BY created_at DESC LIMIT 10")
    ->fetch_all(MYSQLI_ASSOC) ?: [];
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Admin Management</h1>
        <p>Super Admin only — create and manage administrator accounts.</p>
    </div>
    <div class="page-header-actions">
        <button type="button" class="btn btn-primary" onclick="openCreateModal()">
            <i class="fas fa-plus"></i> Add New Admin
        </button>
    </div>
</div>

    <?php if (isset($success)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

<!-- Pending Admin Access Requests -->
<div class="chart-card" style="margin-bottom: 22px;">
    <div class="card-header">
        <h3>Pending Admin Access Requests (<?= number_format(count($pendingAdminRequests)) ?>)</h3>
    </div>
    <div class="card-body">
        <?php if (empty($pendingAdminRequests)): ?>
            <div class="empty-state">
                <i class="fas fa-user-shield"></i>
                <p>No pending admin access requests</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Requested</th>
                            <th>Expires</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingAdminRequests as $request): ?>
                            <tr>
                                <td><?= htmlspecialchars($request['full_name']) ?></td>
                                <td><?= htmlspecialchars($request['email']) ?></td>
                                <td><span class="time-ago" data-ts="<?= htmlspecialchars($request['created_at']) ?>"><?= time_elapsed_string($request['created_at']) ?></span></td>
                                <td><?= htmlspecialchars($request['token_expires_at']) ?></td>
                                <td>
                                    <div class="action-buttons" style="flex-wrap: wrap; gap: 8px;">
                                        <form method="POST" action="<?= appUrl('admin/approve_admin_access.php') ?>" style="display:inline; margin:0;">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="token" value="<?= htmlspecialchars($request['approval_token']) ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Approve this admin access request?')">
                                                Approve
                                            </button>
                                        </form>
                                        <form method="POST" action="<?= appUrl('admin/approve_admin_access.php') ?>" style="display:inline; margin:0;">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="token" value="<?= htmlspecialchars($request['approval_token']) ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="btn btn-sm btn-outline" onclick="return confirm('Reject this admin access request?')">
                                                Reject
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Admin Statistics -->
<div class="stats-grid">
    <div class="stat-card primary">
        <div class="stat-icon"><i class="fas fa-user-shield"></i></div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($totalAdmins) ?></div>
            <div class="stat-label">Total Admins</div>
        </div>
    </div>
    <div class="stat-card success">
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($activeAdmins) ?></div>
            <div class="stat-label">Active Admins</div>
        </div>
    </div>
    <div class="stat-card warning">
        <div class="stat-icon"><i class="fas fa-user-clock"></i></div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($inactiveAdmins) ?></div>
            <div class="stat-label">Inactive Admins</div>
        </div>
    </div>
    <div class="stat-card info">
        <div class="stat-icon"><i class="fas fa-user-plus"></i></div>
        <div class="stat-content">
            <div class="stat-value"><?= htmlspecialchars($lastAddedAdmin['name'] ?? '—') ?></div>
            <div class="stat-label">Last Added Admin</div>
        </div>
    </div>
</div>

<!-- Recent Admin Activity -->
<div class="chart-card" style="margin-bottom: 22px;">
    <div class="card-header">
        <h3>Recent Admin Activity</h3>
    </div>
    <div class="card-body">
        <?php if (empty($recentAdminActivity)): ?>
            <div class="empty-state">
                <i class="fas fa-history"></i>
                <p>No recent admin activity</p>
            </div>
        <?php else: ?>
            <div class="activity-list activity-list-v2">
                <?php foreach ($recentAdminActivity as $evt): ?>
                    <div class="activity-row" style="cursor: default;">
                        <div class="activity-row-main">
                            <div class="activity-row-name"><?= htmlspecialchars($evt['action']) ?></div>
                            <div class="activity-row-sub">
                                By <?= htmlspecialchars($evt['actor_name'] ?? 'Unknown') ?>
                                <?php if (!empty($evt['entity_id'])): ?>
                                    <span class="role-pill">Admin #<?= (int) $evt['entity_id'] ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="activity-row-side">
                            <span class="activity-row-time time-ago" data-ts="<?= htmlspecialchars($evt['created_at']) ?>"><?= time_elapsed_string($evt['created_at']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Admins Table -->
<div class="table-container">
    <div class="table-header">
        <h2>Admins (<?= number_format($totalAdmins) ?>)</h2>
    </div>
        
        <?php if (empty($admins)): ?>
            <div class="empty-state">
                <i class="fas fa-user-shield"></i>
                <h3>No admin accounts found</h3>
                <p>Create your first admin account to get started.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admins as $adm): ?>
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar">
                                            <?= strtoupper(substr($adm['name'], 0, 1)) ?>
                                        </div>
                                        <div class="user-info">
                                            <div class="user-name"><?= htmlspecialchars($adm['name']) ?></div>
                                            <?php if ($adm['id'] === $admin['id']): ?>
                                                <div class="user-email small">(You)</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($adm['email']) ?></td>
                                <td>
                                    <span class="status-pill status-<?= htmlspecialchars(($adm['role'] ?? '') === 'super_admin' ? 'approved' : 'recorded') ?>">
                                        <?= ($adm['role'] ?? '') === 'super_admin' ? 'Super Admin' : 'Admin' ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $adm['status'] ?>">
                                        <?= ucfirst($adm['status']) ?>
                                    </span>
                                </td>
                                <td><?= $adm['last_login_at'] ? date('M d, Y H:i', strtotime($adm['last_login_at'])) : 'Never' ?></td>
                                <td><?= date('M d, Y', strtotime($adm['created_at'])) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-icon" onclick="openEditModal(<?= htmlspecialchars(json_encode($adm, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)) ?>)" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($adm['id'] !== $admin['id']): ?>
                                            <button class="btn-icon" onclick="openPasswordModal(<?= $adm['id'] ?>, '<?= htmlspecialchars($adm['name']) ?>')" title="Change Password">
                                                <i class="fas fa-key"></i>
                                            </button>
                                            <button class="btn-icon danger" onclick="deleteAdmin(<?= $adm['id'] ?>, '<?= htmlspecialchars($adm['name']) ?>')" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Create/Edit Modal -->
<div class="modal" id="adminModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Add Admin Account</h2>
            <button class="modal-close" onclick="closeModal('adminModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="adminForm">
                <input type="hidden" id="adminId" name="admin_id">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                
                <div class="form-group">
                    <label for="adminName">Full Name</label>
                    <input type="text" id="adminName" name="name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="adminEmail">Email</label>
                    <input type="email" id="adminEmail" name="email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="adminRole">Role</label>
                    <select id="adminRole" name="role" class="form-control" required disabled>
                        <option value="admin" selected>Admin</option>
                    </select>
                    <small>Only Super Admin can manage administrators. New accounts are created as Admin.</small>
                </div>
                
                <div class="form-group">
                    <label for="adminStatus">Status</label>
                    <select id="adminStatus" name="status" class="form-control" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('adminModal')">Cancel</button>
            <button class="btn btn-primary" onclick="submitAdminForm()">Save</button>
        </div>
    </div>
</div>

<!-- Password Change Modal -->
<div class="modal" id="passwordModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Change Password</h2>
            <button class="modal-close" onclick="closeModal('passwordModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="passwordForm">
                <input type="hidden" id="passwordAdminId" name="admin_id">
                <input type="hidden" name="action" value="change_password">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                
                <p id="passwordAdminName"></p>
                
                <div class="form-group">
                    <label for="newPassword">New Password</label>
                    <input type="password" id="newPassword" name="new_password" class="form-control" required minlength="8">
                    <small>Minimum 8 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <input type="password" id="confirmPassword" class="form-control" required minlength="8">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('passwordModal')">Cancel</button>
            <button class="btn btn-primary" onclick="submitPasswordForm()">Change Password</button>
        </div>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Add Admin Account';
    document.getElementById('formAction').value = 'create';
    document.getElementById('adminId').value = '';
    document.getElementById('adminName').value = '';
    document.getElementById('adminEmail').value = '';
    document.getElementById('adminRole').value = 'admin';
    document.getElementById('adminStatus').value = 'active';
    document.getElementById('adminModal').classList.add('show');
}

function openEditModal(adminData) {
    document.getElementById('modalTitle').textContent = 'Edit Admin Account';
    document.getElementById('formAction').value = 'update';
    document.getElementById('adminId').value = adminData.id;
    document.getElementById('adminName').value = adminData.name;
    document.getElementById('adminEmail').value = adminData.email;
    document.getElementById('adminRole').value = (adminData.role === 'super_admin') ? 'admin' : 'admin';
    document.getElementById('adminStatus').value = adminData.status;
    document.getElementById('adminModal').classList.add('show');
}

function openPasswordModal(adminId, adminName) {
    document.getElementById('passwordAdminId').value = adminId;
    document.getElementById('passwordAdminName').textContent = 'Change password for: ' + adminName;
    document.getElementById('newPassword').value = '';
    document.getElementById('confirmPassword').value = '';
    document.getElementById('passwordModal').classList.add('show');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('show');
}

function submitAdminForm() {
    const form = document.getElementById('adminForm');
    const formData = new FormData(form);
    
    fetch('<?= appUrl('admin/admins.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to save admin account');
    });
}

function submitPasswordForm() {
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    if (newPassword !== confirmPassword) {
        alert('Passwords do not match');
        return;
    }
    
    const form = document.getElementById('passwordForm');
    const formData = new FormData(form);
    
    fetch('<?= appUrl('admin/admins.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to change password');
    });
}

function deleteAdmin(adminId, adminName) {
    if (confirm('Are you sure you want to delete admin account: ' + adminName + '? This action cannot be undone.')) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('admin_id', adminId);
        formData.append('csrf_token', '<?= csrfToken() ?>');
        
        fetch('<?= appUrl('admin/admins.php') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete admin account');
        });
    }
}

function getCsrfToken() {
    return '<?= $_SESSION['csrf_token'] ?? '' ?>';
}


</script>

<style>
.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-success {
    background: #D1FAE5;
    color: #059669;
    border: 1px solid #10B981;
}

.alert-error {
    background: #FEE2E2;
    color: #DC2626;
    border: 1px solid #EF4444;
}
</style>

<?php require_once 'includes/admin_footer.php'; ?>
