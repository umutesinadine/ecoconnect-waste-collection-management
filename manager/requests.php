<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin('manager');

$title = 'Priority Requests';

$requests = [];
try {
    $stmt = $db->query("SELECT cr.request_id, cr.request_code, cr.status, cr.preferred_date, cr.total_price, u.full_name AS customer_name, wc.category_name FROM collection_requests cr JOIN users u ON cr.customer_id = u.user_id JOIN waste_categories wc ON cr.category_id = wc.category_id ORDER BY cr.created_at DESC");
    $requests = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Manager requests query error: ' . $e->getMessage());
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2><i class="fas fa-clipboard-list me-2"></i>Priority Requests</h2>
            <p>Review active pickups, dispatch work, and ensure every service request is handled on time.</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-success">Back to Overview</a>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list me-2 text-success"></i>Request Queue</h5>
        <span class="badge bg-success-subtle text-success"><?= count($requests) ?> requests</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 table-modern">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Customer</th>
                        <th>Category</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Amount</th>
                        <th>Action</th>
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
                            <td><?= formatCurrency($request['total_price'] ?? 0) ?></td>
                            <td><a href="dashboard.php" class="btn btn-sm btn-soft">Assign</a></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No requests to display.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
