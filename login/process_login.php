<?php
session_start(); // Start a session
require './../data/db.php'; // Include database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare the SQL query using PDO
    $stmt = $conn->prepare("SELECT user_id, first_name, last_name, password, is_admin FROM users WHERE email = :email");
    
    // Bind the parameter using bindValue() for PDO
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    
    // Execute the query
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Verify the password
        if (password_verify($password, $result['password'])) {
            // Store user data in the session
            $_SESSION['user_id'] = $result['user_id'];
            $_SESSION['first_name'] = $result['first_name'];
            $_SESSION['last_name'] = $result['last_name'];
            $_SESSION['is_admin'] = $result['is_admin'];

            // Redirect based on user role
            if ($result['is_admin']) {
                header("Location: ./../admin");
            } else {
                header("Location: ./../user");
            }
            exit;
        } else {
            // Invalid password
            $_SESSION['error_message'] = "Invalid email or password.";
            header("Location: index.php");
        }
    } else {
        // No user found
        $_SESSION['error_message'] = "No account found with this email.";
        header("Location: index.php");
    }
    $stmt->close();
}
?>
