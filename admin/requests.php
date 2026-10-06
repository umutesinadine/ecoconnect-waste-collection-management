<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin('admin');

// Assign request to cashier
if (isset($_GET['assign']) && isset($_GET['cashier_id'])) {
    $request_id = (int)$_GET['assign'];
    $cashier_id = (int)$_GET['cashier_id'];
    $db->prepare("UPDATE collection_requests SET assigned_to = ?, status = 'assigned' WHERE request_id = ?")->execute([$cashier_id, $request_id]);
    createNotification($cashier_id, 'New Assignment', "A collection request has been assigned to you.", 'info', '../cashier/requests.php');
    setSuccess("Request assigned successfully.");
    logActivity('Request Assigned', "Assigned request #$request_id to cashier #$cashier_id");
    header("Location: requests.php");
    exit();
}

// Get all requests
$requests = $db->query("
    SELECT cr.*, u.full_name as customer_name, u.phone as customer_phone, 
           wc.category_name, a.full_name as assigned_name
    FROM collection_requests cr
    JOIN users u ON cr.customer_id = u.user_id
    JOIN waste_categories wc ON cr.category_id = wc.category_id
    LEFT JOIN users a ON cr.assigned_to = a.user_id
    ORDER BY cr.created_at DESC
")->fetchAll();

// Get available cashiers
$cashiers = $db->query("SELECT user_id, full_name FROM users WHERE role = 'cashier' AND status = 'active'")->fetchAll();

$title = 'All Requests';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2><i class="fas fa-clipboard-list me-2"></i>Collection Requests</h2>
        <p>Manage all waste collection requests</p>
    </div>
    <div>
        <span class="badge bg-warning text-dark fs-6 me-2">Pending: <?= count(array_filter($requests, fn($r) => $r['status'] === 'pending')) ?></span>
        <span class="badge bg-success fs-6">Completed: <?= count(array_filter($requests, fn($r) => $r['status'] === 'completed')) ?></span>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Customer</th>
                        <th>Category</th>
                        <th>Qty</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Assigned To</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($requests) > 0): ?>
                        <?php foreach ($requests as $req): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($req['request_code']) ?></strong></td>
                            <td><?= htmlspecialchars($req['customer_name']) ?><br><small class="text-muted"><?= htmlspecialchars($req['customer_phone']) ?></small></td>
                            <td><?= htmlspecialchars($req['category_name']) ?></td>
                            <td><?= $req['waste_quantity_kg'] ?> kg</td>
                            <td><?= formatCurrency($req['total_price']) ?></td>
                            <td><small><?= date('d M Y', strtotime($req['preferred_date'])) ?></small></td>
                            <td><?= $req['assigned_name'] ? htmlspecialchars($req['assigned_name']) : '<span class="text-muted">Unassigned</span>' ?></td>
                            <td><?= getStatusBadge($req['status']) ?></td>
                            <td>
                                <?php if ($req['status'] === 'pending' && count($cashiers) > 0): ?>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown">Assign</button>
                                    <ul class="dropdown-menu">
                                        <?php foreach ($cashiers as $cashier): ?>
                                        <li><a class="dropdown-item" href="?assign=<?= $req['request_id'] ?>&cashier_id=<?= $cashier['user_id'] ?>"><?= htmlspecialchars($cashier['full_name']) ?></a></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <?php elseif ($req['status'] === 'completed'): ?>
                                    <span class="text-success"><i class="fas fa-check-circle"></i> Done</span>
                                <?php else: ?>
                                    <span class="text-muted"><?= ucwords(str_replace('_', ' ', $req['status'])) ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="9" class="text-center text-muted py-4">No requests found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>