<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin('admin');

// Handle reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_reply'])) {
    $message_id = (int)$_POST['message_id'];
    $reply = sanitize($_POST['reply_message']);
    
    $stmt = $db->prepare("INSERT INTO replies (message_id, admin_id, reply_message) VALUES (?, ?, ?)");
    $stmt->execute([$message_id, $_SESSION['user_id'], $reply]);
    setSuccess("Reply sent successfully.");
    header("Location: messages.php");
    exit();
}

$messages = $db->query("
    SELECT cm.*, (SELECT COUNT(*) FROM replies WHERE message_id = cm.id) as reply_count 
    FROM contact_messages cm 
    ORDER BY cm.created_at DESC
")->fetchAll();

$title = 'Messages';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h2><i class="fas fa-comments me-2"></i>Contact Messages</h2>
    <p>View and reply to customer messages</p>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th>Replies</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($messages) > 0): $c = 1; ?>
                        <?php foreach ($messages as $msg): ?>
                        <tr>
                            <td><?= $c++ ?></td>
                            <td><?= htmlspecialchars($msg['name']) ?></td>
                            <td><?= htmlspecialchars($msg['email']) ?></td>
                            <td><?= htmlspecialchars($msg['phone'] ?? '-') ?></td>
                            <td><small><?= htmlspecialchars(substr($msg['message'], 0, 50)) ?>...</small></td>
                            <td><small><?= date('d M Y', strtotime($msg['created_at'])) ?></small></td>
                            <td><span class="badge bg-<?= $msg['reply_count'] > 0 ? 'success' : 'secondary' ?>"><?= $msg['reply_count'] ?></span></td>
                            <td>
                                <button class="btn btn-sm btn-primary reply-btn" data-id="<?= $msg['id'] ?>" data-email="<?= htmlspecialchars($msg['email']) ?>" data-name="<?= htmlspecialchars($msg['name']) ?>">
                                    <i class="fas fa-reply"></i> Reply
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">No messages yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Reply Modal -->
<div class="modal fade" id="replyModal">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header"><h5>Send Reply</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="send_reply" value="1">
                <input type="hidden" name="message_id" id="reply_msg_id">
                <div class="mb-3">
                    <label class="form-label">To</label>
                    <input type="text" class="form-control" id="reply_to" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Your Reply</label>
                    <textarea class="form-control" name="reply_message" rows="5" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success">Send Reply</button>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.reply-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('reply_msg_id').value = this.dataset.id;
        document.getElementById('reply_to').value = this.dataset.name + ' (' + this.dataset.email + ')';
        new bootstrap.Modal(document.getElementById('replyModal')).show();
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>