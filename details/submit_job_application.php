<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ./../login/');
    exit;
}

$mysqli = new mysqli("localhost", "root", "", "foodbanks");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$foodBankId = $_POST['food_bank_id'];
$jobId = $_POST['job_id'];
$userId = $_SESSION['user_id'];
$name = $_POST['name'];
$surname = $_POST['surname'];
$jobExperience = $_POST['job_experience'];
$coverLetter = $_POST['cover_letter'];
$yearsOfExperience = $_POST['years_of_experience'];

// Insert the job application into the database
$query = "INSERT INTO job_applications (job_id, user_id, name, surname, job_experience, cover_letter, years_of_experience, status, applied_at) 
          VALUES (?, ?, ?, ?, ?, ?, ?, 'PENDING', NOW())";

$stmt = $mysqli->prepare($query);
$stmt->bind_param('iissssii', $jobId, $userId, $name, $surname, $jobExperience, $coverLetter, $yearsOfExperience);

if ($stmt->execute()) {
    echo "Your application has been submitted successfully!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$mysqli->close();
?>
