<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $request_id = $_POST['request_id'];
    $team_id = $_POST['team_id'];

    // Add assignment to the database
    $stmt = $conn->prepare("INSERT INTO assignments (request_id, team_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $request_id, $team_id);
    $stmt->execute();

    // Update pickup request status
    $update = $conn->prepare("UPDATE pickup_requests SET status = 'Assigned' WHERE request_id = ?");
    $update->bind_param("i", $request_id);
    $update->execute();

    echo "Pickup request assigned successfully.";
    header("Location: admin_dashboard.php"); // Redirect back to dashboard
}
?>
