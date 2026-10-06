<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin('manager');

$title = 'Collectors';

$collectors = [];
try {
    $stmt = $db->query("SELECT user_id, full_name, phone, email, status FROM users WHERE role = 'collector' ORDER BY full_name ASC");
    $collectors = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Collector listing error: ' . $e->getMessage());
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2><i class="fas fa-users-cog me-2"></i>Collectors</h2>
            <p>Manage team members, assign work, and track field service availability.</p>
        </div>
        <a href="dashboard.php" class="btn btn-success">Return</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-user-tie me-2 text-success"></i>Collector Team</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 table-modern">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($collectors) > 0): ?>
                        <?php foreach ($collectors as $collector): ?>
                        <tr>
                            <td><?= htmlspecialchars($collector['full_name']) ?></td>
                            <td><?= htmlspecialchars($collector['email']) ?></td>
                            <td><?= htmlspecialchars($collector['phone']) ?></td>
                            <td><?= getStatusBadge($collector['status'] ?? 'active') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center text-muted py-4">No collector profile data available.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
