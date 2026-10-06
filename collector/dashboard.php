<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin('collector');

$title = 'Collector Dashboard';
$stats = getDashboardStats('collector', $_SESSION['user_id']);

$assigned_jobs = [];
try {
    $stmt = $db->prepare("SELECT cr.request_code, cr.status, cr.preferred_date, u.full_name AS customer_name, wc.category_name FROM collection_requests cr JOIN users u ON cr.customer_id = u.user_id JOIN waste_categories wc ON cr.category_id = wc.category_id WHERE cr.assigned_to = ? ORDER BY cr.preferred_date ASC LIMIT 6");
    $stmt->execute([$_SESSION['user_id']]);
    $assigned_jobs = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Collector dashboard error: ' . $e->getMessage());
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2><i class="fas fa-truck me-2"></i>Collector Dashboard</h2>
            <p>Manage your assigned pickups and keep service updates current for customers.</p>
        </div>
        <a href="assigned_requests.php" class="btn btn-primary">
            <i class="fas fa-list me-1"></i> View Jobs
        </a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="metric-card">
            <div class="metric-label">Assigned Jobs</div>
            <div class="metric-value"><?= number_format($stats['assigned_requests'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="metric-card">
            <div class="metric-label">In Progress</div>
            <div class="metric-value"><?= number_format($stats['in_progress'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="metric-card">
            <div class="metric-label">Completed</div>
            <div class="metric-value"><?= number_format($stats['completed_requests'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="metric-card">
            <div class="metric-label">Pending</div>
            <div class="metric-value"><?= number_format($stats['pending_requests'] ?? 0) ?></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-route me-2 text-success"></i>Today’s Assigned Jobs</h5>
        <a href="assigned_requests.php" class="btn btn-sm btn-soft">Open Full List</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 table-modern">
                <thead>
                    <tr>
                        <th>Request Code</th>
                        <th>Customer</th>
                        <th>Category</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($assigned_jobs) > 0): ?>
                        <?php foreach ($assigned_jobs as $job): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($job['request_code']) ?></strong></td>
                            <td><?= htmlspecialchars($job['customer_name']) ?></td>
                            <td><?= htmlspecialchars($job['category_name']) ?></td>
                            <td><?= date('d M Y', strtotime($job['preferred_date'])) ?></td>
                            <td><?= getStatusBadge($job['status']) ?></td>
                            <td><a href="assigned_requests.php" class="btn btn-sm btn-soft">Update</a></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No jobs assigned yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
