<?php
require_once __DIR__ . '/../../configs/database.php';
header('Content-Type: application/json'); // Add this line


try {
    $pdo->beginTransaction();

    // Sanitize and validate basic fields
    $booking_id = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
    if (!$booking_id) throw new Exception('Invalid booking ID');

    $customer_name = htmlspecialchars($_POST['customer_name'] ?? '');
    $age = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT);
    $gender = htmlspecialchars($_POST['gender'] ?? '');
    $contact_number = htmlspecialchars($_POST['contact_number'] ?? '');
    $check_in = htmlspecialchars($_POST['check_in_date'] ?? '');
    $check_out = htmlspecialchars($_POST['check_out_date'] ?? '');
    $status = htmlspecialchars($_POST['booking_status'] ?? '');
    $down_payment = filter_input(INPUT_POST, 'down_payment', FILTER_VALIDATE_FLOAT);

    if (!$customer_name || !$contact_number || !$check_in || !$check_out) {
        throw new Exception("Required fields are missing.");
    }

    // Update booking info
    $stmt = $pdo->prepare("UPDATE bookings SET 
        customer_name = :customer_name, age = :age, gender = :gender, 
        contact_number = :contact_number, check_in_date = :check_in, 
        check_out_date = :check_out, booking_status = :status, 
        down_payment = :down_payment, updated_at = NOW()
        WHERE booking_id = :booking_id");
    $stmt->execute([
        ':customer_name' => $customer_name,
        ':age' => $age,
        ':gender' => $gender,
        ':contact_number' => $contact_number,
        ':check_in' => $check_in,
        ':check_out' => $check_out,
        ':status' => $status,
        ':down_payment' => $down_payment,
        ':booking_id' => $booking_id
    ]);

    // Fetch existing assigned rooms with their types
    $stmt = $pdo->prepare("SELECT br.booking_room_id, br.room_id, br.room_type_id, br.booked_rate 
                          FROM booking_rooms br 
                          WHERE br.booking_id = ?");
    $stmt->execute([$booking_id]);
    $existing_rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $existing_room_ids = array_column($existing_rooms, 'room_id');

    // Posted room and person info
    $posted_rooms = $_POST['room_ids'] ?? [];
    $posted_persons = $_POST['additional_persons'] ?? [];
    
    // Get rooms marked for removal
    $removed_rooms = $_POST['removed_rooms'] ?? [];
    $removed_rooms = is_array($removed_rooms) ? array_map('intval', $removed_rooms) : [];

    $total_cost = 0;
    $stay_days = max(1, (new DateTime($check_in))->diff(new DateTime($check_out))->days);

    // Process room removals first
    if (!empty($removed_rooms)) {
        $stmt = $pdo->prepare("DELETE FROM booking_rooms 
                              WHERE booking_id = ? AND room_id IN (" . implode(',', array_fill(0, count($removed_rooms), '?')) . ")");
        $stmt->execute(array_merge([$booking_id], $removed_rooms));
        
        // Remove deleted rooms from existing rooms array
        $existing_rooms = array_filter($existing_rooms, function($room) use ($removed_rooms) {
            return !in_array($room['room_id'], $removed_rooms);
        });
        $existing_room_ids = array_column($existing_rooms, 'room_id');
    }

    // Calculate total cost from remaining existing rooms
    foreach ($existing_rooms as $existing) {
        $total_cost += $existing['booked_rate'] * $stay_days;
    }

    // Process new rooms if any were added
    if (is_array($posted_rooms) && count($posted_rooms) > 0) {
        $posted_rooms = array_map('intval', $posted_rooms);
        $posted_persons = array_map('intval', $posted_persons);

        foreach ($posted_rooms as $i => $room_id) {
            // Skip if this room already exists in the booking
            if (in_array($room_id, $existing_room_ids)) {
                continue;
            }

            // Get room details including type
            $stmt = $pdo->prepare("SELECT r.room_type_id, rt.rate, rt.max_person, rt.additional_fee
                                 FROM rooms r 
                                 JOIN room_types rt ON r.room_type_id = rt.room_type_id 
                                 WHERE r.room_id = ?");
            $stmt->execute([$room_id]);
            $room = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$room) throw new Exception("Room ID $room_id not found");

            $room_type_id = $room['room_type_id'];
            $rate = $room['rate'];
            $max_person = $room['max_person'];
            $additional = $posted_persons[$i] ?? 0;

            if ($additional > $max_person) {
                throw new Exception("Additional persons exceed max allowed for room $room_id");
            }

            $cost = $rate * $stay_days;
            $total_cost += $cost;

            // Insert new room assignment
            $stmt = $pdo->prepare("INSERT INTO booking_rooms 
                (booking_id, room_id, room_type_id, booked_rate, additional_persons, additional_fees) 
                VALUES (:booking_id, :room_id, :room_type_id, :booked_rate, :additional_persons, :additional_fees)");
            
            $stmt->execute([
                ':booking_id' => $booking_id,
                ':room_id' => $room_id,
                ':room_type_id' => $room_type_id,
                ':booked_rate' => $rate,
                ':additional_persons' => $additional,
                ':additional_fees' => 0
            ]);
        }
    }

    // Update total cost in booking
    $stmt = $pdo->prepare("UPDATE bookings SET total_cost = ? WHERE booking_id = ?");
    $stmt->execute([$total_cost, $booking_id]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Booking updated successfully']);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error updating booking: ' . $e->getMessage()]);
}
exit();