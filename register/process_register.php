<?php
require './../data/db.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone_number = $_POST['phone_number'];

    try {
        // Prepare SQL query
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, phone_number) VALUES (:first_name, :last_name, :email, :password, :phone_number)");

        // Bind parameters
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':phone_number', $phone_number);

        // Execute the query
        $stmt->execute();

        // Redirect to login page with success message
        header("Location: ./../login?message=" . urlencode("Registration successful! You can now log in."));
        exit();
    } catch (PDOException $e) {
        // Handle duplicate entry error (SQLSTATE 23000)
        if ($e->getCode() == 23000) {
            header("Location: index.php?error=" . urlencode("This email is already registered. Please use another email."));
        } else {
            header("Location: index.php?error=" . urlencode("An error occurred. Please try again later."));
        }
        exit();
    }
}
?>
