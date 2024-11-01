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

// Initialize variables for semester and subject
$selectedSem = $selectedSubject = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sem = (int)$_POST['link_sem'];
    $subject = $conn->real_escape_string($_POST['subject']);
    $description = $conn->real_escape_string($_POST['link_description']);
    $link = $conn->real_escape_string($_POST['link']);
    $type = $conn->real_escape_string($_POST['type']);

    // Convert Google Drive link to a downloadable link if it matches the typical pattern
    if (preg_match('/drive\.google\.com\/file\/d\/([^\/]+)\/view/', $link, $matches)) {
        $file_id = $matches[1];
        $link = "https://drive.google.com/uc?export=download&id=" . $file_id;
    }

    // Construct the semester table name
    $semTable = "sem" . $sem;

    // Insert into the appropriate semester table
    $sql = "INSERT INTO $semTable (subject, description, link, type) VALUES ('$subject', '$description', '$link', '$type')";

    if ($conn->query($sql) === TRUE) {
        header("Location: add_link.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sem = (int)$_POST['link_sem'];
    $subject = $conn->real_escape_string($_POST['subject']);
    $description = $conn->real_escape_string($_POST['link_description']);
    $link = $conn->real_escape_string($_POST['link']);
    $type = $conn->real_escape_string($_POST['type']);

    // Check if the link is a Google Drive link
    if (preg_match('/drive\.google\.com\/file\/d\/([^\/]+)\/view/', $link, $matches)) {
        $file_id = $matches[1];
        $link = "https://drive.google.com/uc?export=download&id=" . $file_id;

    // Check if the link is a PowerPoint file (.ppt, .pptx) and convert it to a downloadable format
    } elseif (preg_match('/\.(pptx?|PPTX?)$/', $link)) {
        $link = str_replace("view", "download", $link);

    // Check if the link is a Word document (.doc, .docx) and convert it to a downloadable format
    } elseif (preg_match('/\.(docx?|DOCX?)$/', $link)) {
        $link = str_replace("view", "download", $link);

    // Check if the link is a PDF file and make it downloadable
    } elseif (preg_match('/\.pdf$/i', $link)) {
        // No special Google Drive conversion needed; just ensure it's a downloadable link
        $link = str_replace("view", "download", $link);  // Handle hosted PDFs

    // If the link is of any other type, don't modify it
    }

    // Construct the semester table name
    $semTable = "sem" . $sem;

    // Insert into the appropriate semester table
    $sql = "INSERT INTO $semTable (subject, description, link, type) VALUES ('$subject', '$description', '$link', '$type')";

    if ($conn->query($sql) === TRUE) {
        header("Location: add_link.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Fetch subjects based on semester for AJAX
if (isset($_GET['sem'])) {
    $sem = (int)$_GET['sem'];
    $subject_query = $conn->query("SELECT subject FROM cards WHERE sem = $sem");
    $subjects = [];
    while ($row = $subject_query->fetch_assoc()) {
        $subjects[] = $row['subject'];
    }
    echo json_encode($subjects);
    exit();
}

// Preload subjects if a semester is selected (similar to edit_subject.php)
if (isset($_POST['link_sem'])) {
    $selectedSem = (int)$_POST['link_sem'];
    $subject_query = $conn->query("SELECT subject FROM cards WHERE sem = $selectedSem");
    $selectedSubjects = [];
    while ($row = $subject_query->fetch_assoc()) {
        $selectedSubjects[] = $row['subject'];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Link</title>
    <link rel="stylesheet" href="add_link.css">
    <script>
        // Function to load subjects based on the selected semester
        function loadSubjects() {
            var sem = document.getElementById('link_sem').value;
            var subjectDropdown = document.getElementById('subject');
            subjectDropdown.innerHTML = '<option>Loading...</option>';  // Show loading text

            // Send AJAX request to fetch subjects based on semester
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'add_link.php?sem=' + sem, true);
            xhr.onload = function() {
                if (this.status == 200) {
                    var subjects = JSON.parse(this.responseText);
                    subjectDropdown.innerHTML = ''; // Clear previous options
                    if (subjects.length > 0) {
                        subjects.forEach(function(subject) {
                            var option = document.createElement('option');
                            option.value = subject;
                            option.textContent = subject;
                            subjectDropdown.appendChild(option);
                        });
                    } else {
                        subjectDropdown.innerHTML = '<option>No subjects available</option>';
                    }
                }
            };
            xhr.send();
        }

        // Preload selected semester and subjects when the page loads
        window.onload = function() {
            var sem = "<?php echo $selectedSem; ?>";
            if (sem) {
                document.getElementById('link_sem').value = sem;
                loadSubjects();
                document.getElementById('subject').value = "<?php echo $selectedSubject; ?>";
            }
        };

        // Function to confirm form submission
        function confirmSubmission() {
            return confirm("Are you sure you want to add this link?");
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Add a Data Link</h1>
        <form method="POST" onsubmit="return confirmSubmission()">
            <label for="link_sem"><b>Semester:</b></label>
            <select id="link_sem" name="link_sem" onchange="loadSubjects()" required>
                <option value="">Select Semester</option>
                <!-- Dynamically generate options from 1 to 10 -->
                <?php for ($i = 1; $i <= 10; $i++): ?>
                    <option value="<?= $i ?>" <?= ($i == $selectedSem) ? 'selected' : '' ?>><?= $i ?></option>
                <?php endfor; ?>
            </select>

            <label for="subject"><b>Subject:</b></label>
            <select id="subject" name="subject" required>
                <option value="">Select Subject</option>
                <?php if (!empty($selectedSubjects)): ?>
                    <?php foreach ($selectedSubjects as $subject): ?>
                        <option value="<?= $subject ?>" <?= ($subject == $selectedSubject) ? 'selected' : '' ?>><?= $subject ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>

            <label for="link_description"><b>Description:</b></label>
            <input type="text" id="link_description" name="link_description" required>

            <label for="link"><b>Link:</b></label>
            <input type="url" id="link" name="link" required>

            <label for="type"><b>Type:</b></label>
            <select id="type" name="type" required>
                <option value="college">College</option>
                <option value="youtube">YouTube</option>
                <option value="books">Books</option>
                <option value="other">Other</option>
            </select>

            <input type="submit" value="Add Link">
        </form>
        <a href="admin_panel.php" class="center-block">Back to Admin Panel</a>
    </div>
</body>
</html>