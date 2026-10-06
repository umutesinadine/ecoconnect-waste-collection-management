<?php
// ============================================================
// EcoConnect - Cashier Follow-up Messages
// ============================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin('cashier');

$user_id = $_SESSION['user_id'];
$is_super = isSuperCashier($user_id);

// Permission check
if (!$is_super && !hasPermission($user_id, 'can_manage_customers')) {
    setError("You do not have permission to manage customers. Contact your administrator.");
    header("Location: dashboard.php");
    exit();
}

// Send follow-up message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $customer_id = (int)$_POST['customer_id'];
    $message = sanitize($_POST['message'] ?? '');
    
    if (!empty($message) && $customer_id > 0) {
        try {
            // Store the message as a notification to the customer
            $title = "Follow-up from Cashier: " . $_SESSION['full_name'];
            createNotification($customer_id, $title, $message, 'info', '../customer/messages.php');
            
            logActivity('Follow-up Sent', "Sent follow-up to customer #$customer_id");
            setSuccess("Follow-up message sent to customer.");
        } catch (PDOException $e) {
            setError("Error sending message: " . $e->getMessage());
        }
    } else {
        setError("Please enter a message and select a customer.");
    }
    header("Location: followup.php");
    exit();
}

// Get customers - super cashier sees ALL, regular cashier sees only assigned
$customers = [];
try {
    if ($is_super) {
        // Super cashier sees ALL customers
        $stmt = $db->query("
            SELECT DISTINCT u.user_id, u.full_name, u.email, u.phone, u.address,
                   (SELECT COUNT(*) FROM collection_requests WHERE customer_id = u.user_id) as request_count,
                   (SELECT MAX(created_at) FROM collection_requests WHERE customer_id = u.user_id) as last_request
            FROM collection_requests cr
            JOIN users u ON cr.customer_id = u.user_id
            ORDER BY u.full_name ASC
        ");
        $customers = $stmt->fetchAll();
    } else {
        // Regular cashier sees only assigned customers
        $stmt = $db->prepare("
            SELECT DISTINCT u.user_id, u.full_name, u.email, u.phone, u.address,
                   (SELECT COUNT(*) FROM collection_requests WHERE customer_id = u.user_id AND assigned_to = ?) as request_count,
                   (SELECT MAX(created_at) FROM collection_requests WHERE customer_id = u.user_id AND assigned_to = ?) as last_request
            FROM collection_requests cr
            JOIN users u ON cr.customer_id = u.user_id
            WHERE cr.assigned_to = ?
            ORDER BY u.full_name ASC
        ");
        $stmt->execute([$user_id, $user_id, $user_id]);
        $customers = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    error_log("Followup customers error: " . $e->getMessage());
}

$title = 'Follow-up Messages';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2><i class="fas fa-paper-plane me-2"></i>Follow-up Messages</h2>
            <p><?= $is_super ? 'Send follow-up messages to all customers' : 'Send follow-up messages to your assigned customers' ?></p>
        </div>
        <?php if ($is_super): ?>
            <span class="badge bg-warning text-dark fs-6"><i class="fas fa-star"></i> Super Cashier View</span>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i><?= $is_super ? 'All Customers' : 'Your Customers' ?></h5>
            </div>
            <div class="card-body p-0">
                <?php if (count($customers) > 0): ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($customers as $customer): ?>
                    <div class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1"><?= htmlspecialchars($customer['full_name']) ?></h6>
                            <small class="text-muted"><?= $customer['request_count'] ?> requests</small>
                        </div>
                        <p class="mb-1 small text-muted"><?= htmlspecialchars($customer['email']) ?> | <?= htmlspecialchars($customer['phone']) ?></p>
                        <button class="btn btn-sm btn-outline-primary mt-1 followup-btn" 
                                data-id="<?= $customer['user_id'] ?>" 
                                data-name="<?= htmlspecialchars($customer['full_name']) ?>">
                            <i class="fas fa-envelope me-1"></i> Send Message
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center text-muted py-4">
                    <i class="fas fa-users fa-3x mb-3"></i>
                    <p>No customers found.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-comments me-2"></i>Quick Tips</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="fas fa-lightbulb me-2"></i>Effective Follow-ups</h6>
                    <ul class="mb-0">
                        <li class="mb-2">Confirm upcoming collection schedules with customers</li>
                        <li class="mb-2">Notify customers about any delays or changes</li>
                        <li class="mb-2">Send reminders for pending payments</li>
                        <li class="mb-2">Request feedback after completed collections</li>
                        <li class="mb-2">Address any customer concerns promptly</li>
                    </ul>
                </div>
                <div class="mt-3">
                    <h6><i class="fas fa-history me-2"></i>Recent Activity</h6>
                    <p class="text-muted small">Messages are sent as notifications to customers. They will see them in their dashboard.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Message Modal -->
<div class="modal fade" id="messageModal">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5><i class="fas fa-envelope me-2"></i>Send Message to <span id="modal_customer_name"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="send_message" value="1">
                <input type="hidden" name="customer_id" id="modal_customer_id">
                <div class="mb-3">
                    <label class="form-label fw-bold">Message</label>
                    <textarea class="form-control" name="message" rows="6" placeholder="Type your follow-up message here..." required></textarea>
                </div>
                <div class="alert alert-warning small">
                    <i class="fas fa-info-circle me-1"></i> This message will be sent as a notification to the customer.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane me-1"></i> Send Message
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.followup-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('modal_customer_id').value = this.dataset.id;
        document.getElementById('modal_customer_name').textContent = this.dataset.name;
        new bootstrap.Modal(document.getElementById('messageModal')).show();
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
