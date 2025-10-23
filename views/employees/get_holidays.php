<?php
// get_holidays.php
require_once __DIR__ . '/../../configs/database.php';

header('Content-Type: application/json');

try {
    $query = "SELECT holiday_date FROM holidays";
    $stmt = $pdo->query($query);
    $holidays = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode($holidays);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}