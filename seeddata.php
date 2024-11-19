<?php
include 'config.php';

// Adjust your SQL query to include opening_time and closing_time
$sql = "INSERT INTO food_banks (name, latitude, longitude, address, email, phone, social_media, job_opportunities, accessibility_info, needs, referral_required, opening_time, closing_time) VALUES
    (' Bank', 53.80311, -2.23402, '123 Example St, City, State, ZIP', 'contact@example.com', '+1234567890',
    '{\"facebook\": \"fb.com/example\", \"twitter\": \"@example\", \"instagram\": \"@example\"}',
    '[{\"position\": \"Volunteer\", \"description\": \"Help with food distribution\", \"application_link\": \"example.com/apply\"}]',
    '{\"wheelchair_accessible\": true, \"languages_spoken\": [\"English\", \"Spanish\"]}',
    '{\"food\": [\"Canned Goods\", \"Fresh Produce\"], \"hygiene\": [\"Toothpaste\", \"Soap\"]}',
    1,
    '09:00:00',
    '20:00:00'
    )";

if ($conn->query($sql) === TRUE) {
    echo "Data inserted successfully";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
