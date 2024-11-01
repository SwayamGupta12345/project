<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "root"; // Replace with your database password
$dbname = "elp";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['sem'])) {
    $sem = (int)$_GET['sem'];
    
    // Prepare SQL query to fetch subjects (subjects) from the cards table for the selected semester
    $stmt = $conn->prepare("SELECT subject FROM cards WHERE sem = ?");
    $stmt->bind_param("i", $sem);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<option value='" . $row['subject'] . "'>" . $row['subject'] . "</option>";
        }
    } else {
        echo "<option value=''>No subjects found for selected semester</option>";
    }

    $stmt->close();
}

$conn->close();
?>
