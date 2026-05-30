<?php
$page_title = 'Analytics & Reports';
require_once 'includes/admin_header.php';

requireAdminPermission($conn, 'view_analytics');

// Get date range
$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
    $startDate = date('Y-m-d', strtotime('-30 days'));
    $endDate = date('Y-m-d');
}

// Get overview stats
$stats = [
    'total_users' => $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'] ?? 0,
    'active_users' => $conn->query("SELECT COUNT(*) as count FROM users WHERE verified = 1")->fetch_assoc()['count'] ?? 0,
    'total_applications' => $conn->query("SELECT COUNT(*) as count FROM applications")->fetch_assoc()['count'] ?? 0,
    'pending_applications' => $conn->query("SELECT COUNT(*) as count FROM applications WHERE status = 'pending'")->fetch_assoc()['count'] ?? 0,
    'approved_applications' => $conn->query("SELECT COUNT(*) as count FROM applications WHERE status = 'approved'")->fetch_assoc()['count'] ?? 0,
    'rejected_applications' => $conn->query("SELECT COUNT(*) as count FROM applications WHERE status = 'rejected'")->fetch_assoc()['count'] ?? 0,
    'total_jobs' => $conn->query("SELECT COUNT(*) as count FROM jobs")->fetch_assoc()['count'] ?? 0,
    'active_jobs' => $conn->query("SELECT COUNT(*) as count FROM jobs WHERE status = 'active'")->fetch_assoc()['count'] ?? 0,
    'total_resumes' => $conn->query("SELECT COUNT(*) as count FROM applications WHERE resume_path IS NOT NULL AND resume_path != ''")->fetch_assoc()['count'] ?? 0
];

// Get user growth data
$stmt = $conn->prepare("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count FROM users WHERE created_at BETWEEN ? AND ? GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY month");
$stmt->bind_param('ss', $startDate, $endDate);
$stmt->execute();
$userGrowth = $stmt->get_result()->fetch_all(MYSQLI_ASSOC) ?: [];
$stmt->close();

// Get application trends
$stmt = $conn->prepare("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, status, COUNT(*) as count FROM applications WHERE created_at BETWEEN ? AND ? GROUP BY DATE_FORMAT(created_at, '%Y-%m'), status ORDER BY month");
$stmt->bind_param('ss', $startDate, $endDate);
$stmt->execute();
$appTrends = $stmt->get_result()->fetch_all(MYSQLI_ASSOC) ?: [];
$stmt->close();

