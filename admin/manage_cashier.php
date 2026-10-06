<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin('admin');

$edit_mode = false;
$cashier = null;
$permissions = null;
$error = '';
$success = '';

// Check if editing existing cashier
$cashier_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($cashier_id > 0) {
    $edit_mode = true;
    try {
        $stmt = $db->prepare("SELECT * FROM users WHERE user_id = ? AND role = 'cashier'");
        $stmt->execute([$cashier_id]);
        $cashier = $stmt->fetch();
        
        if (!$cashier) {
            setError("Cashier not found.");
            header("Location: cashiers.php");
            exit();
        }
        
        // Get permissions
        $stmt = $db->prepare("SELECT * FROM permissions WHERE user_id = ?");
        $stmt->execute([$cashier_id]);
        $permissions = $stmt->fetch();
    } catch (PDOException $e) {
        setError("Error: " . $e->getMessage());
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    $full_name = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Permissions
    $can_manage_requests = isset($_POST['can_manage_requests']) ? 1 : 0;
    $can_process_payments = isset($_POST['can_process_payments']) ? 1 : 0;
    $can_view_reports = isset($_POST['can_view_reports']) ? 1 : 0;
    $can_manage_customers = isset($_POST['can_manage_customers']) ? 1 : 0;
    $can_manage_collections = isset($_POST['can_manage_collections']) ? 1 : 0;
    $is_super_cashier = isset($_POST['is_super_cashier']) ? 1 : 0;
    
    if (empty($full_name) || empty($email) || empty($phone)) {
        $error = "Full name, email, and phone are required.";
    } elseif (!validateEmail($email)) {
        $error = "Please enter a valid email address.";
    } elseif (!$edit_mode && empty($password)) {
        $error = "Password is required for new cashiers.";
    } elseif (!$edit_mode && strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        try {
            if ($edit_mode) {
                // Update existing cashier
                if (!empty($password)) {
                    $hashed = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $db->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, password = ? WHERE user_id = ?");
                    $stmt->execute([$full_name, $email, $phone, $hashed, $cashier_id]);
                } else {
                    $stmt = $db->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE user_id = ?");
                    $stmt->execute([$full_name, $email, $phone, $cashier_id]);
                }
                
                // Handle Super Cashier assignment
                if ($is_super_cashier) {
                    assignSuperCashier($cashier_id, $_SESSION['user_id']);
                    setSuccess("Cashier updated. Super Cashier status granted.");
                } else {
                    // If was super cashier and now unchecked, remove the status
                    if ($permissions && $permissions['is_super_cashier']) {
                        removeSuperCashier($cashier_id);
                    }
                    // Update permissions
                    if ($permissions) {
                        $stmt = $db->prepare("UPDATE permissions SET can_manage_requests = ?, can_process_payments = ?, can_view_reports = ?, can_manage_customers = ?, can_manage_collections = ?, is_super_cashier = 0 WHERE user_id = ?");
                        $stmt->execute([$can_manage_requests, $can_process_payments, $can_view_reports, $can_manage_customers, $can_manage_collections, $cashier_id]);
                    } else {
                        $stmt = $db->prepare("INSERT INTO permissions (user_id, can_manage_requests, can_process_payments, can_view_reports, can_manage_customers, can_manage_collections, is_super_cashier, granted_by) VALUES (?, ?, ?, ?, ?, ?, 0, ?)");
                        $stmt->execute([$cashier_id, $can_manage_requests, $can_process_payments, $can_view_reports, $can_manage_customers, $can_manage_collections, $_SESSION['user_id']]);
                    }
                    setSuccess("Cashier updated successfully.");
                }
                
                logActivity('Cashier Updated', "Updated cashier: $full_name");
                
                header("Location: cashiers.php");
                exit();
            } else {
                // Check if email exists
                $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()['count'] > 0) {
                    $error = "A user with this email already exists.";
                } else {
                    // Create new cashier
                    $hashed = password_hash($password, PASSWORD_BCRYPT);
                    $username = explode('@', $email)[0];
                    
                    $stmt = $db->prepare("INSERT INTO users (username, email, password, full_name, phone, role, status) VALUES (?, ?, ?, ?, ?, 'cashier', 'active')");
                    $stmt->execute([$username, $email, $hashed, $full_name, $phone]);
                    $new_id = $db->lastInsertId();
                    
                    // Set permissions
                    $stmt = $db->prepare("INSERT INTO permissions (user_id, can_manage_requests, can_process_payments, can_view_reports, can_manage_customers, can_manage_collections, is_super_cashier, granted_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$new_id, $can_manage_requests, $can_process_payments, $can_view_reports, $can_manage_customers, $can_manage_collections, $is_super_cashier, $_SESSION['user_id']]);
                    
                    // If super cashier, use the assign function for logging/notification
                    if ($is_super_cashier) {
                        assignSuperCashier($new_id, $_SESSION['user_id']);
                    }
                    
                    logActivity('Cashier Created', "Created cashier: $full_name");
                    setSuccess("Cashier created successfully. They can now login with the provided credentials.");
                    header("Location: cashiers.php");
                    exit();
                }
            }
            
            if ($edit_mode) {
                header("Location: cashiers.php");
                exit();
            }
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

$title = $edit_mode ? 'Edit Cashier' : 'Add New Cashier';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2><i class="fas fa-<?= $edit_mode ? 'edit' : 'user-plus' ?> me-2"></i><?= $title ?></h2>
            <p><?= $edit_mode ? 'Update cashier details and permissions' : 'Create a new cashier account with permissions' ?></p>
        </div>
        <a href="cashiers.php" class="btn btn-light">
            <i class="fas fa-arrow-left me-1"></i> Back to Cashiers
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Account Information</h5>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <?= csrfField() ?>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="full_name" value="<?= $edit_mode ? htmlspecialchars($cashier['full_name']) : '' ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" value="<?= $edit_mode ? htmlspecialchars($cashier['email']) : '' ?>" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="phone" value="<?= $edit_mode ? htmlspecialchars($cashier['phone']) : '' ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Password <?= $edit_mode ? '<small class="text-muted">(leave blank to keep current)</small>' : '<span class="text-danger">*</span>' ?></label>
                            <input type="password" class="form-control" name="password" placeholder="<?= $edit_mode ? 'Leave blank to keep current' : 'Min 6 characters' ?>" <?= $edit_mode ? '' : 'required' ?>>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <h5 class="mb-3">Set Permissions</h5>
                    <p class="text-muted small mb-3">Select what this cashier is allowed to do:</p>
                    
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="can_manage_requests" id="can_manage_requests" value="1" <?= ($edit_mode && $permissions && $permissions['can_manage_requests']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="can_manage_requests">
                                    <strong>Manage Requests</strong><br>
                                    <small class="text-muted">View and update collection requests</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="can_process_payments" id="can_process_payments" value="1" <?= ($edit_mode && $permissions && $permissions['can_process_payments']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="can_process_payments">
                                    <strong>Process Payments</strong><br>
                                    <small class="text-muted">Accept and record payments</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="can_view_reports" id="can_view_reports" value="1" <?= ($edit_mode && $permissions && $permissions['can_view_reports']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="can_view_reports">
                                    <strong>View Reports</strong><br>
                                    <small class="text-muted">Access reporting features</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="can_manage_customers" id="can_manage_customers" value="1" <?= ($edit_mode && $permissions && $permissions['can_manage_customers']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="can_manage_customers">
                                    <strong>Manage Customers</strong><br>
                                    <small class="text-muted">View and manage customer information</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="can_manage_collections" id="can_manage_collections" value="1" <?= ($edit_mode && $permissions && $permissions['can_manage_collections']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="can_manage_collections">
                                    <strong>Manage Collections</strong><br>
                                    <small class="text-muted">Assign and manage waste collection teams</small>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <!-- Super Cashier Checkbox -->
                    <div class="mb-3 p-3 border border-warning rounded" style="background: #fff9e6;">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_super_cashier" id="is_super_cashier" value="1" <?= ($edit_mode && $permissions && $permissions['is_super_cashier']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_super_cashier">
                                <strong class="text-warning"><i class="fas fa-star me-1"></i>Super Cashier</strong><br>
                                <small class="text-muted">Grants ALL permissions. Only one super cashier can exist at a time.</small>
                            </label>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="cashiers.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> <?= $edit_mode ? 'Update Cashier' : 'Create Cashier' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Important Notes</h5>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li class="mb-2">Cashiers cannot register themselves.</li>
                    <li class="mb-2">Only admins can create cashier accounts.</li>
                    <li class="mb-2">Set appropriate permissions for each cashier.</li>
                    <li class="mb-2">Cashiers will receive their login credentials from you.</li>
                    <li class="mb-2">You can activate/deactivate cashiers anytime.</li>
                    <li class="mb-2">Deactivated cashiers cannot login.</li>
                    <li class="mb-2"><strong class="text-warning">Super Cashier</strong> has ALL permissions and can perform every cashier role.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
