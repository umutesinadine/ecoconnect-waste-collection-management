<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin('admin');

// Handle customer status toggle
if (isset($_GET['action']) && isset($_GET['id'])) {
    $customer_id = (int)$_GET['id'];
    
    try {
        if ($_GET['action'] === 'activate') {
            $stmt = $db->prepare("UPDATE users SET status = 'active' WHERE user_id = ? AND role = 'customer'");
            $stmt->execute([$customer_id]);
            createNotification($customer_id, 'Account Activated', 'Your account has been activated. You can now login and request waste collection.', 'success');
            logActivity('Customer Activated', "Activated customer ID: $customer_id");
            setSuccess("Customer activated successfully.");
        } elseif ($_GET['action'] === 'deactivate') {
            $stmt = $db->prepare("UPDATE users SET status = 'inactive' WHERE user_id = ? AND role = 'customer'");
            $stmt->execute([$customer_id]);
            createNotification($customer_id, 'Account Deactivated', 'Your account has been deactivated. Contact support for more info.', 'danger');
            logActivity('Customer Deactivated', "Deactivated customer ID: $customer_id");
            setSuccess("Customer deactivated successfully.");
        }
    } catch (PDOException $e) {
        setError("Error: " . $e->getMessage());
    }
    header("Location: customers.php");
    exit();
}

// Get all customers with their stats
$customers = [];
try {
    $stmt = $db->query("
        SELECT u.*, 
               (SELECT COUNT(*) FROM collection_requests WHERE customer_id = u.user_id) as total_requests,
               (SELECT COUNT(*) FROM collection_requests WHERE customer_id = u.user_id AND status = 'completed') as completed_requests,
               (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE customer_id = u.user_id AND payment_status = 'completed') as total_spent
        FROM users u
        WHERE u.role = 'customer'
        ORDER BY u.created_at DESC
    ");
    $customers = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Customer fetch error: " . $e->getMessage());
}

$title = 'Manage Customers';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2><i class="fas fa-users me-2"></i>Manage Customers</h2>
            <p>View, activate, and manage customer accounts</p>
        </div>
        <div>
            <span class="badge bg-success fs-6 me-2">Active: <?= count(array_filter($customers, fn($c) => $c['status'] === 'active')) ?></span>
            <span class="badge bg-warning text-dark fs-6">Pending: <?= count(array_filter($customers, fn($c) => $c['status'] === 'pending')) ?></span>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Requests</th>
                        <th>Completed</th>
                        <th>Total Spent</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($customers) > 0): ?>
                        <?php $count = 1; ?>
                        <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td><?= $count++ ?></td>
                            <td><strong><?= htmlspecialchars($customer['full_name']) ?></strong></td>
                            <td><?= htmlspecialchars($customer['email']) ?></td>
                            <td><?= htmlspecialchars($customer['phone']) ?></td>
                            <td><?= getStatusBadge($customer['status']) ?></td>
                            <td><span class="badge bg-info"><?= $customer['total_requests'] ?></span></td>
                            <td><span class="badge bg-success"><?= $customer['completed_requests'] ?></span></td>
                            <td><strong><?= formatCurrency($customer['total_spent']) ?></strong></td>
                            <td><small><?= $customer['last_login'] ? date('d M Y H:i', strtotime($customer['last_login'])) : 'Never' ?></small></td>
                            <td class="action-btns">
                                <?php if ($customer['status'] === 'active'): ?>
                                    <a href="?action=deactivate&id=<?= $customer['user_id'] ?>" class="btn btn-sm btn-warning" title="Deactivate" data-confirm="Deactivate this customer?">
                                        <i class="fas fa-ban"></i>
                                    </a>
                                <?php elseif ($customer['status'] === 'pending'): ?>
                                    <a href="?action=activate&id=<?= $customer['user_id'] ?>" class="btn btn-sm btn-success" title="Activate">
                                        <i class="fas fa-check-circle"></i> Approve
                                    </a>
                                    <a href="?action=deactivate&id=<?= $customer['user_id'] ?>" class="btn btn-sm btn-danger" title="Reject" data-confirm="Reject this customer?">
                                        <i class="fas fa-times"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="?action=activate&id=<?= $customer['user_id'] ?>" class="btn btn-sm btn-success" title="Activate">
                                        <i class="fas fa-check"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="10" class="text-center text-muted py-4">No customers registered yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>