<?php
require_once __DIR__ . '/../../configs/database.php';
header('Content-Type: application/json');

try {
    // Validate transaction ID
    $transactionId = $_GET['id'] ?? null;
    if (empty($transactionId)) {
        throw new Exception('Transaction ID is required');
    }

    // Get transaction details with prepared statement
    $transactionQuery = "
        SELECT 
            t.transaction_id,
            t.transaction_date,
            t.subtotal,
            t.vat,
            t.total,
            t.discount_type,
            t.discount_amount,
            t.payment_method,
            t.amount_tendered,
            t.change_amount,
            u.username as cashier_name
        FROM transactions t
        LEFT JOIN users u ON t.cashier_id = u.user_id
        WHERE t.transaction_id = :transaction_id
    ";
    
    $stmt = $pdo->prepare($transactionQuery);
    $stmt->bindParam(':transaction_id', $transactionId, PDO::PARAM_STR);
    $stmt->execute();
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$transaction) {
        throw new Exception("Transaction not found with ID: $transactionId");
    }

    // Get transaction items
    $itemsQuery = "
        SELECT 
            product_id,
            product_name,
            quantity,
            price,
            original_price,
            discount_applied
        FROM transaction_items
        WHERE transaction_id = :transaction_id
    ";
    
    $stmt = $pdo->prepare($itemsQuery);
    $stmt->bindParam(':transaction_id', $transactionId, PDO::PARAM_STR);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate derived values
    $transaction['total_before_discount'] = (float)$transaction['subtotal'] + (float)$transaction['vat'];
    $transaction['discount_value'] = 0;
    
    if ($transaction['discount_type'] !== 'none' && $transaction['discount_amount'] > 0) {
        $transaction['discount_value'] = $transaction['total_before_discount'] * ($transaction['discount_amount'] / 100);
    }

    // Prepare response
    $response = [
        'status' => 'success',
        'transaction' => $transaction,
        'items' => $items
    ];

    echo json_encode($response, JSON_NUMERIC_CHECK);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}