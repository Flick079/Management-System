<?php

require_once __DIR__ . '/../configs/database.php';

//for products

function insertProduct($pdo, $product_name, $price, $initial_stock, $reorder_point, 
                      $category_id, $unit_id, $expiration_date, $image_url) {
    $pdo->beginTransaction();
    
    try {
        // Insert product
        $query = "INSERT INTO products (product_name, price, reorder_point, category_id, unit_id, image) 
                  VALUES (:product_name, :price, :reorder_point, :category_id, :unit_id, :image)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":product_name", $product_name);
        $stmt->bindParam(":price", $price);
        $stmt->bindParam(":reorder_point", $reorder_point);
        $stmt->bindParam(":category_id", $category_id);
        $stmt->bindParam(":unit_id", $unit_id);
        $stmt->bindParam(":image", $image_url);
        $stmt->execute();
        
        $product_id = $pdo->lastInsertId();
        
        // Create primary unit mapping
        $query = "INSERT INTO product_unit_mapping (product_id, unit_id, is_primary, conversion_factor)
                  VALUES (:product_id, :unit_id, 1, 1.0000)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":product_id", $product_id);
        $stmt->bindParam(":unit_id", $unit_id);
        $stmt->execute();
        
        // Add initial stock batch
        if ($initial_stock > 0) {
            addInventoryBatch($pdo, $product_id, $initial_stock, $expiration_date);
        }
        
        $pdo->commit();
        return $product_id;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// Add these new functions to PosModel.php

function getProductUnits($pdo, $product_id) {
    $query = "SELECT m.*, u.measurement 
              FROM product_unit_mapping m
              JOIN unit_measurement u ON m.unit_id = u.unit_id
              WHERE m.product_id = :product_id
              ORDER BY m.is_primary DESC";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":product_id", $product_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addProductUnit($pdo, $product_id, $unit_id, $is_primary, $conversion_factor, $price) {
    // If adding a new primary unit, first unset any existing primary
    if ($is_primary) {
        $query = "UPDATE product_unit_mapping SET is_primary = 0 
                  WHERE product_id = :product_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":product_id", $product_id);
        $stmt->execute();
    }
    
    // Add the new unit mapping
    $query = "INSERT INTO product_unit_mapping 
              (product_id, unit_id, is_primary, conversion_factor, unit_price)
              VALUES (:product_id, :unit_id, :is_primary, :conversion_factor, :unit_price)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":product_id", $product_id);
    $stmt->bindParam(":unit_id", $unit_id);
    $stmt->bindParam(":is_primary", $is_primary, PDO::PARAM_INT);
    $stmt->bindParam(":conversion_factor", $conversion_factor);
    $stmt->bindParam(":unit_price", $price);
    return $stmt->execute();
}

function removeProductUnit($pdo, $mapping_id) {
    $query = "DELETE FROM product_unit_mapping WHERE mapping_id = :mapping_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":mapping_id", $mapping_id);
    return $stmt->execute();
}

function setPrimaryUnit($pdo, $product_id, $mapping_id) {
    $pdo->beginTransaction();
    try {
        // First unset any existing primary
        $query = "UPDATE product_unit_mapping SET is_primary = 0 
                  WHERE product_id = :product_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":product_id", $product_id);
        $stmt->execute();
        
        // Set the new primary
        $query = "UPDATE product_unit_mapping SET is_primary = 1 
                  WHERE mapping_id = :mapping_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":mapping_id", $mapping_id);
        $stmt->execute();
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}


