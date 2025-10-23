<?php
require_once __DIR__ . '/../../configs/database.php';
header('Content-Type: application/json');

try {
    $product_id = $_GET['product_id'] ?? null;
    
    if (!$product_id) {
        throw new Exception('Product ID is required');
    }
    
    $query = "SELECT batch_id, quantity 
              FROM inventory_batches 
              WHERE product_id = ? 
              AND (expiration_date IS NULL OR expiration_date >= CURDATE())
              ORDER BY 
                CASE WHEN expiration_date IS NULL THEN 1 ELSE 0 END,
                expiration_date ASC,
                received_date ASC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$product_id]);
    $batches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($batches);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}