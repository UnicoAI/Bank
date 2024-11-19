<?php
session_start();

// Check if the user is logged in and the user_id is set
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page with a URL message
    $message = urlencode("You need to log in first.");
    header("Location: ./../login?message=$message");
    exit;
}


$mysqli = new mysqli("localhost", "root", "", "foodbanks");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$userId = $_SESSION['user_id'];
$message = '';

// Fetch all referral requests with food bank details for the logged-in user
$referralQuery = "
    SELECT r.*, fb.name AS food_bank_name
    FROM referrals r
    JOIN food_banks fb ON r.food_bank_id = fb.food_bank_id
    WHERE r.user_id = ?
";
$referralStmt = $mysqli->prepare($referralQuery);
$referralStmt->bind_param('i', $userId);
$referralStmt->execute();
$referralResult = $referralStmt->get_result();

// Fetch all job applications with job position, food bank details for the logged-in user
$jobQuery = "
    SELECT ja.*, jp.title, jp.description, jp.requirements, fb.name AS food_bank_name
    FROM job_applications ja
    JOIN job_positions jp ON ja.job_id = jp.job_id
    JOIN food_banks fb ON jp.food_bank_id = fb.food_bank_id
    WHERE ja.user_id = ?
";
$jobStmt = $mysqli->prepare($jobQuery);
$jobStmt->bind_param('i', $userId);
$jobStmt->execute();
$jobResult = $jobStmt->get_result();

// Password change handling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $oldPassword = $_POST['old_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($newPassword === $confirmPassword) {
        // Fetch the current password
        $passwordQuery = "SELECT password FROM users WHERE user_id = ?";
        $passwordStmt = $mysqli->prepare($passwordQuery);
        $passwordStmt->bind_param('i', $userId);
        $passwordStmt->execute();
        $passwordResult = $passwordStmt->get_result();
        $passwordRow = $passwordResult->fetch_assoc();

        if (password_verify($oldPassword, $passwordRow['password'])) {
            // Hash the new password and update it
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updatePasswordQuery = "UPDATE users SET password = ? WHERE user_id = ?";
            $updatePasswordStmt = $mysqli->prepare($updatePasswordQuery);
            $updatePasswordStmt->bind_param('si', $hashedPassword, $userId);

            if ($updatePasswordStmt->execute()) {
                $message = "Password changed successfully!";
            } else {
                $message = "Failed to change password.";
            }
        } else {
            $message = "Old password is incorrect.";
        }
    } else {
        $message = "New password and confirmation do not match.";
    }
}

$referralStmt->close();
$jobStmt->close();
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="./../css/styles.css" rel="stylesheet">
    
</head>
<body>
<?php include './../navbar/navbar.php'; ?>
<div class="container mt-4">
    <h1>User Dashboard</h1>
    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <ul class="nav nav-tabs" id="dashboardTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active" id="referrals-tab" data-bs-toggle="tab" href="#referrals" role="tab">Referral Requests</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="applications-tab" data-bs-toggle="tab" href="#applications" role="tab">Job Applications</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="password-tab" data-bs-toggle="tab" href="#password" role="tab">Change Password</a>
        </li>
    </ul>

    <div class="tab-content mt-3">
        <div class="tab-pane fade show active" id="referrals" role="tabpanel">
            <h3>Your Referral Requests</h3>
            <?php if ($referralResult->num_rows > 0): ?>
                <div class="glass" style="overflow-x:auto;">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Food Bank Name</th>
                         
                            <th>Request Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($referral = $referralResult->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($referral['food_bank_name']) ?></td>
                              <td><?= htmlspecialchars($referral['request_date']) ?></td>
                                <td><?= htmlspecialchars($referral['status']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                </div>
            <?php else: ?>
                <p>No referral requests found.</p>
            <?php endif; ?>
        </div>

        <div class="tab-pane fade" id="applications" role="tabpanel">
            <h3>Your Job Applications</h3>
            <?php if ($jobResult->num_rows > 0): ?>
                <div class="glass" style="overflow-x:auto;">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Food Bank Name</th>
                            <th>Job Title</th>
                            <th>Job Description</th>
                            <th>Requirements</th>
                            <th>Application Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($job = $jobResult->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($job['food_bank_name']) ?></td>
                                <td><?= htmlspecialchars($job['title']) ?></td>
                                <td><?= htmlspecialchars($job['description']) ?></td>
                                <td><?= htmlspecialchars($job['requirements']) ?></td>
                                <td><?= htmlspecialchars($job['applied_at']) ?></td>
                                <td><?= htmlspecialchars($job['status']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                </div>
            <?php else: ?>
                <p>No job applications found.</p>
            <?php endif; ?>
        </div>

        <div class="tab-pane fade" id="password" role="tabpanel">
        <div class="d-flex justify-content-center align-items-center vh-100">
    <div class="col-md-4 text-center">
        <div class="modal-content">
            <h3>Change Password</h3>
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="old_password" class="form-label">Old Password</label>
                    <input type="password" class="form-control" id="old_password" name="old_password" required>
                </div>
                <div class="mb-3">
                    <label for="new_password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
            </form>
            </div>
            </div>
            </div>
        </div>
    </div>
</div>

<?php include './../footer/footer.php'; ?>
</body>
</html>
