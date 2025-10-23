<?php
require_once __DIR__ . '/../../configs/database.php';

header('Content-Type: application/json');

// Get product_id from request
$productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

try {
    // Calculate available stock (sum of all active batches)
    $stmt = $pdo->prepare("
        SELECT 
            COALESCE(SUM(CAST(quantity AS DECIMAL(10,4))), 0) as stock 
        FROM inventory_batches 
        WHERE product_id = ? 
        AND status = 'active'
    ");
    $stmt->execute([$productId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Return stock as float with 4 decimal places
    echo json_encode([
        'success' => true,
        'stock' => round((float)$result['stock'], 4)
    ]);
    
} catch (PDOException $e) {
    // Log error and return proper response
    error_log("get_stock.php error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Database error',
        'stock' => 0
    ]);
}