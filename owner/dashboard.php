<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin('owner');

$title = 'Owner Dashboard';
$stats = getDashboardStats('owner');

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2><i class="fas fa-crown me-2"></i>Owner Dashboard</h2>
            <p>Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?>. You control the full EcoConnect platform.</p>
        </div>
        <div class="text-end">
            <small><?= date('l, F d, Y') ?></small>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card bg-gradient-green">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-number"><?= number_format($stats['total_users'] ?? 0) ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card bg-gradient-blue">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-number"><?= number_format($stats['total_admins'] ?? 0) ?></div>
                    <div class="stat-label">Admins</div>
                </div>
                <div class="stat-icon"><i class="fas fa-user-shield"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card bg-gradient-orange">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-number"><?= number_format($stats['total_cashiers'] ?? 0) ?></div>
                    <div class="stat-label">Cashiers</div>
                </div>
                <div class="stat-icon"><i class="fas fa-user-tie"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card bg-gradient-red">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-number"><?= number_format($stats['total_customers'] ?? 0) ?></div>
                    <div class="stat-label">Customers</div>
                </div>
                <div class="stat-icon"><i class="fas fa-users-cog"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="stat-card bg-gradient-purple">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-number"><?= number_format($stats['total_managers'] ?? 0) ?></div>
                    <div class="stat-label">Managers</div>
                </div>
                <div class="stat-icon"><i class="fas fa-briefcase"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="stat-card bg-gradient-teal">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-number"><?= number_format($stats['total_collectors'] ?? 0) ?></div>
                    <div class="stat-label">Collectors</div>
                </div>
                <div class="stat-icon"><i class="fas fa-truck"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-12 mb-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #1b5e20, #2e7d32);">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-number"><?= formatCurrency($stats['total_revenue'] ?? 0) ?></div>
                    <div class="stat-label">Platform Revenue</div>
                </div>
                <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Owner Controls</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-success">
            <strong>Owner access granted.</strong> You can supervise the admin, cashier, customer, manager, and collector modules from one place.
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <a href="../admin/dashboard.php" class="btn btn-outline-success w-100 py-3"><i class="fas fa-user-shield me-2"></i>Open Admin Area</a>
            </div>
            <div class="col-md-6">
                <a href="../cashier/dashboard.php" class="btn btn-outline-primary w-100 py-3"><i class="fas fa-money-bill-wave me-2"></i>Open Cashier Area</a>
            </div>
            <div class="col-md-6">
                <a href="../customer/dashboard.php" class="btn btn-outline-info w-100 py-3"><i class="fas fa-users me-2"></i>Open Customer Area</a>
            </div>
            <div class="col-md-6">
                <a href="../manager/dashboard.php" class="btn btn-outline-warning w-100 py-3"><i class="fas fa-briefcase me-2"></i>Open Manager Area</a>
            </div>
        </div>
    </div>
</div>
