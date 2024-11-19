<?php
$mysqli = new mysqli("localhost", "root", "", "foodbanks");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
// Seeding users
$users = [
    ['first_name' => 'John', 'last_name' => 'Doe', 'email' => 'john@example.com', 'password' => password_hash('password123', PASSWORD_DEFAULT), 'phone_number' => '1234567890', 'is_admin' => 1],
    ['first_name' => 'Jane', 'last_name' => 'Smith', 'email' => 'jane@example.com', 'password' => password_hash('password123', PASSWORD_DEFAULT), 'phone_number' => '0987654321', 'is_admin' => 0]
];

foreach ($users as $user) {
    $mysqli->query("INSERT INTO users (first_name, last_name, email, password, phone_number, is_admin) 
                    VALUES ('{$user['first_name']}', '{$user['last_name']}', '{$user['email']}', '{$user['password']}', '{$user['phone_number']}', {$user['is_admin']})");
}

// Seeding referrals
$referrals = [
    ['food_bank_id' => 1, 'user_id' => 1, 'status' => 'PENDING'],
    ['food_bank_id' => 2, 'user_id' => 2, 'status' => 'APPROVED']
];

foreach ($referrals as $referral) {
    $mysqli->query("INSERT INTO referrals (food_bank_id, user_id, status) 
                    VALUES ({$referral['food_bank_id']}, {$referral['user_id']}, '{$referral['status']}')");
}

// Seeding job applications
$jobApplications = [
    ['job_id' => 1, 'user_id' => 1, 'status' => 'PENDING'],
    ['job_id' => 2, 'user_id' => 2, 'status' => 'APPROVED']
];

foreach ($jobApplications as $application) {
    $mysqli->query("INSERT INTO job_applications (job_id, user_id, status) 
                    VALUES ({$application['job_id']}, {$application['user_id']}, '{$application['status']}')");
}
// Insert food banks data
$foodBanks = [
    ['name' => 'Food Bank A', 'address' => '123 Main St', 'latitude' => '37.7749', 'longitude' => '-122.4194', 'phone' => '123-456-7890', 'email' => 'contact@foodbanka.com', 'referral_required' => 1],
    ['name' => 'Food Bank B', 'address' => '456 Oak St', 'latitude' => '37.8044', 'longitude' => '-122.2711', 'phone' => '987-654-3210', 'email' => 'contact@foodbankb.com', 'referral_required' => 0]
];

foreach ($foodBanks as $foodBank) {
    $mysqli->query("INSERT INTO food_banks (name, address, latitude, longitude, phone, email, referral_required) VALUES ('{$foodBank['name']}', '{$foodBank['address']}', '{$foodBank['latitude']}', '{$foodBank['longitude']}', '{$foodBank['phone']}', '{$foodBank['email']}', {$foodBank['referral_required']})");
}

// Insert food bank needs data
$needs = [
    ['food_bank_id' => 1, 'item_name' => 'Canned Food', 'quantity_needed' => 100],
    ['food_bank_id' => 1, 'item_name' => 'Clothing', 'quantity_needed' => 50],
    ['food_bank_id' => 2, 'item_name' => 'Rice', 'quantity_needed' => 200],
    ['food_bank_id' => 2, 'item_name' => 'Canned Food', 'quantity_needed' => 150]
];

foreach ($needs as $need) {
    $mysqli->query("INSERT INTO food_bank_needs (food_bank_id, item_name, quantity_needed) VALUES ({$need['food_bank_id']}, '{$need['item_name']}', {$need['quantity_needed']})");
}

$jobPositions = [
    ['food_bank_id' => 1, 'title' => 'Volunteer Coordinator', 'description' => 'Coordinate volunteers and organize food distribution.', 'requirements' => 'Must have strong communication skills.'],
    ['food_bank_id' => 1, 'title' => 'Warehouse Manager', 'description' => 'Manage inventory and food supplies in the warehouse.', 'requirements' => 'Prior experience in inventory management required.'],
    ['food_bank_id' => 2, 'title' => 'Food Bank Manager', 'description' => 'Oversee daily operations of the food bank and manage volunteers.', 'requirements' => 'Leadership experience required.'],
    ['food_bank_id' => 2, 'title' => 'Donation Coordinator', 'description' => 'Handle donation collection and distribution.', 'requirements' => 'Experience in logistics is preferred.']
];

foreach ($jobPositions as $job) {
    $mysqli->query("INSERT INTO job_positions (food_bank_id, title, description, requirements) VALUES ({$job['food_bank_id']}, '{$job['title']}', '{$job['description']}', '{$job['requirements']}')");
}
// Example donation data
$donations = [
    ['food_bank_id' => 1, 'user_id' => 1, 'item_name' => 'Canned Beans', 'quantity' => 50],
    ['food_bank_id' => 1, 'user_id' => 2, 'item_name' => 'Rice', 'quantity' => 100],
    ['food_bank_id' => 2, 'user_id' => 3, 'item_name' => 'Pasta', 'quantity' => 200],
    ['food_bank_id' => 2, 'user_id' => 4, 'item_name' => 'Canned Soup', 'quantity' => 30]
];

foreach ($donations as $donation) {
    $mysqli->query("INSERT INTO donations (food_bank_id, user_id, item_name, quantity) 
                    VALUES ({$donation['food_bank_id']}, {$donation['user_id']}, '{$donation['item_name']}', {$donation['quantity']})");
}
echo "Database seeded successfully.";
$mysqli->close();
?>
