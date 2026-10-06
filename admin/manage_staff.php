<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin('admin');

$edit_mode = false;
$staff = null;
$error = '';
$success = '';

$staff_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($staff_id > 0) {
    $edit_mode = true;
    try {
        $stmt = $db->prepare("SELECT * FROM users WHERE user_id = ? AND role IN ('manager', 'collector')");
        $stmt->execute([$staff_id]);
        $staff = $stmt->fetch();

        if (!$staff) {
            setError("Staff member not found.");
            header("Location: staff.php");
            exit();
        }
    } catch (PDOException $e) {
        setError("Error: " . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    $full_name = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $role = sanitize($_POST['role'] ?? 'collector');
    $password = $_POST['password'] ?? '';

    if (empty($full_name) || empty($email) || empty($phone)) {
        $error = "Full name, email, and phone are required.";
    } elseif (!validateEmail($email)) {
        $error = "Please enter a valid email address.";
    } elseif (!$edit_mode && empty($password)) {
        $error = "Password is required for new staff accounts.";
    } elseif (!$edit_mode && strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        try {
            if ($edit_mode) {
                if (!empty($password)) {
                    $hashed = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $db->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, role = ?, password = ? WHERE user_id = ?");
                    $stmt->execute([$full_name, $email, $phone, $role, $hashed, $staff_id]);
                } else {
                    $stmt = $db->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, role = ? WHERE user_id = ?");
                    $stmt->execute([$full_name, $email, $phone, $role, $staff_id]);
                }
                setSuccess("Staff account updated successfully.");
                logActivity('Staff Updated', "Updated staff account: $full_name");
                header("Location: staff.php");
                exit();
            }

            $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()['count'] > 0) {
                $error = "A user with this email already exists.";
            } else {
                $hashed = password_hash($password, PASSWORD_BCRYPT);
                $username = explode('@', $email)[0];
                $stmt = $db->prepare("INSERT INTO users (username, email, password, full_name, phone, role, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
                $stmt->execute([$username, $email, $hashed, $full_name, $phone, $role]);
                logActivity('Staff Created', "Created staff account: $full_name as $role");
                setSuccess("Staff account created successfully. They can now login.");
                header("Location: staff.php");
                exit();
            }
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

$title = $edit_mode ? 'Edit Staff Member' : 'Add Staff Member';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2><i class="fas fa-<?= $edit_mode ? 'edit' : 'user-plus' ?> me-2"></i><?= $title ?></h2>
            <p><?= $edit_mode ? 'Update staff details and role' : 'Create a manager or collector account' ?></p>
        </div>
        <a href="staff.php" class="btn btn-light">
            <i class="fas fa-arrow-left me-1"></i> Back to Staff
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Staff Account Details</h5>
            </div>
            <div class="card-body">
                <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
                <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

                <form method="POST" action="">
                    <?= csrfField() ?>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="full_name" value="<?= $edit_mode ? htmlspecialchars($staff['full_name']) : '' ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" value="<?= $edit_mode ? htmlspecialchars($staff['email']) : '' ?>" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="phone" value="<?= $edit_mode ? htmlspecialchars($staff['phone']) : '' ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Role <span class="text-danger">*</span></label>
                            <select class="form-select" name="role">
                                <option value="collector" <?= ($edit_mode && $staff['role'] === 'collector') ? 'selected' : '' ?>>Collector</option>
                                <option value="manager" <?= ($edit_mode && $staff['role'] === 'manager') ? 'selected' : '' ?>>Manager</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Password <?= $edit_mode ? '<small class="text-muted">(leave blank to keep current)</small>' : '<span class="text-danger">*</span>' ?></label>
                        <input type="password" class="form-control" name="password" placeholder="<?= $edit_mode ? 'Leave blank to keep current' : 'Min 6 characters' ?>" <?= $edit_mode ? '' : 'required' ?>>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="staff.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> <?= $edit_mode ? 'Update Staff' : 'Create Staff' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Notes</h5>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li class="mb-2">Managers can oversee routes and requests.</li>
                    <li class="mb-2">Collectors can work assigned jobs and route plans.</li>
                    <li class="mb-2">Staff accounts become active immediately after creation.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
