<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin('customer');

$payments = $db->prepare("
    SELECT p.*, cr.request_code, wc.category_name 
    FROM payments p
    JOIN collection_requests cr ON p.request_id = cr.request_id
    JOIN waste_categories wc ON cr.category_id = wc.category_id
    WHERE p.customer_id = ?
    ORDER BY p.created_at DESC
");
$payments->execute([$_SESSION['user_id']]);
$payments = $payments->fetchAll();

$title = 'Payment History';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h2><i class="fas fa-receipt me-2"></i>My Payment History</h2>
    <p>View your payment records and receipts</p>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Receipt #</th>
                        <th>Request</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($payments) > 0): ?>
                        <?php foreach ($payments as $p): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($p['receipt_number'] ?? 'N/A') ?></strong></td>
                            <td><?= htmlspecialchars($p['request_code']) ?></td>
                            <td><?= htmlspecialchars($p['category_name']) ?></td>
                            <td><?= formatCurrency($p['amount']) ?></td>
                            <td><?= ucwords(str_replace('_', ' ', $p['payment_method'])) ?></td>
                            <td><?= getStatusBadge($p['payment_status']) ?></td>
                            <td><small><?= date('d M Y', strtotime($p['payment_date'] ?? $p['created_at'])) ?></small></td>
                            <td class="action-btns">
                                <a href="../receipt.php?id=<?= $p['payment_id'] ?>" class="btn btn-sm btn-info" title="View Receipt" target="_blank">
                                    <i class="fas fa-receipt"></i> Receipt
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No payments yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>