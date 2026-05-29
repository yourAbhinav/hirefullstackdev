<?php
$page_title = 'Dashboard Overview';
require_once 'includes/admin_header.php';

$stats = getDashboardStats($conn);

// Get recent activity
$recentApplications = $conn->query("SELECT id, full_name, job_position, status, resume_path, created_at FROM applications ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC) ?: [];
$recentUsers = $conn->query("SELECT id, fullName, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC) ?: [];
$recentJobs = $conn->query("SELECT id, title, company_id, status, created_at FROM jobs ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC) ?: [];

// Get monthly stats for charts
$monthlyUsers = $conn->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY month")->fetch_all(MYSQLI_ASSOC) ?: [];
$monthlyApplications = $conn->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count FROM applications WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY month")->fetch_all(MYSQLI_ASSOC) ?: [];

// Get application status distribution
$appStatusBreakdown = $conn->query("SELECT status, COUNT(*) as count FROM applications GROUP BY status")->fetch_all(MYSQLI_ASSOC) ?: [];
?>

<!-- Quick Actions -->
<section class="quick-actions" aria-label="Quick actions">
    <h2 class="quick-actions-title">Quick Actions</h2>
    <div class="quick-actions-grid">
        <a href="<?= appUrl('admin/users.php') ?>" class="quick-action-card">
            <i class="fas fa-user-plus"></i>
            <span>Manage Users</span>
        </a>
        <a href="<?= appUrl('admin/applications.php') ?>" class="quick-action-card">
            <i class="fas fa-inbox"></i>
            <span>Applications</span>
        </a>
        <a href="<?= appUrl('admin/jobs.php') ?>" class="quick-action-card">
            <i class="fas fa-briefcase"></i>
            <span>Post / Edit Jobs</span>
        </a>
        <a href="<?= appUrl('admin/resumes.php') ?>" class="quick-action-card">
            <i class="fas fa-file-pdf"></i>
            <span>Resumes</span>
        </a>
        <a href="<?= appUrl('admin/analytics.php') ?>" class="quick-action-card">
            <i class="fas fa-chart-pie"></i>
            <span>Analytics</span>
        </a>
        <a href="<?= appUrl('admin/settings.php') ?>" class="quick-action-card">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
        </a>
    </div>
</section>

<!-- Stats Cards -->
<div class="stats-grid">
    <a href="<?= appUrl('admin/users.php') ?>" class="stat-card primary">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($stats['total_users']) ?></div>
            <div class="stat-label">Total Users</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i> <?= $stats['active_users'] ?> active
            </div>
        </div>
    </a>
    
    <a href="<?= appUrl('admin/applications.php') ?>" class="stat-card success">
        <div class="stat-icon">
            <i class="fas fa-file-alt"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($stats['total_applications']) ?></div>
            <div class="stat-label">Total Applications</div>
            <div class="stat-change">
                <?= $stats['applications_pending'] ?? 0 ?> pending review
            </div>
        </div>
    </a>
    
    <a href="<?= appUrl('admin/jobs.php') ?>" class="stat-card info">
        <div class="stat-icon">
            <i class="fas fa-briefcase"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($stats['total_jobs']) ?></div>
            <div class="stat-label">Total Jobs</div>
            <div class="stat-change positive">
                <i class="fas fa-check-circle"></i> <?= $stats['active_jobs'] ?> active
            </div>
        </div>
    </a>
    
    <a href="<?= appUrl('admin/resumes.php') ?>" class="stat-card warning">
        <div class="stat-icon">
            <i class="fas fa-file-pdf"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($stats['total_resumes']) ?></div>
            <div class="stat-label">Resumes Uploaded</div>
            <div class="stat-change">
                This month
            </div>
        </div>
    </a>
</div>

<!-- Charts Section -->
<div class="charts-section">
    <div class="chart-card">
        <div class="card-header">
            <h3>User Growth</h3>
            <div class="card-actions">
                <select class="time-filter">
                    <option>Last 6 months</option>
                    <option>Last year</option>
                    <option>All time</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <canvas id="userGrowthChart" height="300"></canvas>
        </div>
    </div>
    
    <div class="chart-card">
        <div class="card-header">
            <h3>Applications Overview</h3>
            <div class="card-actions">
                <select class="time-filter">
                    <option>Last 6 months</option>
                    <option>Last year</option>
                    <option>All time</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <canvas id="applicationsChart" height="300"></canvas>
        </div>
    </div>
</div>

<!-- Application Status Distribution -->
<div class="status-distribution">
    <div class="chart-card">
        <div class="card-header">
            <h3>Application Status Distribution</h3>
        </div>
        <div class="card-body">
            <canvas id="statusChart" height="250"></canvas>
        </div>
    </div>
    
    <div class="chart-card">
        <div class="card-header">
            <h3>Hiring Funnel</h3>
        </div>
        <div class="card-body">
            <div class="funnel-container">
                <div class="funnel-step">
                    <div class="funnel-label">Applications Received</div>
                    <div class="funnel-bar" style="width: 100%">
                        <span><?= $stats['total_applications'] ?></span>
                    </div>
                </div>
                <div class="funnel-step">
                    <div class="funnel-label">Under Review</div>
                    <div class="funnel-bar" style="width: 75%">
                        <span><?= (int)($stats['total_applications'] * 0.75) ?></span>
                    </div>
                </div>
                <div class="funnel-step">
                    <div class="funnel-label">Interviews</div>
                    <div class="funnel-bar" style="width: 40%">
                        <span><?= (int)($stats['total_applications'] * 0.40) ?></span>
                    </div>
                </div>
                <div class="funnel-step">
                    <div class="funnel-label">Approved</div>
                    <div class="funnel-bar" style="width: 15%">
                        <span><?= (int)($stats['total_applications'] * 0.15) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="recent-activity-section">
    <div class="activity-card">
        <div class="card-header">
            <h3>Recent Applications</h3>
            <a href="<?= appUrl('admin/applications.php') ?>" class="view-all">View All</a>
        </div>
        <div class="card-body">
            <?php if (empty($recentApplications)): ?>
                <div class="empty-state">
                    <i class="fas fa-file-alt"></i>
                    <p>No applications yet</p>
                </div>
            <?php else: ?>
                <div class="activity-list activity-list-v2">
                    <?php foreach ($recentApplications as $app): ?>
                        <a href="<?= appUrl('admin/applications.php') ?>" class="activity-row activity-row-link">
                            <div class="activity-row-main">
                                <div class="activity-row-name"><?= htmlspecialchars($app['full_name']) ?></div>
                                <div class="activity-row-sub"><?= htmlspecialchars($app['job_position'] ?: 'General Application') ?></div>
                            </div>
                            <div class="activity-row-side">
                                <span class="status-pill status-<?= htmlspecialchars($app['status']) ?>">
                                    <?= applicationStatusLabel($app['status']) ?>
                                </span>
                                <span class="activity-row-time"><?= time_elapsed_string($app['created_at']) ?></span>
                                <?= renderResumeStatusBadge($app['resume_path'] ?? null, true) ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="activity-card">
        <div class="card-header">
            <h3>Recent Users</h3>
            <a href="<?= appUrl('admin/users.php') ?>" class="view-all">View All</a>
        </div>
        <div class="card-body">
            <?php if (empty($recentUsers)): ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <p>No users yet</p>
                </div>
            <?php else: ?>
                <div class="activity-list activity-list-v2">
                    <?php foreach ($recentUsers as $user): ?>
                        <a href="<?= appUrl('admin/users.php') ?>" class="activity-row activity-row-link activity-row-user">
                            <div class="activity-row-avatar user"><?= strtoupper(substr($user['fullName'], 0, 1)) ?></div>
                            <div class="activity-row-main">
                                <div class="activity-row-name"><?= htmlspecialchars($user['fullName']) ?></div>
                                <div class="activity-row-sub">
                                    <span class="role-pill"><?= ucfirst($user['role']) ?></span>
                                    <span class="activity-row-email"><?= htmlspecialchars($user['email']) ?></span>
                                </div>
                            </div>
                            <div class="activity-row-side">
                                <span class="activity-row-time">Joined <?= time_elapsed_string($user['created_at']) ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="activity-card">
        <div class="card-header">
            <h3>Recent Jobs</h3>
            <a href="<?= appUrl('admin/jobs.php') ?>" class="view-all">View All</a>
        </div>
        <div class="card-body">
            <?php if (empty($recentJobs)): ?>
                <div class="empty-state">
                    <i class="fas fa-briefcase"></i>
                    <p>No jobs posted yet</p>
                </div>
            <?php else: ?>
                <div class="activity-list">
                    <?php foreach ($recentJobs as $job): ?>
                        <div class="activity-item">
                            <div class="activity-icon job">
                                <i class="fas fa-briefcase"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">
                                    <?= htmlspecialchars($job['title']) ?>
                                </div>
                                <div class="activity-meta">
                                    <span class="activity-status <?= $job['status'] ?>">
                                        <?= ucfirst($job['status']) ?>
                                    </span>
                                    <span class="activity-time">
                                        <?= time_elapsed_string($job['created_at']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>


<script>
// User Growth Chart
const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
const userGrowthData = <?= json_encode($monthlyUsers) ?>;
const userGrowthLabels = userGrowthData.map(d => d.month);
const userGrowthValues = userGrowthData.map(d => d.count);

new Chart(userGrowthCtx, {
    type: 'line',
    data: {
        labels: userGrowthLabels.length ? userGrowthLabels : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
            label: 'New Users',
            data: userGrowthValues.length ? userGrowthValues : [0, 0, 0, 0, 0, 0],
            borderColor: '#4F46E5',
            backgroundColor: 'rgba(79, 70, 229, 0.1)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Applications Chart
const applicationsCtx = document.getElementById('applicationsChart').getContext('2d');
const applicationsData = <?= json_encode($monthlyApplications) ?>;
const applicationsLabels = applicationsData.map(d => d.month);
const applicationsValues = applicationsData.map(d => d.count);

new Chart(applicationsCtx, {
    type: 'bar',
    data: {
        labels: applicationsLabels.length ? applicationsLabels : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
            label: 'Applications',
            data: applicationsValues.length ? applicationsValues : [0, 0, 0, 0, 0, 0],
            backgroundColor: 'rgba(16, 185, 129, 0.8)',
            borderColor: '#10B981',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Status Distribution Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusData = <?= json_encode($appStatusBreakdown) ?>;
const statusLabels = statusData.map(d => d.status);
const statusValues = statusData.map(d => d.count);

const statusColors = {
    'pending': '#F59E0B',
    'approved': '#10B981',
    'rejected': '#DC2626',
    'interview': '#3B82F6',
    'reviewed': '#8B5CF6'
};

new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: statusLabels.length ? statusLabels : ['Pending', 'Approved', 'Rejected'],
        datasets: [{
            data: statusValues.length ? statusValues : [0, 0, 0],
            backgroundColor: statusLabels.length ? statusLabels.map(s => statusColors[s] || '#4F46E5') : ['#F59E0B', '#10B981', '#DC2626']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right'
            }
        }
    }
});
</script>

<?php require_once 'includes/admin_footer.php'; ?>