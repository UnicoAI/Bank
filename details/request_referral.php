<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if the user is not logged in
    header('Location: ./../login/');
    exit;
}

$mysqli = new mysqli("localhost", "root", "", "foodbanks");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$foodBankId = isset($_GET['food_bank_id']) ? $_GET['food_bank_id'] : null;
$userId = $_SESSION['user_id'];

if (!$foodBankId) {
    echo "Missing food bank ID.";
    exit;
}

// Insert referral request into the database
$query = "INSERT INTO referrals (food_bank_id, user_id, request_date) VALUES (?, ?, NOW())";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('ii', $foodBankId, $userId);

if ($stmt->execute()) {
    echo "Your referral request has been submitted successfully!";
} else {
    echo "Failed to submit your referral request. Please try again.";
}

$stmt->close();
$mysqli->close();
?>