// In PosModel.php, modify the getProducts function to include batch information
// In PosModel.php, update the getProducts function
function getProducts($pdo) {
    $query = "SELECT 
        p.product_id, 
        p.product_name, 
        p.price, 
        p.reorder_point, 
        p.image, 
        p.category_id, 
        p.unit_id,
        MIN(CASE WHEN b.status != 'discarded' THEN b.expiration_date ELSE NULL END) as expiration_date,  
        c.category_name, 
        u.measurement,
        COALESCE(SUM(
            CASE 
                WHEN b.status != 'discarded' AND 
                     (b.expiration_date IS NULL OR b.expiration_date >= CURDATE()) 
                THEN b.quantity 
                ELSE 0 
            END
        ), 0) as stock
      FROM products p
      JOIN category c ON c.category_id = p.category_id
      JOIN unit_measurement u ON u.unit_id = p.unit_id
      LEFT JOIN inventory_batches b ON b.product_id = p.product_id
      GROUP BY p.product_id";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addNewStock($pdo, $product_id, $added_stock, $expiration_date = null) {
    return addInventoryBatch($pdo, $product_id, $added_stock, $expiration_date);
}

// Add these functions to your PosModel.php

function getProductById($pdo, $product_id) {
    $query = "SELECT * FROM products WHERE product_id = :product_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":product_id", $product_id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateProduct($pdo, $product_id, $product_name, $price, $reorder_point, 
                      $category_id, $unit_id, $expiration_date, $image_url = null) {
    $query = "UPDATE products SET 
              product_name = :product_name,
              price = :price,
              reorder_point = :reorder_point,
              category_id = :category_id,
              unit_id = :unit_id,
              expiration_date = :expiration_date" . 
              ($image_url ? ", image = :image" : "") . "
              WHERE product_id = :product_id";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":product_name", $product_name);
    $stmt->bindParam(":price", $price);
    $stmt->bindParam(":reorder_point", $reorder_point);
    $stmt->bindParam(":category_id", $category_id);
    $stmt->bindParam(":unit_id", $unit_id);
    $stmt->bindParam(":expiration_date", $expiration_date);
    $stmt->bindParam(":product_id", $product_id);
    
    if ($image_url) {
        $stmt->bindParam(":image", $image_url);
    }
    
    return $stmt->execute();
}

function updateProductExpiration($pdo, $product_id, $expiration_date) {
    $query = "UPDATE products SET expiration_date = :expiration_date 
              WHERE product_id = :product_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":expiration_date", $expiration_date);
    $stmt->bindParam(":product_id", $product_id);
    return $stmt->execute();
}

function getExpiringProducts($pdo, $days = 30) {
    $query = "SELECT * FROM products 
              WHERE expiration_date IS NOT NULL 
              AND expiration_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
              ORDER BY expiration_date";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":days", $days, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getExpiredProducts($pdo) {
    $query = "SELECT * FROM products 
              WHERE expiration_date IS NOT NULL 
              AND expiration_date < CURDATE()
              ORDER BY expiration_date";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

//for batches and expirations

function addInventoryBatch($pdo, $product_id, $quantity, $expiration_date = null) {
    $query = "INSERT INTO inventory_batches (product_id, quantity, expiration_date) 
              VALUES (:product_id, :quantity, :expiration_date)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":product_id", $product_id);
    $stmt->bindParam(":quantity", $quantity);
    $stmt->bindParam(":expiration_date", $expiration_date);
    return $stmt->execute();
}

// In PosModel.php, update the getProductStock function
function getProductStock($pdo, $product_id) {
    $query = "SELECT SUM(quantity) as total_stock FROM inventory_batches 
              WHERE product_id = :product_id
              AND status != 'discarded'
              AND (expiration_date IS NULL OR expiration_date >= CURDATE())";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":product_id", $product_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total_stock'] ?? 0;
}


function getEarliestExpirationDate($pdo, $product_id) {
    $query = "SELECT MIN(expiration_date) as earliest_date 
              FROM inventory_batches 
              WHERE product_id = :product_id
              AND expiration_date IS NOT NULL";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":product_id", $product_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['earliest_date'] ?? null;
}

