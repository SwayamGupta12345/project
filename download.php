<?php
// Initialize the session or retrieve the subject from the request
session_start();
$subject = isset($_GET['subject']) ? $_GET['subject'] : 'default';  // Default name if subject isn't found

// Example file URL (from Google Drive or external source)
$fileUrl = "https://example.com/path/to/your/file.pdf";  // Replace with the actual file URL
$fileExtension = pathinfo($fileUrl, PATHINFO_EXTENSION);  // Get the file extension (e.g., pdf or ppt)
$fileName = $subject . "." . $fileExtension;  // Generate the file name based on subject

// Use cURL to fetch the file from the URL
$ch = curl_init($fileUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  // Follow any redirects if necessary
$fileData = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Check if the file was fetched successfully
if ($httpCode == 200 && $fileData !== false) {
    // Set headers for file download
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . strlen($fileData));

    // Output the file data
    echo $fileData;
    exit();
} else {
    echo "File does not exist or could not be fetched.";
}
?>