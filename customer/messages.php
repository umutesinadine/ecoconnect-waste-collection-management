<?php
// ============================================================
// EcoConnect - Customer Messages & Notifications
// ============================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin('customer');

// Mark notification as read
if (isset($_GET['read']) && isset($_GET['id'])) {
    $notification_id = (int)$_GET['id'];
    $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND user_id = ?");
    $stmt->execute([$notification_id, $_SESSION['user_id']]);
    header("Location: messages.php");
    exit();
}

// Mark all as read
if (isset($_GET['read_all'])) {
    $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? OR user_id IS NULL");
    $stmt->execute([$_SESSION['user_id']]);
    setSuccess("All notifications marked as read.");
    header("Location: messages.php");
    exit();
}

// Get all notifications for this customer
$notifications = [];
try {
    $stmt = $db->prepare("
        SELECT * FROM notifications 
        WHERE (user_id = ? OR user_id IS NULL) 
        ORDER BY created_at DESC 
        LIMIT 50
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $notifications = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Notifications fetch error: " . $e->getMessage());
}

$title = 'Messages & Notifications';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2><i class="fas fa-envelope me-2"></i>Messages & Notifications</h2>
            <p>View all your system notifications and messages</p>
        </div>
        <a href="?read_all=1" class="btn btn-light">
            <i class="fas fa-check-double me-1"></i> Mark All Read
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (count($notifications) > 0): ?>
        <div class="list-group list-group-flush">
            <?php foreach ($notifications as $notif): ?>
            <div class="list-group-item list-group-item-action <?= $notif['is_read'] ? '' : 'list-group-item-primary' ?>">
                <div class="d-flex w-100 justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center mb-1">
                            <?php 
                            $type_icons = [
                                'info' => 'fa-info-circle text-info',
                                'success' => 'fa-check-circle text-success',
                                'warning' => 'fa-exclamation-triangle text-warning',
                                'danger' => 'fa-times-circle text-danger'
                            ];
                            $icon = $type_icons[$notif['type']] ?? 'fa-bell text-secondary';
                            ?>
                            <i class="fas <?= $icon ?> me-2"></i>
                            <strong><?= htmlspecialchars($notif['title']) ?></strong>
                            <?php if (!$notif['is_read']): ?>
                            <span class="badge bg-primary ms-2">New</span>
                            <?php endif; ?>
                        </div>
                        <p class="mb-1"><?= htmlspecialchars($notif['message']) ?></p>
                        <small class="text-muted">
                            <i class="far fa-clock me-1"></i><?= timeAgo($notif['created_at']) ?>
                        </small>
                    </div>
                    <div class="ms-3">
                        <?php if ($notif['link']): ?>
                        <a href="<?= htmlspecialchars($notif['link']) ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        <?php endif; ?>
                        <?php if (!$notif['is_read']): ?>
                        <a href="?read=1&id=<?= $notif['notification_id'] ?>" class="btn btn-sm btn-outline-secondary mt-1" title="Mark as read">
                            <i class="fas fa-check"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center text-muted py-5">
            <i class="fas fa-bell fa-4x mb-3"></i>
            <h5>No Notifications</h5>
            <p>You don't have any notifications yet. They will appear here when you receive updates.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>