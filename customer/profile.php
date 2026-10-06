<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin('customer');

$user_id = $_SESSION['user_id'];

// Get current user data
$stmt = $db->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!empty($password)) {
        if (strlen($password) < 6) {
            setError("Password must be at least 6 characters.");
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("UPDATE users SET full_name = ?, phone = ?, address = ?, password = ? WHERE user_id = ?");
            $stmt->execute([$full_name, $phone, $address, $hashed, $user_id]);
            setSuccess("Profile updated successfully.");
        }
    } else {
        $stmt = $db->prepare("UPDATE users SET full_name = ?, phone = ?, address = ? WHERE user_id = ?");
        $stmt->execute([$full_name, $phone, $address, $user_id]);
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
    <p>Update your personal information</p>
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
                            <label class="form-label fw-bold">Member Since</label>
                            <input type="text" class="form-control" value="<?= date('d M Y', strtotime($user['created_at'])) ?>" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Address</label>
                        <textarea class="form-control" name="address" rows="2"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                    </div>
                    <hr>
                    <h5 class="mb-3">Change Password (Optional)</h5>
                    <div class="mb-3">
                        <label class="form-label fw-bold">New Password</label>
                        <input type="password" class="form-control" name="password" placeholder="Leave blank to keep current">
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update Profile</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Account Status</h5></div>
            <div class="card-body text-center">
                <i class="fas fa-user-circle fa-5x text-success mb-3"></i>
                <h4><?= htmlspecialchars($user['full_name']) ?></h4>
                <p class="text-muted"><?= htmlspecialchars($user['email']) ?></p>
                <p>Status: <?= getStatusBadge($user['status']) ?></p>
                <p><small>Last login: <?= $user['last_login'] ? date('d M Y H:i', strtotime($user['last_login'])) : 'Never' ?></small></p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>