// In PosModel.php
function getProductBatches($pdo, $product_id) {
    $query = "SELECT * FROM inventory_batches 
              WHERE product_id = :product_id
              AND status != 'discarded'
              ORDER BY 
                CASE WHEN expiration_date IS NULL THEN 1 ELSE 0 END,
                expiration_date ASC";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":product_id", $product_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getExpiringBatches($pdo, $days = 30) {
    $query = "SELECT b.*, p.product_name, p.image 
              FROM inventory_batches b
              JOIN products p ON b.product_id = p.product_id
              WHERE b.expiration_date IS NOT NULL
              AND b.expiration_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
              ORDER BY b.expiration_date ASC";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":days", $days, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getExpiredBatches($pdo) {
    $query = "SELECT b.*, p.product_name, p.image 
              FROM inventory_batches b
              JOIN products p ON b.product_id = p.product_id
              WHERE b.expiration_date IS NOT NULL
              AND b.expiration_date < CURDATE()
              ORDER BY b.expiration_date ASC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// In PosModel.php
function updateBatchStatuses($pdo) {
    // Mark expired batches
    $query = "UPDATE inventory_batches 
              SET status = 'expired' 
              WHERE expiration_date < CURDATE() 
              AND status != 'expired' 
              AND status != 'discarded'";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    // Mark active batches that will expire soon (optional)
    $query = "UPDATE inventory_batches 
              SET status = 'active' 
              WHERE expiration_date >= CURDATE() 
              AND status != 'discarded'";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    return true;
}

// In PosModel.php
// In PosModel.php
function discardBatch($pdo, $batch_id) {
    $query = "UPDATE inventory_batches SET status = 'discarded' WHERE batch_id = :batch_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":batch_id", $batch_id);
    return $stmt->execute();
}

// In PosModel.php
function getExpiryAlerts($pdo, $days_threshold = 7) {
    $query = "SELECT b.*, p.product_name, 
              DATEDIFF(b.expiration_date, CURDATE()) AS days_remaining
              FROM inventory_batches b
              JOIN products p ON b.product_id = p.product_id
              WHERE b.expiration_date IS NOT NULL
              AND b.status != 'discarded'
              AND (
                  (b.expiration_date < CURDATE() AND b.status != 'expired')
                  OR 
                  (b.expiration_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days_threshold DAY))
              )
              ORDER BY b.expiration_date ASC";
              
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":days_threshold", $days_threshold, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// In PosModel.php
function getExpiredInventoryReport($pdo, $start_date = null, $end_date = null) {
    $query = "SELECT b.*, p.product_name, p.price, 
              c.category_name, u.measurement
              FROM inventory_batches b
              JOIN products p ON b.product_id = p.product_id
              JOIN category c ON p.category_id = c.category_id
              JOIN unit_measurement u ON p.unit_id = u.unit_id
              WHERE b.expiration_date < CURDATE()";
              
    if ($start_date) {
        $query .= " AND b.expiration_date >= :start_date";
    }
    if ($end_date) {
        $query .= " AND b.expiration_date <= :end_date";
    }
    
    $query .= " ORDER BY b.expiration_date DESC";
    
    $stmt = $pdo->prepare($query);
    
    if ($start_date) {
        $stmt->bindParam(":start_date", $start_date);
    }
    if ($end_date) {
        $stmt->bindParam(":end_date", $end_date);
    }
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// In PosModel.php
function getDiscardedBatches($pdo, $product_id) {
    $query = "SELECT b.*, d.reason 
    FROM inventory_batches b
    LEFT JOIN batch_disposals d ON b.batch_id = d.batch_id
    WHERE b.product_id = :product_id AND b.status IN ('discarded', 'expired', 'returned')
    ORDER BY b.status_change_date DESC";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":product_id", $product_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
//for categories

function insertCategory($pdo, $category){
    $query = "INSERT INTO category (category_name) VALUES (:category);";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":category", $category);
    $stmt->execute();
}

function getCategories($pdo){
    $query = "SELECT * FROM category";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}

function updateCategory($pdo, $category_id, $category){
    $query = "UPDATE category SET category_name = :category WHERE category_id = :category_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":category", $category);
    $stmt->bindParam(":category_id", $category_id);
    $stmt->execute();
}

function deleteCategory($pdo, $category_id){
    $query = "DELETE FROM category WHERE category_id = :category_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":category_id", $category_id);
    $stmt->execute();
}


// for measurements

function insertMeasurement($pdo, $measurement){
    $query = "INSERT INTO unit_measurement (measurement) VALUES (:measurement);";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":measurement", $measurement);
    $stmt->execute();
}


function getMeasurement($pdo){
    $query = "SELECT * FROM unit_measurement";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}

function updateMeasurement($pdo, $unit_id, $measurement){
    $query = "UPDATE unit_measurement SET measurement = :measurement WHERE unit_id = :unit_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":measurement", $measurement);
    $stmt->bindParam(":unit_id", $unit_id);
    $stmt->execute();
}

function deleteMeasurement($pdo, $unit_id){
    $query = "DELETE FROM unit_measurement WHERE unit_id = :unit_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":unit_id", $unit_id);
    $stmt->execute();
}

// Supplier functions
function insertSupplier($pdo, $supplier_name, $contact_person, $email, $phone, $address) {
    $query = "INSERT INTO suppliers (supplier_name, contact_person, email, phone, address) 
              VALUES (:supplier_name, :contact_person, :email, :phone, :address)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":supplier_name", $supplier_name);
    $stmt->bindParam(":contact_person", $contact_person);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":phone", $phone);
    $stmt->bindParam(":address", $address);
    return $stmt->execute();
}

