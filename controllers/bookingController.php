<?php
require_once __DIR__ . '/../models/RoomModel.php';
function getAvailableRooms($checkInDate, $checkOutDate) {
    global $pdo;
    
    // SQL query to find available rooms
    $sql = "SELECT r.room_id, r.room_number, rt.name as room_type, rt.price 
            FROM rooms r
            JOIN room_types rt ON r.room_type_id = rt.room_type_id
            WHERE r.room_id NOT IN (
                SELECT room_id FROM bookings 
                WHERE (check_in_date <= :check_out_date AND check_out_date >= :check_in_date) 
                OR (check_in_date <= :check_out_date2 AND check_out_date >= :check_in_date2)
                OR (check_in_date >= :check_in_date3 AND check_out_date <= :check_out_date3)
                AND status != 'Cancelled'
            )
            AND r.room_status = 0"; // Assuming 0 means available
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':check_in_date', $checkInDate);
    $stmt->bindParam(':check_out_date', $checkOutDate);
    $stmt->bindParam(':check_in_date2', $checkInDate);
    $stmt->bindParam(':check_out_date2', $checkOutDate);
    $stmt->bindParam(':check_in_date3', $checkInDate);
    $stmt->bindParam(':check_out_date3', $checkOutDate);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}