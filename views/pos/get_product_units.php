<?php
require_once __DIR__ . '/../../configs/database.php';

header('Content-Type: application/json');

if (!isset($_GET['product_id'])) {
    echo json_encode([]);
    exit;
}

$productId = (int)$_GET['product_id'];

$query = "SELECT m.*, u.measurement, 
                 CASE WHEN m.unit_price IS NOT NULL THEN m.unit_price ELSE p.price END as price
          FROM product_unit_mapping m
          JOIN unit_measurement u ON m.unit_id = u.unit_id
          JOIN products p ON m.product_id = p.product_id
          WHERE m.product_id = ?
          ORDER BY m.is_primary DESC";
$stmt = $pdo->prepare($query);
$stmt->execute([$productId]);
$units = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($units);