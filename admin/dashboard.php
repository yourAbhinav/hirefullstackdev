<?php
$page_title = 'Dashboard Overview';
require_once 'includes/admin_header.php';

$stats = getDashboardStats($conn);

// Get recent activity
$recentApplications = $conn->query("SELECT id, full_name, job_position, status, created_at FROM applications ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC) ?: [];
$recentUsers = $conn->query("SELECT id, fullName, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC) ?: [];
$recentJobs = $conn->query("SELECT id, title, company_id, status, created_at FROM jobs ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC) ?: [];

// Get monthly stats for charts
$monthlyUsers = $conn->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY month")->fetch_all(MYSQLI_ASSOC) ?: [];
$monthlyApplications = $conn->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count FROM applications WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY month")->fetch_all(MYSQLI_ASSOC) ?: [];

// Get application status distribution
$appStatusBreakdown = $conn->query("SELECT status, COUNT(*) as count FROM applications GROUP BY status")->fetch_all(MYSQLI_ASSOC) ?: [];
?>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card primary">
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
    </div>
    
    <div class="stat-card success">
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
    </div>
    
    <div class="stat-card info">
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
    </div>
    
    <div class="stat-card warning">
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
    </div>
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
                <div class="activity-list">
                    <?php foreach ($recentApplications as $app): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">
                                    <?= htmlspecialchars($app['full_name']) ?> applied for 
                                    <strong><?= htmlspecialchars($app['job_position']) ?></strong>
                                </div>
                                <div class="activity-meta">
                                    <span class="activity-status <?= $app['status'] ?>">
                                        <?= ucfirst($app['status']) ?>
                                    </span>
                                    <span class="activity-time">
                                        <?= time_elapsed_string($app['created_at']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
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
                <div class="activity-list">
                    <?php foreach ($recentUsers as $user): ?>
                        <div class="activity-item">
                            <div class="activity-icon user">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">
                                    <?= htmlspecialchars($user['fullName']) ?>
                                    <span class="activity-role"><?= ucfirst($user['role']) ?></span>
                                </div>
                                <div class="activity-meta">
                                    <span class="activity-email"><?= htmlspecialchars($user['email']) ?></span>
                                    <span class="activity-time">
                                        <?= time_elapsed_string($user['created_at']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
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

<style>
/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 16px;
    padding: 25px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    transition: all 0.3s;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}

.stat-card.primary .stat-icon {
    background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
}

.stat-card.success .stat-icon {
    background: linear-gradient(135deg, #10B981 0%, #059669 100%);
}

.stat-card.info .stat-icon {
    background: linear-gradient(135deg, #3B82F6 0%, #1D4ED8 100%);
}

.stat-card.warning .stat-icon {
    background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 32px;
    font-weight: 700;
    color: #1a1a2e;
    line-height: 1;
    margin-bottom: 8px;
}

.stat-label {
    font-size: 14px;
    color: #666;
    margin-bottom: 8px;
}

.stat-change {
    font-size: 13px;
    font-weight: 500;
    color: #666;
}

.stat-change.positive {
    color: #10B981;
}

.stat-change i {
    margin-right: 5px;
}

/* Charts Section */
.charts-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.chart-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    overflow: hidden;
}

.card-header {
    padding: 20px 25px;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h3 {
    font-size: 18px;
    font-weight: 600;
    color: #1a1a2e;
}

.card-actions .time-filter {
    padding: 8px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 13px;
    color: #666;
    background: white;
    cursor: pointer;
}

.card-body {
    padding: 25px;
}

/* Status Distribution */
.status-distribution {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

/* Funnel Chart */
.funnel-container {
    padding: 10px 0;
}

.funnel-step {
    margin-bottom: 20px;
}

.funnel-label {
    font-size: 14px;
    font-weight: 600;
    color: #1a1a2e;
    margin-bottom: 8px;
}

.funnel-bar {
    height: 40px;
    background: linear-gradient(90deg, #4F46E5 0%, #7C3AED 100%);
    border-radius: 8px;
    display: flex;
    align-items: center;
    padding: 0 15px;
    color: white;
    font-weight: 600;
    font-size: 14px;
}

/* Recent Activity */
.recent-activity-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 25px;
}

.activity-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    overflow: hidden;
}

.view-all {
    color: #4F46E5;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
}

.view-all:hover {
    text-decoration: underline;
}

.activity-list {
    display: flex;
    flex-direction: column;
}

.activity-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #f3f4f6;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    background: #E0E7FF;
    color: #4F46E5;
}

.activity-icon.user {
    background: #D1FAE5;
    color: #10B981;
}

.activity-icon.job {
    background: #DBEAFE;
    color: #3B82F6;
}

.activity-content {
    flex: 1;
}

.activity-title {
    font-size: 14px;
    font-weight: 500;
    color: #1a1a2e;
    margin-bottom: 8px;
}

.activity-role {
    font-size: 12px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 4px;
    background: #F3F4F6;
    color: #666;
    margin-left: 8px;
}

.activity-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 13px;
}

.activity-status {
    padding: 2px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.activity-status.pending {
    background: #FEF3C7;
    color: #D97706;
}

.activity-status.approved {
    background: #D1FAE5;
    color: #059669;
}

.activity-status.rejected {
    background: #FEE2E2;
    color: #DC2626;
}

.activity-status.interview {
    background: #DBEAFE;
    color: #1D4ED8;
}

.activity-status.active {
    background: #D1FAE5;
    color: #059669;
}

.activity-status.closed {
    background: #FEE2E2;
    color: #DC2626;
}

.activity-email {
    color: #666;
}

.activity-time {
    color: #999;
}

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

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .charts-section {
        grid-template-columns: 1fr;
    }
    
    .status-distribution {
        grid-template-columns: 1fr;
    }
    
    .recent-activity-section {
        grid-template-columns: 1fr;
    }
}
</style>

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