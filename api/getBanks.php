<?php
header('Content-Type: application/json');
require_once './../data/db.php'; // Database connection

try {
    $stmt = $conn->query("SELECT id, name FROM food_banks ORDER BY name ASC");
    $banks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $banks]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
