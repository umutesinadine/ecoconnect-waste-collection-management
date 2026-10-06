<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin('customer');

// Handle cancellation
if (isset($_GET['cancel']) && isset($_GET['id'])) {
    $request_id = (int)$_GET['id'];
    try {
        $stmt = $db->prepare("UPDATE collection_requests SET status = 'cancelled' WHERE request_id = ? AND customer_id = ? AND status = 'pending'");
        $stmt->execute([$request_id, $_SESSION['user_id']]);
        if ($stmt->rowCount() > 0) {
            setSuccess("Request cancelled successfully.");
            logActivity('Request Cancelled', "Customer cancelled request #$request_id");
        } else {
            setError("Cannot cancel this request. Only pending requests can be cancelled.");
        }
    } catch (PDOException $e) {
        setError("Error cancelling request.");
    }
    header("Location: my_requests.php");
    exit();
}

$requests = $db->prepare("SELECT cr.*, wc.category_name FROM collection_requests cr JOIN waste_categories wc ON cr.category_id = wc.category_id WHERE cr.customer_id = ? ORDER BY cr.created_at DESC");
$requests->execute([$_SESSION['user_id']]);
$requests = $requests->fetchAll();

$title = 'My Requests';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2><i class="fas fa-clipboard-check me-2"></i>My Collection Requests</h2>
        <p>Track the status of your waste collection requests</p>
    </div>
    <a href="request.php" class="btn btn-light"><i class="fas fa-plus me-1"></i>New Request</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Category</th>
                        <th>Quantity</th>
                        <th>Pickup Date</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($requests) > 0): ?>
                        <?php foreach ($requests as $req): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($req['request_code']) ?></strong></td>
                            <td><?= htmlspecialchars($req['category_name']) ?></td>
                            <td><?= $req['waste_quantity_kg'] ?> kg</td>
                            <td><small><?= date('d M Y', strtotime($req['preferred_date'])) ?></small></td>
                            <td><small><?= htmlspecialchars(substr($req['pickup_address'], 0, 30)) ?>...</small></td>
                            <td><?= getStatusBadge($req['status']) ?></td>
                            <td><?= formatCurrency($req['total_price']) ?></td>
                            <td><small><?= date('d M Y', strtotime($req['created_at'])) ?></small></td>
                            <td class="action-btns">
                                <?php if ($req['status'] === 'pending'): ?>
                                <a href="?cancel=1&id=<?= $req['request_id'] ?>" class="btn btn-sm btn-danger" title="Cancel Request" data-confirm="Cancel this request?">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                                <?php elseif ($req['status'] === 'completed'): ?>
                                <span class="text-success"><i class="fas fa-check-circle"></i></span>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="9" class="text-center text-muted py-4">No requests yet. <a href="request.php">Submit your first request</a></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>