<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin('cashier');

$user_id = $_SESSION['user_id'];
$is_super = isSuperCashier($user_id);

$stats = getDashboardStats('cashier', $user_id);

// Get assigned requests
$assigned_requests = [];
try {
    if ($is_super) {
        // Super cashier sees ALL requests
        $stmt = $db->query("
            SELECT cr.*, u.full_name as customer_name, u.phone as customer_phone, wc.category_name 
            FROM collection_requests cr
            JOIN users u ON cr.customer_id = u.user_id
            JOIN waste_categories wc ON cr.category_id = wc.category_id
            ORDER BY cr.created_at DESC LIMIT 10
        ");
        $assigned_requests = $stmt->fetchAll();
    } else {
        // Regular cashier sees only assigned requests
        $stmt = $db->prepare("
            SELECT cr.*, u.full_name as customer_name, u.phone as customer_phone, wc.category_name 
            FROM collection_requests cr
            JOIN users u ON cr.customer_id = u.user_id
            JOIN waste_categories wc ON cr.category_id = wc.category_id
            WHERE cr.assigned_to = ?
            ORDER BY cr.created_at DESC LIMIT 10
        ");
        $stmt->execute([$user_id]);
        $assigned_requests = $stmt->fetchAll();
    }
} catch (PDOException $e) {}

// Get recent payments processed by this cashier
$recent_payments = [];
try {
    if ($is_super) {
        $stmt = $db->query("
            SELECT p.*, u.full_name as customer_name, cr.request_code
            FROM payments p
            JOIN users u ON p.customer_id = u.user_id
            JOIN collection_requests cr ON p.request_id = cr.request_id
            ORDER BY p.created_at DESC LIMIT 10
        ");
    } else {
        $stmt = $db->prepare("
            SELECT p.*, u.full_name as customer_name, cr.request_code
            FROM payments p
            JOIN users u ON p.customer_id = u.user_id
            JOIN collection_requests cr ON p.request_id = cr.request_id
            WHERE p.processed_by = ?
            ORDER BY p.created_at DESC LIMIT 10
        ");
        $stmt->execute([$user_id]);
    }
    $recent_payments = $stmt->fetchAll();
} catch (PDOException $e) {}

$title = 'Cashier Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2><i class="fas fa-tachometer-alt me-2"></i>Cashier Dashboard</h2>
            <p>Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?>! Manage your collection requests.</p>
        </div>
        <?php if ($is_super): ?>
            <span class="badge bg-warning text-dark fs-6"><i class="fas fa-star"></i> Super Cashier</span>
        <?php endif; ?>
    </div>
</div>

<div class="row mb-4">
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="stat-card bg-gradient-blue">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-number"><?= $is_super ? number_format($stats['total_requests'] ?? 0) : number_format($stats['assigned_requests'] ?? 0) ?></div>
                    <div class="stat-label"><?= $is_super ? 'Total Requests' : 'Assigned Requests' ?></div>
                </div>
                <div class="stat-icon"><i class="fas fa-tasks"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="stat-card bg-gradient-orange">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-number"><?= $is_super ? number_format($stats['pending_requests'] ?? 0) : number_format($stats['pending_assigned'] ?? 0) ?></div>
                    <div class="stat-label"><?= $is_super ? 'Pending' : 'Pending Collection' ?></div>
                </div>
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="stat-card bg-gradient-green">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-number"><?= number_format($stats['completed_requests'] ?? 0) ?></div>
                    <div class="stat-label">Completed</div>
                </div>
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Assigned Requests -->
    <div class="col-xl-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-clipboard-list me-2 text-primary"></i><?= $is_super ? 'All Collection Requests' : 'My Assigned Requests' ?></h5>
                <a href="requests.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Code</th>
                                <th>Customer</th>
                                <th>Phone</th>
                                <th>Category</th>
                                <th>Quantity</th>
                                <th>Pickup Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($assigned_requests) > 0): ?>
                                <?php foreach ($assigned_requests as $req): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($req['request_code']) ?></strong></td>
                                    <td><?= htmlspecialchars($req['customer_name']) ?></td>
                                    <td><?= htmlspecialchars($req['customer_phone']) ?></td>
                                    <td><?= htmlspecialchars($req['category_name']) ?></td>
                                    <td><?= $req['waste_quantity_kg'] ?> kg</td>
                                    <td><small><?= date('d M Y', strtotime($req['preferred_date'])) ?></small></td>
                                    <td><?= getStatusBadge($req['status']) ?></td>
                                    <td>
                                        <a href="requests.php?update=<?= $req['request_id'] ?>" class="btn btn-sm btn-success">
                                            <i class="fas fa-check"></i> Update
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="8" class="text-center text-muted py-4">No requests found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Payments -->
    <div class="col-xl-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-credit-card me-2 text-success"></i>Recent Payments</h5>
                <a href="payments.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Receipt</th>
                                <th>Customer</th>
                                <th>Request</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($recent_payments) > 0): ?>
                                <?php foreach ($recent_payments as $pay): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($pay['receipt_number'] ?? 'N/A') ?></strong></td>
                                    <td><?= htmlspecialchars($pay['customer_name']) ?></td>
                                    <td><?= htmlspecialchars($pay['request_code']) ?></td>
                                    <td><?= formatCurrency($pay['amount']) ?></td>
                                    <td><?= getStatusBadge($pay['payment_status']) ?></td>
                                    <td><small><?= date('d M Y', strtotime($pay['created_at'])) ?></small></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center text-muted py-3">No payments yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt me-2 text-warning"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="requests.php" class="btn btn-outline-primary w-100 py-3">
                            <i class="fas fa-tasks d-block mb-1"></i> View Requests
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="payments.php" class="btn btn-outline-success w-100 py-3">
                            <i class="fas fa-credit-card d-block mb-1"></i> Process Payments
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="reports.php" class="btn btn-outline-info w-100 py-3">
                            <i class="fas fa-chart-line d-block mb-1"></i> View Reports
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="followup.php" class="btn btn-outline-warning w-100 py-3">
                            <i class="fas fa-paper-plane d-block mb-1"></i> Follow-ups
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="profile.php" class="btn btn-outline-secondary w-100 py-3">
                            <i class="fas fa-user d-block mb-1"></i> My Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
