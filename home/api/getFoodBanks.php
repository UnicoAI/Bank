<?php
require_once './../data/db.php'; // Include your database connection script

$lat = $_GET['lat'] ?? 0;
$lng = $_GET['lng'] ?? 0;
$radius = $_GET['radius'] ?? 10; // Default radius in kilometers

try {
    $stmt = $conn->prepare("
        SELECT *, 
        (6371 * ACOS(COS(RADIANS(:lat)) * COS(RADIANS(latitude)) 
        * COS(RADIANS(longitude) - RADIANS(:lng)) + SIN(RADIANS(:lat)) * SIN(RADIANS(latitude)))) AS distance
        FROM food_banks
        HAVING distance <= :radius
        ORDER BY distance
    ");

    $stmt->execute([':lat' => $lat, ':lng' => $lng, ':radius' => $radius]);
    $foodbanks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ensure that the 'food_bank_id' is included in the output
    $foodbanksWithId = array_map(function($foodbank) {
        // Add the bank ID to the data object
        $foodbank['id'] = $foodbank['food_bank_id']; // Assuming food_bank_id is the column name
        return $foodbank;
    }, $foodbanks);

    echo json_encode(['status' => 'success', 'data' => $foodbanksWithId]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
