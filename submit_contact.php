<?php
$host = "localhost";
$user = "root";  // or your DB username
$password = "";  // your DB password
$database = "ecoconnect"; // your DB name

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$name = htmlspecialchars($_POST['name']);
$email = htmlspecialchars($_POST['email']);
$phone = htmlspecialchars($_POST['phone']);
$message = htmlspecialchars($_POST['message']);

$sql = "INSERT INTO contact_messages (name, email, phone, message)
        VALUES ('$name', '$email', '$phone', '$message')";

if ($conn->query($sql) === TRUE) {
  echo "<script>alert('Thank you! Your message was sent.'); window.location.href='contact.html';</script>";
} else {
  echo "Error: " . $conn->error;
}

$conn->close();
?>
