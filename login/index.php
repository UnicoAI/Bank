<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="./../css/styles.css" rel="stylesheet">
</head>
<body>
<?php include './../navbar/navbar.php'; ?>
<div class="d-flex justify-content-center align-items-center vh-100">
    <div class="col-md-4 text-center">
        <div class="modal-content">
        <h1>Login</h1>
        
        <!-- Display success message from registration -->
        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Display error message from session -->
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error_message']; 
                unset($_SESSION['error_message']); // Clear the error message
                ?>
            </div>
        <?php endif; ?>

        <form action="process_login.php" method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
    </div>
    <div class="mt-3">
        <p>Don't have an account? <a href="./../register">Register here</a></p>
    </div>
    </div>
    </div>
    <?php include './../footer/footer.php'; ?>
</body>
</html>
