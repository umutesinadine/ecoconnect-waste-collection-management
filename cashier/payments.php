<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin('cashier');

$user_id = $_SESSION['user_id'];
$is_super = isSuperCashier($user_id);

// Permission check
if (!$is_super && !hasPermission($user_id, 'can_process_payments')) {
    setError("You do not have permission to process payments. Contact your administrator.");
    header("Location: dashboard.php");
    exit();
}

// Process payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
    $request_id = (int)$_POST['request_id'];
    $amount = floatval($_POST['amount']);
    $method = $_POST['payment_method'];
    
    $payment_code = generatePaymentCode();
    $receipt = generateReceiptNumber();
    
    $req = $db->prepare("SELECT customer_id FROM collection_requests WHERE request_id = ?");
    $req->execute([$request_id]);
    $r = $req->fetch();
    
    $stmt = $db->prepare("INSERT INTO payments (payment_code, request_id, customer_id, amount, payment_method, payment_status, payment_date, processed_by, receipt_number) VALUES (?, ?, ?, ?, ?, 'completed', NOW(), ?, ?)");
    $stmt->execute([$payment_code, $request_id, $r['customer_id'], $amount, $method, $user_id, $receipt]);
    
    $db->prepare("UPDATE collection_requests SET status = 'completed', completed_at = NOW() WHERE request_id = ?")->execute([$request_id]);
    
    createNotification($r['customer_id'], 'Payment Received', "Payment of " . formatCurrency($amount) . " received. Receipt: $receipt", 'success', '../customer/payments.php');
    setSuccess("Payment processed. Receipt: $receipt");
    header("Location: payments.php");
    exit();
}

// Get completed requests eligible for payment
if ($is_super) {
    // Super cashier sees ALL completed requests without payments
    $completed = $db->query("SELECT cr.*, u.full_name as customer_name, wc.category_name FROM collection_requests cr JOIN users u ON cr.customer_id = u.user_id JOIN waste_categories wc ON cr.category_id = wc.category_id WHERE cr.status = 'completed' AND cr.request_id NOT IN (SELECT request_id FROM payments) ORDER BY cr.created_at DESC")->fetchAll();
} else {
    // Regular cashier sees only their own completed requests without payments
    $completed = $db->prepare("SELECT cr.*, u.full_name as customer_name, wc.category_name FROM collection_requests cr JOIN users u ON cr.customer_id = u.user_id JOIN waste_categories wc ON cr.category_id = wc.category_id WHERE cr.assigned_to = ? AND cr.status = 'completed' AND cr.request_id NOT IN (SELECT request_id FROM payments) ORDER BY cr.created_at DESC");
    $completed->execute([$user_id]);
    $completed = $completed->fetchAll();
}

// Get payment history
if ($is_super) {
    // Super cashier sees ALL payments
    $payments = $db->query("SELECT p.*, cr.request_code, u.full_name as customer_name FROM payments p JOIN collection_requests cr ON p.request_id = cr.request_id JOIN users u ON p.customer_id = u.user_id ORDER BY p.created_at DESC LIMIT 20")->fetchAll();
} else {
    // Regular cashier sees only their own payments
    $payments = $db->prepare("SELECT p.*, cr.request_code, u.full_name as customer_name FROM payments p JOIN collection_requests cr ON p.request_id = cr.request_id JOIN users u ON p.customer_id = u.user_id WHERE p.processed_by = ? ORDER BY p.created_at DESC LIMIT 20");
    $payments->execute([$user_id]);
    $payments = $payments->fetchAll();
}

$title = 'Process Payments';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2><i class="fas fa-money-bill-wave me-2"></i>Process Payments</h2>
            <p>Collect payments and issue receipts for completed collections</p>
        </div>
        <?php if ($is_super): ?>
            <span class="badge bg-warning text-dark fs-6"><i class="fas fa-star"></i> Super Cashier View</span>
        <?php endif; ?>
    </div>
</div>

<?php if (count($completed) > 0): ?>
<div class="card mb-4 border-success">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">Pending Payments to Collect (<?= count($completed) ?>)</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Request</th><th>Customer</th><th>Category</th><th>Amount</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($completed as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['request_code']) ?></td>
                        <td><?= htmlspecialchars($c['customer_name']) ?></td>
                        <td><?= htmlspecialchars($c['category_name']) ?></td>
                        <td><strong><?= formatCurrency($c['total_price']) ?></strong></td>
                        <td>
                            <button class="btn btn-sm btn-success pay-btn" data-id="<?= $c['request_id'] ?>" data-amount="<?= $c['total_price'] ?>" data-code="<?= htmlspecialchars($c['request_code']) ?>">
                                <i class="fas fa-cash-register"></i> Collect Payment
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><h5 class="mb-0"><?= $is_super ? 'All Payment History' : 'My Payment History' ?></h5></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Receipt</th><th>Request</th><th>Customer</th><th>Amount</th><th>Method</th><th>Date</th></tr>
                </thead>
                <tbody>
                    <?php if (count($payments) > 0): ?>
                        <?php foreach ($payments as $p): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($p['receipt_number'] ?? 'N/A') ?></strong></td>
                            <td><?= htmlspecialchars($p['request_code']) ?></td>
                            <td><?= htmlspecialchars($p['customer_name']) ?></td>
                            <td><?= formatCurrency($p['amount']) ?></td>
                            <td><?= ucwords(str_replace('_', ' ', $p['payment_method'])) ?></td>
                            <td><small><?= date('d M Y', strtotime($p['created_at'])) ?></small></td>
                            <td class="action-btns">
                                <a href="../receipt.php?id=<?= $p['payment_id'] ?>" class="btn btn-sm btn-info" title="View Receipt" target="_blank">
                                    <i class="fas fa-receipt"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No payments processed yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="payModal">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header"><h5>Collect Payment</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="process_payment" value="1">
                <input type="hidden" name="request_id" id="pay_request_id">
                <div class="mb-3">
                    <label class="form-label">Request Code</label>
                    <input type="text" class="form-control" id="pay_code" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Amount (RWF)</label>
                    <input type="number" class="form-control" name="amount" id="pay_amount" step="0.01" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Payment Method</label>
                    <select class="form-select" name="payment_method" required>
                        <option value="cash">Cash</option>
                        <option value="mobile_money">Mobile Money</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success">Confirm Payment</button>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.pay-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('pay_request_id').value = this.dataset.id;
        document.getElementById('pay_code').value = this.dataset.code;
        document.getElementById('pay_amount').value = this.dataset.amount;
        new bootstrap.Modal(document.getElementById('payModal')).show();
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
