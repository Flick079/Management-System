<?php

require_once __DIR__ . '/../configs/database.php';

//room types
//adding room types
function insertRoomType($pdo, $room_type, $rate, $add_fee, $max_per, $caption){
    //for activity_logs
    $action = "Room type added";
    $user = $_SESSION["username"];
    $details = "$user added room type $room_type";
                
    $log_query = "INSERT INTO activity_logs (action, user, details) VALUES (:action, :user, :details);";
    $log_stmt = $pdo->prepare($log_query);
    $log_stmt->bindParam(":action", $action);
    $log_stmt->bindParam(":user", $user);
    $log_stmt->bindParam(":details", $details);
    $log_stmt->execute();

    $query = "INSERT INTO room_types (name, rate, additional_fee, max_person, caption) 
                VALUES (:name, :rate, :additional_fee, :max_person, :caption);";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":name", $room_type);
    $stmt->bindParam(":rate", $rate);
    $stmt->bindParam(":additional_fee", $add_fee);
    $stmt->bindParam(":max_person", $max_per);
    $stmt->bindParam(":caption", $caption);
    $stmt->execute();
}

function getRoomType($pdo){
    $query = "SELECT * FROM room_types";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}

//updating room types

function updateRoomType($pdo, $room_type_id, $room_type, $rate, $add_fee, $max_per, $caption){
    //for activity_logs
    $action = "Room type updated";
    $user = $_SESSION["username"];
    $details = "$user updated room type $room_type";
                    
    $log_query = "INSERT INTO activity_logs (action, user, details) VALUES (:action, :user, :details);";
    $log_stmt = $pdo->prepare($log_query);
    $log_stmt->bindParam(":action", $action);
    $log_stmt->bindParam(":user", $user);
    $log_stmt->bindParam(":details", $details);
    $log_stmt->execute();


    $query = "UPDATE room_types SET name = :name, rate = :rate, additional_fee = :additional_fee, 
                max_person = :max_person, caption = :caption
                WHERE room_type_id = :room_type_id
                ";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":room_type_id", $room_type_id);
    $stmt->bindParam(":name", $room_type);
    $stmt->bindParam(":rate", $rate);
    $stmt->bindParam(":additional_fee", $add_fee);
    $stmt->bindParam(":max_person", $max_per);
    $stmt->bindParam(":caption", $caption);
    $stmt->execute();
}

function deleteRoomType($pdo, $room_type_name, $room_type_id) {
    $action = "Room type deleted";
    $user = $_SESSION["username"];
    $details = "$user deleted room type $room_type_name";
    
    // Log activity
    $log_query = "INSERT INTO activity_logs (action, user, details) VALUES (:action, :user, :details);";
    $log_stmt = $pdo->prepare($log_query);
    $log_stmt->bindParam(":action", $action);
    $log_stmt->bindParam(":user", $user);
    $log_stmt->bindParam(":details", $details);
    $log_stmt->execute();

    // Get room type record
    $room_type_query = "SELECT * FROM room_types WHERE room_type_id = :room_type_id;";
    $room_type_stmt = $pdo->prepare($room_type_query);
    $room_type_stmt->bindParam(":room_type_id", $room_type_id);
    $room_type_stmt->execute();
    $room_type = $room_type_stmt->fetch(PDO::FETCH_ASSOC);

    // Get related room records
    $room_query = "SELECT r.* FROM rooms r
                   JOIN room_types rt ON rt.room_type_id = r.room_type_id
                   WHERE r.room_type_id = :room_type_id";
    $room_stmt = $pdo->prepare($room_query);
    $room_stmt->bindParam(":room_type_id", $room_type_id);
    $room_stmt->execute();
    $rooms = $room_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if room type already archived
    $check_archive_room_type_query = "SELECT archive_id FROM archives WHERE original_id = :room_type_id AND original_table = 'room_types'";
    $check_archive_room_type_stmt = $pdo->prepare($check_archive_room_type_query);
    $check_archive_room_type_stmt->bindParam(":room_type_id", $room_type_id);
    $check_archive_room_type_stmt->execute();
    $existing_room_type = $check_archive_room_type_stmt->fetch(PDO::FETCH_ASSOC);  

    $user_id = $_SESSION["user_id"];

    // Archive rooms
    foreach ($rooms as $room) {
        // Check if room already archived
        $check_archive_room_query = "SELECT archive_id FROM archives WHERE original_id = :room_id AND original_table = 'rooms'";
        $check_archive_room_stmt = $pdo->prepare($check_archive_room_query);
        $check_archive_room_stmt->bindParam(":room_id", $room["room_id"]);
        $check_archive_room_stmt->execute();
        $existing_room = $check_archive_room_stmt->fetch(PDO::FETCH_ASSOC);  

        if ($existing_room !== false) {
            // Update archive if exists
            $update_archive_room_query = "UPDATE archives SET archived_data = :archived_data, archived_at = NOW(), restored_at = NULL, details = :room_number WHERE archive_id = :archive_id";
            $update_archive_room_stmt = $pdo->prepare($update_archive_room_query);
            $update_archive_room_stmt->bindParam(":archived_data", json_encode($room));
            $update_archive_room_stmt->bindParam(":archive_id", $existing_room["archive_id"]);
            $update_archive_room_stmt->bindParam(":room_number", $room["room_number"]);
            $update_archive_room_stmt->execute();
        } else {
            // Insert new archive
            $query2 = "INSERT INTO archives (original_table, original_id, details, archived_data, archived_by, reason)
                       VALUES ('rooms', :room_id, :room_number, :archived_data, :user_id, 'No room type')";
            $stmt2 = $pdo->prepare($query2);
            $stmt2->bindParam(":room_id", $room["room_id"]);
            $stmt2->bindParam(":room_number", $room["room_number"]);
            $stmt2->bindParam(":archived_data", json_encode($room));
            $stmt2->bindParam(":user_id", $user_id);
            $stmt2->execute();
        }

        // Delete room after archiving
        $delete_room_query = "DELETE FROM rooms WHERE room_id = :room_id";
        $delete_room_stmt = $pdo->prepare($delete_room_query);
        $delete_room_stmt->bindParam(":room_id", $room["room_id"]);
        $delete_room_stmt->execute();
    }

    // Archive room type
    if ($existing_room_type !== false) {
        $update_archive_room_type_query = "UPDATE archives SET archived_data = :archived_data, archived_at = NOW(), restored_at = NULL, details = :room_type_name WHERE archive_id = :archive_id";
        $update_archive_room_type_stmt = $pdo->prepare($update_archive_room_type_query);
        $update_archive_room_type_stmt->bindParam(":archived_data", json_encode($room_type));
        $update_archive_room_type_stmt->bindParam(":archive_id", $existing_room_type["archive_id"]);
        $update_archive_room_type_stmt->bindParam(":room_type_name", $room_type_name);
        $update_archive_room_type_stmt->execute();
    } else {
        $query3 = "INSERT INTO archives (original_table, original_id, details, archived_data, archived_by, reason)
                   VALUES ('room_types', :room_type_id, :room_type_name, :archived_data, :user_id, 'New room type')";
        $stmt3 = $pdo->prepare($query3);
        $stmt3->bindParam(":room_type_id", $room_type_id);
        $stmt3->bindParam(":room_type_name", $room_type_name);
        $stmt3->bindParam(":archived_data", json_encode($room_type));
        $stmt3->bindParam(":user_id", $user_id);
        $stmt3->execute();
    }

    // Finally delete room type
    $delete_room_type_query = "DELETE FROM room_types WHERE room_type_id = :room_type_id";
    $delete_room_type_stmt = $pdo->prepare($delete_room_type_query);
    $delete_room_type_stmt->bindParam(":room_type_id", $room_type_id);
    $delete_room_type_stmt->execute();
}

