<?php
// api/export_transactions.php
require_once __DIR__ . '/../../configs/database.php';

try {
    // Get parameters
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $dateFrom = isset($_GET['dateFrom']) ? $_GET['dateFrom'] : '';
    $dateTo = isset($_GET['dateTo']) ? $_GET['dateTo'] : '';
    $discount = isset($_GET['discount']) ? $_GET['discount'] : '';
    
    // Build query conditions
    $conditions = [];
    $params = [];
    
    if (!empty($search)) {
        $conditions[] = "(t.transaction_id LIKE ? OR u.username LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if (!empty($dateFrom)) {
        $conditions[] = "DATE(t.transaction_date) >= ?";
        $params[] = $dateFrom;
    }
    
    if (!empty($dateTo)) {
        $conditions[] = "DATE(t.transaction_date) <= ?";
        $params[] = $dateTo;
    }
    
    if (!empty($discount) && $discount !== 'none') {
        $conditions[] = "t.discount_type = ?";
        $params[] = $discount;
    }
    
    $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
    
    // Get transactions with item details
    $query = "
        SELECT 
            t.transaction_id,
            t.transaction_date,
            u.username as cashier_name,
            t.subtotal,
            t.vat,
            t.total,
            t.payment_method,
            t.amount_tendered,
            t.change_amount,
            t.discount_type,
            t.discount_amount,
            t.discount_percentage,
            ti.product_name,
            ti.quantity,
            ti.primary_quantity,
            ti.price,
            ti.original_price,
            ti.discount_applied
        FROM transactions t
        LEFT JOIN users u ON t.cashier_id = u.user_id
        LEFT JOIN transaction_items ti ON t.transaction_id = ti.transaction_id
        $whereClause
        ORDER BY t.transaction_date DESC, t.transaction_id, ti.product_name
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Organize data by transaction
    $transactions = [];
    foreach ($results as $row) {
        $transactionId = $row['transaction_id'];
        if (!isset($transactions[$transactionId])) {
            $transactions[$transactionId] = [
                'transaction_id' => $row['transaction_id'],
                'transaction_date' => $row['transaction_date'],
                'cashier_name' => $row['cashier_name'] ?: 'System',
                'subtotal' => $row['subtotal'],
                'vat' => $row['vat'],
                'total' => $row['total'],
                'payment_method' => $row['payment_method'],
                'amount_tendered' => $row['amount_tendered'],
                'change_amount' => $row['change_amount'],
                'discount_type' => $row['discount_type'],
                'discount_amount' => $row['discount_amount'],
                'discount_percentage' => $row['discount_percentage'],
                'items' => []
            ];
        }
        
        if ($row['product_name']) {
            $transactions[$transactionId]['items'][] = [
                'product_name' => $row['product_name'],
                'quantity' => $row['quantity'],
                'primary_quantity' => $row['primary_quantity'],
                'price' => $row['price'],
                'original_price' => $row['original_price'],
                'discount_applied' => $row['discount_applied']
            ];
        }
    }
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="transactions_export_' . date('Y-m-d') . '.csv"');
    
    // Create CSV file
    $output = fopen('php://output', 'w');
    
    // CSV Header
    fputcsv($output, [
        'Transaction ID', 
        'Date & Time', 
        'Cashier', 
        'Payment Method',
        'Subtotal', 
        'VAT', 
        'Discount Type',
        'Discount Amount',
        'Discount Percentage',
        'Total',
        'Amount Tendered',
        'Change',
        'Product Name',
        'Quantity',
        'Primary Quantity',
        'Price',
        'Original Price',
        'Discount Applied'
    ]);
    
    // CSV Data
    foreach ($transactions as $transaction) {
        // Format discount type for display
        $discountType = $transaction['discount_type'];
        switch ($discountType) {
            case 'senior':
                $discountType = 'Senior Citizen';
                break;
            case 'student':
                $discountType = 'Student';
                break;
            case 'pwd':
                $discountType = 'PWD';
                break;
            case 'employee':
                $discountType = 'Employee';
                break;
            case 'custom':
                $discountType = 'Custom';
                break;
            case 'none':
            default:
                $discountType = 'None';
                break;
        }
        
        // Output transaction row with first item
        $firstItem = !empty($transaction['items']) ? $transaction['items'][0] : null;
        
        $baseData = [
            $transaction['transaction_id'],
            $transaction['transaction_date'],
            $transaction['cashier_name'],
            $transaction['payment_method'],
            $transaction['subtotal'],
            $transaction['vat'],
            $discountType,
            $transaction['discount_amount'],
            $transaction['discount_percentage'],
            $transaction['total'],
            $transaction['amount_tendered'],
            $transaction['change_amount']
        ];
        
        if ($firstItem) {
            $itemData = [
                $firstItem['product_name'],
                $firstItem['quantity'],
                $firstItem['primary_quantity'],
                $firstItem['price'],
                $firstItem['original_price'],
                $firstItem['discount_applied'] ? 'Yes' : 'No'
            ];
        } else {
            $itemData = array_fill(0, 6, '');
        }
        
        fputcsv($output, array_merge($baseData, $itemData));
        
        // Output additional items if they exist
        if (count($transaction['items']) > 1) {
            for ($i = 1; $i < count($transaction['items']); $i++) {
                $item = $transaction['items'][$i];
                $emptyBaseData = array_fill(0, 12, '');
                $itemData = [
                    $item['product_name'],
                    $item['quantity'],
                    $item['primary_quantity'],
                    $item['price'],
                    $item['original_price'],
                    $item['discount_applied'] ? 'Yes' : 'No'
                ];
                fputcsv($output, array_merge($emptyBaseData, $itemData));
            }
        }
    }
    
    fclose($output);
    exit;
    
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error exporting data: ' . $e->getMessage()]);
}