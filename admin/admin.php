<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    echo "You need to log in as an admin.";
    exit;
}

$mysqli = new mysqli("localhost", "root", "", "foodbanks");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$message = '';
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'food_banks';

// Fetch Data
$foodBanks = $mysqli->query("SELECT * FROM food_banks");
$jobPositions = $mysqli->query("
    SELECT jp.*, fb.name AS food_bank_name 
    FROM job_positions jp 
    JOIN food_banks fb ON jp.food_bank_id = fb.food_bank_id
");
$jobApplications = $mysqli->query("
    SELECT ja.*, jp.title AS job_title, fb.name AS food_bank_name 
    FROM job_applications ja 
    JOIN job_positions jp ON ja.job_id = jp.job_id 
    JOIN food_banks fb ON jp.food_bank_id = fb.food_bank_id
");
$foodBankNeeds = $mysqli->query("
    SELECT fbn.*, fb.name AS food_bank_name 
    FROM food_bank_needs fbn 
    JOIN food_banks fb ON fbn.food_bank_id = fb.food_bank_id
");
$users = $mysqli->query("SELECT * FROM users");

// CRUD Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Food Banks
    if (isset($_POST['add_food_bank'])) {
        $stmt = $mysqli->prepare("
            INSERT INTO food_banks (name, address, latitude, longitude, opening_time, closing_time, email, phone, referral_required) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            'ssddssssi',
            $_POST['name'], $_POST['address'], $_POST['latitude'], $_POST['longitude'],
            $_POST['opening_time'], $_POST['closing_time'], $_POST['email'], $_POST['phone'], $_POST['referral_required']
        );
        $message = $stmt->execute() ? "Food bank added successfully!" : "Error: {$stmt->error}";
    } elseif (isset($_POST['update_food_bank'])) {
        $stmt = $mysqli->prepare("
            UPDATE food_banks 
            SET name = ?, address = ?, latitude = ?, longitude = ?, opening_time = ?, closing_time = ?, email = ?, phone = ?, referral_required = ? 
            WHERE food_bank_id = ?
        ");
        $stmt->bind_param(
            'ssddssssi',
            $_POST['name'], $_POST['address'], $_POST['latitude'], $_POST['longitude'],
            $_POST['opening_time'], $_POST['closing_time'], $_POST['email'], $_POST['phone'], $_POST['referral_required'], $_POST['food_bank_id']
        );
        $message = $stmt->execute() ? "Food bank updated successfully!" : "Error: {$stmt->error}";
    } elseif (isset($_POST['delete_food_bank'])) {
        $stmt = $mysqli->prepare("DELETE FROM food_banks WHERE food_bank_id = ?");
        $stmt->bind_param('i', $_POST['food_bank_id']);
        $message = $stmt->execute() ? "Food bank deleted successfully!" : "Error: {$stmt->error}";
    }

    // Job Positions
    if (isset($_POST['add_job_position'])) {
        $stmt = $mysqli->prepare("
            INSERT INTO job_positions (food_bank_id, title, description, requirements) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param(
            'isss',
            $_POST['food_bank_id'], $_POST['title'], $_POST['description'], $_POST['requirements']
        );
        $message = $stmt->execute() ? "Job position added successfully!" : "Error: {$stmt->error}";
    } elseif (isset($_POST['update_job_position'])) {
        $stmt = $mysqli->prepare("
            UPDATE job_positions 
            SET food_bank_id = ?, title = ?, description = ?, requirements = ? 
            WHERE job_id = ?
        ");
        $stmt->bind_param(
            'isssi',
            $_POST['food_bank_id'], $_POST['title'], $_POST['description'], $_POST['requirements'], $_POST['job_id']
        );
        $message = $stmt->execute() ? "Job position updated successfully!" : "Error: {$stmt->error}";
    } elseif (isset($_POST['delete_job_position'])) {
        $stmt = $mysqli->prepare("DELETE FROM job_positions WHERE job_id = ?");
        $stmt->bind_param('i', $_POST['job_id']);
        $message = $stmt->execute() ? "Job position deleted successfully!" : "Error: {$stmt->error}";
    }

    // Other entities...
}

$mysqli->close();
?>
