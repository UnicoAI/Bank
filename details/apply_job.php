<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ./../login/');
    exit;
}
$isLoggedIn = isset($_SESSION['user_id']); // Assuming you store `user_id` in session upon login


$mysqli = new mysqli("localhost", "root", "", "foodbanks");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Get parameters from the URL
$foodBankId = isset($_GET['food_bank_id']) ? $_GET['food_bank_id'] : null;
$jobId = isset($_GET['job_id']) ? $_GET['job_id'] : null;

// Check if the required parameters are provided
if ($foodBankId === null || $jobId === null) {
    die("Error: Missing parameters (food_bank_id, job_id).");
}

$userId = $_SESSION['user_id'];
$name = isset($_POST['name']) ? $_POST['name'] : '';
$surname = isset($_POST['surname']) ? $_POST['surname'] : '';
$jobExperience = isset($_POST['job_experience']) ? $_POST['job_experience'] : '';
$coverLetter = isset($_POST['cover_letter']) ? $_POST['cover_letter'] : '';
$yearsOfExperience = isset($_POST['years_of_experience']) ? $_POST['years_of_experience'] : 0;

// Insert the job application into the database
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Prepare the query and bind parameters
    $query = "INSERT INTO job_applications (job_id, user_id, name, surname, job_experience, cover_letter, years_of_experience, status, applied_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $mysqli->prepare($query);

    // Define the status as a variable
    $status = 'PENDING'; // The default status value
    // Bind the parameters for job_id, user_id, name, surname, job_experience, cover_letter, years_of_experience, and status
    $stmt->bind_param('iissssis', $jobId, $userId, $name, $surname, $jobExperience, $coverLetter, $yearsOfExperience, $status);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Your application has been submitted successfully!";
        header("Location: apply_job.php?food_bank_id=" . urlencode($foodBankId) . "&job_id=" . urlencode($jobId));
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
    

    $stmt->close();
}

// Close the connection
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Job</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="./../css/styles.css" rel="stylesheet">
</head>
<body>
<?php include './../navbar/navbar.php'; ?>
    <div class="container mt-5">
        <h2>Job Application</h2>
        <?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($_SESSION['success_message']); ?>
    </div>
    <?php unset($_SESSION['success_message']); // Clear the message after displaying ?>
<?php endif; ?>

        <form action="apply_job.php?food_bank_id=<?= htmlspecialchars($foodBankId) ?>&job_id=<?= htmlspecialchars($jobId) ?>" method="POST">
            <!-- Hidden input for food_bank_id and job_id passed through URL -->
            <input type="hidden" name="food_bank_id" value="<?= htmlspecialchars($foodBankId) ?>">
            <input type="hidden" name="job_id" value="<?= htmlspecialchars($jobId) ?>">

            <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>
            </div>

            <div class="mb-3">
                <label for="surname" class="form-label">Surname</label>
                <input type="text" class="form-control" id="surname" name="surname" value="<?= htmlspecialchars($surname) ?>" required>
            </div>

            <div class="mb-3">
                <label for="job_experience" class="form-label">Job Experience</label>
                <textarea class="form-control" id="job_experience" name="job_experience" rows="4" required><?= htmlspecialchars($jobExperience) ?></textarea>
            </div>

            <div class="mb-3">
                <label for="cover_letter" class="form-label">Cover Letter</label>
                <textarea class="form-control" id="cover_letter" name="cover_letter" rows="4" required><?= htmlspecialchars($coverLetter) ?></textarea>
            </div>

            <div class="mb-3">
                <label for="years_of_experience" class="form-label">Years of Experience</label>
                <input type="number" class="form-control" id="years_of_experience" name="years_of_experience" value="<?= htmlspecialchars($yearsOfExperience) ?>" required>
            </div>

            <button type="submit" class="btn btn-primary">Apply for Job</button>
        </form>
    </div>
    <?php include './../footer/footer.php'; ?>
    <!-- Bootstrap JS (optional, for interactivity like modals) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
