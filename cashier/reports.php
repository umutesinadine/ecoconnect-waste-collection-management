<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin('cashier');

$user_id = $_SESSION['user_id'];
$is_super = isSuperCashier($user_id);

// Permission check
if (!$is_super && !hasPermission($user_id, 'can_view_reports')) {
    setError("You do not have permission to view reports. Contact your administrator.");
    header("Location: dashboard.php");
    exit();
}

try {
    if ($is_super) {
        // Super cashier sees system-wide stats
        $stmt = $db->query("SELECT COUNT(*) as c FROM collection_requests");
        $total_assigned = $stmt->fetch()['c'];

        $stmt = $db->query("SELECT COUNT(*) as c FROM collection_requests WHERE status = 'completed'");
        $completed = $stmt->fetch()['c'];

        $stmt = $db->query("SELECT COALESCE(SUM(amount),0) as t FROM payments WHERE payment_status = 'completed'");
        $revenue = $stmt->fetch()['t'];

        $stmt = $db->query("SELECT COUNT(*) as c FROM collection_requests WHERE status = 'pending'");
        $pending = $stmt->fetch()['c'];

        $stmt = $db->query("SELECT COUNT(*) as c FROM collection_requests WHERE status = 'in_progress'");
        $in_progress = $stmt->fetch()['c'];
    } else {
        // Regular cashier sees personal stats
        $stmt = $db->prepare("SELECT COUNT(*) as c FROM collection_requests WHERE assigned_to = ?");
        $stmt->execute([$user_id]);
        $total_assigned = $stmt->fetch()['c'];

        $stmt = $db->prepare("SELECT COUNT(*) as c FROM collection_requests WHERE assigned_to = ? AND status = 'completed'");
        $stmt->execute([$user_id]);
        $completed = $stmt->fetch()['c'];

        $stmt = $db->prepare("SELECT COALESCE(SUM(amount),0) as t FROM payments WHERE processed_by = ?");
        $stmt->execute([$user_id]);
        $revenue = $stmt->fetch()['t'];

        $pending = 0;
        $in_progress = 0;
    }
} catch (PDOException $e) {
    $total_assigned = 0;
    $completed = 0;
    $revenue = 0;
    $pending = 0;
    $in_progress = 0;
}

$title = 'My Reports';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2><i class="fas fa-chart-line me-2"></i><?= $is_super ? 'System Reports' : 'My Performance Reports' ?></h2>
            <p><?= $is_super ? 'View system-wide collection and payment statistics' : 'Track your collection and payment activities' ?></p>
        </div>
        <?php if ($is_super): ?>
            <span class="badge bg-warning text-dark fs-6"><i class="fas fa-star"></i> Super Cashier View</span>
        <?php endif; ?>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="stat-card bg-gradient-blue">
            <div class="stat-number"><?= $total_assigned ?></div>
            <div class="stat-label"><?= $is_super ? 'Total Requests' : 'Total Assigned' ?></div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card bg-gradient-green">
            <div class="stat-number"><?= $completed ?></div>
            <div class="stat-label">Completed</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card bg-gradient-orange">
            <div class="stat-number"><?= $pending ?? 0 ?></div>
            <div class="stat-label"><?= $is_super ? 'Pending' : 'Pending Assigned' ?></div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #1a237e, #283593);">
            <div class="stat-number"><?= formatCurrency($revenue) ?></div>
            <div class="stat-label">Revenue Collected</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h5 class="mb-0"><?= $is_super ? 'System Performance Summary' : 'Performance Summary' ?></h5></div>
    <div class="card-body">
        <div class="mb-3">
            <label>Completion Rate</label>
            <div class="progress" style="height: 30px;">
                <div class="progress-bar bg-success" style="width: <?= $total_assigned > 0 ? ($completed/$total_assigned)*100 : 0 ?>%; font-size: 14px; font-weight: bold;">
                    <?= $total_assigned > 0 ? round(($completed/$total_assigned)*100) : 0 ?>%
                </div>
            </div>
        </div>
        <p class="text-muted mt-3">
            <?= $is_super 
                ? "System-wide: $completed out of $total_assigned requests completed." 
                : "You have completed $completed out of $total_assigned assigned collection requests." ?>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
