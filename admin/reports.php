<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin('admin');

// Get report data
$total_customers = $db->query("SELECT COUNT(*) as c FROM users WHERE role = 'customer'")->fetch()['c'];
$total_cashiers = $db->query("SELECT COUNT(*) as c FROM users WHERE role = 'cashier'")->fetch()['c'];
$total_requests = $db->query("SELECT COUNT(*) as c FROM collection_requests")->fetch()['c'];
$completed = $db->query("SELECT COUNT(*) as c FROM collection_requests WHERE status = 'completed'")->fetch()['c'];
$pending = $db->query("SELECT COUNT(*) as c FROM collection_requests WHERE status = 'pending'")->fetch()['c'];
$total_revenue = $db->query("SELECT COALESCE(SUM(amount),0) as t FROM payments WHERE payment_status = 'completed'")->fetch()['t'];

// Monthly revenue
$monthly = $db->query("SELECT DATE_FORMAT(payment_date, '%Y-%m') as month, SUM(amount) as total FROM payments WHERE payment_status = 'completed' GROUP BY month ORDER BY month DESC LIMIT 12")->fetchAll();

// Category breakdown
$categories = $db->query("SELECT wc.category_name, COUNT(cr.request_id) as count, COALESCE(SUM(cr.total_price),0) as revenue FROM waste_categories wc LEFT JOIN collection_requests cr ON wc.category_id = cr.category_id GROUP BY wc.category_id ORDER BY count DESC")->fetchAll();

$title = 'Reports';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h2><i class="fas fa-chart-bar me-2"></i>Reports Dashboard</h2>
    <p>System analytics and performance metrics</p>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Summary</h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 mb-3">
                        <div class="p-3 bg-light rounded text-center">
                            <h3 class="text-primary"><?= $total_customers ?></h3>
                            <small class="text-muted">Total Customers</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="p-3 bg-light rounded text-center">
                            <h3 class="text-info"><?= $total_cashiers ?></h3>
                            <small class="text-muted">Cashiers</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="p-3 bg-light rounded text-center">
                            <h3 class="text-warning"><?= $total_requests ?></h3>
                            <small class="text-muted">Total Requests</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="p-3 bg-light rounded text-center">
                            <h3 class="text-success"><?= formatCurrency($total_revenue) ?></h3>
                            <small class="text-muted">Total Revenue</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Request Status</h5></div>
            <div class="card-body">
                <div class="mb-3">
                    <label>Completed</label>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-success" style="width: <?= $total_requests > 0 ? ($completed/$total_requests)*100 : 0 ?>%"><?= $completed ?></div>
                    </div>
                </div>
                <div class="mb-3">
                    <label>Pending</label>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-warning" style="width: <?= $total_requests > 0 ? ($pending/$total_requests)*100 : 0 ?>%"><?= $pending ?></div>
                    </div>
                </div>
                <div class="mb-3">
                    <label>Other</label>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-info" style="width: <?= $total_requests > 0 ? (($total_requests-$completed-$pending)/$total_requests)*100 : 0 ?>%"><?= $total_requests - $completed - $pending ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Revenue by Category</h5></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr><th>Category</th><th>Requests</th><th>Revenue</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $c): ?>
                            <tr>
                                <td><?= htmlspecialchars($c['category_name']) ?></td>
                                <td><?= $c['count'] ?></td>
                                <td><?= formatCurrency($c['revenue']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Monthly Revenue</h5></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr><th>Month</th><th>Revenue</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($monthly as $m): ?>
                            <tr>
                                <td><?= htmlspecialchars($m['month']) ?></td>
                                <td><?= formatCurrency($m['total']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (count($monthly) == 0): ?><tr><td colspan="2" class="text-center text-muted">No data yet</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>