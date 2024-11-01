<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "elp";
$port = "3306"; // Adjust if needed
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Admin email and plain text password
$email = 'rishugoyal@gmail.com';
$plainPassword = 'rishu@1234'; // Replace with your desired password

// Hash the password
$passwordHash = password_hash($plainPassword, PASSWORD_BCRYPT);

// Insert into the admin table
$stmt = $conn->prepare("INSERT INTO admin (email, password_hash) VALUES (?, ?)");
$stmt->bind_param("ss", $email, $passwordHash);
$stmt->execute();

echo "Admin account created successfully.";

$stmt->close();
$conn->close();
?>