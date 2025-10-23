<?php
require_once __DIR__ . '/../configs/database.php';


function getSalesReport($pdo, $period = 'month', $customStart = null, $customEnd = null) {
    // Determine date range based on period
    list($startDate, $endDate) = getDateRange($period, $customStart, $customEnd);
    
    $query = "SELECT 
                t.transaction_id,
                t.transaction_date,
                t.subtotal,
                t.vat,
                t.total,
                t.payment_method,
                t.discount_type,
                t.discount_amount,
                COUNT(ti.id) as items_count,
                SUM(ti.quantity) as total_quantity
              FROM transactions t
              JOIN transaction_items ti ON t.transaction_id = ti.transaction_id
              WHERE t.transaction_date BETWEEN :start_date AND :end_date
              GROUP BY t.transaction_id ORDER BY t.transaction_date DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":start_date", $startDate);
    $stmt->bindParam(":end_date", $endDate);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSalesSummary($pdo, $period = 'month', $customStart = null, $customEnd = null) {
    list($startDate, $endDate) = getDateRange($period, $customStart, $customEnd);
    
    $query = "SELECT 
                COUNT(DISTINCT t.transaction_id) as total_transactions,
                SUM(t.total) as total_sales,
                SUM(t.vat) as total_vat,
                SUM(t.discount_amount) as total_discounts,
                SUM(CASE WHEN t.payment_method = 'cash' THEN t.total ELSE 0 END) as cash_sales,
                SUM(CASE WHEN t.payment_method = 'credit_card' THEN t.total ELSE 0 END) as credit_card_sales,
                SUM(CASE WHEN t.payment_method = 'gcash' THEN t.total ELSE 0 END) as gcash_sales,
                SUM(CASE WHEN t.payment_method = 'bank_transfer' THEN t.total ELSE 0 END) as bank_transfer_sales
              FROM transactions t
              WHERE t.transaction_date BETWEEN :start_date AND :end_date";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":start_date", $startDate);
    $stmt->bindParam(":end_date", $endDate);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getTopProducts($pdo, $period = 'month', $customStart = null, $customEnd = null, $limit = 5) {
    list($startDate, $endDate) = getDateRange($period, $customStart, $customEnd);
    
    $query = "SELECT 
                ti.product_id,
                ti.product_name,
                SUM(ti.quantity) as total_quantity,
                SUM(ti.quantity * ti.price) as total_sales
              FROM transaction_items ti
              JOIN transactions t ON ti.transaction_id = t.transaction_id
              WHERE t.transaction_date BETWEEN :start_date AND :end_date
              GROUP BY ti.product_id ORDER BY total_sales DESC LIMIT :limit";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":start_date", $startDate);
    $stmt->bindParam(":end_date", $endDate);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getDateRange($period, $customStart = null, $customEnd = null) {
    if ($customStart && $customEnd) {
        return [$customStart, $customEnd];
    }

    $today = date('Y-m-d');
    switch ($period) {
        case 'day':
            return [$today . ' 00:00:00', $today . ' 23:59:59'];        
        case 'week':
            return [date('Y-m-d', strtotime('monday this week')), date('Y-m-d', strtotime('sunday this week'))];
        case 'month':
            return [date('Y-m-01'), date('Y-m-t')];
        case 'year':
            return [date('Y-01-01'), date('Y-12-31')];
        default:
            return [date('Y-m-01'), date('Y-m-t')];
    }
}

function getInventoryOverview($pdo) {
    $query = "SELECT 
                COUNT(DISTINCT p.product_id) as total_products,
                SUM(ib.quantity) as total_stock,
                SUM(p.price * ib.quantity) as total_inventory_value,
                SUM(CASE WHEN total_per_product <= p.reorder_point THEN 1 ELSE 0 END) as low_stock_items
              FROM products p
              LEFT JOIN (
                  SELECT product_id, SUM(quantity) as total_per_product
                  FROM inventory_batches
                  GROUP BY product_id
              ) ib_sum ON p.product_id = ib_sum.product_id
              LEFT JOIN inventory_batches ib ON p.product_id = ib.product_id";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


function getLowStockItems($pdo) {
    $query = "SELECT 
                p.product_id,
                p.product_name,
                IFNULL(SUM(ib.quantity), 0) as stock,
                p.reorder_point,
                p.price,
                c.category_name,
                u.measurement
              FROM products p
              LEFT JOIN inventory_batches ib ON p.product_id = ib.product_id
              JOIN category c ON p.category_id = c.category_id
              JOIN unit_measurement u ON p.unit_id = u.unit_id
              GROUP BY p.product_id
              HAVING IFNULL(SUM(ib.quantity), 0) <= p.reorder_point
              ORDER BY (IFNULL(SUM(ib.quantity), 0) / NULLIF(p.reorder_point, 0)) ASC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function getInventoryByCategory($pdo) {
    $query = "SELECT 
                c.category_id,
                c.category_name,
                COUNT(DISTINCT p.product_id) as product_count,
                SUM(ib.quantity) as total_stock,
                SUM(p.price * ib.quantity) as category_value
              FROM category c
              LEFT JOIN products p ON c.category_id = p.category_id
              LEFT JOIN inventory_batches ib ON p.product_id = ib.product_id
              GROUP BY c.category_id
              ORDER BY category_value DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function getProductMovement($pdo, $period = 'month', $customStart = null, $customEnd = null) {
    list($startDate, $endDate) = getDateRange($period, $customStart, $customEnd);
    $days = round((strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24)) + 1;

    $query = "SELECT 
                p.product_id,
                p.product_name,
                IFNULL(SUM(ib.quantity), 0) as stock,
                SUM(CASE WHEN ti.transaction_id IS NOT NULL THEN ti.quantity ELSE 0 END) as sold_quantity,
                (SUM(CASE WHEN ti.transaction_id IS NOT NULL THEN ti.quantity ELSE 0 END) / :days) as avg_daily_sales,
                (IFNULL(SUM(ib.quantity), 0) / NULLIF((SUM(CASE WHEN ti.transaction_id IS NOT NULL THEN ti.quantity ELSE 0 END) / :days), 0)) as days_of_supply
              FROM products p
              LEFT JOIN inventory_batches ib ON p.product_id = ib.product_id
              LEFT JOIN transaction_items ti ON p.product_id = ti.product_id
              LEFT JOIN transactions t ON ti.transaction_id = t.transaction_id
                  AND t.transaction_date BETWEEN :start_date AND :end_date
              GROUP BY p.product_id
              HAVING sold_quantity > 0
              ORDER BY sold_quantity DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':days', (int)$days, PDO::PARAM_INT);
    $stmt->bindParam(':start_date', $startDate);
    $stmt->bindParam(':end_date', $endDate);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


