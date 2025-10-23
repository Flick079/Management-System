<?php
// save_transaction.php - Fixed Version
require_once __DIR__ . '/../../configs/database.php';

// Set headers first to ensure JSON response
header('Content-Type: application/json; charset=utf-8');

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/pos_errors.log');

function jsonResponse($status, $message, $data = []) {
    http_response_code($status);
    echo json_encode([
        'status' => $status >= 200 && $status < 300 ? 'success' : 'error',
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

try {
    // Only accept POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(405, 'Method Not Allowed');
    }

    // Get the raw POST data
    $json = file_get_contents('php://input');
    if ($json === false) {
        jsonResponse(400, 'Failed to read input data');
    }

    // Decode JSON
    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        jsonResponse(400, 'Invalid JSON: ' . json_last_error_msg());
    }

    // Debug log the entire transaction data
    error_log("=== TRANSACTION DATA ===");
    error_log(print_r($data, true));

    // Validate required fields
    $required = ['transaction_id', 'cashier_id', 'items', 'subtotal'];
    foreach ($required as $field) {
        if (!isset($data[$field])) {
            jsonResponse(400, "Missing required field: $field");
        }
    }

    // Check for duplicate transaction ID before processing
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM transactions WHERE transaction_id = ?");
    $stmt->execute([$data['transaction_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['count'] > 0) {
        jsonResponse(400, 'Duplicate transaction ID');
    }

    // Calculate totals and VAT
    $subtotal = (float)$data['subtotal'];
    $vat = ($subtotal * 0.0122) /10; // 
    $totalBeforeDiscount = $subtotal;
    
    // Calculate discount
    $discountType = $data['discount_type'] ?? 'none';
    $discountPercentage = (float)($data['discount_percentage'] ?? 0);
    $discountAmount = $totalBeforeDiscount * ($discountPercentage / 100);
    $total = $totalBeforeDiscount - $discountAmount;

    // Start transaction
    $pdo->beginTransaction();

    // 1. Insert Transaction
    $stmt = $pdo->prepare("
        INSERT INTO transactions (
            transaction_id, cashier_id, transaction_date,
            subtotal, vat, total, payment_method,
            amount_tendered, change_amount, discount_type,
            discount_amount, discount_percentage
        ) VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $success = $stmt->execute([
        $data['transaction_id'],
        $data['cashier_id'],
        $subtotal,
        $vat,
        $total,
        $data['payment_method'] ?? 'cash',
        $data['amount_tendered'] ?? 0,
        $data['change_amount'] ?? 0,
        $discountType,
        $discountAmount,
        $discountPercentage
    ]);

    if (!$success) {
        throw new PDOException("Failed to insert transaction");
    }

    // 2. Insert Items and Update Inventory
    foreach ($data['items'] as $item) {
        // Skip invalid items
        if (empty($item['product_name']) || !isset($item['quantity'], $item['price'])) {
            continue;
        }

        // Ensure we have conversion_factor as a float
        $conversionFactor = 1.0; // Default to 1.0
        if (isset($item['conversion_factor']) && $item['conversion_factor'] !== null) {
            $conversionFactor = (float)$item['conversion_factor'];
            error_log("Using provided conversion factor: $conversionFactor");
        } else {
            // If no conversion factor, try to look it up from the database
            if (isset($item['unit_id']) && $item['unit_id'] > 0 && isset($item['product_id']) && $item['product_id'] > 0) {
                $unitStmt = $pdo->prepare("
                    SELECT conversion_factor 
                    FROM product_unit_mapping 
                    WHERE product_id = ? AND unit_id = ?
                ");
                $unitStmt->execute([$item['product_id'], $item['unit_id']]);
                $unitData = $unitStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($unitData && isset($unitData['conversion_factor'])) {
                    $conversionFactor = (float)$unitData['conversion_factor'];
                    error_log("Retrieved conversion factor from DB: $conversionFactor");
                }
            }
        }
        
        // Calculate primary quantity to deduct with precise decimal handling
        $primaryQty = (float)$item['quantity'] * $conversionFactor;
        
        // Log for debugging
        error_log("Processing item: {$item['product_name']}, quantity: {$item['quantity']}");
        error_log("Unit ID: " . (isset($item['unit_id']) ? $item['unit_id'] : 'not set'));
        error_log("Conversion Factor: $conversionFactor, Primary Qty: $primaryQty");

        // Insert transaction item with precise quantities
        $stmt = $pdo->prepare("
            INSERT INTO transaction_items (
                transaction_id, product_id, product_name,
                quantity, primary_quantity, price, original_price,
                discount_applied, unit_id, conversion_factor
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $success = $stmt->execute([
            $data['transaction_id'],
            $item['product_id'] > 0 ? $item['product_id'] : null,
            $item['product_name'],
            $item['quantity'],
            $primaryQty, // Store the exact primary quantity with decimals
            $item['price'],
            $item['original_price'] ?? $item['price'],
            isset($item['discount_applied']) ? ($item['discount_applied'] ? 1 : 0) : 0,
            $item['unit_id'] ?? null,
            $conversionFactor  // Store the exact conversion factor
        ]);

        if (!$success) {
            throw new PDOException("Failed to insert transaction item");
        }

        // Update inventory (only for real products)
        if ($item['product_id'] > 0) {
            $remainingQty = (float)$primaryQty;
            $productId = (int)$item['product_id'];
            
            error_log("Deducting inventory for product ID: $productId, Primary Qty: $primaryQty");
            
            // Set strict SQL mode for decimal operations
            $pdo->exec("SET SESSION sql_mode = 'TRADITIONAL'");
            
            $batches = $pdo->prepare("
                SELECT batch_id, CAST(quantity AS DECIMAL(10,4)) as quantity 
                FROM inventory_batches 
                WHERE product_id = ? 
                AND status = 'active'
                AND quantity > 0
                ORDER BY 
                    CASE WHEN expiration_date IS NULL THEN 1 ELSE 0 END,
                    expiration_date ASC,
                    received_date ASC
            ");
            $batches->execute([$productId]);
            
            $batchCount = 0;
            while ($batch = $batches->fetch(PDO::FETCH_ASSOC)) {
                $batchCount++;
                if ($remainingQty <= 0.0001) break;
                
                $batchId = (int)$batch['batch_id'];
                $batchQty = (float)$batch['quantity'];
                $deductAmount = min($remainingQty, $batchQty);
                
                error_log("Batch $batchId: Current Qty: $batchQty, Deducting: $deductAmount");
                
                // Update with precise decimal values
                $update = $pdo->prepare("
                    UPDATE inventory_batches 
                    SET quantity = CAST(quantity - ? AS DECIMAL(10,4))
                    WHERE batch_id = ?
                    AND CAST(quantity AS DECIMAL(10,4)) >= CAST(? AS DECIMAL(10,4))
                ");
                
                $success = $update->execute([
                    (float)$deductAmount,
                    $batchId,
                    (float)$deductAmount
                ]);
                
                if (!$success || $update->rowCount() === 0) {
                    error_log("Failed to update batch $batchId. Query: " . $update->queryString);
                    throw new PDOException("Failed to update batch $batchId");
                }
                
                $remainingQty -= $deductAmount;
                error_log("After deduction, remaining qty: $remainingQty");
                
                // Mark empty batches (with floating point tolerance)
                $newQty = $batchQty - $deductAmount;
                if ($newQty < 0.0001) {
                    $pdo->prepare("
                        UPDATE inventory_batches 
                        SET status = 'empty', 
                            status_change_date = NOW() 
                        WHERE batch_id = ?
                    ")->execute([$batchId]);
                    error_log("Marked batch $batchId as empty");
                }
            }
            
            if ($batchCount === 0) {
                error_log("No active batches found for product $productId");
            }
            
            if ($remainingQty > 0.0001) {
                error_log("Insufficient stock for product $productId. Still need: $remainingQty");
                throw new PDOException("Insufficient stock for product $productId. Remaining: $remainingQty");
            }
        }
    }

    $pdo->commit();
    jsonResponse(200, 'Transaction saved successfully', [
        'transaction_id' => $data['transaction_id'],
        'subtotal' => $subtotal,
        'vat' => $vat,
        'discount_amount' => $discountAmount,
        'total' => $total
    ]);

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("PDO Error: " . $e->getMessage());
    jsonResponse(500, 'Database error: ' . $e->getMessage());
} catch (Exception $e) {
    error_log("General Error: " . $e->getMessage());
    jsonResponse(500, 'Error: ' . $e->getMessage());
}