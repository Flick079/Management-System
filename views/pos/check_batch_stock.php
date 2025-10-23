<?php
require_once __DIR__ . '/../../configs/database.php';

$productId = (int)$_GET['product_id'];
$neededQty = (float)$_GET['needed'];

// Get available stock from batches
$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(quantity), 0) as stock 
    FROM inventory_batches 
    WHERE product_id = ? 
    AND status = 'active'
");
$stmt->execute([$productId]);
$availableStock = (float)$stmt->fetchColumn();

// Get primary unit name for error message
$stmt = $pdo->prepare("
    SELECT u.measurement 
    FROM products p
    JOIN unit_measurement u ON p.unit_id = u.unit_id
    WHERE p.product_id = ?
");
$stmt->execute([$productId]);
$primaryUnit = $stmt->fetchColumn();

header('Content-Type: application/json');
echo json_encode([
    'available' => $availableStock >= $neededQty,
    'available_stock' => $availableStock,
    'needed' => $neededQty,
    'primary_unit' => $primaryUnit
], JSON_PRESERVE_ZERO_FRACTION);