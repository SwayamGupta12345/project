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

// Initialize variables for success and message
$success = false;
$message = "";

// Handle deletion confirmation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_delete']) && isset($_POST['sem']) && isset($_POST['id'])) {
    $sem = (int)$_POST['sem'];
    $subject = $_POST['id'];

    // Determine the semester table
    $semesterTable = "sem" . $sem;

    // Delete from the corresponding semester table
    $stmt = $conn->prepare("DELETE FROM $semesterTable WHERE subject = ?");
    $stmt->bind_param("s", $subject);
    if ($stmt->execute()) {
        // Delete from the cards table
        $stmt_cards = $conn->prepare("DELETE FROM cards WHERE subject = ? AND sem = ?");
        $stmt_cards->bind_param("si", $subject, $sem);
        if ($stmt_cards->execute()) {
            $success = true;
            $message = "The subject has been successfully deleted.";
        } else {
            $message = "Error deleting subject from cards table: " . $stmt_cards->error;
        }
        $stmt_cards->close();
    } else {
        $message = "Error deleting subject from $semesterTable table: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch unique semesters for dropdown
$semesters = [];
$result = $conn->query("SELECT DISTINCT sem FROM cards ORDER BY sem ASC");
while ($row = $result->fetch_assoc()) {
    $semesters[] = $row['sem'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Subject</title>
    <link rel="stylesheet" href="delete_subject.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function loadSubjects(sem) {
            if (sem) {
                $.ajax({
                    url: "fetch_subjects.php",
                    type: "GET",
                    data: { sem: sem },
                    success: function (response) {
                        $('#subjectSelect').html(response);
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error: " + status + ": " + error);
                    }
                });
            } else {
                $('#subjectSelect').html('<option value="">Select a subject</option>');
            }
        }
    </script>
</head>
<body>
<div class="container">
    <?php if ($success): ?>
        <h1>Subject Deleted</h1>
        <p><?php echo $message; ?></p>
        <a href="admin_panel.php" class="btn">Back to Admin Panel</a>
    <?php else: ?>
        <h1>Delete Subject</h1>
        <form method="POST">
            <div class="form-group">
                <label for="sem">Select Semester:</label>
                <select id="sem" name="sem" onchange="loadSubjects(this.value)">
                    <option value="">Select a semester</option>
                    <?php foreach ($semesters as $semester): ?>
                        <option value="<?php echo $semester; ?>"><?php echo $semester; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="subjectSelect">Select Subject:</label>
                <select id="subjectSelect" name="id">
                    <option value="">Select a subject</option>
                </select>
            </div>

            <input type="submit" name="confirm_delete" value="Delete Selected Subject" class="btn" onclick="return confirm('Are you sure you want to delete this subject?');">
        </form>
        <a href="admin_panel.php">Back to Admin Panel</a>
    <?php endif; ?>
</div>
</body>
</html>

<?php
$conn->close();
?>