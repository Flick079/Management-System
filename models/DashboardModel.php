<?php
require_once __DIR__ . '/../configs/database.php';

// Sales Dashboard Functions
function getTodaySales($pdo) {
    $query = "SELECT 
                COUNT(*) as transaction_count,
                SUM(total) as total_sales,
                SUM(vat) as total_vat,
                SUM(discount_amount) as total_discounts
              FROM transactions
              WHERE DATE(transaction_date) = CURDATE()";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getRecentTransactions($pdo, $limit = 5) {
    $query = "SELECT 
                t.transaction_id,
                t.transaction_date,
                t.total,
                t.payment_method,
                COUNT(ti.id) as items_count
              FROM transactions t
              JOIN transaction_items ti ON t.transaction_id = ti.transaction_id
              GROUP BY t.transaction_id
              ORDER BY t.transaction_date DESC
              LIMIT :limit";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Inventory Dashboard Functions
function getInventoryStatus($pdo) {
    $query = "SELECT 
    COUNT(ib.product_id) as total_products,
    SUM(ib.quantity) as total_stock,
    SUM(CASE WHEN ib.quantity <= p.reorder_point THEN 1 ELSE 0 END) as low_stock_items
FROM inventory_batches ib
JOIN products p ON p.product_id = ib.product_id;

              ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getLowStockProducts($pdo, $limit = 5) {
    $query = "SELECT 
                p.product_id,
                p.product_name,
                ib.quantity,
                p.reorder_point,
                c.category_name
              FROM products p
              JOIN category c ON p.category_id = c.category_id
              JOIN inventory_batches ib ON ib.product_id = p.product_id
              WHERE ib.quantity <= p.reorder_point
              ORDER BY (ib.quantity / p.reorder_point) ASC
              LIMIT :limit";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getExpiringInventory($pdo, $days = 7) {
    $query = "SELECT 
                ib.batch_id,
                p.product_name,
                ib.quantity,
                ib.expiration_date
              FROM inventory_batches ib
              JOIN products p ON ib.product_id = p.product_id
              WHERE ib.status = 'active'
                AND ib.expiration_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
              ORDER BY ib.expiration_date ASC";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':days', (int)$days, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Supplier Orders Functions
function getSupplierOrdersSummary($pdo) {
    $query = "SELECT 
                COUNT(*) as total_orders,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_count,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count,
                SUM(total_amount) as total_amount
              FROM supplier_orders";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getRecentSupplierOrders($pdo, $limit = 5) {
    $query = "SELECT 
                so.order_id,
                s.supplier_name,
                so.order_date,
                so.expected_delivery_date,
                so.total_amount,
                so.status
              FROM supplier_orders so
              JOIN suppliers s ON so.supplier_id = s.supplier_id
              ORDER BY so.order_date DESC
              LIMIT :limit";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Attendance Dashboard Functions
function getTodayAttendance($pdo) {
    $query = "SELECT 
                COUNT(*) as total_employees,
                SUM(CASE WHEN time_in IS NOT NULL THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN time_out IS NOT NULL THEN 1 ELSE 0 END) as completed_count
              FROM attendance
              WHERE date = CURDATE()";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getRecentAttendance($pdo, $limit = 5) {
    $query = "SELECT 
                a.qr_employee_id,
                a.date,
                a.time_in,
                a.time_out,
                ws.status as schedule_status
              FROM attendance a
              LEFT JOIN work_schedule ws ON a.qr_employee_id = ws.employee_id AND a.date = ws.date
              ORDER BY a.date DESC, a.time_in DESC
              LIMIT :limit";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTodayScheduledShifts($pdo) {
    $query = "SELECT 
                ws.employee_id,
                ws.status as schedule_status,
                s.shift_name,
                s.start_time,
                s.end_time,
                a.time_in,
                a.time_out
              FROM work_schedule ws
              JOIN shifts s ON ws.shift_id = s.shift_id
              LEFT JOIN attendance a ON ws.employee_id = a.qr_employee_id AND ws.date = a.date
              WHERE ws.date = CURDATE()
              ORDER BY s.start_time";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Leave Requests Functions
function getLeaveRequestsSummary($pdo) {
    $query = "SELECT 
                COUNT(*) as total_requests,
                SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_requests,
                SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved_requests,
                SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected_requests
              FROM leave_requests";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getRecentLeaveRequests($pdo, $limit = 5) {
    $query = "SELECT 
                lr.leave_id,
                CONCAT(e.first_name, '', e.last_name) as full_name,
                lr.leave_type,
                lr.start_date,
                lr.end_date,
                lr.status
              FROM leave_requests lr
              JOIN employees e ON lr.employee_id = e.employee_id
              ORDER BY lr.created_at DESC
              LIMIT :limit";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Chart Data Functions
function getSalesDataForChart($pdo, $days = 7) {
    $query = "SELECT 
                DATE(transaction_date) as date,
                SUM(total) as total_sales,
                COUNT(*) as transaction_count
              FROM transactions
              WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
              GROUP BY DATE(transaction_date)
              ORDER BY date";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':days', (int)$days, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAttendanceDataForChart($pdo, $days = 7) {
    $query = "SELECT 
                date,
                COUNT(*) as total_employees,
                SUM(CASE WHEN time_in IS NOT NULL THEN 1 ELSE 0 END) as present_count
              FROM attendance
              WHERE date >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
              GROUP BY date
              ORDER BY date";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':days', (int)$days, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}