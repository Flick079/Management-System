<?php
require_once __DIR__ . '/../../configs/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $booking_id = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
        $charge_type = htmlspecialchars($_POST['charge_type'] ?? '');
        $description = htmlspecialchars($_POST['description'] ?? '');
        $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);

        if (!$booking_id || !$charge_type || $amount === false) {
            throw new Exception("Invalid input data");
        }

        // Insert the charge
        $stmt = $pdo->prepare("INSERT INTO extra_charges 
                             (booking_id, charge_type, description, amount, created_at) 
                             VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$booking_id, $charge_type, $description, $amount]);

        // Update the booking's total cost
        $stmt = $pdo->prepare("UPDATE bookings 
                             SET total_cost = total_cost + ? 
                             WHERE booking_id = ?");
        $stmt->execute([$amount, $booking_id]);

        $_SESSION['success_message'] = 'Extra charge added successfully';
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Error adding extra charge: ' . $e->getMessage();
    }
}

header("Location: show_bookings.php");
exit();