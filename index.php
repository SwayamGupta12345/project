<?php
session_start(); // This must be the very first thing in your script

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "elp";
$port = "3306"; // Set this to your phpMyAdmin port if different

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Handle logout
if(isset($_GET['logout'])) {
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session
    header("Location: index.php"); // Redirect to the homepage after logout
    exit();
}

// Handle user registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $email = $_POST['reg_email'];
    $password = $_POST['reg_password'];

    // Regular expression for stronger email validation
    $pattern = "/^(23[0-9]{6}|99(23|24|25|26|27|28|29)[0-9]{4}|JEG(23|24|25|26|27|28|29)[0-9]{4}|NRG(23|24|25|26|27|28|29)[0-9]{4}|ECN(23|24|25|26|27|28|29)[0-9]{4}|NJG(23|24|25|26|27|28|29)[0-9]{4})@mail\.jiit\.ac\.in$/";

    // Check if email matches the pattern
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match($pattern, $email)) {
        echo "<script>alert('Invalid email format. Please use an appropriate JIIT email address.');</script>";
    } else {
        // Check if the email already exists
        $checkEmailStmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $checkEmailStmt->bind_param("s", $email);
        $checkEmailStmt->execute();
        $checkEmailStmt->store_result();

        if ($checkEmailStmt->num_rows > 0) {
            // Email already exists, show an error message
            echo "<script>alert('This email is already registered. Please use a different email or login.');</script>";
        } else {
            // Hash the password
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);

            // Prepare and bind
            $stmt = $conn->prepare("INSERT INTO users (email, password_hash) VALUES (?, ?)");
            $stmt->bind_param("ss", $email, $passwordHash);

            if ($stmt->execute()) {
                echo "<script>alert('Registration successful!');</script>";
            } else {
                echo "<script>alert('Error: " . $stmt->error . "');</script>";
            }

            $stmt->close();
        }

        $checkEmailStmt->close();
    }
}

// Handle user login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // First, check if the email exists in the admin table
    $stmt = $conn->prepare("SELECT password_hash FROM admin WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Admin found
        $stmt->bind_result($passwordHash);
        $stmt->fetch();

        // Verify the password for admin
        if (password_verify($password, $passwordHash)) {
            // Store session data for admin
            $_SESSION['user_email'] = $email;
            header("Location: admin_panel.php"); // Redirect to the add item page
            exit();
        } else {
            echo "<script>alert('Invalid admin credentials.');</script>";
        }
    } else {
        // If not found in admin, check the users table
        $stmt->close(); // Close the previous statement

        $stmt = $conn->prepare("SELECT password_hash FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // User found
            $stmt->bind_result($passwordHash);
            $stmt->fetch();

            // Verify the password for user
            if (password_verify($password, $passwordHash)) {
                // Store session data for regular user
                $_SESSION['user_email'] = $email;
                header("Location: index.php"); // Redirect to a regular page
                exit();
            } else {
                echo "<script>alert('Invalid user credentials.');</script>";
            }
        } else {
            echo "<script>alert('No user found with this email.');</script>";
        }
    }

    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Learning Platform</title>
    <link rel="stylesheet" href="inde.css">
</head>
<header>
<h1>Jaypee Learning Hub</h1>
</header>
<hr>
<nav>
    <div class="burger" id="burger-menu">
        <div></div>
        <div></div>
        <div></div>
    </div>
    <a class="home" href="#">HOME</a>
    <div class="nav-links" id="nav-links">
        <select name="semester" id="semester-menu" onchange="navigateToSemester()">
            <option value="">--Select Semester--</option>
            <option value="sem1">I</option>
            <option value="sem2">II</option>
            <option value="sem3">III</option>
            <option value="sem4">IV</option>
            <option value="sem5">V</option>
            <option value="sem6">VI</option>
            <option value="sem7">VII</option>
            <option value="sem8">VIII</option>
        </select>
       <!-- Conditionally show login or user email with dropdown -->
        <?php if (isset($_SESSION['user_email'])): ?>
            <div class="user-dropdown">
                <span class="user-email" onclick="toggleDropdown()">
                    <?php echo $_SESSION['user_email']; ?>
                </span>
                <div class="dropdown-content" id="dropdown-content">
                    <a href="index.php?logout=true">Logout</a>
                </div>
            </div>
        <?php else: ?>
            <a href="#" class="login-button">Login</a>
        <?php endif; ?>
    </div>
</nav>

<!-- Login Form -->
<div class="flex-col login-form" id="login-form">
    <button class="close-button" id="close-button">&times;</button> <!-- Close Button -->
    <h2>Login</h2>
    <form method="POST" action="">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login">Login</button>
        <p>   Not a user? <a href="#" id="show-register">Don't worry, register first!</a></p>
    </form>
    <div id="message"></div> <!-- For displaying messages -->
</div>

<!-- Registration Form -->
<div class="flex-col register-form" id="register-form">
    <button class="close-button" id="close-register-button">&times;</button> <!-- Close Button -->
    <h2>Register</h2>
    <form method="POST" action="">
        <input type="email" name="reg_email" placeholder="Email" required>
        <input type="password" name="reg_password" placeholder="Password" required>
        <button type="submit" name="register">Register</button>
    </form>
    <p>   remember username and password? <a href="#" id="login-button">Don't worry, login!</a></p>
    <div id="register-message"></div> <!-- For displaying registration messages -->
</div>

