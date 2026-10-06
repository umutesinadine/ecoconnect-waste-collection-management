<?php
session_start();
require 'vendor/autoload.php'; // PHPMailer via Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 🔐 Gmail credentials
$senderEmail = 'ug2320239@ines.ac.rw';
$senderName  = 'Giselle Uwajeneza';
$appPassword = 'rdxe rknl osdg hqvs'; // <-- Replace this

// 📨 Collect POST form data
$message_id = $_POST['message_id'] ?? null;
$recipient_email = $_POST['recipient_email'] ?? null;
$recipient_name = $_POST['recipient_name'] ?? null;
$reply_message = $_POST['reply_message'] ?? null;

// 👤 Logged in admin name (optional: replace with actual session value if needed)
$replied_by = $_SESSION['admin_name'] ?? 'Admin';

if (!$message_id || !$recipient_email || !$reply_message) {
    $_SESSION['error'] = "Incomplete reply information.";
    header("Location: view_messages.php");
    exit;
}

// ✅ 1. Send Email using PHPMailer
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $senderEmail;
    $mail->Password   = $appPassword;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom($senderEmail, $senderName);
    $mail->addAddress($recipient_email, $recipient_name);

    $mail->isHTML(true);
    $mail->Subject = "Reply to your message on EcoConnect";
    $mail->Body    = nl2br(htmlspecialchars($reply_message));
    $mail->AltBody = $reply_message;

    $mail->send();

    // ✅ 2. Store reply in ecoconnect DB
    $eco_conn = new mysqli("localhost", "root", "", "ecoconnect");
    if ($eco_conn->connect_error) {
        throw new Exception("Database connection failed: " . $eco_conn->connect_error);
    }

    $stmt = $eco_conn->prepare("INSERT INTO replies (message_id, reply_text, replied_by) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $message_id, $reply_message, $replied_by);
    $stmt->execute();
    $stmt->close();
    $eco_conn->close();

    $_SESSION['success'] = "Reply sent and saved successfully!";
} catch (Exception $e) {
    $_SESSION['error'] = "Message could not be sent. Mailer Error: " . $mail->ErrorInfo;
}

// 🔁 Redirect back
header("Location: view_messages.php");
exit;
