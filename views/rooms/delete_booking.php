<?php
require_once __DIR__ . '/../../configs/database.php';
require_once __DIR__ . '/../../middleware/verify.php';

// Check if booking_id is provided
if (!isset($_GET['booking_id']) || !is_numeric($_GET['booking_id'])) {
    header("Location: booking.php?error=Invalid booking ID");
    exit();
}

$booking_id = (int)$_GET['booking_id'];

try {
    $pdo->beginTransaction();

    // 1. Get booking details for logging
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE booking_id = ?");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        throw new Exception("Booking not found");
    }

    // 2. Get associated rooms for logging
    $stmt = $pdo->prepare("SELECT * FROM booking_rooms WHERE booking_id = ?");
    $stmt->execute([$booking_id]);
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Archive booking data
    $archive_query = "INSERT INTO archives (original_table, original_id, details, archived_data, archived_by, reason)
                      VALUES ('bookings', :booking_id, :details, :archived_data, :user_id, 'Booking deletion')";
    $archive_stmt = $pdo->prepare($archive_query);
    
    $archive_data = [
        'booking' => $booking,
        'rooms' => $rooms
    ];
    
    $archive_stmt->execute([
        ':booking_id' => $booking_id,
        ':details' => "Booking #$booking_id for " . $booking['customer_name'],
        ':archived_data' => json_encode($archive_data),
        ':user_id' => $_SESSION['user_id']
    ]);

    // 4. Delete booking rooms
    $stmt = $pdo->prepare("DELETE FROM booking_rooms WHERE booking_id = ?");
    $stmt->execute([$booking_id]);

    // 5. Delete the booking
    $stmt = $pdo->prepare("DELETE FROM bookings WHERE booking_id = ?");
    $stmt->execute([$booking_id]);

    // Log the activity
    $action = "Booking deleted";
    $user = $_SESSION["username"];
    $details = "$user deleted booking #$booking_id for " . $booking['customer_name'];
    
    $log_query = "INSERT INTO activity_logs (action, user, details) VALUES (:action, :user, :details)";
    $log_stmt = $pdo->prepare($log_query);
    $log_stmt->execute([
        ':action' => $action,
        ':user' => $user,
        ':details' => $details
    ]);

    $pdo->commit();
    
    header("Location: booking.php?success=Booking deleted successfully");
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    header("Location: booking.php?error=" . urlencode($e->getMessage()));
    exit();
}