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
    $sem = (int)$_POST['link_sem'];
    $semTable = "sem" . $sem;

    $subject = $_POST['subject'];
    $description = $_POST['link_description'];
    $link_url = $_POST['link'];
    $type = $_POST['type'];

    // Update query with prepared statement
    $stmt = $conn->prepare("UPDATE $semTable SET description = ?, link = ?, type = ? WHERE subject = ?");
    $stmt->bind_param("ssss", $description, $link_url, $type, $subject);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: edit_link.php");
        exit();
    } else {
        $error_message = "Error updating record: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Link</title>
    <link rel="stylesheet" href="edit_link.css">
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

        function loadSubjectDetails(subject) {
            if (subject) {
                $.ajax({
                    url: "edit_link.php", 
                    type: "GET",
                    data: { subject: subject, sem: $('#link_sem').val() },
                    dataType: 'html',
                    success: function (data) {
                        const parsedData = $(data).find('.form')[0]; 
                        $('#link_description').val($(parsedData).find('[name="link_description"]').val());
                        $('#link').val($(parsedData).find('[name="link"]').val());
                        $('#type').val($(parsedData).find('[name="type"]').val());
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error: " + status + ": " + error);
                    }
                });
            } else {
                $('#link_description').val('');
                $('#link').val('');
                $('#type').val('');
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Edit Data Link</h1>
        <?php if (isset($error_message)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <form method="POST" class="form">
            <div class="form-group">
                <label for="link_sem">Select Semester:</label>
                <select id="link_sem" name="link_sem" class="form-control" onchange="loadSubjects(this.value)">
                    <option value="">Select a semester</option>
                    <?php
                    // Fetch all unique semesters from the 'cards' table
                    $result = $conn->query("SELECT DISTINCT sem FROM cards ORDER BY sem ASC");
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row['sem'] . "'>" . $row['sem'] . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="subjectSelect">Select Subject:</label>
                <select id="subjectSelect" name="subject" class="form-control" onchange="loadSubjectDetails(this.value)">
                    <option value="">Select a subject</option>
                </select>
            </div>

            <div class="form-group">
                <label for="link_description">Description:</label>
                <input type="text" id="link_description" name="link_description" required>
            </div>

            <div class="form-group">
                <label for="link">Link:</label>
                <input type="url" id="link" name="link" required>
            </div>

            <div class="form-group">
                <label for="type">Type:</label>
                <select id="type" name="type" required>
                    <option value="college">College</option>
                    <option value="youtube">YouTube</option>
                    <option value="books">Books</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <input type="submit" value="Update Link">
        </form>
        <a href="admin_panel.php">Back to Admin Panel</a>
    </div>
</body>
</html>

<?php
$conn->close();
?>