function getSuppliers($pdo) {
    $query = "SELECT * FROM suppliers ORDER BY supplier_name";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSupplierById($pdo, $supplier_id) {
    $query = "SELECT * FROM suppliers WHERE supplier_id = :supplier_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":supplier_id", $supplier_id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateSupplier($pdo, $supplier_id, $supplier_name, $contact_person, $email, $phone, $address) {
    $query = "UPDATE suppliers SET 
              supplier_name = :supplier_name, 
              contact_person = :contact_person, 
              email = :email, 
              phone = :phone, 
              address = :address 
              WHERE supplier_id = :supplier_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":supplier_name", $supplier_name);
    $stmt->bindParam(":contact_person", $contact_person);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":phone", $phone);
    $stmt->bindParam(":address", $address);
    $stmt->bindParam(":supplier_id", $supplier_id);
    return $stmt->execute();
}

function deleteSupplier($pdo, $supplier_id) {
    $query = "DELETE FROM suppliers WHERE supplier_id = :supplier_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":supplier_id", $supplier_id);
    return $stmt->execute();
}

// Product-Supplier relationship functions
function addProductSupplier($pdo, $product_id, $supplier_id) {
    $query = "INSERT INTO product_suppliers (product_id, supplier_id) 
              VALUES (:product_id, :supplier_id)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":product_id", $product_id);
    $stmt->bindParam(":supplier_id", $supplier_id);
    return $stmt->execute();
}

