<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin('admin');

// Handle cashier status toggle
if (isset($_GET['action']) && isset($_GET['id'])) {
    $cashier_id = (int)$_GET['id'];
    
    try {
        if ($_GET['action'] === 'activate') {
            $stmt = $db->prepare("UPDATE users SET status = 'active' WHERE user_id = ? AND role = 'cashier'");
            $stmt->execute([$cashier_id]);
            createNotification($cashier_id, 'Account Activated', 'Your cashier account has been activated. You can now login.', 'success');
            logActivity('Cashier Activated', "Activated cashier ID: $cashier_id");
            setSuccess("Cashier activated successfully.");
        } elseif ($_GET['action'] === 'deactivate') {
            $stmt = $db->prepare("UPDATE users SET status = 'inactive' WHERE user_id = ? AND role = 'cashier'");
            $stmt->execute([$cashier_id]);
            logActivity('Cashier Deactivated', "Deactivated cashier ID: $cashier_id");
            setSuccess("Cashier deactivated successfully.");
        } elseif ($_GET['action'] === 'delete') {
            $stmt = $db->prepare("DELETE FROM users WHERE user_id = ? AND role = 'cashier'");
            $stmt->execute([$cashier_id]);
            logActivity('Cashier Deleted', "Deleted cashier ID: $cashier_id");
            setSuccess("Cashier deleted successfully.");
        } elseif ($_GET['action'] === 'assign_super') {
            if (assignSuperCashier($cashier_id, $_SESSION['user_id'])) {
                setSuccess("Super Cashier assigned successfully.");
            } else {
                setError("Failed to assign Super Cashier.");
            }
        } elseif ($_GET['action'] === 'remove_super') {
            if (removeSuperCashier($cashier_id)) {
                setSuccess("Super Cashier status removed.");
            } else {
                setError("Failed to remove Super Cashier status.");
            }
        }
    } catch (PDOException $e) {
        setError("Error: " . $e->getMessage());
    }
    header("Location: cashiers.php");
    exit();
}

// Get all cashiers
$cashiers = [];
try {
    $stmt = $db->query("
        SELECT u.*, p.permission_id, p.can_manage_requests, p.can_process_payments, 
               p.can_view_reports, p.can_manage_customers, p.can_manage_collections,
               p.is_super_cashier
        FROM users u
        LEFT JOIN permissions p ON u.user_id = p.user_id
        WHERE u.role = 'cashier'
        ORDER BY u.created_at DESC
    ");
    $cashiers = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Cashier fetch error: " . $e->getMessage());
}

$title = 'Manage Cashiers';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2><i class="fas fa-user-tie me-2"></i>Manage Cashiers</h2>
            <p>Create, manage permissions, and control cashier accounts</p>
        </div>
        <a href="manage_cashier.php" class="btn btn-light">
            <i class="fas fa-plus-circle me-1"></i> Add New Cashier
        </a>
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
                        <th>Permissions</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($cashiers) > 0): ?>
                        <?php $count = 1; ?>
                        <?php foreach ($cashiers as $cashier): ?>
                        <tr>
                            <td><?= $count++ ?></td>
                            <td>
                                <strong><?= htmlspecialchars($cashier['full_name']) ?></strong>
                                <?php if ($cashier['is_super_cashier']): ?>
                                    <span class="badge bg-warning text-dark ms-1"><i class="fas fa-star"></i> Super Cashier</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($cashier['email']) ?></td>
                            <td><?= htmlspecialchars($cashier['phone']) ?></td>
                            <td><?= getStatusBadge($cashier['status']) ?></td>
                            <td>
                                <small>
                                    <?php if ($cashier['can_manage_requests']): ?><span class="badge bg-info me-1">Requests</span><?php endif; ?>
                                    <?php if ($cashier['can_process_payments']): ?><span class="badge bg-success me-1">Payments</span><?php endif; ?>
                                    <?php if ($cashier['can_view_reports']): ?><span class="badge bg-warning text-dark me-1">Reports</span><?php endif; ?>
                                    <?php if ($cashier['can_manage_customers']): ?><span class="badge bg-primary me-1">Customers</span><?php endif; ?>
                                    <?php if ($cashier['can_manage_collections']): ?><span class="badge bg-secondary me-1">Collections</span><?php endif; ?>
                                    <?php if (!$cashier['can_manage_requests'] && !$cashier['can_process_payments'] && !$cashier['can_view_reports'] && !$cashier['can_manage_customers'] && !$cashier['can_manage_collections']): ?>
                                        <span class="text-muted">No permissions set</span>
                                    <?php endif; ?>
                                </small>
                            </td>
                            <td><small><?= $cashier['last_login'] ? date('d M Y H:i', strtotime($cashier['last_login'])) : 'Never' ?></small></td>
                            <td class="action-btns">
                                <a href="manage_cashier.php?id=<?= $cashier['user_id'] ?>" class="btn btn-sm btn-info" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($cashier['is_super_cashier']): ?>
                                    <a href="?action=remove_super&id=<?= $cashier['user_id'] ?>" class="btn btn-sm btn-warning" title="Remove Super Cashier" data-confirm="Remove Super Cashier status from this cashier?">
                                        <i class="fas fa-star-of-david"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="?action=assign_super&id=<?= $cashier['user_id'] ?>" class="btn btn-sm btn-success" title="Assign as Super Cashier" data-confirm="Assign this cashier as Super Cashier? This will grant ALL permissions.">
                                        <i class="fas fa-star"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if ($cashier['status'] === 'active'): ?>
                                    <a href="?action=deactivate&id=<?= $cashier['user_id'] ?>" class="btn btn-sm btn-warning" title="Deactivate" data-confirm="Deactivate this cashier?">
                                        <i class="fas fa-ban"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="?action=activate&id=<?= $cashier['user_id'] ?>" class="btn btn-sm btn-success" title="Activate">
                                        <i class="fas fa-check"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="?action=delete&id=<?= $cashier['user_id'] ?>" class="btn btn-sm btn-danger" title="Delete" data-confirm="Delete this cashier permanently?">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">No cashiers found. Click "Add New Cashier" to create one.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
