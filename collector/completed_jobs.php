<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin('collector');

$title = 'Completed Jobs';

$jobs = [];
try {
    $stmt = $db->prepare("SELECT cr.request_code, cr.status, cr.completed_at, u.full_name AS customer_name, wc.category_name FROM collection_requests cr JOIN users u ON cr.customer_id = u.user_id JOIN waste_categories wc ON cr.category_id = wc.category_id WHERE cr.assigned_to = ? AND cr.status = 'completed' ORDER BY cr.completed_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $jobs = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Collector completed jobs error: ' . $e->getMessage());
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2><i class="fas fa-check-circle me-2"></i>Completed Jobs</h2>
            <p>Review completed pickups and keep your service history up to date.</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-success">Back</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-history me-2 text-success"></i>Completion Log</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 table-modern">
                <thead>
                    <tr>
                        <th>Request Code</th>
                        <th>Customer</th>
                        <th>Category</th>
                        <th>Completed</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($jobs) > 0): ?>
                        <?php foreach ($jobs as $job): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($job['request_code']) ?></strong></td>
                            <td><?= htmlspecialchars($job['customer_name']) ?></td>
                            <td><?= htmlspecialchars($job['category_name']) ?></td>
                            <td><?= !empty($job['completed_at']) ? date('d M Y H:i', strtotime($job['completed_at'])) : '—' ?></td>
                            <td><?= getStatusBadge($job['status']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No completed jobs yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
