<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin('collector');

$title = 'Route Plan';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2><i class="fas fa-route me-2"></i>Route Plan</h2>
            <p>Follow your weekly pickup schedule and service zones.</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-success">Back</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="alert alert-success">Your route plan is now available for daily field operations.</div>
        <ul class="mb-0">
            <li>Morning: Kacyiru and Kimironko pickups</li>
            <li>Afternoon: Nyamirambo and Remera pickups</li>
            <li>End of day: Report completions and customer follow-up</li>
        </ul>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