<div class="container">
        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Sed sequi facilis dolorum dignissimos nihil. Fugit</p>
        <img src="https://media.istockphoto.com/id/1409722748/photo/students-raising-hands-while-teacher-asking-them-questions-in-classroom.jpg?s=612x612&w=0&k=20&c=NbVChOV9wIbQOhUD6BqpouZHHBbyQ2rkSjaVfIhpMv8=" alt="Image description">
    </div>
    <br><br>

    <div class="container">
        <img src="https://media.istockphoto.com/id/1409722748/photo/students-raising-hands-while-teacher-asking-them-questions-in-classroom.jpg?s=612x612&w=0&k=20&c=NbVChOV9wIbQOhUD6BqpouZHHBbyQ2rkSjaVfIhpMv8=" alt="Image description">
        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Sed sequi facilis dolorum dignissimos nihil. Fugit
            eveniet id.</p>
    </div>
    <br><br>
    <div class="container">
        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Sed sequi facilis dolorum dignissimos nihil. Fugit</p>
        <img src="https://media.istockphoto.com/id/1409722748/photo/students-raising-hands-while-teacher-asking-them-questions-in-classroom.jpg?s=612x612&w=0&k=20&c=NbVChOV9wIbQOhUD6BqpouZHHBbyQ2rkSjaVfIhpMv8=" alt="Image description">
    </div>

<div class="card-container">
<?php
// Database connection and query for cards remains the same
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "elp";
$port = "3306"; // Adjust if needed

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sem_sql = "SELECT DISTINCT sem FROM cards ORDER BY sem ASC";
$sem_result = $conn->query($sem_sql);

if ($sem_result->num_rows > 0) {
    while ($sem_row = $sem_result->fetch_assoc()) {
        $sem = $sem_row['sem'];

        echo '<div class="sems" id="sem' . $sem . '">';
        echo '<p class="SEMTEXT">SEM-' . $sem . '</p>';
        echo '</div>';

        $sql = "SELECT image, subject, description FROM cards WHERE sem = $sem";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<div class="card">';
                echo '<img src="' . $row["image"] . '" alt="Image">';
                echo '<h3>' . $row["subject"] . '</h3>';
                echo '<div class="desc"><p><a href= "subject_details.php?sem=' . $sem . '&subject=' . urlencode($row["subject"]) . '" target="_blank">' . $row["description"] . '</a></p></div>';
                echo '</div>';
            }
        } else {
            echo '<p>No cards available for SEM-' . $sem . '</p>';
        }
    }
} else {
    echo '<p>No SEM data available</p>';
}

$conn->close();
?>
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
<script>
        function navigateToSemester() {
            const semester = document.getElementById("semester-menu").value;
            if (semester) {
                const semesterSection = document.getElementById(semester);
                if (semesterSection) {
                    semesterSection.scrollIntoView({ behavior: "smooth" });
                }
            }
        }

        function toggleDropdown() {
            const dropdown = document.getElementById('dropdown-content');
            dropdown.classList.toggle('show');
        }   


        window.onclick = function(event) {
            const emailElement = document.querySelector('.user-email');
            const dropdown = document.getElementById('dropdown-content');
            const burgerMenu = document.getElementById('burger-menu');
            const navLinks = document.getElementById('nav-links');
    
            // Close dropdown if clicking outside of email and dropdown
            if (dropdown.classList.contains('show') && !emailElement.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.remove('show');
            }
    
            // Close nav-links if clicking outside of burger icon and nav-links
            if (navLinks.classList.contains('show') && !burgerMenu.contains(event.target) && !navLinks.contains(event.target)) {
                navLinks.classList.remove('show');
            }
        };

        document.getElementById('burger-menu').addEventListener('click', function() {
            document.getElementById('nav-links').classList.toggle('show');
        });

        document.getElementById('burger-menu').addEventListener('click', function(event) {
            event.stopPropagation(); // Prevent click from propagating
            document.getElementById('nav-links').classList.toggle('show');
        });

        
        // Close the menu when clicking outside the burger menu or nav-links
        document.addEventListener("DOMContentLoaded", function() {
            const burger = document.querySelector('.burger');
            const navLinks = document.querySelector('.nav-links');

            burger.addEventListener('click', () => {
            navLinks.classList.toggle('show'); // Toggles the 'show' class on click
            });
        }); 

        // Close the menu when clicking any link inside the nav-links
        document.querySelectorAll('#nav-links a').forEach(function (link) {
            link.addEventListener('click', function () {
                document.getElementById('nav-links').classList.remove('show');
            });
        });
        
        
        // Toggle login form on "Login" button click
        document.querySelector('.login-button').addEventListener('click', function (event) {
            event.preventDefault(); // Prevent default link behavior
            document.getElementById('login-form').classList.toggle('active');
        });

        // Close login form on close button click
        document.getElementById('close-button').addEventListener('click', function () {
            document.getElementById('login-form').classList.remove('active');
        })
        
        // Show registration form when "register first" is clicked
        document.getElementById('show-register').addEventListener('click', function (event) {
            event.preventDefault(); // Prevent the default link behavior
            document.getElementById('login-form').classList.remove('active'); // Hide login form
            document.getElementById('register-form').classList.add('active'); // Show registration form
        });
        // Show login form when "login" is clicked on registration form
        document.getElementById('login-button').addEventListener('click', function (event) {
            event.preventDefault(); // Prevent the default link behavior
            document.getElementById('register-form').classList.remove('active'); // Hide registration form
            document.getElementById('login-form').classList.add('active'); // Show login form
        });
        
        // Close button functionality for the registration form
        document.getElementById('close-register-button').addEventListener('click', function () {
            document.getElementById('register-form').classList.remove('active'); // Hide registration form
        });
         
        </script>
</body>
</html>
