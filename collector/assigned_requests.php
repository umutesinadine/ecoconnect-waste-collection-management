<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin('collector');

$title = 'Assigned Jobs';

$jobs = [];
try {
    $stmt = $db->prepare("SELECT cr.request_code, cr.status, cr.preferred_date, u.full_name AS customer_name, wc.category_name FROM collection_requests cr JOIN users u ON cr.customer_id = u.user_id JOIN waste_categories wc ON cr.category_id = wc.category_id WHERE cr.assigned_to = ? ORDER BY cr.preferred_date ASC");
    $stmt->execute([$_SESSION['user_id']]);
    $jobs = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Collector assigned jobs error: ' . $e->getMessage());
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2><i class="fas fa-tasks me-2"></i>Assigned Jobs</h2>
            <p>Track your pickup list and update job progress.</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-success">Back</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-list me-2 text-success"></i>Job List</h5>
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
                    <?php if (count($jobs) > 0): ?>
                        <?php foreach ($jobs as $job): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($job['request_code']) ?></strong></td>
                            <td><?= htmlspecialchars($job['customer_name']) ?></td>
                            <td><?= htmlspecialchars($job['category_name']) ?></td>
                            <td><?= date('d M Y', strtotime($job['preferred_date'])) ?></td>
                            <td><?= getStatusBadge($job['status']) ?></td>
                            <td><a href="dashboard.php" class="btn btn-sm btn-soft">Update</a></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No jobs assigned.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
