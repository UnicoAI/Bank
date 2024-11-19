<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']); // Assuming you store `user_id` in session upon login

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

$foodBankId = isset($_GET['id']) ? $_GET['id'] : null;

if (!$foodBankId) {
    echo "Food bank ID is missing.";
    exit;
}

$message = '';  // Initialize message variable to store success or error message

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$foodBankId || !isset($_SESSION['user_id'])) {
        $message = "Missing food bank ID or user ID.";
    } else {
        // Get user_id from session
        $userId = $_SESSION['user_id'];

        // Insert referral request into the database
        $query = "INSERT INTO referrals (food_bank_id, user_id, request_date) VALUES (?, ?, NOW())";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('ii', $foodBankId, $userId);

        if ($stmt->execute()) {
            $message = "Your referral request has been submitted successfully!";
        } else {
            $message = "Failed to submit your referral request. Please try again.";
        }

        $stmt->close();
    }
}

// Fetch food bank details
$query = "SELECT * FROM food_banks WHERE food_bank_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('i', $foodBankId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $foodBank = $result->fetch_assoc();
} else {
    echo "Food bank not found.";
    exit;
}

// Fetch food bank needs (items and quantities)
$needsQuery = "SELECT * FROM food_bank_needs WHERE food_bank_id = ?";
$needsStmt = $mysqli->prepare($needsQuery);
$needsStmt->bind_param('i', $foodBankId);
$needsStmt->execute();
$needsResult = $needsStmt->get_result();

// Check if there are any available job positions
$jobQuery = "SELECT * FROM job_positions WHERE food_bank_id = ? AND created_at <= NOW()";  // Assuming job positions with valid creation dates
$jobStmt = $mysqli->prepare($jobQuery);
$jobStmt->bind_param('i', $foodBankId);
$jobStmt->execute();
$jobResult = $jobStmt->get_result();
$hasAvailableJob = $jobResult->num_rows > 0;

$stmt->close();
$jobStmt->close();
$needsStmt->close();
?>

<!-- HTML Section for the Referral Form and Messages -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Food Bank Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="./../css/styles.css" rel="stylesheet">
</head>
<body>
    <?php include './../navbar/navbar.php'; ?>
<div class="container mt-4">
    <div class="row">
        <!-- Left Column: Food Bank Details, Needs, and Donation Form -->
        <div class="col-md-6">
            <h2><?= htmlspecialchars($foodBank['name']) ?></h2>
            <p><strong>Address:</strong> <?= htmlspecialchars($foodBank['address']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($foodBank['phone']) ?></p>
            <p><strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($foodBank['email']) ?>"><?= htmlspecialchars($foodBank['email']) ?></a></p>
            
            <!-- Display Referral Request Form -->
            <?php if ($message): ?>
                <div class="alert alert-info">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            
            <!-- Referral Request Form -->
            <p><strong>Referral Required:</strong> <?= $foodBank['referral_required'] ? 'Yes' : 'No' ?></p>
            <?php if ($foodBank['referral_required'] || $hasAvailableJob): ?>
                <?php if ($foodBank['referral_required']): ?>
                    <form action="" method="POST">
                        <input type="hidden" name="food_bank_id" value="<?= htmlspecialchars($foodBank['food_bank_id']) ?>">
                        <button type="submit" class="btn btn-info">Request Referral</button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
            
            <!-- Donation Form -->
            <div class="glass" role="alert">
            <h5 class="card-title">Make a Difference: Donate Today</h5>
            <p class="card-text">Your donation helps provide meals, shelter, and hope for those in need. Food banks rely on your generosity to continue their essential work. Whether you can donate food, money, or time, your contributions are always welcome. Together, we can make a lasting impact on our community.</p>
            <a href="https://buy.stripe.com/7sI292fWV9vSdZ69AA?locale=en-GB&__embed_source=buy_btn_1QMWGpDCQrwsyQEze28onzE3" class="btn-primary">Donate Now</a>
        </div>

            <h4>Food Bank Needs</h4>
            <?php if ($needsResult->num_rows > 0): ?>
                
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Quantity Needed</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($need = $needsResult->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($need['item_name']) ?></td>
                                <td><?= htmlspecialchars($need['quantity_needed']) ?></td>
                                <td><?= htmlspecialchars($need['created_at']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No food bank needs listed at the moment.</p>
            <?php endif; ?>
        </div>

        <!-- Right Column: Available Job Positions -->
        <div class="col-md-6">
            <?php if ($hasAvailableJob): ?>
                <h4>Available Job Positions</h4>
                <ul>
                    <?php while ($job = $jobResult->fetch_assoc()): ?>
                        <li>
                            <h5><?= htmlspecialchars($job['title']) ?></h5>
                            <p><strong>Description:</strong> <?= htmlspecialchars($job['description']) ?></p>
                            <p><strong>Requirements:</strong> <?= htmlspecialchars($job['requirements']) ?></p>
                            <form action="apply_job.php" method="get">
                                <input type="hidden" name="food_bank_id" value="<?= htmlspecialchars($foodBank['food_bank_id']) ?>">
                                <input type="hidden" name="job_id" value="<?= htmlspecialchars($job['job_id']) ?>">
                                <button type="submit" class="btn btn-primary">Apply for this Job</button>
                            </form>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>No available job positions at the moment.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include './../footer/footer.php'; ?>
</body>
</html>

<?php $mysqli->close(); ?>
