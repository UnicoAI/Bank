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

$foodBankId = isset($_POST['food_bank_id']) ? $_POST['food_bank_id'] : null;
$userId = $_SESSION['user_id'];
$itemName = isset($_POST['item_name']) ? $_POST['item_name'] : null;
$quantity = isset($_POST['quantity']) ? $_POST['quantity'] : null;

if (!$foodBankId || !$itemName || !$quantity) {
    echo "Missing donation details.";
    exit;
}

// Insert donation into the database
$query = "INSERT INTO donations (food_bank_id, user_id, item_name, quantity, donation_date) VALUES (?, ?, ?, ?, NOW())";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('iisi', $foodBankId, $userId, $itemName, $quantity);

if ($stmt->execute()) {
    echo "Your donation has been submitted successfully!";
} else {
    echo "Failed to submit your donation. Please try again.";
}

$stmt->close();
$mysqli->close();
?>
