<?php
session_start();
if (!isset($_SESSION['user_email'])) {
    header("Location: index.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "root"; // Replace with your database password
$dbname = "elp";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form input values
    $subject = $_POST['subject'];
    $image = $_POST['image'];
    $description = $_POST['description'];
    $sem = (int)$_POST['sem'];

    // Check if subject already exists
    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM cards WHERE subject = ?");
    $check_stmt->bind_param("s", $subject);
    $check_stmt->execute();
    $check_stmt->bind_result($count);
    $check_stmt->fetch();
    $check_stmt->close();
    // Check if semester is greater than zero
    if ($sem < 1 || $sem > 10) {
        echo "<script>alert('Semester must be greater than zero.');</script>";
    }
    else if($count > 0){
        echo "<script>alert('Subject already exists.');</script>";
    }
    else {
        // Insert into 'cards' table
        $stmt_cards = $conn->prepare("INSERT INTO cards (subject, image, description, sem) VALUES (?, ?, ?, ?)");
        if ($stmt_cards) {
            $stmt_cards->bind_param("sssi", $subject, $image, $description, $sem);
            if ($stmt_cards->execute()) {
                echo "<script>alert('Data successfully inserted into cards table.');</script>";
            } else {
                echo "<script>alert('Error inserting into cards: " . $stmt_cards->error . "');</script>";
            }
            $stmt_cards->close();
        }

        // Redirect to admin panel after submission
        header("Location: add_subject.php");
        exit();
    }
}

// C    lose the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Subject</title>
    <link rel="stylesheet" href="add_subject.css">
    <script>
        function confirmSubmission() {
            const semValue = document.getElementById('sem').value;
            if (semValue < 1 || samValue > 10) {
                alert("Semester must be greater than zero.");
                return false;
            }
            return confirm("Are you sure you want to add this subject?");
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Add New Subject</h1>
        <form method="POST" onsubmit="return confirmSubmission();">
            <label for="subject">Subject:</label>
            <input type="text" id="subject" name="subject" required>

            <label for="image">Image URL:</label>
            <input type="text" id="image" name="image" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea>

            <label for="sem">Semester:</label>
            <input type="number" id="sem" name="sem" min="1" max="10" required>

            <input type="submit" value="Add Subject">
            <h3>Note: Check the detail twice before submission</h3>
        </form>
        <a href="admin_panel.php">Back to Admin Panel</a>
    </div>
</body>
</html>