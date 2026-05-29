<?php
$page_title = 'Admin Accounts';
require_once 'includes/admin_header.php';

requireAdminPermission($conn, 'manage_admins');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    requireApiCsrf();
    
    if ($_POST['action'] === 'create') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'admin';
        $status = $_POST['status'] ?? 'active';
        
        if (empty($name) || empty($email) || empty($password)) {
            $error = 'All fields are required';
        } else {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM admin_accounts WHERE email = ?");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $error = 'Email already exists';
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
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
        $role = $_POST['role'] ?? 'admin';
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
                $stmt = $conn->prepare("UPDATE admin_accounts SET name = ?, email = ?, role = ?, status = ? WHERE id = ?");
                $stmt->bind_param('ssssi', $name, $email, $role, $status, $adminId);
                if ($stmt->execute()) {
                    logAdminAction($conn, $admin['id'], 'update_admin', 'admin_account', $adminId, $oldAdmin, ['name' => $name, 'email' => $email, 'role' => $role, 'status' => $status]);
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

// Get available roles
$roles = ['super_admin', 'admin', 'manager', 'recruiter', 'viewer'];
?>

<div class="content-area">
    <div class="page-header">
        <div class="page-header-left">
            <h1>Admin Accounts</h1>
            <p>Manage admin users and their permissions</p>
        </div>
        <div class="page-header-right">
            <button class="btn btn-primary" onclick="openCreateModal()">
                <i class="fas fa-plus"></i> Add Admin
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

    <!-- Stats Cards -->
    <div class="stats-cards">
        <div class="stat-card primary">
            <div class="stat-icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format(count($admins)) ?></div>
                <div class="stat-label">Total Admins</div>
            </div>
        </div>
        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format(count(array_filter($admins, fn($a) => $a['status'] === 'active'))) ?></div>
                <div class="stat-label">Active Admins</div>
            </div>
        </div>
        <div class="stat-card warning">
            <div class="stat-icon">
                <i class="fas fa-crown"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format(count(array_filter($admins, fn($a) => $a['role'] === 'super_admin'))) ?></div>
                <div class="stat-label">Super Admins</div>
            </div>
        </div>
    </div>

    <!-- Admins Table -->
    <div class="table-container">
        <div class="table-header">
            <h2>Admin Accounts (<?= number_format(count($admins)) ?>)</h2>
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
                                    <span class="badge badge-<?= $adm['role'] ?>">
                                        <?= getAdminRoleLabel($adm['role']) ?>
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
                                        <button class="btn-icon" onclick="openEditModal(<?= htmlspecialchars(json_encode($adm)) ?>)" title="Edit">
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
                <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
                
                <div class="form-group">
                    <label for="adminName">Full Name</label>
                    <input type="text" id="adminName" name="name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="adminEmail">Email</label>
                    <input type="email" id="adminEmail" name="email" class="form-control" required>
                </div>
                
                <div class="form-group" id="passwordGroup">
                    <label for="adminPassword">Password</label>
                    <input type="password" id="adminPassword" name="password" class="form-control">
                    <small>Minimum 8 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="adminRole">Role</label>
                    <select id="adminRole" name="role" class="form-control" required>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= $role ?>"><?= getAdminRoleLabel($role) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="adminStatus">Status</label>
                    <select id="adminStatus" name="status" class="form-control" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
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
                <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
                
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
    document.getElementById('adminPassword').value = '';
    document.getElementById('adminPassword').required = true;
    document.getElementById('passwordGroup').style.display = 'block';
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
    document.getElementById('adminPassword').value = '';
    document.getElementById('adminPassword').required = false;
    document.getElementById('passwordGroup').style.display = 'none';
    document.getElementById('adminRole').value = adminData.role;
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
    
    fetch('admins.php', {
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
    
    fetch('admins.php', {
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
        formData.append('csrf_token', '<?= getCsrfToken() ?>');
        
        fetch('admins.php', {
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

<?php
function getAdminRoleLabel($role) {
    $labels = [
        'super_admin' => 'Super Admin',
        'admin' => 'Admin',
        'manager' => 'Manager',
        'recruiter' => 'Recruiter',
        'viewer' => 'Viewer'
    ];
    return $labels[$role] ?? ucfirst($role);
}
?>
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
