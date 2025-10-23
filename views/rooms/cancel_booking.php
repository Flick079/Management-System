<?php
require_once __DIR__ . '/../middleware/verify.php';
require_once __DIR__ . '/../configs/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'], $_POST['cancel_reason'])) {
    $bookingId = $_POST['booking_id'];
    $cancelReason = $_POST['cancel_reason'];
    
    try {
        $stmt = $pdo->prepare("UPDATE bookings SET booking_status = 'Cancelled', cancellation_reason = ?, cancelled_at = NOW() WHERE booking_id = ?");
        $stmt->execute([$cancelReason, $bookingId]);
        
        header('Location: booking.php?success=Cancelled booking #' . $bookingId);
        exit;
    } catch (PDOException $e) {
        header('Location: booking.php?error=Failed to cancel booking');
        exit;
    }
} else {
    header('Location: booking.php');
    exit;
}
?>