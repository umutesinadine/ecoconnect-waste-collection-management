<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin('customer');

$categories = $db->query("SELECT * FROM waste_categories WHERE is_active = 1 ORDER BY category_name")->fetchAll();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = (int)($_POST['category_id'] ?? 0);
    $quantity = floatval($_POST['quantity'] ?? 0);
    $pickup_address = sanitize($_POST['pickup_address'] ?? '');
    $preferred_date = $_POST['preferred_date'] ?? '';
    $preferred_time = $_POST['preferred_time'] ?? '';
    $instructions = sanitize($_POST['instructions'] ?? '');
    
    if ($category_id <= 0 || $quantity <= 0 || empty($pickup_address) || empty($preferred_date)) {
        $error = "Please fill all required fields.";
    } else {
        try {
            // Get category price
            $stmt = $db->prepare("SELECT price_per_kg FROM waste_categories WHERE category_id = ?");
            $stmt->execute([$category_id]);
            $cat = $stmt->fetch();
            $total_price = $quantity * ($cat['price_per_kg'] ?? 0);
            
            $request_code = generateRequestCode();
            
            $stmt = $db->prepare("INSERT INTO collection_requests (request_code, customer_id, category_id, waste_quantity_kg, pickup_address, preferred_date, preferred_time, special_instructions, total_price, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$request_code, $_SESSION['user_id'], $category_id, $quantity, $pickup_address, $preferred_date, $preferred_time, $instructions, $total_price]);
            
            // Notify admin
            $admin = $db->query("SELECT user_id FROM users WHERE role = 'admin' LIMIT 1")->fetch();
            if ($admin) {
                createNotification($admin['user_id'], 'New Collection Request', "New request $request_code from {$_SESSION['full_name']}", 'info', '../admin/requests.php');
            }
            
            logActivity('Request Created', "Created collection request: $request_code");
            setSuccess("Request submitted successfully! Your request code is: $request_code");
            header("Location: my_requests.php");
            exit();
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

$title = 'New Collection Request';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h2><i class="fas fa-plus-circle me-2"></i>Request Waste Collection</h2>
    <p>Fill in the details below to schedule a waste pickup</p>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
                
                <form method="POST" action="">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Waste Category <span class="text-danger">*</span></label>
                            <select class="form-select" name="category_id" required>
                                <option value="">-- Select Category --</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['category_id'] ?>" data-price="<?= $cat['price_per_kg'] ?>">
                                    <?= htmlspecialchars($cat['category_name']) ?> (<?= formatCurrency($cat['price_per_kg']) ?>/kg)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Quantity (kg) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="quantity" step="0.1" min="0.1" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pickup Address <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="pickup_address" rows="2" required></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Preferred Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="preferred_date" min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Preferred Time</label>
                            <input type="time" class="form-control" name="preferred_time">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Special Instructions</label>
                        <textarea class="form-control" name="instructions" rows="3" placeholder="Any special instructions for the collection team..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-1"></i> Submit Request
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Pricing Info</h5></div>
            <div class="card-body">
                <p class="text-muted">Prices vary by waste category. The total will be calculated based on quantity × price per kg.</p>
                <hr>
                <h6>Available Categories:</h6>
                <ul class="list-unstyled">
                    <?php foreach ($categories as $cat): ?>
                    <li class="mb-2">
                        <strong><?= htmlspecialchars($cat['category_name']) ?></strong><br>
                        <small class="text-muted"><?= formatCurrency($cat['price_per_kg']) ?> per kg</small>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>