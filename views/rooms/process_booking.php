<?php
require_once __DIR__ . '/../../configs/database.php';
require_once __DIR__ . '/../../middleware/verify.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// Validate required fields
$required = ['full_name', 'contact_number', 'age', 'gender', 'check_in_date', 'check_out_date'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        $response['message'] = "Missing required field: $field";
        echo json_encode($response);
        exit;
    }
}

try {
// Add this at the beginning of your try block in process_booking.php

$pdo->beginTransaction(); // Start transactions

    // 1. Insert into bookings table
    $bookingStmt = $pdo->prepare("
        INSERT INTO bookings (
            customer_name, 
            contact_number, 
            age,
            gender,
            check_in_date, 
            check_out_date,
            total_cost,
            down_payment,
            booking_status
        ) VALUES (:name, :contact, :age, :gender, :check_in, :check_out, :total, :down_payment, 'Pending')
    ");
    
    $bookingStmt->execute([
        ':name' => $_POST['full_name'],
        ':contact' => $_POST['contact_number'],
        ':age' => $_POST['age'],
        ':gender' => $_POST['gender'],
        ':check_in' => $_POST['check_in_date'],
        ':check_out' => $_POST['check_out_date'],
        ':total' => str_replace(['₱', ','], '', $_POST['total_cost']),
        ':down_payment' => str_replace(['₱', ','], '', $_POST['down_payment'])
    ]);
    
    $bookingId = $pdo->lastInsertId();
    
    // 2. Insert into booking_rooms table
    $rooms = json_decode($_POST['rooms'], true);
    $roomStmt = $pdo->prepare("
        INSERT INTO booking_rooms (
            booking_id,
            room_id,
            room_type_id,
            booked_rate,
            additional_persons,
            additional_fees
        ) VALUES (:booking_id, :room_id, :room_type_id, :rate, :persons, :fees)
    ");
    
    foreach ($rooms as $room) {
        $roomStmt->execute([
            ':booking_id' => $bookingId,
            ':room_id' => $room['room_id'],
            ':room_type_id' => $room['room_type_id'],
            ':rate' => $room['booked_rate'],
            ':persons' => $room['additional_persons'],
            ':fees' => $room['additional_fees']
        ]);
    }
    
    $pdo->commit();
    
    $response = [
        'success' => true,
        'booking_id' => $bookingId,
        'message' => 'Booking created successfully'
    ];
    
} catch (PDOException $e) {
    $pdo->rollBack();
    $response['message'] = 'Database error: ' . $e->getMessage();
    error_log('Booking Error: ' . $e->getMessage());
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
?>