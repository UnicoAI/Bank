<?php
session_start();

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    echo "You need to log in as an admin.";
    exit;
}

$mysqli = new mysqli("localhost", "root", "", "foodbanks");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$message = '';
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'food_banks';

$foodBanks = $mysqli->query("SELECT * FROM food_banks");
$foodBankNeeds = $mysqli->query("SELECT food_bank_needs.need_id, food_banks.name as food_bank_name, food_bank_needs.item_name, food_bank_needs.quantity_needed FROM food_bank_needs INNER JOIN food_banks ON food_bank_needs.food_bank_id = food_banks.food_bank_id");
$jobPositions = $mysqli->query("SELECT job_positions.job_id, food_banks.name as food_bank_name, job_positions.title, job_positions.description FROM job_positions INNER JOIN food_banks ON job_positions.food_bank_id = food_banks.food_bank_id");
$jobApplications = $mysqli->query("SELECT job_applications.application_id, job_applications.user_id, job_applications.job_id, 
job_applications.status, job_applications.cover_letter, job_applications.applied_at, 
job_positions.title, job_applications.job_experience, job_applications.years_of_experience, 
food_banks.name AS food_bank_name, users.first_name, users.last_name
FROM job_applications
JOIN job_positions ON job_applications.job_id = job_positions.job_id
JOIN food_banks ON job_positions.food_bank_id = food_banks.food_bank_id
JOIN users ON job_applications.user_id = users.user_id");

$referrals = $mysqli->query("SELECT referrals.referral_id, referrals.status, users.first_name AS user_name, food_banks.name AS food_bank_name 
        FROM referrals
        JOIN users ON referrals.user_id = users.user_id
        JOIN food_banks ON referrals.food_bank_id = food_banks.food_bank_id");


$users = $mysqli->query("SELECT * FROM users");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add Food Bank
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
    }

    // Add Food Bank Need
    elseif (isset($_POST['add_food_bank_need'])) {
        $stmt = $mysqli->prepare("
            INSERT INTO food_bank_needs (food_bank_id, item_name, quantity_needed) 
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param('isi', $_POST['food_bank_id'], $_POST['item_name'], $_POST['quantity_needed']);
        $message = $stmt->execute() ? "Food bank need added successfully!" : "Error: {$stmt->error}";
    }

    // Add Job Position
    elseif (isset($_POST['add_job_position'])) {
        $stmt = $mysqli->prepare("
            INSERT INTO job_positions (food_bank_id, title, description) 
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param('iss', $_POST['food_bank_id'], $_POST['title'], $_POST['description']);
        $message = $stmt->execute() ? "Job position added successfully!" : "Error: {$stmt->error}";
    }

    // Add Job Application
    elseif (isset($_POST['add_job_application'])) {
        $stmt = $mysqli->prepare("
            INSERT INTO job_applications (job_id, user_id, status) 
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param('iis', $_POST['job_id'], $_POST['user_id'], $_POST['status']);
        $message = $stmt->execute() ? "Job application added successfully!" : "Error: {$stmt->error}";
    }

    // Add Referral
    elseif (isset($_POST['add_referral'])) {
        $stmt = $mysqli->prepare("
            INSERT INTO referrals (user_id, referral_name, status) 
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param('iss', $_POST['user_id'], $_POST['referral_name'], $_POST['status']);
        $message = $stmt->execute() ? "Referral added successfully!" : "Error: {$stmt->error}";
    }

    // Update Job Application Status
    elseif (isset($_POST['update_job_application_status'])) {
        $stmt = $mysqli->prepare("UPDATE job_applications SET status = ? WHERE application_id = ?");
        $stmt->bind_param('si', $_POST['status'], $_POST['application_id']);
        $message = $stmt->execute() ? "Job application status updated successfully!" : "Error: {$stmt->error}";
    }

    // Delete Job Application
    elseif (isset($_POST['delete_job_application'])) {
        $stmt = $mysqli->prepare("DELETE FROM job_applications WHERE application_id = ?");
        $stmt->bind_param('i', $_POST['application_id']);
        $message = $stmt->execute() ? "Job application deleted successfully!" : "Error: {$stmt->error}";
    }

    // Update Referral Status
    elseif (isset($_POST['update_referral_status'])) {
        $stmt = $mysqli->prepare("UPDATE referrals SET status = ? WHERE referral_id = ?");
        $stmt->bind_param('si', $_POST['status'], $_POST['referral_id']);
        $message = $stmt->execute() ? "Referral status updated successfully!" : "Error: {$stmt->error}";
    }

    // Delete Referral
    elseif (isset($_POST['delete_referral'])) {
        $stmt = $mysqli->prepare("DELETE FROM referrals WHERE referral_id = ?");
        $stmt->bind_param('i', $_POST['referral_id']);
        $message = $stmt->execute() ? "Referral deleted successfully!" : "Error: {$stmt->error}";
    }

    // Delete Food Bank Need
    elseif (isset($_POST['delete_food_bank_need'])) {
        $stmt = $mysqli->prepare("DELETE FROM food_bank_needs WHERE need_id = ?");
        $stmt->bind_param('i', $_POST['need_id']);
        $message = $stmt->execute() ? "Food bank need deleted successfully!" : "Error: {$stmt->error}";
    }

    // Delete Job Position
    elseif (isset($_POST['delete_job_position'])) {
        $stmt = $mysqli->prepare("DELETE FROM job_positions WHERE job_id = ?");
        $stmt->bind_param('i', $_POST['job_id']);
        $message = $stmt->execute() ? "Job position deleted successfully!" : "Error: {$stmt->error}";
    }

    // Delete User (if needed)
    elseif (isset($_POST['delete_user'])) {
        $stmt = $mysqli->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param('i', $_POST['user_id']);
        $message = $stmt->execute() ? "User deleted successfully!" : "Error: {$stmt->error}";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="./../css/styles.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Admin Dashboard</a>
        <div class="navbar-nav">
            <a class="nav-item nav-link" href="?tab=food_banks">Food Banks</a>
            <a class="nav-item nav-link" href="?tab=food_bank_needs">Food Bank Needs</a>
            <a class="nav-item nav-link" href="?tab=job_positions">Job Positions</a>
            <a class="nav-item nav-link" href="?tab=job_applications">Job Applications</a>
            <a class="nav-item nav-link" href="?tab=referrals">Referrals</a>
            <a class="nav-item nav-link" href="?tab=users">Users</a>
        </div>
    </nav>

    <p><?= $message ?></p>

    <!-- Food Banks Section -->
    <?php if ($tab === 'food_banks'): ?>
        <h2>Food Banks</h2>
        <!-- Button to trigger modal -->
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFoodBankModal">
  Add New Food Bank
</button>

<!-- Modal -->
<div class="modal fade" id="addFoodBankModal" tabindex="-1" aria-labelledby="addFoodBankModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addFoodBankModalLabel">Add New Food Bank</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
      </div>
      <div class="modal-body">
        <!-- Form inside the modal -->
        <form method="POST">
            <div class="form-group">
                <input type="text" name="name" class="form-control" placeholder="Name" required>
            </div>
            <div class="form-group">
                <input type="text" name="address" class="form-control" placeholder="Address" required>
            </div>
            <div class="form-group">
                <input type="text" name="latitude" class="form-control" placeholder="Latitude" required>
            </div>
            <div class="form-group">
                <input type="text" name="longitude" class="form-control" placeholder="Longitude" required>
            </div>
            <div class="form-group">
                <input type="time" name="opening_time" class="form-control" placeholder="Opening Time" required>
            </div>
            <div class="form-group">
                <input type="time" name="closing_time" class="form-control" placeholder="Closing Time" required>
            </div>
            <div class="form-group">
                <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>
            <div class="form-group">
                <input type="text" name="phone" class="form-control" placeholder="Phone" required>
            </div>
            <div class="form-group">
                <input type="checkbox" name="referral_required"> Referral Required
            </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary" name="add_food_bank">Add Food Bank</button>
      </div>
    </div>
  </div>
</div>
<div class="glass" style="overflow-x:auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Address</th>
                    <th>Opening Time</th>
                    <th>Closing Time</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($foodBank = $foodBanks->fetch_assoc()): ?>
                    <tr>
                        <td><?= $foodBank['name'] ?></td>
                        <td><?= $foodBank['address'] ?></td>
                        <td><?= $foodBank['opening_time'] ?></td>
                        <td><?= $foodBank['closing_time'] ?></td>
                        <td><?= $foodBank['email'] ?></td>
                        <td><?= $foodBank['phone'] ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="food_bank_id" value="<?= $foodBank['food_bank_id'] ?>">
                                <button type="submit" class="btn btn-danger" name="delete_food_bank">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
   </div>
      
    <?php endif; ?>

    <!-- Food Bank Needs Section -->
    <?php if ($tab === 'food_bank_needs'): ?>
        <h2>Food Bank Needs</h2>
        <!-- Button to trigger modal -->
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFoodBankNeedModal">
  Add New Food Bank Need
</button>

<!-- Modal -->
<div class="modal fade" id="addFoodBankNeedModal" tabindex="-1" aria-labelledby="addFoodBankNeedModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addFoodBankNeedModalLabel">Add New Food Bank Need</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
      </div>
      <div class="modal-body">
        <!-- Form inside the modal -->
        <form method="POST">
            <div class="form-group">
            <select name="food_bank_id" class="form-control" required>
    <option value="">Select Food Bank</option>
    <?php 
    // Create an array to keep track of food bank names
    $foodBankNames = [];
    while ($foodBank = $foodBanks->fetch_assoc()):
        // Check if the food bank name is already in the array
        if (!in_array($foodBank['name'], $foodBankNames)):
            // Add the food bank name to the array to avoid duplicates
            $foodBankNames[] = $foodBank['name'];
    ?>
        <option value="<?= $foodBank['food_bank_id'] ?>"><?= $foodBank['name'] ?></option>
    <?php 
        endif;
    endwhile; 
    ?>
</select>

            </div>
            <div class="form-group">
                <input type="text" name="item_name" class="form-control" placeholder="Item Name" required>
            </div>
            <div class="form-group">
                <input type="number" name="quantity_needed" class="form-control" placeholder="Quantity Needed" required>
            </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary" name="add_food_bank_need">Add Food Bank Need</button>
      </div>
    </div>
  </div>
</div>
<div class="glass" style="overflow-x:auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>Food Bank</th>
                    <th>Item Name</th>
                    <th>Quantity Needed</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($need = $foodBankNeeds->fetch_assoc()): ?>
                    <tr>
                        <td><?= $need['food_bank_name'] ?></td>
                        <td><?= $need['item_name'] ?></td>
                        <td><?= $need['quantity_needed'] ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="need_id" value="<?= $need['need_id'] ?>">
                                <button type="submit" class="btn btn-danger" name="delete_food_bank_need">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        </div>

        
    <?php endif; ?>

    <!-- Job Positions Section -->
    <?php if ($tab === 'job_positions'): ?>
        <h2>Job Positions</h2>
        <!-- Button to trigger modal -->
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addJobPositionModal">
  Add New Job Position
</button>

<!-- Modal -->
<div class="modal fade" id="addJobPositionModal" tabindex="-1" aria-labelledby="addJobPositionModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addJobPositionModalLabel">Add New Job Position</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
      </div>
      <div class="modal-body">
        <!-- Form inside the modal -->
        <form method="POST">
            <div class="form-group">
                <select name="food_bank_id" class="form-control" required>
                    <option value="">Select Food Bank</option>
                    <?php 
                    // Create an array to keep track of food bank names to avoid duplicates
                    $foodBankNames = [];
                    while ($foodBank = $foodBanks->fetch_assoc()):
                        // Check if the food bank name is already in the array
                        if (!in_array($foodBank['name'], $foodBankNames)):
                            // Add the food bank name to the array to avoid duplicates
                            $foodBankNames[] = $foodBank['name'];
                    ?>
                        <option value="<?= $foodBank['food_bank_id'] ?>"><?= $foodBank['name'] ?></option>
                    <?php 
                        endif;
                    endwhile; 
                    ?>
                </select>
            </div>
            <div class="form-group">
                <input type="text" name="title" class="form-control" placeholder="Job Title" required>
            </div>
            <div class="form-group">
                <textarea name="description" class="form-control" placeholder="Job Description" required></textarea>
            </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary" name="add_job_position">Add Job Position</button>
      </div>
        </form>
    </div>
  </div>
</div>
<div class="glass" style="overflow-x:auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>Food Bank</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($jobPosition = $jobPositions->fetch_assoc()): ?>
                    <tr>
                        <td><?= $jobPosition['food_bank_name'] ?></td>
                        <td><?= $jobPosition['title'] ?></td>
                        <td><?= $jobPosition['description'] ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="job_id" value="<?= $jobPosition['job_id'] ?>">
                                <button type="submit" class="btn btn-danger" name="delete_job_position">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
</div>
       
    <?php endif; ?>

   <!-- Job Applications Section -->
<?php if ($tab === 'job_applications'): ?>
    <h2>Job Applications</h2>
    <div class="glass" style="overflow-x:auto;">
    <table class="table">
        <thead>
            <tr>
                <th>User ID</th>
                <th>User Name</th>
                <th>Job ID</th>
                <th>Bank Name</th>
                <th>Title</th>
                <th>Experience</th>
                <th>Years</th>
                <th>Cover Letter</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($jobApplication = $jobApplications->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($jobApplication['user_id']) ?></td>
                    <td><?= htmlspecialchars($jobApplication['last_name']) ?></td>
                    <td><?= htmlspecialchars($jobApplication['job_id']) ?></td>
                    <td><?= htmlspecialchars($jobApplication['food_bank_name']) ?></td>
                    <td><?= htmlspecialchars($jobApplication['title']) ?></td>
                    <td><?= htmlspecialchars($jobApplication['job_experience']) ?></td>
                    <td><?= htmlspecialchars($jobApplication['years_of_experience']) ?></td>
                    <td><?= htmlspecialchars($jobApplication['cover_letter']) ?></td>
                    <td><?= htmlspecialchars($jobApplication['applied_at']) ?></td>
                    <td><?= htmlspecialchars($jobApplication['status']) ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="application_id" value="<?= $jobApplication['application_id'] ?>">
                            <div class="form-group">
    <label for="status">Status</label>
    <select name="status" class="form-control" required>
        <option value="">Select Status</option>
        <option value="PENDING">PENDING</option>
        <option value="APPROVED">APPROVED</option>
        <option value="DENIED">DENIED</option>
    </select>
</div>

                            <button type="submit" class="btn btn-primary" name="update_job_application_status">Update Status</button>
                        </form>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="application_id" value="<?= $jobApplication['application_id'] ?>">
                            <button type="submit" class="btn btn-danger" name="delete_job_application">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    </div>
<?php endif; ?>

<!-- Referrals Section -->
<?php if ($tab === 'referrals'): ?>
    <h2>Referrals</h2>
    <div class="glass" style="overflow-x:auto;">
    <table class="table">
        <thead>
            <tr>
                <th>User Name</th>
                <th>Bank Name</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($referral = $referrals->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($referral['user_name']) ?></td>
                    <td><?= htmlspecialchars($referral['food_bank_name']) ?></td>
                    <td><?= htmlspecialchars($referral['status']) ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="referral_id" value="<?= $referral['referral_id'] ?>">
                            <div class="form-group">
    <label for="status">Status</label>
    <select name="status" class="form-control" required>
        <option value="">Select Status</option>
        <option value="PENDING">PENDING</option>
        <option value="APPROVED">APPROVED</option>
        <option value="DENIED">DENIED</option>
    </select>
</div>

                            <button type="submit" class="btn btn-primary" name="update_referral_status">Update Status</button>
                        </form>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="referral_id" value="<?= $referral['referral_id'] ?>">
                            <button type="submit" class="btn btn-danger" name="delete_referral">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    </div>
<?php endif; ?>


    <!-- Users Section -->
    <?php if ($tab === 'users'): ?>
        <h2>Users</h2>
        <div class="glass" style="overflow-x:auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?= $user['user_id'] ?></td>
                        <td><?= $user['first_name'] ?> <?= $user['last_name'] ?></td>
                        <td><?= $user['email'] ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                <button type="submit" class="btn btn-danger" name="delete_user">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<?php include './../footer/footer.php'; ?>
</body>
</html>
