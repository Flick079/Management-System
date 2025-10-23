<?php
require_once __DIR__ . '/../../configs/database.php';

header('Content-Type: application/json');

try {
    // Get parameters with defaults
    $page = max(1, intval($_GET['page'] ?? 1));
    $perPage = 10;
    $offset = ($page - 1) * $perPage;
    
    $searchTerm = $_GET['search'] ?? '';
    $dateFrom = $_GET['dateFrom'] ?? '';
    $dateTo = $_GET['dateTo'] ?? '';
    $discountFilter = $_GET['discount'] ?? 'all';

    // Base query - start with WHERE 1=1 to easily add conditions
    $query = "SELECT t.transaction_id, t.transaction_date, 
              t.subtotal, t.vat, t.total, t.discount_type, t.discount_amount,
              t.payment_method, t.amount_tendered, t.change_amount,
              u.username as cashier_name,
              (SELECT COUNT(*) FROM transaction_items WHERE transaction_id = t.transaction_id) as item_count
              FROM transactions t
              LEFT JOIN users u ON t.cashier_id = u.user_id
              WHERE 1=1";
    
    $params = [];
    $types = [];

    // Add search condition
    if (!empty($searchTerm)) {
        $query .= " AND (t.transaction_id LIKE :search OR u.username LIKE :search)";
        $params[':search'] = '%' . $searchTerm . '%';
        $types[':search'] = PDO::PARAM_STR;
    }

    // Add date range conditions
    if (!empty($dateFrom)) {
        $query .= " AND t.transaction_date >= :dateFrom";
        $params[':dateFrom'] = $dateFrom . ' 00:00:00';
        $types[':dateFrom'] = PDO::PARAM_STR;
    }
    
    if (!empty($dateTo)) {
        $query .= " AND t.transaction_date <= :dateTo";
        $params[':dateTo'] = $dateTo . ' 23:59:59';
        $types[':dateTo'] = PDO::PARAM_STR;
    }

    // Add discount filter condition
    if ($discountFilter !== 'all') {
        if ($discountFilter === 'none') {
            $query .= " AND (t.discount_type IS NULL OR t.discount_type = 'none' OR t.discount_amount = 0)";
        } else {
            $query .= " AND t.discount_type = :discountType";
            $params[':discountType'] = $discountFilter;
            $types[':discountType'] = PDO::PARAM_STR;
        }
    }

    // Get total count with same filters
    $countQuery = "SELECT COUNT(*) FROM transactions t LEFT JOIN users u ON t.cashier_id = u.user_id WHERE 1=1" . 
                  substr($query, strpos($query, "WHERE 1=1") + 8);
    
    $countStmt = $pdo->prepare($countQuery);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value, $types[$key] ?? PDO::PARAM_STR);
    }
    $countStmt->execute();
    $totalRecords = $countStmt->fetchColumn();

    // Add sorting and pagination to main query
    $query .= " ORDER BY t.transaction_date DESC LIMIT :limit OFFSET :offset";
    $params[':limit'] = $perPage;
    $types[':limit'] = PDO::PARAM_INT;
    $params[':offset'] = $offset;
    $types[':offset'] = PDO::PARAM_INT;

    // Prepare and execute main query
    $stmt = $pdo->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, $types[$key] ?? PDO::PARAM_STR);
    }
    
    $stmt->execute();
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return response
    echo json_encode([
        'status' => 'success',
        'transactions' => $transactions,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($totalRecords / $perPage),
            'total_records' => $totalRecords
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage(),
        'query' => $query ?? '',
        'params' => $params ?? []
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}