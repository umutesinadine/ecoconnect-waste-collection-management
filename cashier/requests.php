<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin('cashier');

$user_id = $_SESSION['user_id'];
$is_super = isSuperCashier($user_id);

// Permission check
if (!$is_super && !hasPermission($user_id, 'can_manage_requests')) {
    setError("You do not have permission to manage requests. Contact your administrator.");
    header("Location: dashboard.php");
    exit();
}

// Update request status
if (isset($_GET['update']) && isset($_GET['status'])) {
    $request_id = (int)$_GET['update'];
    $status = $_GET['status'];
    $valid_statuses = ['assigned', 'in_progress', 'completed'];
    
    if (in_array($status, $valid_statuses)) {
        $completed_at = $status === 'completed' ? date('Y-m-d H:i:s') : null;
        
        if ($is_super) {
            // Super cashier can update any request
            $stmt = $db->prepare("UPDATE collection_requests SET status = ?, completed_at = ? WHERE request_id = ?");
            $stmt->execute([$status, $completed_at, $request_id]);
        } else {
            // Regular cashier can only update their assigned requests
            $stmt = $db->prepare("UPDATE collection_requests SET status = ?, completed_at = ? WHERE request_id = ? AND assigned_to = ?");
            $stmt->execute([$status, $completed_at, $request_id, $user_id]);
        }
        
        // Notify customer
        $req = $db->prepare("SELECT customer_id, request_code FROM collection_requests WHERE request_id = ?");
        $req->execute([$request_id]);
        $r = $req->fetch();
        if ($r) {
            createNotification($r['customer_id'], "Request {$r['request_code']}", "Your request status updated to: $status", 'info', '../customer/my_requests.php');
        }
        
        setSuccess("Request status updated to: " . ucwords(str_replace('_', ' ', $status)));
        logActivity('Request Status Updated', "Updated request #$request_id to $status");
    }
    header("Location: requests.php");
    exit();
}

// Get requests - super cashier sees ALL, regular cashier sees only assigned
if ($is_super) {
    $requests = $db->query("SELECT cr.*, u.full_name as customer_name, u.phone as customer_phone, u.email as customer_email, u.address as customer_address, wc.category_name FROM collection_requests cr JOIN users u ON cr.customer_id = u.user_id JOIN waste_categories wc ON cr.category_id = wc.category_id ORDER BY cr.created_at DESC")->fetchAll();
} else {
    $requests = $db->prepare("SELECT cr.*, u.full_name as customer_name, u.phone as customer_phone, u.email as customer_email, u.address as customer_address, wc.category_name FROM collection_requests cr JOIN users u ON cr.customer_id = u.user_id JOIN waste_categories wc ON cr.category_id = wc.category_id WHERE cr.assigned_to = ? ORDER BY cr.created_at DESC");
    $requests->execute([$user_id]);
    $requests = $requests->fetchAll();
}

$title = 'Assigned Requests';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2><i class="fas fa-tasks me-2"></i><?= $is_super ? 'All Collection Requests' : 'Assigned Collection Requests' ?></h2>
            <p><?= $is_super ? 'View and update all collection requests across the system' : 'View and update the status of your assigned waste collection requests' ?></p>
        </div>
        <?php if ($is_super): ?>
            <span class="badge bg-warning text-dark fs-6"><i class="fas fa-star"></i> Super Cashier View</span>
        <?php endif; ?>
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
                        <th>Phone</th>
                        <th>Category</th>
                        <th>Qty</th>
                        <th>Address</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($requests) > 0): ?>
                        <?php foreach ($requests as $req): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($req['request_code']) ?></strong></td>
                            <td><?= htmlspecialchars($req['customer_name']) ?></td>
                            <td><?= htmlspecialchars($req['customer_phone']) ?></td>
                            <td><?= htmlspecialchars($req['category_name']) ?></td>
                            <td><?= $req['waste_quantity_kg'] ?> kg</td>
                            <td><small><?= htmlspecialchars(substr($req['pickup_address'], 0, 25)) ?>...</small></td>
                            <td><small><?= date('d M Y', strtotime($req['preferred_date'])) ?></small></td>
                            <td><?= getStatusBadge($req['status']) ?></td>
                            <td class="action-btns">
                                <?php if ($req['status'] === 'assigned' || $is_super && $req['status'] === 'pending'): ?>
                                    <a href="?update=<?= $req['request_id'] ?>&status=in_progress" class="btn btn-sm btn-primary">Start</a>
                                <?php elseif ($req['status'] === 'in_progress'): ?>
                                    <a href="?update=<?= $req['request_id'] ?>&status=completed" class="btn btn-sm btn-success">Complete</a>
                                <?php elseif ($req['status'] === 'pending' && !$is_super): ?>
                                    <a href="?update=<?= $req['request_id'] ?>&status=assigned" class="btn btn-sm btn-info">Accept</a>
                                <?php else: ?>
                                    <span class="text-muted">Done</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="9" class="text-center text-muted py-4"><?= $is_super ? 'No requests found.' : 'No requests assigned to you.' ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
