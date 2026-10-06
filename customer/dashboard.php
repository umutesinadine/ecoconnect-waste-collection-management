<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin('customer');

$user_id = $_SESSION['user_id'];
$stats = getDashboardStats('customer', $user_id);

// Get my recent requests
$my_requests = [];
try {
    $stmt = $db->prepare("
        SELECT cr.*, wc.category_name 
        FROM collection_requests cr
        JOIN waste_categories wc ON cr.category_id = wc.category_id
        WHERE cr.customer_id = ?
        ORDER BY cr.created_at DESC LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $my_requests = $stmt->fetchAll();
} catch (PDOException $e) {}

// Get my recent payments
$my_payments = [];
try {
    $stmt = $db->prepare("
        SELECT p.*, cr.request_code, cr.category_id, wc.category_name
        FROM payments p
        JOIN collection_requests cr ON p.request_id = cr.request_id
        JOIN waste_categories wc ON cr.category_id = wc.category_id
        WHERE p.customer_id = ?
        ORDER BY p.created_at DESC LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $my_payments = $stmt->fetchAll();
} catch (PDOException $e) {}

// Get my recent notifications
$my_notifications = [];
try {
    $stmt = $db->prepare("
        SELECT * FROM notifications 
        WHERE (user_id = ? OR user_id IS NULL) 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $my_notifications = $stmt->fetchAll();
} catch (PDOException $e) {}

// Get waste categories
$waste_categories = [];
try {
    $waste_categories = $db->query("SELECT * FROM waste_categories WHERE is_active = 1 ORDER BY category_name")->fetchAll();
} catch (PDOException $e) {}

// Get current user data for profile summary
$current_user = getCurrentUser();

$title = 'Customer Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2><i class="fas fa-tachometer-alt me-2"></i>Customer Dashboard</h2>
            <p>Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?>! Manage your waste collection requests.</p>
        </div>
        <a href="request.php" class="btn btn-light">
            <i class="fas fa-plus-circle me-1"></i> New Request
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card bg-gradient-green">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-number"><?= $stats['my_requests'] ?? 0 ?></div>
                    <div class="stat-label">Total Requests</div>
                </div>
                <div class="stat-icon"><i class="fas fa-clipboard-list"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card bg-gradient-orange">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-number"><?= $stats['pending_requests'] ?? 0 ?></div>
                    <div class="stat-label">Pending</div>
                </div>
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card bg-gradient-purple">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-number"><?= $stats['completed_requests'] ?? 0 ?></div>
                    <div class="stat-label">Completed</div>
                </div>
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #1a237e, #283593);">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-number"><?= formatCurrency($stats['total_spent'] ?? 0) ?></div>
                    <div class="stat-label">Total Spent</div>
                </div>
                <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Profile Summary & Recent Notifications -->
<div class="row mb-4">
    <!-- Profile Summary -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i>My Profile</h5>
                <a href="profile.php" class="btn btn-sm btn-outline-primary">Edit</a>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <img src="../images/gisellelogo.png" alt="Profile" class="rounded-circle" width="80" height="80" style="object-fit: cover; border: 3px solid #e0e0e0;">
                </div>
                <table class="table table-sm table-borderless">
                    <tr>
                        <th>Name:</th>
                        <td><?= htmlspecialchars($current_user['full_name'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td><?= htmlspecialchars($current_user['email'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <th>Phone:</th>
                        <td><?= htmlspecialchars($current_user['phone'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <th>Member Since:</th>
                        <td><small><?= date('d M Y', strtotime($_SESSION['created_at'] ?? '2026-07-23')) ?></small></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Notifications -->
    <div class="col-xl-8 col-md-12 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-bell me-2"></i>Recent Notifications</h5>
                <a href="messages.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (count($my_notifications) > 0): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($my_notifications as $notif): ?>
                            <a href="<?= $notif['link'] ?? 'messages.php' ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-start">
                                <div class="d-flex align-items-start">
                                    <div class="me-3 mt-1">
                                        <?php if ($notif['type'] === 'success'): ?>
                                            <i class="fas fa-check-circle text-success fa-lg"></i>
                                        <?php elseif ($notif['type'] === 'warning'): ?>
                                            <i class="fas fa-exclamation-triangle text-warning fa-lg"></i>
                                        <?php elseif ($notif['type'] === 'danger'): ?>
                                            <i class="fas fa-exclamation-circle text-danger fa-lg"></i>
                                        <?php else: ?>
                                            <i class="fas fa-info-circle text-info fa-lg"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <strong><?= htmlspecialchars($notif['title']) ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars(substr($notif['message'], 0, 80)) ?>...</small>
                                        <br><small class="text-muted"><i class="fas fa-clock me-1"></i><?= date('d M Y H:i', strtotime($notif['created_at'])) ?></small>
                                    </div>
                                </div>
                                <?php if (!$notif['is_read']): ?>
                                    <span class="badge bg-primary">New</span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-bell-slash"></i>
                        <p>No notifications yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- My Recent Requests & Payments -->
<div class="row">
    <!-- My Recent Requests -->
    <div class="col-xl-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-history me-2 text-primary"></i>My Recent Requests</h5>
                <a href="my_requests.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Code</th>
                                <th>Category</th>
                                <th>Quantity</th>
                                <th>Pickup Date</th>
                                <th>Status</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($my_requests) > 0): ?>
                                <?php foreach ($my_requests as $req): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($req['request_code']) ?></strong></td>
                                    <td><?= htmlspecialchars($req['category_name']) ?></td>
                                    <td><?= $req['waste_quantity_kg'] ?> kg</td>
                                    <td><small><?= date('d M Y', strtotime($req['preferred_date'])) ?></small></td>
                                    <td><?= getStatusBadge($req['status']) ?></td>
                                    <td><?= formatCurrency($req['total_price']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center text-muted py-4">No requests yet. <a href="request.php">Submit your first request</a></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- My Recent Payments -->
    <div class="col-xl-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-receipt me-2 text-success"></i>My Recent Payments</h5>
                <a href="payments.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Receipt</th>
                                <th>Request</th>
                                <th>Category</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($my_payments) > 0): ?>
                                <?php foreach ($my_payments as $pay): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($pay['receipt_number'] ?? 'N/A') ?></strong></td>
                                    <td><?= htmlspecialchars($pay['request_code']) ?></td>
                                    <td><?= htmlspecialchars($pay['category_name']) ?></td>
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

<!-- Waste Categories Quick View -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-recycle me-2 text-primary"></i>Waste Categories & Pricing</h5>
                <a href="request.php" class="btn btn-sm btn-primary">New Request</a>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php if (count($waste_categories) > 0): ?>
                        <?php foreach ($waste_categories as $cat): ?>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="metric-card h-100 text-center">
                                <div class="metric-label"><?= htmlspecialchars($cat['category_name']) ?></div>
                                <div class="metric-value"><?= formatCurrency($cat['price_per_kg']) ?>/kg</div>
                                <?php if ($cat['category_image']): ?>
                                    <img src="../images/<?= htmlspecialchars($cat['category_image']) ?>" alt="<?= htmlspecialchars($cat['category_name']) ?>" class="img-fluid rounded mt-2" style="max-height: 60px; object-fit: cover;" onerror="this.style.display='none'">
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center text-muted py-3">
                            <i class="fas fa-recycle fa-2x mb-2"></i>
                            <p>Waste categories are being set up. Check back soon!</p>
                        </div>
                    <?php endif; ?>
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
                        <a href="request.php" class="btn btn-outline-primary w-100 py-3">
                            <i class="fas fa-plus-circle d-block mb-1"></i> New Request
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="my_requests.php" class="btn btn-outline-success w-100 py-3">
                            <i class="fas fa-clipboard-check d-block mb-1"></i> My Requests
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="payments.php" class="btn btn-outline-info w-100 py-3">
                            <i class="fas fa-receipt d-block mb-1"></i> Payment History
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="messages.php" class="btn btn-outline-warning w-100 py-3">
                            <i class="fas fa-envelope d-block mb-1"></i> Messages
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