// Get job performance
$jobPerformance = $conn->query("SELECT j.id, j.title, COUNT(a.id) as application_count, j.status FROM jobs j LEFT JOIN applications a ON j.id = a.job_id GROUP BY j.id ORDER BY application_count DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC) ?: [];

// Get top tech stacks
$topTechStacks = $conn->query("SELECT tech_stack, COUNT(*) as count FROM users WHERE tech_stack IS NOT NULL AND tech_stack != '' GROUP BY tech_stack ORDER BY count DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC) ?: [];

// Get conversion funnel
$conversionFunnel = [
    'visitors' => $stats['total_users'],
    'applications' => $stats['total_applications'],
    'approved' => $stats['approved_applications'],
    'interview' => $conn->query("SELECT COUNT(*) as count FROM applications WHERE status = 'interview'")->fetch_assoc()['count'] ?? 0
];

// Get approval rate
$approvalRate = $stats['total_applications'] > 0 ? round(($stats['approved_applications'] / $stats['total_applications']) * 100, 1) : 0;
$rejectionRate = $stats['total_applications'] > 0 ? round(($stats['rejected_applications'] / $stats['total_applications']) * 100, 1) : 0;
?>

<div class="content-area">
    <div class="page-header">
        <div class="page-header-left">
            <h1>Analytics & Reports</h1>
            <p>Track platform performance and user engagement</p>
        </div>
        <div class="page-header-right">
            <button class="btn btn-primary" onclick="exportReport()">
                <i class="fas fa-download"></i> Export Report
            </button>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="filters-section">
        <div class="filters-row">
            <div class="filter-group">
                <label>Start Date</label>
                <input type="date" id="startDate" class="form-control" value="<?= $startDate ?>">
            </div>
            <div class="filter-group">
                <label>End Date</label>
                <input type="date" id="endDate" class="form-control" value="<?= $endDate ?>">
            </div>
            <button class="btn btn-secondary" onclick="applyDateRange()">
                <i class="fas fa-filter"></i> Apply
            </button>
            <button class="btn btn-outline" onclick="resetDateRange()">
                <i class="fas fa-redo"></i> Reset
            </button>
        </div>
    </div>

    <!-- Overview Stats -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($stats['total_users']) ?></div>
                <div class="stat-label">Total Users</div>
                <div class="stat-change positive">
                    <i class="fas fa-check"></i> <?= number_format($stats['active_users']) ?> active
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
                    <?= number_format($stats['pending_applications']) ?> pending
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
                <div class="stat-change">
                    <?= number_format($stats['active_jobs']) ?> active
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
                    <?= round(($stats['total_resumes'] / max($stats['total_users'], 1)) * 100, 1) ?>% attachment rate
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-section">
        <div class="chart-row">
            <div class="chart-card">
                <div class="chart-header">
                    <h3>User Growth</h3>
                    <p>New user registrations over time</p>
                </div>
                <div class="chart-container">
                    <canvas id="userGrowthChart"></canvas>
                </div>
            </div>
            
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Application Trends</h3>
                    <p>Applications by status over time</p>
                </div>
                <div class="chart-container">
                    <canvas id="applicationTrendsChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="chart-row">
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Conversion Funnel</h3>
                    <p>User journey through hiring process</p>
                </div>
                <div class="chart-container">
                    <canvas id="conversionFunnelChart"></canvas>
                </div>
            </div>
            
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Approval vs Rejection Rate</h3>
                    <p>Application outcome distribution</p>
                </div>
                <div class="chart-container">
                    <canvas id="approvalRateChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Reports -->
    <div class="reports-section">
        <div class="report-row">
            <div class="report-card">
                <div class="report-header">
                    <h3>Top Performing Jobs</h3>
                    <p>Jobs with most applications</p>
                </div>
                <div class="report-content">
                    <?php if (empty($jobPerformance)): ?>
                        <div class="empty-state">
                            <p>No job data available</p>
                        </div>
                    <?php else: ?>
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Job Title</th>
                                    <th>Applications</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($jobPerformance as $job): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($job['title']) ?></td>
                                        <td><?= number_format($job['application_count']) ?></td>
                                        <td>
                                            <span class="badge badge-<?= $job['status'] ?>">
                                                <?= ucfirst($job['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="report-card">
                <div class="report-header">
                    <h3>Top Tech Stacks</h3>
                    <p>Most popular technologies</p>
                </div>
                <div class="report-content">
                    <?php if (empty($topTechStacks)): ?>
                        <div class="empty-state">
                            <p>No tech stack data available</p>
                        </div>
                    <?php else: ?>
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Tech Stack</th>
                                    <th>Users</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topTechStacks as $tech): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($tech['tech_stack']) ?></td>
                                        <td><?= number_format($tech['count']) ?></td>
                                        <td><?= round(($tech['count'] / $stats['total_users']) * 100, 1) ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="metrics-section">
        <div class="metrics-header">
            <h3>Key Performance Metrics</h3>
        </div>
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-label">Approval Rate</div>
                <div class="metric-value"><?= $approvalRate ?>%</div>
                <div class="metric-bar">
                    <div class="metric-fill" style="width: <?= $approvalRate ?>%"></div>
                </div>
            </div>
            
            <div class="metric-card">
                <div class="metric-label">Rejection Rate</div>
                <div class="metric-value"><?= $rejectionRate ?>%</div>
                <div class="metric-bar">
                    <div class="metric-fill warning" style="width: <?= $rejectionRate ?>%"></div>
                </div>
            </div>
            
            <div class="metric-card">
                <div class="metric-label">Application per Job</div>
                <div class="metric-value"><?= $stats['total_jobs'] > 0 ? round($stats['total_applications'] / $stats['total_jobs'], 1) : 0 ?></div>
                <div class="metric-bar">
                    <div class="metric-fill info" style="width: <?= min(100, round(($stats['total_applications'] / max($stats['total_jobs'], 1)) * 10)) ?>%"></div>
                </div>
            </div>
            
            <div class="metric-card">
                <div class="metric-label">Resume Attachment Rate</div>
                <div class="metric-value"><?= round(($stats['total_resumes'] / max($stats['total_applications'], 1)) * 100, 1) ?>%</div>
                <div class="metric-bar">
                    <div class="metric-fill success" style="width: <?= round(($stats['total_resumes'] / max($stats['total_applications'], 1)) * 100) ?>%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// User Growth Chart
const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
const userGrowthData = <?= json_encode($userGrowth) ?>;
const userGrowthLabels = userGrowthData.map(d => d.month);
const userGrowthValues = userGrowthData.map(d => d.count);

new Chart(userGrowthCtx, {
    type: 'line',
    data: {
        labels: userGrowthLabels,
        datasets: [{
            label: 'New Users',
            data: userGrowthValues,
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

// Application Trends Chart
const appTrendsCtx = document.getElementById('applicationTrendsChart').getContext('2d');
const appTrendsData = <?= json_encode($appTrends) ?>;
const months = [...new Set(appTrendsData.map(d => d.month))];
const statuses = [...new Set(appTrendsData.map(d => d.status))];
const colors = {
    'pending': '#F59E0B',
    'approved': '#10B981',
    'rejected': '#EF4444',
    'interview': '#3B82F6',
    'shortlist': '#8B5CF6'
};

const datasets = statuses.map(status => ({
    label: status.charAt(0).toUpperCase() + status.slice(1),
    data: months.map(month => {
        const item = appTrendsData.find(d => d.month === month && d.status === status);
        return item ? item.count : 0;
    }),
    borderColor: colors[status] || '#6B7280',
    backgroundColor: (colors[status] || '#6B7280') + '20',
    fill: false,
    tension: 0.4
}));

new Chart(appTrendsCtx, {
    type: 'line',
    data: {
        labels: months,
        datasets: datasets
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Conversion Funnel Chart
const funnelCtx = document.getElementById('conversionFunnelChart').getContext('2d');
const funnelData = <?= json_encode($conversionFunnel) ?>;

new Chart(funnelCtx, {
    type: 'bar',
    data: {
        labels: ['Visitors', 'Applications', 'Approved', 'Interview'],
        datasets: [{
            label: 'Count',
            data: [funnelData.visitors, funnelData.applications, funnelData.approved, funnelData.interview],
            backgroundColor: [
                '#4F46E5',
                '#10B981',
                '#F59E0B',
                '#3B82F6'
            ]
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

// Approval Rate Chart
const approvalCtx = document.getElementById('approvalRateChart').getContext('2d');
const approvalRate = <?= $approvalRate ?>;
const rejectionRate = <?= $rejectionRate ?>;

new Chart(approvalCtx, {
    type: 'doughnut',
    data: {
        labels: ['Approved', 'Rejected', 'Other'],
        datasets: [{
            data: [approvalRate, rejectionRate, 100 - approvalRate - rejectionRate],
            backgroundColor: ['#10B981', '#EF4444', '#6B7280']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

function applyDateRange() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    window.location.href = `analytics.php?start_date=${startDate}&end_date=${endDate}`;
}

function resetDateRange() {
    window.location.href = 'analytics.php';
}

function exportReport() {
    alert('Export functionality would generate a comprehensive PDF/Excel report of all analytics data.');
}
</script>

<style>
.charts-section {
    margin: 30px 0;
}

.chart-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.chart-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.chart-header {
    margin-bottom: 20px;
}

.chart-header h3 {
    font-size: 18px;
    font-weight: 600;
    color: #1a1a2e;
    margin-bottom: 5px;
}

.chart-header p {
    color: #6b7280;
    font-size: 14px;
}

.chart-container {
    height: 300px;
    position: relative;
}

.reports-section {
    margin: 30px 0;
}

.report-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
}

.report-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.report-header {
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e5e7eb;
}

.report-header h3 {
    font-size: 18px;
    font-weight: 600;
    color: #1a1a2e;
    margin-bottom: 5px;
}

.report-header p {
    color: #6b7280;
    font-size: 14px;
}

.report-table {
    width: 100%;
    border-collapse: collapse;
}

.report-table th,
.report-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #f3f4f6;
}

.report-table th {
    font-weight: 600;
    color: #374151;
    font-size: 13px;
    text-transform: uppercase;
}

.report-table td {
    color: #6b7280;
    font-size: 14px;
}

.metrics-section {
    margin: 30px 0;
}

.metrics-header {
    margin-bottom: 20px;
}

.metrics-header h3 {
    font-size: 20px;
    font-weight: 600;
    color: #1a1a2e;
}

.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.metric-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.metric-label {
    font-size: 14px;
    color: #6b7280;
    margin-bottom: 10px;
}

.metric-value {
    font-size: 32px;
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 15px;
}

.metric-bar {
    height: 8px;
    background: #f3f4f6;
    border-radius: 4px;
    overflow: hidden;
}

.metric-fill {
    height: 100%;
    background: #4F46E5;
    border-radius: 4px;
    transition: width 0.3s;
}

.metric-fill.warning {
    background: #F59E0B;
}

.metric-fill.info {
    background: #3B82F6;
}

.metric-fill.success {
    background: #10B981;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #6b7280;
}
</style>

<?php require_once 'includes/admin_footer.php'; ?>
