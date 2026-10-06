<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin('admin');

if (isset($_GET['action']) && isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];

    try {
        if ($_GET['action'] === 'activate') {
            $stmt = $db->prepare("UPDATE users SET status = 'active' WHERE user_id = ? AND role IN ('manager', 'collector')");
            $stmt->execute([$user_id]);
            createNotification($user_id, 'Account Activated', 'Your staff account has been activated. You can now login.', 'success');
            logActivity('Staff Activated', "Activated staff ID: $user_id");
            setSuccess("Staff account activated successfully.");
        } elseif ($_GET['action'] === 'deactivate') {
            $stmt = $db->prepare("UPDATE users SET status = 'inactive' WHERE user_id = ? AND role IN ('manager', 'collector')");
            $stmt->execute([$user_id]);
            logActivity('Staff Deactivated', "Deactivated staff ID: $user_id");
            setSuccess("Staff account deactivated successfully.");
        } elseif ($_GET['action'] === 'delete') {
            $stmt = $db->prepare("DELETE FROM users WHERE user_id = ? AND role IN ('manager', 'collector')");
            $stmt->execute([$user_id]);
            logActivity('Staff Deleted', "Deleted staff ID: $user_id");
            setSuccess("Staff account deleted successfully.");
        }
    } catch (PDOException $e) {
        setError("Error: " . $e->getMessage());
    }

    header("Location: staff.php");
    exit();
}

$staffMembers = [];
try {
    $stmt = $db->query("SELECT * FROM users WHERE role IN ('manager', 'collector') ORDER BY created_at DESC");
    $staffMembers = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Staff fetch error: " . $e->getMessage());
}

$title = 'Manage Staff';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2><i class="fas fa-user-shield me-2"></i>Manage Staff</h2>
            <p>Create and manage managers and collectors</p>
        </div>
        <a href="manage_staff.php" class="btn btn-light">
            <i class="fas fa-plus-circle me-1"></i> Add New Staff
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
                        <th>Role</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($staffMembers) > 0): ?>
                        <?php $count = 1; ?>
                        <?php foreach ($staffMembers as $member): ?>
                        <tr>
                            <td><?= $count++ ?></td>
                            <td><strong><?= htmlspecialchars($member['full_name']) ?></strong></td>
                            <td><?= htmlspecialchars($member['email']) ?></td>
                            <td>
                                <span class="badge bg-<?= $member['role'] === 'manager' ? 'primary' : 'info' ?>">
                                    <?= ucfirst($member['role']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($member['phone']) ?></td>
                            <td><?= getStatusBadge($member['status']) ?></td>
                            <td><small><?= $member['last_login'] ? date('d M Y H:i', strtotime($member['last_login'])) : 'Never' ?></small></td>
                            <td class="action-btns">
                                <a href="manage_staff.php?id=<?= $member['user_id'] ?>" class="btn btn-sm btn-info" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($member['status'] === 'active'): ?>
                                    <a href="?action=deactivate&id=<?= $member['user_id'] ?>" class="btn btn-sm btn-warning" title="Deactivate" data-confirm="Deactivate this staff account?">
                                        <i class="fas fa-ban"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="?action=activate&id=<?= $member['user_id'] ?>" class="btn btn-sm btn-success" title="Activate">
                                        <i class="fas fa-check"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="?action=delete&id=<?= $member['user_id'] ?>" class="btn btn-sm btn-danger" title="Delete" data-confirm="Delete this staff account permanently?">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">No staff accounts found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
