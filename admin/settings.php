<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin('admin');

// Get current settings
$settings = [];
try {
    $stmt = $db->query("SELECT * FROM system_settings ORDER BY setting_key");
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (PDOException $e) {
    error_log("Settings fetch error: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $allowed_settings = [
        'site_name', 'site_email', 'site_phone', 'site_address',
        'currency', 'price_per_kg', 'business_hours', 'notification_email'
    ];
    
    try {
        foreach ($allowed_settings as $key) {
            $value = sanitize($_POST[$key] ?? '');
            if (isset($settings[$key])) {
                $stmt = $db->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = ?");
                $stmt->execute([$value, $key]);
            } else {
                $stmt = $db->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?)");
                $stmt->execute([$key, $value]);
            }
        }
        logActivity('Settings Updated', 'System settings were updated');
        setSuccess("Settings updated successfully.");
    } catch (PDOException $e) {
        setError("Error updating settings: " . $e->getMessage());
    }
    header("Location: settings.php");
    exit();
}

$title = 'System Settings';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h2><i class="fas fa-cog me-2"></i>System Settings</h2>
    <p>Configure system-wide settings for EcoConnect</p>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">General Settings</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <?= csrfField() ?>
                    <input type="hidden" name="update_settings" value="1">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Site Name</label>
                            <input type="text" class="form-control" name="site_name" value="<?= htmlspecialchars($settings['site_name'] ?? 'EcoConnect') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Currency</label>
                            <select class="form-select" name="currency" required>
                                <option value="RWF" <?= ($settings['currency'] ?? 'RWF') === 'RWF' ? 'selected' : '' ?>>RWF - Rwandan Franc</option>
                                <option value="USD" <?= ($settings['currency'] ?? '') === 'USD' ? 'selected' : '' ?>>USD - US Dollar</option>
                                <option value="EUR" <?= ($settings['currency'] ?? '') === 'EUR' ? 'selected' : '' ?>>EUR - Euro</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Site Email</label>
                            <input type="email" class="form-control" name="site_email" value="<?= htmlspecialchars($settings['site_email'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Site Phone</label>
                            <input type="text" class="form-control" name="site_phone" value="<?= htmlspecialchars($settings['site_phone'] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Site Address</label>
                        <textarea class="form-control" name="site_address" rows="2"><?= htmlspecialchars($settings['site_address'] ?? '') ?></textarea>
                    </div>
                    
                    <hr>
                    <h5 class="mb-3">Waste Collection Settings</h5>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Default Price Per Kg (RWF)</label>
                            <input type="number" class="form-control" name="price_per_kg" value="<?= htmlspecialchars($settings['price_per_kg'] ?? '500') ?>" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Business Hours</label>
                            <input type="text" class="form-control" name="business_hours" value="<?= htmlspecialchars($settings['business_hours'] ?? 'Mon-Sat 8:00 AM - 6:00 PM') ?>" required>
                        </div>
                    </div>
                    
                    <hr>
                    <h5 class="mb-3">Notification Settings</h5>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Notification Email</label>
                        <input type="email" class="form-control" name="notification_email" value="<?= htmlspecialchars($settings['notification_email'] ?? '') ?>">
                        <small class="text-muted">Email address for receiving system notifications</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Settings
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>System Info</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><strong>PHP Version:</strong> <?= phpversion() ?></li>
                    <li class="mb-2"><strong>Database:</strong> MySQL / MariaDB</li>
                    <li class="mb-2"><strong>Server:</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></li>
                    <li class="mb-2"><strong>Site URL:</strong> <small><?= SITE_URL ?></small></li>
                </ul>
                <hr>
                <h6>Reset Password</h6>
                <p><small class="text-muted">To reset the admin password, contact the database administrator to update the password hash.</small></p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>