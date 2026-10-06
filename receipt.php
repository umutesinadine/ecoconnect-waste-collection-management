<?php
// ============================================================
// EcoConnect - Professional Printable Receipt
// ============================================================
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$receipt_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : 'view'; // view, print, pdf

if ($receipt_id <= 0) {
    die("Invalid receipt.");
}

// Get payment with full details
try {
    $stmt = $db->prepare("
        SELECT p.*, 
               u.full_name as customer_name, u.email as customer_email, u.phone as customer_phone, u.address as customer_address,
               cr.request_code, cr.waste_quantity_kg, cr.pickup_address, cr.preferred_date, cr.preferred_time,
               wc.category_name, wc.price_per_kg,
               proc.full_name as processed_by_name
        FROM payments p
        JOIN users u ON p.customer_id = u.user_id
        JOIN collection_requests cr ON p.request_id = cr.request_id
        JOIN waste_categories wc ON cr.category_id = wc.category_id
        LEFT JOIN users proc ON p.processed_by = proc.user_id
        WHERE p.payment_id = ?
    ");
    $stmt->execute([$receipt_id]);
    $receipt = $stmt->fetch();
    
    if (!$receipt) {
        die("Receipt not found.");
    }
    
    // Check authorization
    $current_role = $_SESSION['role'] ?? '';
    $current_user_id = $_SESSION['user_id'] ?? 0;
    
    $authorized = false;
    if ($current_role === 'admin') {
        $authorized = true;
    } elseif ($current_role === 'cashier') {
        $authorized = true;
    } elseif ($current_role === 'customer' && $receipt['customer_id'] == $current_user_id) {
        $authorized = true;
    }
    
    if (!$authorized) {
        die("You are not authorized to view this receipt.");
    }
    
} catch (PDOException $e) {
    error_log("Receipt fetch error: " . $e->getMessage());
    die("Error loading receipt.");
}

// Get system settings
$site_name = 'EcoConnect';
$site_email = '';
$site_phone = '';
$site_address = '';
try {
    $settings = $db->query("SELECT * FROM system_settings")->fetchAll();
    foreach ($settings as $s) {
        if ($s['setting_key'] === 'site_name') $site_name = $s['setting_value'];
        if ($s['setting_key'] === 'site_email') $site_email = $s['setting_value'];
        if ($s['setting_key'] === 'site_phone') $site_phone = $s['setting_value'];
        if ($s['setting_key'] === 'site_address') $site_address = $s['setting_value'];
    }
} catch (PDOException $e) {}

$payment_methods = [
    'cash' => 'Cash',
    'mobile_money' => 'Mobile Money',
    'bank_transfer' => 'Bank Transfer',
    'card' => 'Card'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - <?= htmlspecialchars($receipt['receipt_number'] ?? 'N/A') ?> - EcoConnect</title>
    <link rel="icon" type="image/jpg" sizes="32x32" href="favicon-32x32.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @page {
            size: A4;
            margin: 10mm;
        }
        body {
            background: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .receipt-wrapper {
            max-width: 800px;
            margin: 40px auto;
        }
        .receipt-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .receipt-header {
            background: linear-gradient(135deg, #2e7d32, #1a936f);
            color: white;
            padding: 30px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .receipt-header .logo-area {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .receipt-header .logo-area h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 700;
        }
        .receipt-header .receipt-id {
            text-align: right;
        }
        .receipt-header .receipt-id h3 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
        }
        .receipt-header .receipt-id small {
            opacity: 0.9;
        }
        .receipt-body {
            padding: 40px;
        }
        .receipt-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        .receipt-details .label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #999;
            font-weight: 600;
        }
        .receipt-details .value {
            font-size: 1rem;
            font-weight: 500;
            color: #333;
        }
        .receipt-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .receipt-table th {
            background: #f8f9fa;
            padding: 12px 15px;
            text-align: left;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #666;
            border-bottom: 2px solid #2e7d32;
        }
        .receipt-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }
        .receipt-table tfoot td {
            font-weight: 600;
            border-bottom: none;
        }
        .receipt-total {
            text-align: right;
            padding-top: 20px;
            border-top: 2px solid #2e7d32;
        }
        .receipt-total .total-label {
            font-size: 1rem;
            color: #666;
        }
        .receipt-total .total-amount {
            font-size: 2rem;
            font-weight: 700;
            color: #2e7d32;
        }
        .receipt-footer {
            margin-top: 30px;
            text-align: center;
            color: #999;
            font-size: 0.85rem;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .receipt-footer p {
            margin: 5px 0;
        }
        .action-bar {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-top: 1px solid #eee;
        }
        .action-bar .btn {
            margin: 0 5px;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .status-badge.completed {
            background: #e8f5e9;
            color: #2e7d32;
        }
        @media print {
            body {
                background: white;
            }
            .receipt-wrapper {
                margin: 0;
                max-width: 100%;
            }
            .action-bar, .no-print {
                display: none !important;
            }
            .receipt-card {
                box-shadow: none;
                border-radius: 0;
            }
            .receipt-header {
                background: #2e7d32 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .receipt-table th {
                background: #f8f9fa !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-wrapper">
        <div class="receipt-card">
            <!-- Header -->
            <div class="receipt-header">
                <div class="logo-area">
                    <img src="images/gisellelogo.png" alt="EcoConnect" height="50">
                    <div>
                        <h1><?= htmlspecialchars($site_name) ?></h1>
                        <small>Waste Management Solutions</small>
                    </div>
                </div>
                <div class="receipt-id">
                    <h3>PAYMENT RECEIPT</h3>
                    <small>#<?= htmlspecialchars($receipt['receipt_number'] ?? 'N/A') ?></small>
                </div>
            </div>
            
            <!-- Body -->
            <div class="receipt-body">
                <div class="receipt-details">
                    <div>
                        <div class="label">Customer Information</div>
                        <div class="value"><?= htmlspecialchars($receipt['customer_name']) ?></div>
                        <div class="text-muted small"><?= htmlspecialchars($receipt['customer_email']) ?></div>
                        <div class="text-muted small"><?= htmlspecialchars($receipt['customer_phone']) ?></div>
                        <div class="text-muted small"><?= htmlspecialchars($receipt['customer_address']) ?></div>
                    </div>
                    <div>
                        <div class="label">Payment Details</div>
                        <div class="value">Request: <strong><?= htmlspecialchars($receipt['request_code']) ?></strong></div>
                        <div class="text-muted small">Date: <?= date('d F Y H:i', strtotime($receipt['payment_date'] ?? $receipt['created_at'])) ?></div>
                        <div class="text-muted small">Payment Code: <?= htmlspecialchars($receipt['payment_code']) ?></div>
                        <div class="text-muted small">
                            Method: <?= htmlspecialchars($payment_methods[$receipt['payment_method']] ?? $receipt['payment_method']) ?>
                        </div>
                        <div class="text-muted small">
                            Status: <span class="status-badge completed">Paid</span>
                        </div>
                    </div>
                </div>
                
                <!-- Items Table -->
                <table class="receipt-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Waste Collection Service</td>
                            <td><?= htmlspecialchars($receipt['category_name']) ?></td>
                            <td><?= number_format($receipt['waste_quantity_kg'], 2) ?> kg</td>
                            <td><?= formatCurrency($receipt['price_per_kg']) ?></td>
                            <td class="text-end"><?= formatCurrency($receipt['amount']) ?></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end"><strong>Total Amount Paid:</strong></td>
                            <td class="text-end" style="font-size:1.2rem; font-weight:700; color:#2e7d32;">
                                <?= formatCurrency($receipt['amount']) ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
                
                <div class="receipt-total">
                    <div class="total-label">Total Amount</div>
                    <div class="total-amount"><?= formatCurrency($receipt['amount']) ?></div>
                    <div class="text-muted small">Payment Method: <?= htmlspecialchars($payment_methods[$receipt['payment_method']] ?? $receipt['payment_method']) ?></div>
                </div>
                
                <div class="receipt-footer">
                    <p><strong><?= htmlspecialchars($site_name) ?></strong></p>
                    <p><?= htmlspecialchars($site_address) ?></p>
                    <p>Email: <?= htmlspecialchars($site_email) ?> | Phone: <?= htmlspecialchars($site_phone) ?></p>
                    <p>Processed by: <?= htmlspecialchars($receipt['processed_by_name'] ?? 'System') ?></p>
                    <p class="mt-2"><small>This is a computer-generated receipt. No signature required.</small></p>
                </div>
            </div>
            
            <!-- Action Bar -->
            <div class="action-bar">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fas fa-print me-1"></i> Print Receipt
                </button>
                <button onclick="downloadPDF()" class="btn btn-success">
                    <i class="fas fa-download me-1"></i> Download PDF
                </button>
                <a href="javascript:window.close()" class="btn btn-secondary">
                    <i class="fas fa-times me-1"></i> Close
                </a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function downloadPDF() {
        // Use the browser's print function with "Save as PDF" destination
        window.print();
    }
    
    // Auto print if print action is specified
    <?php if ($action === 'print'): ?>
    window.onload = function() {
        setTimeout(function() { window.print(); }, 500);
    };
    <?php endif; ?>
    </script>
</body>
</html>