//for rooms

function insertRoom($pdo, $room_number, $room_type_id, $image_url, $room_status){
    //for activity_logs
    $room_query = "SELECT rt.name FROM room_types rt WHERE rt.room_type_id = :room_type_id";
    $room_stmt = $pdo->prepare($room_query);
    $room_stmt->bindParam(":room_type_id", $room_type_id);
    $room_stmt->execute();

    $room_type = $room_stmt->fetch(PDO::FETCH_ASSOC);

    $action = "Room added";
    $user = $_SESSION["username"];
    $details = "$user added room $room_number with a room type {$room_type['name']}";
                    
    $log_query = "INSERT INTO activity_logs (action, user, details) VALUES (:action, :user, :details);";
    $log_stmt = $pdo->prepare($log_query);
    $log_stmt->bindParam(":action", $action);
    $log_stmt->bindParam(":user", $user);
    $log_stmt->bindParam(":details", $details);
    $log_stmt->execute();



    $query = "INSERT INTO rooms (room_number, room_type_id, image, room_status) VALUES
                (:room_number, :room_type_id, :image, :room_status);";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":room_number", $room_number);
    $stmt->bindParam(":room_type_id", $room_type_id);
    $stmt->bindParam(":image", $image_url);
    $stmt->bindParam(":room_status", $room_status);
    $stmt->execute();
}

function getRoom($pdo){
    $query = "SELECT r.image, r.room_number, r.room_type_id, r.room_status, r.room_id
                FROM rooms r
                ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}

function updateRoom($pdo, $room_id, $room_number, $room_type_id,$image_url = null, $room_status){
    //for activity_logs
    $room_query = "SELECT rt.name FROM room_types rt WHERE rt.room_type_id = :room_type_id";
    $room_stmt = $pdo->prepare($room_query);
    $room_stmt->bindParam(":room_type_id", $room_type_id);
    $room_stmt->execute();
    
    $room_type = $room_stmt->fetch(PDO::FETCH_ASSOC);
    
    $action = "Room updated";
    $user = $_SESSION["username"];
    $details = "$user updated Room Number $room_number with a room type {$room_type['name']}";
                        
    $log_query = "INSERT INTO activity_logs (action, user, details) VALUES (:action, :user, :details);";
    $log_stmt = $pdo->prepare($log_query);
    $log_stmt->bindParam(":action", $action);
    $log_stmt->bindParam(":user", $user);
    $log_stmt->bindParam(":details", $details);
    $log_stmt->execute();


    $query = "UPDATE rooms SET room_number = :room_number, room_type_id = :room_type_id, room_status = :room_status";
        if ($image_url) {
            $query .= ", image = :image";
        }
        $query .= " WHERE room_id = :room_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":room_id", $room_id);
    $stmt->bindParam(":room_number", $room_number);
    $stmt->bindParam(":room_type_id", $room_type_id);
    $stmt->bindParam(":room_status", $room_status);
    if ($image_url) {
        $stmt->bindParam(":image", $image_url);
    }
    $stmt->execute();
}


//for booking

// function getAvailableRooms($pdo, $checkin, $checkout) {
//     $stmt = $pdo->prepare("SELECT * FROM rooms WHERE room_id NOT IN (
//         SELECT room_id FROM bookings 
//         WHERE NOT (check_out_date <= :check_in_date OR check_in_date >= :check_out_date)
//     ) AND room_status = 0"); // Only select rooms that are available

//     $stmt->execute([
//         ':check_in_date' => $checkin, 
//         ':check_out_date' => $checkout
//     ]);
//     return $stmt->fetchAll(PDO::FETCH_ASSOC);
// }
