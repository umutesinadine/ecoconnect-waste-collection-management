<?php
// ============================================================
// EcoConnect - Cashier Profile & Password Change
// ============================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin('cashier');

$user_id = $_SESSION['user_id'];

// Get current user data
$stmt = $db->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (!empty($password)) {
        if (strlen($password) < 6) {
            setError("Password must be at least 6 characters.");
        } elseif ($password !== $confirm_password) {
            setError("Passwords do not match.");
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("UPDATE users SET full_name = ?, phone = ?, password = ? WHERE user_id = ?");
            $stmt->execute([$full_name, $phone, $hashed, $user_id]);
            setSuccess("Profile and password updated successfully.");
            logActivity('Profile Updated', "Cashier updated their profile");
        }
    } else {
        $stmt = $db->prepare("UPDATE users SET full_name = ?, phone = ? WHERE user_id = ?");
        $stmt->execute([$full_name, $phone, $user_id]);
        setSuccess("Profile updated successfully.");
    }
    
    // Update session
    $_SESSION['full_name'] = $full_name;
    $_SESSION['phone'] = $phone;
    
    header("Location: profile.php");
    exit();
}

$title = 'My Profile';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h2><i class="fas fa-user me-2"></i>My Profile</h2>
    <p>Update your personal information and password</p>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Account Information</h5></div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Full Name</label>
                            <input type="text" class="form-control" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Phone</label>
                            <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Role</label>
                            <input type="text" class="form-control" value="Cashier" readonly>
                        </div>
                    </div>
                    <hr>
                    <h5 class="mb-3">Change Password</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">New Password</label>
                            <input type="password" class="form-control" name="password" placeholder="Leave blank to keep current">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Confirm Password</label>
                            <input type="password" class="form-control" name="confirm_password" placeholder="Confirm new password">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update Profile</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Account Info</h5></div>
            <div class="card-body text-center">
                <i class="fas fa-user-circle fa-5x text-success mb-3"></i>
                <h4><?= htmlspecialchars($user['full_name']) ?></h4>
                <p class="text-muted"><?= htmlspecialchars($user['email']) ?></p>
                <p>Status: <?= getStatusBadge($user['status']) ?></p>
                <p><small>Last login: <?= $user['last_login'] ? date('d M Y H:i', strtotime($user['last_login'])) : 'Never' ?></small></p>
                <p><small>Member since: <?= date('d M Y', strtotime($user['created_at'])) ?></small></p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>