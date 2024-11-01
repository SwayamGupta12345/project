<?php
session_start();
if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
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

// Initialize variables to store fetched data
$image = $subject = $description = $sem = "";

// Handle form submission for updating entries
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $image = $conn->real_escape_string($_POST['image']);
    $subject = $conn->real_escape_string($_POST['subject']);
    $description = $conn->real_escape_string($_POST['description']);
    $sem = (int)$_POST['sem'];

    // Update the record in the database
    print_r($_POST); // Check if all values are correct.
    $sql = "UPDATE cards SET image='$image', subject='$subject', description='$description', sem='$sem' WHERE subject='$subject'";
    if ($conn->query($sql) === TRUE) {
        header("Location: edit_subject.php");
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

// Handle fetching of the subject details if a subject is selected
if (isset($_GET['subject'])) {
    $subject = $conn->real_escape_string($_GET['subject']);
    
    // Prepare SQL query to fetch all details for a specific subject from the cards table
    $stmt = $conn->prepare("SELECT * FROM cards WHERE subject = ?");
    $stmt->bind_param("s", $subject);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        $image = $data['image'];
        $subject = $data['subject'];
        $description = $data['description'];
        $sem = $data['sem'];
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Subject</title>
    <link rel="stylesheet" href="edit_subject.css">
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
                    url: "edit_subject.php", // Call this file again
                    type: "GET",
                    data: { subject: subject },
                    dataType: 'html',
                    success: function (data) {
                        const parsedData = $(data).find('.form')[0]; // Extract form values from returned HTML
                        $('#image').val($(parsedData).find('[name="image"]').val());
                        $('#subject').val($(parsedData).find('[name="subject"]').val());
                        $('#description').val($(parsedData).find('[name="description"]').val());
                        $('#sem').val($(parsedData).find('[name="sem"]').val());
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error: " + status + ": " + error);
                    }
                });
            } else {
                $('#image').val('');
                $('#subject').val('');
                $('#description').val('');
                $('#sem').val('');
            }
        }
    </script>
</head>
<body>
<div class="container">
    <h1 class="form-title">Edit Subject</h1>
    <form method="POST" class="form">
        <input type="hidden" name="id" value="<?php echo isset($id) ? $id : ''; ?>">
        
        <div class="form-group">
            <label for="sem">Select Semester:</label>
            <select id="sem" name="sem" class="form-control" onchange="loadSubjects(this.value)">
                <option value="">Select a semester</option>
                <?php
                // Fetch all unique semesters from the 'cards' table
                $result = $conn->query("SELECT DISTINCT sem FROM cards ORDER BY sem ASC");
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['sem'] . "'" . ($row['sem'] == $sem ? " selected" : "") . ">" . $row['sem'] . "</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="subjectSelect">Select Subject:</label>
            <select id="subjectSelect" name="subject" class="form-control" onchange="loadSubjectDetails(this.value)">
                <option value="">Select a subject</option>
                <?php
                // If semester is selected, fetch subjects for that semester
                if ($sem) {
                    $stmt = $conn->prepare("SELECT subject FROM cards WHERE sem = ?");
                    $stmt->bind_param("i", $sem);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row['subject'] . "'" . ($row['subject'] == $subject ? " selected" : "") . ">" . $row['subject'] . "</option>";
                    }
                    $stmt->close();
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="image">Image URL:</label>
            <input type="text" id="image" name="image" class="form-control" value="<?php echo $image; ?>">
        </div>

        <div class="form-group">
            <label for="subject">subject:</label>
            <input type="text" id="subject" name="subject" class="form-control" value="<?php echo $subject; ?>">
        </div>

        <div class="form-group">
            <label for="description">Description:</label>
            <input type="text" id="description" name="description" class="form-control" value="<?php echo $description; ?>">
        </div>

        <div class="form-group">
            <label for="sem">Semester:</label>
            <input type="number" id="sem" name="sem" min="1" max="10" class="form-control" required value="<?php echo $sem; ?>">
        </div>

        <button type="submit" class="btn">Update</button>
    </form>

    <a href="admin_panel.php">Go Back</a>
</div>
</body>
</html>

<?php
$conn->close();
?>