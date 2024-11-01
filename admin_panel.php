<?php
session_start();
if (!isset($_SESSION['user_email'])) {
    header("Location: index.php"); // Redirect to login page if not logged in
    exit();
}

// Check if the user is an admin
$servername = "localhost";
$username = "root";
$password = "root"; // Replace with your database password
$dbname = "elp"; // Replace with your database name
$port = "3306"; // Adjust if needed

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$email = $_SESSION['user_email'];
$stmt = $conn->prepare("SELECT is_admin FROM admin WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($isAdmin);
    $stmt->fetch();

    if (!$isAdmin) {
        header("Location: index.php"); // Redirect if not an admin
        exit();
    }
} else {
    header("Location: index.php"); // Redirect if no admin record found
    exit();
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="admin_panel.css">
</head>
<body>
<nav>
    <a class="home" href="index.php">HOME</a>
    <h1>Admin Panel</h1>
</nav>

<div class="card-container">
    <a class="card" href="add_subject.php">
        <h3>Add New Subject</h3>
    </a>
    <a class="card" href="edit_subject.php">
        <h3>Edit a Subject</h3>
    </a>
    <a class="card" href="delete_subject.php">
        <h3>Delete a Subject</h3>
    </a>
    <a class="card" href="add_link.php">
        <h3>Add a Data Link</h3>
    </a>
    <a class="card" href="edit_link.php">
        <h3>Edit a Data Link</h3>
    </a>
    <a class="card" href="delete_link.php">
        <h3>Delete a Data Link</h3>
    </a>
</div>

<footer>
    <div class="footer-container">
        <div class="footer-content">
            <div class="footer-item">
                <div class="footer-profile">
                    <img src="path/to/your-image1.jpg" alt="Profile Image 1" class="profile-image">
                    <div class="footer-details">
                        <h4>Your Name 1</h4>
                        <p>Email: <a href="mailto:youremail1@example.com">youremail1@example.com</a></p>
                        <ul>
                            <li><a href="https://www.linkedin.com/in/yourprofile1" target="_blank">LinkedIn</a></li>
                            <li><a href="https://github.com/yourprofile1" target="_blank">GitHub</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="footer-item">
                <div class="footer-profile">
                    <img src="path/to/your-image2.jpg" alt="Profile Image 2" class="profile-image">
                    <div class="footer-details">
                        <h4>Your Name 2</h4>
                        <p>Email: <a href="mailto:youremail2@example.com">youremail2@example.com</a></p>
                        <ul>
                            <li><a href="https://www.linkedin.com/in/yourprofile2" target="_blank">LinkedIn</a></li>
                            <li><a href="https://github.com/yourprofile2" target="_blank">GitHub</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 Your Company. All Rights Reserved.</p>
        </div>
    </div>
</footer>
</body>
</html>
<?php
$conn->close();
?>