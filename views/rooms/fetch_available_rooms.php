<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

require_once __DIR__ . '/../../middleware/verify.php';
require_once __DIR__ . '/../../configs/database.php';

$response = ["success" => false, "rooms" => []];

if (!isset($_POST['check_in_date']) || !isset($_POST['check_out_date'])) {
    $response["error"] = "Missing required parameters.";
    echo json_encode($response);
    exit;
}

$check_in_date = $_POST['check_in_date'];
$check_out_date = $_POST['check_out_date'];

try {

    
    // Modified query to work with the new booking_rooms table structure
    $stmt = $pdo->prepare("
    SELECT 
        r.room_id, 
        r.room_number, 
        rt.name, 
        rt.rate, 
        rt.additional_fee, 
        rt.max_person,
        rt.room_type_id
    FROM rooms r
    JOIN room_types rt ON r.room_type_id = rt.room_type_id
    WHERE r.room_id NOT IN (
        SELECT br.room_id 
        FROM booking_rooms br
        JOIN bookings b ON br.booking_id = b.booking_id
        WHERE (
            b.check_in_date <= :check_out 
            AND b.check_out_date >= :check_in
            AND b.booking_status IN ('Confirmed', 'Pending', 'Completed')
        )
    ) 
    AND r.room_status = 1
    ORDER BY rt.rate DESC, r.room_number ASC
");


    $stmt->execute([
        ':check_in' => $check_in_date,
        ':check_out' => $check_out_date
    ]);

    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($rooms)) {
        $response["success"] = true;
        $response["rooms"] = array_map(function($room) {
            return [
                'room_id' => $room['room_id'],
                'room_number' => $room['room_number'],
                'name' => $room['name'],
                'rate' => $room['rate'],
                'additional_fee' => $room['additional_fee'],
                'max_person' => $room['max_person'],
                'room_type_id' => $room['room_type_id']
            ];
        }, $rooms);
    } else {
        $response["error"] = "No rooms available for the selected dates.";
    }
} catch (PDOException $e) {
    $response["error"] = "Database error: " . $e->getMessage();
} catch (Exception $e) {
    $response["error"] = "Error: " . $e->getMessage();
}

echo json_encode($response);
?>