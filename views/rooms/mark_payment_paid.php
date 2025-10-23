<?php
require_once __DIR__ . '/../../configs/database.php';
require_once __DIR__ . '/../../middleware/verify.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$booking_id = $_POST['booking_id'] ?? null;

if (!$booking_id) {
    echo json_encode(['success' => false, 'message' => 'Booking ID required']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Update booking status and mark payment as paid
    $stmt = $pdo->prepare("UPDATE bookings 
                          SET booking_status = 'Confirmed', 
                              down_payment_paid = TRUE, 
                              updated_at = NOW() 
                          WHERE booking_id = ? AND booking_status = 'Pending'");
    $stmt->execute([$booking_id]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception("Booking not found or already confirmed");
    }
    
    // Log the activity
    $action = "Down payment confirmed";
    $user = $_SESSION["username"];
    $details = "$user confirmed down payment for booking #$booking_id";
    
    $log_query = "INSERT INTO activity_logs (action, user, details) VALUES (:action, :user, :details)";
    $log_stmt = $pdo->prepare($log_query);
    $log_stmt->execute([
        ':action' => $action,
        ':user' => $user,
        ':details' => $details
    ]);
    
    $pdo->commit();
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}