<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin('manager');

$title = 'Manager Dashboard';
$stats = getDashboardStats('manager', $_SESSION['user_id']);

$requests = [];
try {
    $stmt = $db->query("SELECT cr.request_code, cr.status, cr.preferred_date, cr.total_price, u.full_name AS customer_name, wc.category_name FROM collection_requests cr JOIN users u ON cr.customer_id = u.user_id JOIN waste_categories wc ON cr.category_id = wc.category_id ORDER BY cr.created_at DESC LIMIT 8");
    $requests = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Manager dashboard requests error: ' . $e->getMessage());
}

$collectors = [];
try {
    $stmt = $db->query("SELECT user_id, full_name, phone, status FROM users WHERE role = 'collector' ORDER BY full_name ASC LIMIT 6");
    $collectors = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Manager collectors error: ' . $e->getMessage());
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2><i class="fas fa-chart-line me-2"></i>Operations Dashboard</h2>
            <p>Monitor waste collection performance, routes, and team productivity in Gasabo District.</p>
        </div>
        <a href="requests.php" class="btn btn-primary">
            <i class="fas fa-plus-circle me-1"></i> Review Requests
        </a>
    </div>
</div>

<div class="hero-card mb-4">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <h4 class="mb-2 text-success">Daily operations overview</h4>
            <p class="mb-0 text-muted">Keep pickups flowing smoothly with quick visibility into pending pickups, team status, and service coverage.</p>
        </div>
        <div class="col-lg-4 text-lg-end">
            <span class="badge bg-success-subtle text-success px-3 py-2"><i class="fas fa-leaf me-1"></i> EcoConnect Operations</span>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="metric-card">
            <div class="metric-label">Total Requests</div>
            <div class="metric-value"><?= number_format($stats['total_requests'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="metric-card">
            <div class="metric-label">Pending</div>
            <div class="metric-value"><?= number_format($stats['pending_requests'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="metric-card">
            <div class="metric-label">Active Jobs</div>
            <div class="metric-value"><?= number_format($stats['active_requests'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="metric-card">
            <div class="metric-label">Revenue</div>
            <div class="metric-value"><?= formatCurrency($stats['total_revenue'] ?? 0) ?></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-clipboard-list me-2 text-success"></i>Latest Requests</h5>
                <a href="requests.php" class="btn btn-sm btn-soft">Open Queue</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 table-modern">
                        <thead>
                            <tr>
                                <th>Request Code</th>
                                <th>Customer</th>
                                <th>Category</th>
                                <th>Pickup Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($requests) > 0): ?>
                                <?php foreach ($requests as $request): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($request['request_code']) ?></strong></td>
                                    <td><?= htmlspecialchars($request['customer_name']) ?></td>
                                    <td><?= htmlspecialchars($request['category_name']) ?></td>
                                    <td><?= date('d M Y', strtotime($request['preferred_date'])) ?></td>
                                    <td><?= getStatusBadge($request['status']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center text-muted py-4">No requests available yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-users-cog me-2 text-success"></i>Collector Availability</h5>
            </div>
            <div class="card-body">
                <?php if (count($collectors) > 0): ?>
                    <?php foreach ($collectors as $collector): ?>
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div>
                                <strong><?= htmlspecialchars($collector['full_name']) ?></strong>
                                <div class="text-muted small"><?= htmlspecialchars($collector['phone']) ?></div>
                            </div>
                            <span class="badge bg-success-subtle text-success"><?= htmlspecialchars($collector['status'] ?? 'active') ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-muted">No collector accounts registered yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
