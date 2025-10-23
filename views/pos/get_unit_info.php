<?php
require_once __DIR__ . '/../../configs/database.php';

header('Content-Type: application/json');

if (!isset($_GET['product_id']) || !isset($_GET['unit_id'])) {
    echo json_encode(['error' => 'Product ID and Unit ID required']);
    exit;
}

$product_id = (int)$_GET['product_id'];
$unit_id = (int)$_GET['unit_id'];

try {
    $query = "SELECT conversion_factor, unit_price 
              FROM product_unit_mapping 
              WHERE product_id = ? AND unit_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$product_id, $unit_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo json_encode([
            'conversion_factor' => (float)$result['conversion_factor'],
            'unit_price' => $result['unit_price'] ? (float)$result['unit_price'] : null
        ]);
    } else {
        echo json_encode([
            'conversion_factor' => 1.0,
            'unit_price' => null
        ]);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error']);
}   