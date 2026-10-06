<?php
// ============================================================
// EcoConnect - Shared Header
// ============================================================
$current_user = getCurrentUser();
$unread_count = $current_user ? getUnreadNotificationCount($current_user['user_id']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoConnect - <?= $title ?? 'Dashboard' ?></title>
    <link rel="icon" type="image/jpg" sizes="32x32" href="../favicon-32x32.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="wrapper">
    <!-- Sidebar -->
    <nav id="sidebar" class="sidebar">
        <div class="sidebar-header">
            <a href="dashboard.php">
                <img src="../images/gisellelogo.png" alt="EcoConnect" height="40">
                <h3>EcoConnect</h3>
            </a>
        </div>

        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                </a>
            </li>

            <?php if (hasRole('admin')): ?>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : '' ?>" href="customers.php">
                    <i class="fas fa-users"></i> <span>Customers</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos(basename($_SERVER['PHP_SELF']), 'cashier') !== false ? 'active' : '' ?>" href="cashiers.php">
                    <i class="fas fa-user-tie"></i> <span>Cashiers</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos(basename($_SERVER['PHP_SELF']), 'staff') !== false ? 'active' : '' ?>" href="staff.php">
                    <i class="fas fa-user-shield"></i> <span>Staff</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : '' ?>" href="categories.php">
                    <i class="fas fa-recycle"></i> <span>Waste Categories</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'requests.php' ? 'active' : '' ?>" href="requests.php">
                    <i class="fas fa-clipboard-list"></i> <span>All Requests</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : '' ?>" href="payments.php">
                    <i class="fas fa-credit-card"></i> <span>Payments</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : '' ?>" href="reports.php">
                    <i class="fas fa-chart-bar"></i> <span>Reports</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'messages.php' ? 'active' : '' ?>" href="messages.php">
                    <i class="fas fa-comments"></i> <span>Messages</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>" href="settings.php">
                    <i class="fas fa-cog"></i> <span>Settings</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (hasRole('cashier')): ?>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'requests.php' ? 'active' : '' ?>" href="requests.php">
                    <i class="fas fa-tasks"></i> <span>Assigned Requests</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : '' ?>" href="payments.php">
                    <i class="fas fa-money-bill-wave"></i> <span>Process Payments</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : '' ?>" href="reports.php">
                    <i class="fas fa-chart-line"></i> <span>Reports</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'followup.php' ? 'active' : '' ?>" href="followup.php">
                    <i class="fas fa-paper-plane"></i> <span>Follow-ups</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : '' ?>" href="profile.php">
                    <i class="fas fa-user"></i> <span>My Profile</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (hasRole('customer')): ?>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'request.php' ? 'active' : '' ?>" href="request.php">
                    <i class="fas fa-plus-circle"></i> <span>New Request</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'my_requests.php' ? 'active' : '' ?>" href="my_requests.php">
                    <i class="fas fa-clipboard-check"></i> <span>My Requests</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : '' ?>" href="payments.php">
                    <i class="fas fa-receipt"></i> <span>Payment History</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : '' ?>" href="profile.php">
                    <i class="fas fa-user"></i> <span>My Profile</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'messages.php' ? 'active' : '' ?>" href="messages.php">
                    <i class="fas fa-envelope"></i> <span>Messages</span>
                </a>
            </li>
            <?php endif; ?>

            <li class="nav-item mt-3">
                <hr>
            </li>
            <li class="nav-item">
                <a class="nav-link text-danger" href="../auth/logout.php">
                    <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Page Content -->
    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn btn-outline-secondary">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="ms-auto d-flex align-items-center">
                    <!-- Notifications -->
                    <div class="dropdown me-3">
                        <button class="btn btn-outline-secondary position-relative" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-bell"></i>
                            <?php if ($unread_count > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= $unread_count ?>
                            </span>
                            <?php endif; ?>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end notification-dropdown" style="width: 300px;">
                            <h6 class="dropdown-header">Notifications</h6>
                            <?php
                            if ($current_user) {
                                $stmt = $db->prepare("SELECT * FROM notifications WHERE (user_id = ? OR user_id IS NULL) ORDER BY created_at DESC LIMIT 5");
                                $stmt->execute([$current_user['user_id']]);
                                $notifications = $stmt->fetchAll();
                                if (count($notifications) > 0) {
                                    foreach ($notifications as $notif) {
                                        echo "<a class='dropdown-item' href='" . ($notif['link'] ?? '#') . "'>";
                                        echo "<small class='text-muted'>" . htmlspecialchars($notif['title']) . "</small><br>";
                                        echo "<span>" . htmlspecialchars(substr($notif['message'], 0, 50)) . "...</span>";
                                        echo "</a>";
                                    }
                                } else {
                                    echo "<a class='dropdown-item' href='#'>No notifications</a>";
                                }
                            }
                            ?>
                        </div>
                    </div>
                    <!-- User -->
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            <?= $current_user ? htmlspecialchars($current_user['full_name']) : 'User' ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="../auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <div class="container-fluid">
            <?= displayMessages() ?>