function getProductSuppliers($pdo, $product_id) {
    $query = "SELECT s.* FROM suppliers s
              JOIN product_suppliers ps ON s.supplier_id = ps.supplier_id
              WHERE ps.product_id = :product_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":product_id", $product_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function getSupplierProducts($pdo, $supplier_id) {
    $query = "SELECT 
                p.*,
                COALESCE(SUM(
                    CASE 
                        WHEN b.expiration_date IS NULL OR b.expiration_date >= CURDATE() 
                        THEN b.quantity 
                        ELSE 0 
                    END
                ), 0) as stock
              FROM products p
              JOIN product_suppliers ps ON p.product_id = ps.product_id
              LEFT JOIN inventory_batches b ON b.product_id = p.product_id
              WHERE ps.supplier_id = :supplier_id
              GROUP BY p.product_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":supplier_id", $supplier_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function removeProductSupplier($pdo, $product_id, $supplier_id) {
    $query = "DELETE FROM product_suppliers 
              WHERE product_id = :product_id AND supplier_id = :supplier_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":product_id", $product_id);
    $stmt->bindParam(":supplier_id", $supplier_id);
    return $stmt->execute();
}   

// Order management functions
function createSupplierOrder($pdo, $supplier_id, $expected_delivery_date, $items) {
    $pdo->beginTransaction();
    
    try {
        // Calculate total amount
        $total_amount = 0;
        foreach ($items as $item) {
            $total_amount += $item['quantity'] * $item['unit_price'];
        }
        
        // Insert order
        $query = "INSERT INTO supplier_orders (supplier_id, expected_delivery_date, total_amount) 
                  VALUES (:supplier_id, :expected_delivery_date, :total_amount)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":supplier_id", $supplier_id);
        $stmt->bindParam(":expected_delivery_date", $expected_delivery_date);
        $stmt->bindParam(":total_amount", $total_amount);
        $stmt->execute();
        
        $order_id = $pdo->lastInsertId();
        
        // Insert order items
        foreach ($items as $item) {
            $query = "INSERT INTO order_items (order_id, product_id, quantity, unit_price) 
                      VALUES (:order_id, :product_id, :quantity, :unit_price)";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(":order_id", $order_id);
            $stmt->bindParam(":product_id", $item['product_id']);
            $stmt->bindParam(":quantity", $item['quantity']);
            $stmt->bindParam(":unit_price", $item['unit_price']);
            $stmt->execute();
        }
        
        $pdo->commit();
        return $order_id;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function getSupplierOrders($pdo, $supplier_id = null) {
    $query = "SELECT so.*, s.supplier_name 
              FROM supplier_orders so
              JOIN suppliers s ON so.supplier_id = s.supplier_id";
    
    if ($supplier_id) {
        $query .= " WHERE so.supplier_id = :supplier_id";
    }
    
    $query .= " ORDER BY so.order_date DESC";
    
    $stmt = $pdo->prepare($query);
    
    if ($supplier_id) {
        $stmt->bindParam(":supplier_id", $supplier_id);
    }
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getOrderDetails($pdo, $order_id) {
    // Get order header
    $query = "SELECT so.*, s.supplier_name, s.contact_person, s.phone 
              FROM supplier_orders so
              JOIN suppliers s ON so.supplier_id = s.supplier_id
              WHERE so.order_id = :order_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":order_id", $order_id);
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        return null;
    }
    
    // Get order items
    $query = "SELECT oi.*, p.product_name, p.image 
              FROM order_items oi
              JOIN products p ON oi.product_id = p.product_id
              WHERE oi.order_id = :order_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":order_id", $order_id);
    $stmt->execute();
    $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return $order;
}

function updateOrderStatus($pdo, $order_id, $status) {
    $query = "UPDATE supplier_orders SET status = :status WHERE order_id = :order_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":status", $status);
    $stmt->bindParam(":order_id", $order_id);
    return $stmt->execute();
}

function fulfillOrder($pdo, $order_id, $product_ids, $quantities, $expiration_dates) {
    $pdo->beginTransaction();
    
    try {
        // Update order status
        updateOrderStatus($pdo, $order_id, 'delivered');
        
        // Get order items
        $query = "SELECT product_id, quantity FROM order_items WHERE order_id = :order_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":order_id", $order_id);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add new inventory batches for each product with expiration dates
        foreach ($items as $index => $item) {
            $expiration_date = !empty($expiration_dates[$index]) ? $expiration_dates[$index] : null;
            
            // Add inventory batch with received items and expiration date
            addInventoryBatch($pdo, $item['product_id'], $item['quantity'], $expiration_date);
        }
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}