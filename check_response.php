<?php
// DB connection
$eco_conn = new mysqli("localhost", "root", "", "ecoconnect");
if ($eco_conn->connect_error) {
    die("Connection failed: " . $eco_conn->connect_error);
}

$responseMessage = "";
$responseColor = "";
$messagesWithStatus = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    if (!empty($email) && !empty($phone)) {
        // Prepare statement to find all messages with that email and phone
        $stmt = $eco_conn->prepare("
            SELECT c.id, c.created_at, 
            (SELECT COUNT(*) FROM replies r WHERE r.message_id = c.id) AS reply_count
            FROM contact_messages c
            WHERE c.email = ? AND c.phone = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->bind_param("ss", $email, $phone);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            // Collect messages and their reply status
            while ($row = $result->fetch_assoc()) {
                $messagesWithStatus[] = [
                    'date' => $row['created_at'],
                    'replied' => ($row['reply_count'] > 0)
                ];
            }
        } else {
            $responseMessage = "❌ No messages found for this email and phone number.";
            $responseColor = "red";
        }

        $stmt->close();
    } else {
        $responseMessage = "❗ Please enter both a valid email address and phone number.";
        $responseColor = "orange";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Check Reply Status - EcoConnect</title>
  <style>
    body {
      font-family: sans-serif;
      background: url('images/background-leaves.jpg') no-repeat center center fixed;
      background-size: cover;
      color: #333;
      padding: 40px;
    }
    .check-form {
      max-width: 500px;
      margin: auto;
      padding: 30px;
      background: rgba(255, 255, 255, 0.8);
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }
    .check-form h2 {
      text-align: center;
      color: #2e7d32;
    }
    .check-form form {
      margin-top: 20px;
    }
    .check-form input[type="email"],
    .check-form input[type="text"] {
      width: 100%;
      padding: 14px;
      font-size: 16px;
      margin-bottom: 15px;
      border-radius: 8px;
      border: 1px solid #ccc;
    }
    .check-form button {
      width: 100%;
      padding: 12px;
      font-size: 16px;
      background-color: #2e7d32;
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: bold;
    }
    .check-form button:hover {
      background-color: #1b5e20;
    }
    .message {
      margin-top: 20px;
      font-weight: bold;
      text-align: center;
      color: white;
      padding: 10px;
      border-radius: 6px;
    }
    .message.green { background-color: #4CAF50; }
    .message.red { background-color: #f44336; }
    .message.orange { background-color: #ff9800; }
    .back-link {
      text-align: center;
      margin-top: 20px;
    }
    .back-link a {
      text-decoration: none;
      color: #2e7d32;
      font-weight: bold;
    }
    .back-link a:hover {
      text-decoration: underline;
    }
    .message-list {
      margin-top: 25px;
      padding: 0;
      list-style: none;
    }
    .message-list li {
      background: #eee;
      margin-bottom: 10px;
      padding: 10px 15px;
      border-radius: 6px;
      font-size: 16px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .message-list li .status {
      font-weight: bold;
      padding: 3px 10px;
      border-radius: 4px;
      color: white;
    }
    .message-list li .status.replied {
      background-color: #4CAF50;
    }
    .message-list li .status.not-replied {
      background-color: #f44336;
    }
  </style>
</head>
<body>
  <div class="check-form">
    <h2>Check Reply Status</h2>
    <form method="POST">
      <input type="email" name="email" placeholder="Enter your email" required />
      <input type="text" name="phone" placeholder="Enter your phone number" required />
      <button type="submit">Check Status</button>
    </form>

    <?php if (!empty($responseMessage)): ?>
      <div class="message <?= htmlspecialchars($responseColor); ?>"><?= htmlspecialchars($responseMessage); ?></div>
    <?php endif; ?>

    <?php if (!empty($messagesWithStatus)): ?>
      <ul class="message-list">
        <?php foreach ($messagesWithStatus as $msg): ?>
          <li>
            <span>Message sent on: <?= date("d/M/Y h:i A", strtotime($msg['date'])); ?></span>
            <span class="status <?= $msg['replied'] ? 'replied' : 'not-replied' ?>">
              <?= $msg['replied'] ? 'Replied' : 'No Reply Yet' ?>
            </span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <div class="back-link">
      <a href="contact.html">&larr; Back to Contact</a>
    </div>
  </div>
</body>
</html>

<?php $eco_conn->close(); ?>
