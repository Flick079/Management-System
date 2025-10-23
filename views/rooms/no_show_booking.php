<?php
require_once __DIR__ . '/../middleware/verify.php';
require_once __DIR__ . '/../configs/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    $bookingId = $_POST['booking_id'];
    
    try {
        $stmt = $pdo->prepare("UPDATE bookings SET booking_status = 'No Show' WHERE booking_id = ?");
        $stmt->execute([$bookingId]);
        
        header('Location: booking.php?success=Marked booking #' . $bookingId . ' as No Show');
        exit;
    } catch (PDOException $e) {
        header('Location: booking.php?error=Failed to mark booking as No Show');
        exit;
    }
} else {
    header('Location: booking.php');
    exit;
}
?>