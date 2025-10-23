<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

require_once __DIR__ . '/../../configs/database.php';

try {
    // Get dates from request parameters
    $check_in_date = $_GET['check_in_date'] ?? null;
    $check_out_date = $_GET['check_out_date'] ?? null;
    
    if (!$check_in_date || !$check_out_date) {
        throw new Exception("Both check_in_date and check_out_date parameters are required");
    }

    // SQL query to fetch available rooms
    $query = "SELECT r.room_id, r.room_number, rt.name AS room_type, rt.rate, rt.max_person
              FROM rooms r
              JOIN room_types rt ON r.room_type_id = rt.room_type_id
              WHERE r.room_id NOT IN (
                  SELECT br.room_id 
                  FROM booking_rooms br
                  JOIN bookings b ON br.booking_id = b.booking_id
                  WHERE b.check_in_date <= :check_out_date 
                  AND b.check_out_date >= :check_in_date
                  AND b.booking_status NOT IN ('Cancelled', 'Completed')
              )
              ORDER BY r.room_number";

    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':check_in_date' => $check_in_date,
        ':check_out_date' => $check_out_date
    ]);
    
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($rooms);

} catch (PDOException $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>