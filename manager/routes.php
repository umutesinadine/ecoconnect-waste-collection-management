<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin('manager');

$title = 'Routes & Schedules';

$routes = [
    ['name' => 'Kacyiru Route', 'zone' => 'Kacyiru', 'stops' => 8, 'status' => 'In Progress'],
    ['name' => 'Kimironko Route', 'zone' => 'Kimironko', 'stops' => 6, 'status' => 'Scheduled'],
    ['name' => 'Nyamirambo Route', 'zone' => 'Nyamirambo', 'stops' => 5, 'status' => 'Pending'],
];

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2><i class="fas fa-route me-2"></i>Routes & Schedules</h2>
            <p>Coordinate daily service routes and assign pickups by zone.</p>
        </div>
        <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
    </div>
</div>

<div class="row">
    <?php foreach ($routes as $route): ?>
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><?= htmlspecialchars($route['name']) ?></h5>
                    <span class="badge bg-success-subtle text-success"><?= htmlspecialchars($route['status']) ?></span>
                </div>
                <p class="text-muted mb-3">Zone: <?= htmlspecialchars($route['zone']) ?></p>
                <div class="d-flex justify-content-between text-muted">
                    <span><i class="fas fa-map-marker-alt me-1"></i> Stops</span>
                    <strong><?= $route['stops'] ?></strong>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
