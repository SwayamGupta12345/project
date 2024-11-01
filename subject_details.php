<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject Details</title>
    <link rel="stylesheet" href="subject_details.css">
</head>

<body>
    <nav>
        <a class="home" href="index.php">HOME</a>
    </nav>

    <?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    $servername = "localhost";
    $username = "root";
    $password = "root";
    $dbname = "elp";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get the subject from the URL
    $subject = $_GET['subject'];
    $sem = (int)$_GET['sem'];
    $semTable = "sem".$sem;

    // Fetch subject details
    $sql = "SELECT image, subject, description FROM cards WHERE sem = $sem AND subject = '$subject'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo '<h1 class="head">' . $row["subject"] . '</h1>';
        echo '<div class="container">';
        echo '<p>' . $row["description"] . '</p>';
        echo '<img src="' . $row["image"] . '" alt="Subject Image" class="image">';
    } else {
        echo '<p>No data available for this subject.</p>';
    }

    echo '</div>';

    // Fetch subject links from another table by type
    echo '<div class="flex">';

    // Define the types you want to display
    $types = ['college', 'youtube', 'other', 'books'];

    foreach ($types as $type) {
        echo '<h3>' . ucfirst($type) . ' Resources</h3>';
        echo '<table class="subject-links">';
        echo '<tr><th>Description</th><th>Link</th></tr>';

        $link_sql = "SELECT description, link FROM $semTable WHERE subject = '$subject' AND type='$type'";
        $link_result = $conn->query($link_sql);

        if ($link_result->num_rows > 0) {
            while ($link_row = $link_result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . $link_row["description"] . '</td>';
                echo '<td><a href="'.$link_row["link"].'">Download Link</a></td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="2">No links available for this subject.</td></tr>';
        }

        echo '</table>';
    }

    echo '</div>';
    echo '</div>';

    $conn->close();
    ?>

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
                <p>&copy; 2024 Your Project Name. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>