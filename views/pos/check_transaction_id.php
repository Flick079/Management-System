<?php
require_once __DIR__ . '/../../configs/database.php';

header('Content-Type: application/json');

$transactionId = $_GET['transaction_id'] ?? null;

if (!$transactionId) {
    echo json_encode(['error' => 'Transaction ID required']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM transactions WHERE transaction_id = ?");
    $stmt->execute([$transactionId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'exists' => $result['count'] > 0
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error']);
}