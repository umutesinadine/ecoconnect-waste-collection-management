<?php
// fetch_replies.php
include('config/config.php'); // Adjust if needed for EcoConnect DB connection

$eco_conn = new mysqli("localhost", "root", "", "ecoconnect");
if ($eco_conn->connect_error) {
    http_response_code(500);
    echo "Database connection error";
    exit;
}

if (!isset($_GET['message_id']) || !is_numeric($_GET['message_id'])) {
    http_response_code(400);
    echo "Invalid message ID";
    exit;
}

$message_id = intval($_GET['message_id']);

$stmt = $eco_conn->prepare("SELECT reply_text, replied_at, replied_by FROM replies WHERE message_id = ? ORDER BY replied_at DESC");
$stmt->bind_param("i", $message_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<ul class='list-group'>";
    while ($row = $result->fetch_assoc()) {
        echo "<li class='list-group-item'>";
        echo "<small><em>By: " . htmlspecialchars($row['replied_by']) . " on " . date("d/M/Y h:i A", strtotime($row['replied_at'])) . "</em></small><br>";
        echo nl2br(htmlspecialchars($row['reply_text']));
        echo "</li>";
    }
    echo "</ul>";
} else {
    echo "<p class='text-muted'>No replies yet.</p>";
}

$stmt->close();
$eco_conn->close();
?>
