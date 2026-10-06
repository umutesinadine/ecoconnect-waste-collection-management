<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';

// Require admin login
requireLogin('admin');

$title = 'Admin Dashboard';
$stats = getDashboardStats('admin');

// Handle Super Cashier assignment
if (isset($_POST['assign_super_cashier'])) {
    requireCSRF();
    $cashier_id = (int)$_POST['super_cashier_id'];
    
    if ($cashier_id > 0) {
        if (assignSuperCashier($cashier_id, $_SESSION['user_id'])) {
            setSuccess("Super Cashier assigned successfully. This cashier now has all permissions.");
        } else {
            setError("Failed to assign Super Cashier. Please try again.");
        }
    } else {
        setError("Please select a valid cashier.");
    }
    header("Location: dashboard.php");
    exit();
}

if (isset($_GET['remove_super_cashier'])) {
    requireCSRF();
    $super = getCurrentSuperCashier();
    if ($super) {
        if (removeSuperCashier($super['user_id'])) {
            setSuccess("Super Cashier status removed successfully.");
        } else {
            setError("Failed to remove Super Cashier status.");
        }
    }
    header("Location: dashboard.php");
    exit();
}

// Get all active cashiers for the dropdown
$cashiers = [];
try {
    $stmt = $db->query("
        SELECT u.user_id, u.full_name, u.email, u.last_login,
               p.is_super_cashier
        FROM users u
        LEFT JOIN permissions p ON u.user_id = p.user_id
        WHERE u.role = 'cashier' AND u.status = 'active'
        ORDER BY u.full_name ASC
    ");
    $cashiers = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Cashier fetch error: " . $e->getMessage());
}

// Get current super cashier
$current_super = getCurrentSuperCashier();

// Get pending customers
$pending_customers = [];
try {
    $stmt = $db->query("SELECT user_id, full_name, email, created_at FROM users WHERE role = 'customer' AND status = 'pending' ORDER BY created_at DESC LIMIT 5");
    $pending_customers = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Pending customer query error: " . $e->getMessage());
}

// Get recent requests
$recent_requests = [];
try {
    $stmt = $db->query("
        SELECT cr.*, u.full_name as customer_name, wc.category_name 
        FROM collection_requests cr
        JOIN users u ON cr.customer_id = u.user_id
        JOIN waste_categories wc ON cr.category_id = wc.category_id
        ORDER BY cr.created_at DESC LIMIT 10
    ");
    $recent_requests = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Dashboard query error: " . $e->getMessage());
}

// Get recent payments
$recent_payments = [];
try {
    $stmt = $db->query("
        SELECT p.*, u.full_name as customer_name, cr.request_code
        FROM payments p
        JOIN users u ON p.customer_id = u.user_id
        JOIN collection_requests cr ON p.request_id = cr.request_id
        ORDER BY p.created_at DESC LIMIT 10
    ");
    $recent_payments = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Dashboard payments query error: " . $e->getMessage());
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2><i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard</h2>
            <p>Welcome back, <?= htmlspecialchars($_SESSION['full_name']) ?>! Here's your system overview.</p>
        </div>
        <div class="text-end">
            <small><?= date('l, F d, Y') ?></small>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card bg-gradient-green">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-number"><?= number_format($stats['total_customers'] ?? 0) ?></div>
                    <div class="stat-label">Total Customers</div>
                </div>
                <div class="stat-icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card bg-gradient-blue">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-number"><?= number_format($stats['total_cashiers'] ?? 0) ?></div>
                    <div class="stat-label">Active Cashiers</div>
                </div>
                <div class="stat-icon"><i class="fas fa-user-tie"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card bg-gradient-orange">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-number"><?= number_format($stats['pending_requests'] ?? 0) ?></div>
                    <div class="stat-label">Pending Requests</div>
                </div>
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card bg-gradient-red">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-number"><?= number_format($stats['total_requests'] ?? 0) ?></div>
                    <div class="stat-label">Total Requests</div>
                </div>
                <div class="stat-icon"><i class="fas fa-clipboard-list"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card bg-gradient-purple">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-number"><?= number_format($stats['completed_requests'] ?? 0) ?></div>
                    <div class="stat-label">Completed</div>
                </div>
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card bg-gradient-teal">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-number"><?= number_format($stats['total_categories'] ?? 0) ?></div>
                    <div class="stat-label">Waste Categories</div>
                </div>
                <div class="stat-icon"><i class="fas fa-recycle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-6 col-md-12 mb-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #1a237e, #283593);">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-number"><?= formatCurrency($stats['total_revenue'] ?? 0) ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>
                <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Super Cashier Management Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-warning">
            <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #ffc107, #fd7e14);">
                <h5 class="mb-0"><i class="fas fa-star me-2"></i>Super Cashier Management</h5>
                <span class="badge bg-light text-dark fs-6">One cashier with all permissions</span>
            </div>
            <div class="card-body">
                <?php if ($current_super): ?>
                    <div class="alert alert-success d-flex align-items-center">
                        <i class="fas fa-user-shield fa-2x me-3"></i>
                        <div>
                            <strong>Current Super Cashier:</strong> <?= htmlspecialchars($current_super['full_name']) ?>
                            <br><small class="text-muted">Email: <?= htmlspecialchars($current_super['email']) ?> | Phone: <?= htmlspecialchars($current_super['phone']) ?></small>
                            <br><small class="text-muted">Last login: <?= $current_super['last_login'] ? date('d M Y H:i', strtotime($current_super['last_login'])) : 'Never' ?></small>
                        </div>
                    </div>
                    <form method="GET" action="" class="d-inline">
                        <?= csrfField() ?>
                        <button type="submit" name="remove_super_cashier" value="1" class="btn btn-outline-danger" data-confirm="Remove Super Cashier status from <?= htmlspecialchars($current_super['full_name']) ?>?">
                            <i class="fas fa-star-of-david me-1"></i> Remove Super Cashier
                        </button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> No Super Cashier has been assigned yet. Assign one below to grant a cashier all permissions.
                    </div>
                <?php endif; ?>
                
                <hr class="my-3">
                
                <form method="POST" action="" class="row g-3 align-items-end">
                    <?= csrfField() ?>
                    <div class="col-md-5">
                        <label for="super_cashier_id" class="form-label fw-bold">Select Cashier</label>
                        <select class="form-select" id="super_cashier_id" name="super_cashier_id" required>
                            <option value="">-- Choose a cashier --</option>
                            <?php foreach ($cashiers as $cashier): ?>
                                <?php if (!$current_super || $current_super['user_id'] != $cashier['user_id']): ?>
                                    <option value="<?= $cashier['user_id'] ?>">
                                        <?= htmlspecialchars($cashier['full_name']) ?> - <?= htmlspecialchars($cashier['email']) ?>
                                        <?= $cashier['is_super_cashier'] ? '<span class="text-warning">(Currently Super)</span>' : '' ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <div class="alert alert-warning mb-0 py-2">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            This cashier will gain ALL permissions: manage requests, process payments, view reports, manage customers, and manage collections.
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" name="assign_super_cashier" value="1" class="btn btn-warning w-100 py-2" data-confirm="Assign this cashier as Super Cashier?">
                            <i class="fas fa-star me-1"></i> Assign
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-user-clock me-2"></i>Pending Customer Approvals</h5>
                <a href="customers.php" class="btn btn-sm btn-outline-primary">Manage</a>
            </div>
            <div class="card-body">
                <?php if (count($pending_customers) > 0): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($pending_customers as $customer): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div>
                                    <strong><?= htmlspecialchars($customer['full_name']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($customer['email']) ?></small>
                                    <br><small class="text-muted"><i class="fas fa-calendar me-1"></i>Registered: <?= date('d M Y', strtotime($customer['created_at'])) ?></small>
                                </div>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="customers.php?action=activate&id=<?= $customer['user_id'] ?>" class="btn btn-success" title="Approve" data-confirm="Approve this customer?">
                                        <i class="fas fa-check"></i> Approve
                                    </a>
                                    <a href="customers.php?action=deactivate&id=<?= $customer['user_id'] ?>" class="btn btn-danger" title="Reject" data-confirm="Reject this customer?">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted mb-0">No pending customer approvals right now.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Requests -->
    <div class="col-xl-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-clipboard-list me-2 text-primary"></i>Recent Requests</h5>
                <a href="requests.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Code</th>
                                <th>Customer</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($recent_requests) > 0): ?>
                                <?php foreach ($recent_requests as $req): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($req['request_code']) ?></strong></td>
                                    <td><?= htmlspecialchars($req['customer_name']) ?></td>
                                    <td><?= htmlspecialchars($req['category_name']) ?></td>
                                    <td><?= getStatusBadge($req['status']) ?></td>
                                    <td><small><?= date('d M Y', strtotime($req['created_at'])) ?></small></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center text-muted py-3">No requests yet</td></tr>
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
                                    <td><?= formatCurrency($pay['amount']) ?></td>
                                    <td><?= getStatusBadge($pay['payment_status']) ?></td>
                                    <td><small><?= date('d M Y', strtotime($pay['created_at'])) ?></small></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center text-muted py-3">No payments yet</td></tr>
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
                        <a href="customers.php" class="btn btn-outline-primary w-100 py-3">
                            <i class="fas fa-users d-block mb-1"></i> Manage Customers
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="cashiers.php" class="btn btn-outline-primary w-100 py-3">
                            <i class="fas fa-user-plus d-block mb-1"></i> Manage Cashiers
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="categories.php" class="btn btn-outline-success w-100 py-3">
                            <i class="fas fa-recycle d-block mb-1"></i> Waste Categories
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="requests.php" class="btn btn-outline-warning w-100 py-3">
                            <i class="fas fa-tasks d-block mb-1"></i> View Requests
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="payments.php" class="btn btn-outline-success w-100 py-3">
                            <i class="fas fa-credit-card d-block mb-1"></i> View Receipts
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="reports.php" class="btn btn-outline-info w-100 py-3">
                            <i class="fas fa-chart-bar d-block mb-1"></i> View Reports
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="messages.php" class="btn btn-outline-secondary w-100 py-3">
                            <i class="fas fa-comments d-block mb-1"></i> Send Messages
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="settings.php" class="btn btn-outline-dark w-100 py-3">
                            <i class="fas fa-cog d-block mb-1"></i> System Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
