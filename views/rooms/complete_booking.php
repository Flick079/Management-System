<?php
require_once __DIR__ . '/../../middleware/verify.php';
require_once __DIR__ . '/../../configs/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = $_POST['booking_id'];
    $chargeTypes = $_POST['charge_types'] ?? [];
    $chargeAmounts = $_POST['charge_amounts'] ?? [];
    $notes = $_POST['notes'] ?? '';
    
    try {
        $pdo->beginTransaction();
        
        // 1. Calculate total additional charges
        $totalAdditionalCharges = 0;
        for ($i = 0; $i < count($chargeTypes); $i++) {
            $totalAdditionalCharges += floatval($chargeAmounts[$i]);
        }
        
        // 2. Get the current total cost and down payment
        $getBooking = $pdo->prepare("SELECT total_cost, down_payment FROM bookings WHERE booking_id = ?");
        $getBooking->execute([$bookingId]);
        $booking = $getBooking->fetch(PDO::FETCH_ASSOC);
        
        if (!$booking) {
            throw new Exception("Booking not found");
        }
        
        // 3. Update the booking with new total and status
        $newTotalCost = floatval($booking['total_cost']) + $totalAdditionalCharges;
        $newDownPayment = floatval($booking['down_payment']);
        
        $updateBooking = $pdo->prepare("UPDATE bookings 
                                      SET total_cost = ?, 
                                          booking_status = 'Completed' 
                                      WHERE booking_id = ?");
        $updateBooking->execute([$newTotalCost, $bookingId]);
        
        // 4. Insert additional charges if any
        if (!empty($chargeTypes)) {
            $insertCharge = $pdo->prepare("INSERT INTO additional_charges 
                (booking_id, charge_type, amount, notes) VALUES (?, ?, ?, ?)");
            
            for ($i = 0; $i < count($chargeTypes); $i++) {
                $insertCharge->execute([
                    $bookingId,
                    $chargeTypes[$i],
                    $chargeAmounts[$i],
                    $notes
                ]);
            }
        }
        
        // 5. Log the activity
        $action = "Booking completed";
        $user = $_SESSION["username"];
        $details = "$user marked booking #$bookingId as completed with additional charges of ₱" . number_format($totalAdditionalCharges, 2);
        
        $logQuery = "INSERT INTO activity_logs (action, user, details) VALUES (?, ?, ?)";
        $logStmt = $pdo->prepare($logQuery);
        $logStmt->execute([$action, $user, $details]);
        
        $pdo->commit();
        
        // Redirect back to bookings page with success message
        $_SESSION['success_message'] = "Booking #$bookingId has been successfully completed with additional charges of ₱" . number_format($totalAdditionalCharges, 2);
        header("Location: booking.php");
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Failed to complete booking: " . $e->getMessage();
        header("Location: booking.php");
        exit();
    }
} else {
    $_SESSION['error_message'] = "Method not allowed";
    header("Location: booking.php");
    exit();
}