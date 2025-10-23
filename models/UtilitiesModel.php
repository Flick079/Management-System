<?php

require_once __DIR__ . '/../configs/database.php';

function insertRole($pdo, $role){
    //for activity_logs
    $action = "Role added";
    $user = $_SESSION["username"];
    $details = "$user added $role for accounts";
                
    $log_query = "INSERT INTO activity_logs (action, user, details) VALUES (:action, :user, :details);";
    $log_stmt = $pdo->prepare($log_query);
    $log_stmt->bindParam(":action", $action);
    $log_stmt->bindParam(":user", $user);
    $log_stmt->bindParam(":details", $details);
    $log_stmt->execute();
    


    $query = "INSERT INTO roles (role) VALUES (:role);";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":role", $role);
    $stmt->execute();
}

function getRole($pdo){
    $query = "SELECT * FROM roles";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}

function deleteRole($pdo, $role, $role_id){
    //for activity_logs
    $action = "Role deleted";
    $user = $_SESSION["username"];
    $details = "$user added $role for accounts";
                    
    $log_query = "INSERT INTO activity_logs (action, user, details) VALUES (:action, :user, :details);";
    $log_stmt = $pdo->prepare($log_query);
    $log_stmt->bindParam(":action", $action);
    $log_stmt->bindParam(":user", $user);
    $log_stmt->bindParam(":details", $details);
    $log_stmt->execute();

    $query = "DELETE FROM roles WHERE role_id = :role_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":role_id", $role_id);
    $stmt->execute();
}

function updateRole($pdo, $role_id, $role){
    //for activity_logs
    $action = "Role updated";
    $user = $_SESSION["username"];
    $details = "$user updated $role for accounts";
                        
    $log_query = "INSERT INTO activity_logs (action, user, details) VALUES (:action, :user, :details);";
    $log_stmt = $pdo->prepare($log_query);
    $log_stmt->bindParam(":action", $action);
    $log_stmt->bindParam(":user", $user);
    $log_stmt->bindParam(":details", $details);
    $log_stmt->execute();

    $query = "UPDATE roles SET role = :role WHERE role_id = :role_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":role_id", $role_id);
    $stmt->bindParam(":role", $role);
    $stmt->execute();
}

function insertPermission($pdo, $permission_name){
    //for activity_logs
    $action = "Permission added";
    $user = $_SESSION["username"];
    $details = "$user added $permission_name for accounts";
                    
    $log_query = "INSERT INTO activity_logs (action, user, details) VALUES (:action, :user, :details);";
    $log_stmt = $pdo->prepare($log_query);
    $log_stmt->bindParam(":action", $action);
    $log_stmt->bindParam(":user", $user);
    $log_stmt->bindParam(":details", $details);
    $log_stmt->execute();

    $query = "INSERT INTO permissions (permission_name) VALUES (:permission_name);";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":permission_name", $permission_name);
    $stmt->execute();
}

function getPermission($pdo){
    $query = "SELECT * FROM permissions";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}

function deletePermission($pdo, $permission_name, $permission_id){
    //for activity_logs
    $action = "Permission deleted";
    $user = $_SESSION["username"];
    $details = "$user deleted $permission_name for accounts";
                    
    $log_query = "INSERT INTO activity_logs (action, user, details) VALUES (:action, :user, :details);";
    $log_stmt = $pdo->prepare($log_query);
    $log_stmt->bindParam(":action", $action);
    $log_stmt->bindParam(":user", $user);
    $log_stmt->bindParam(":details", $details);
    $log_stmt->execute();

    $query = "DELETE FROM permissions WHERE permission_id = :permission_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":permission_id", $permission_id);
    $stmt->execute();
}

function updatePermission($pdo, $permission_id, $permission_name){
    //for activity_logs
    $action = "Permission updated";
    $user = $_SESSION["username"];
    $details = "$user updated $permission_name for accounts";
                    
    $log_query = "INSERT INTO activity_logs (action, user, details) VALUES (:action, :user, :details);";
    $log_stmt = $pdo->prepare($log_query);
    $log_stmt->bindParam(":action", $action);
    $log_stmt->bindParam(":user", $user);
    $log_stmt->bindParam(":details", $details);
    $log_stmt->execute();    


    $query = "UPDATE permissions SET permission_name = :permission_name WHERE permission_id = :permission_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":permission_id", $permission_id);
    $stmt->bindParam(":permission_name", $permission_name);
    $stmt->execute();
}

//for logs
function getLogs($pdo, $limit, $offset){
    $query = "SELECT * FROM activity_logs ORDER BY activity_time DESC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTotalLogs($pdo) {
    $query = "SELECT COUNT(*) FROM activity_logs";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchColumn();
}



//archives

function getArchivedRecords($pdo){
    $query = "SELECT a.*, u.username
             FROM archives a
             JOIN users u ON a.archived_by = u.user_id
             WHERE restored_at IS NULL AND original_table != 'rooms'
             ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}

function retrieveRecords($pdo, $archive_id, $original_table) {
    // Fetch the archived record
    $query = "SELECT * FROM archives WHERE archive_id = :archive_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":archive_id", $archive_id);
    $stmt->execute();
    $archived = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$archived) {
        echo "Error, no data found";
        return;
    }

    $archived_data = json_decode($archived["archived_data"], true);

    if ($original_table === "employees") {
        $query = "INSERT INTO employees (
            employee_id, first_name, last_name, email, 
            address, contact_number, shift, position, 
            hired_at, image, qr_code, created_at, qr_employee_id
        ) VALUES (
            :employee_id, :first_name, :last_name, :email,
            :address, :contact_number, :shift, :position,
            :hired_at, :image, :qr_code, :created_at, :qr_employee_id
        )";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ":employee_id" => $archived_data["employee_id"],
            ":first_name" => $archived_data["first_name"],
            ":last_name" => $archived_data["last_name"],
            ":email" => $archived_data["email"],
            ":address" => $archived_data["address"],
            ":contact_number" => $archived_data["contact_number"],
            ":shift" => $archived_data["shift"],
            ":position" => $archived_data["position"],
            ":hired_at" => $archived_data["hired_at"],
            ":image" => $archived_data["image"],
            ":qr_code" => $archived_data["qr_code"],
            ":created_at" => $archived_data["created_at"],
            ":qr_employee_id" => $archived_data["qr_employee_id"]
        ]);
    }

    if ($original_table === "room_types") {
        // Restore room type
        $query = "INSERT INTO room_types (
            room_type_id, name, rate, additional_fee, description, max_person, caption
        ) VALUES (
            :room_type_id, :name, :rate, :additional_fee, :description, :max_person, :caption
        )";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ":room_type_id" => $archived_data["room_type_id"],
            ":name" => $archived_data["name"],
            ":rate" => $archived_data["rate"],
            ":additional_fee" => $archived_data["additional_fee"],
            ":description" => $archived_data["description"],
            ":max_person" => $archived_data["max_person"],
            ":caption" => $archived_data["caption"]
        ]);

        // Restore rooms linked to this room type (if archived)
        $rooms_query = "SELECT * FROM archives WHERE original_table = 'rooms' AND JSON_EXTRACT(archived_data, '$.room_type_id') = :room_type_id AND restored_at IS NULL";
        $rooms_stmt = $pdo->prepare($rooms_query);
        $rooms_stmt->bindParam(":room_type_id", $archived_data["room_type_id"]);
        $rooms_stmt->execute();
        $rooms_archived = $rooms_stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rooms_archived as $room_archive) {
            $room_data = json_decode($room_archive["archived_data"], true);
            $insert_room_query = "INSERT INTO rooms (
                room_id, room_type_id, room_number, image, room_status
            ) VALUES (
                :room_id, :room_type_id, :room_number, :image, :room_status
            )";
            $insert_stmt = $pdo->prepare($insert_room_query);
            $insert_stmt->execute([
                ":room_id" => $room_data["room_id"],
                ":room_type_id" => $room_data["room_type_id"],
                ":room_number" => $room_data["room_number"],
                ":image" => $room_data["image"],
                ":room_status" => $room_data["room_status"]
            ]);

            // Mark room archive as restored
            $update_room_archive = "UPDATE archives SET restored_at = NOW() WHERE archive_id = :archive_id";
            $update_stmt = $pdo->prepare($update_room_archive);
            $update_stmt->bindParam(":archive_id", $room_archive["archive_id"]);
            $update_stmt->execute();
        }
    }

    // Mark the original archive as restored
    $update_archive_query = "UPDATE archives SET restored_at = NOW() WHERE archive_id = :archive_id";
    $update_archive_stmt = $pdo->prepare($update_archive_query);
    $update_archive_stmt->bindParam(":archive_id", $archive_id);
    $update_archive_stmt->execute();
}

//accounts

function insertAccount($pdo, $user_id, $username, $hashedPassword, $permissions) {
    // Insert user with user_id included
    $query = "INSERT INTO users (user_id, username, password) VALUES (:user_id, :username, :password)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindParam(":username", $username);
    $stmt->bindParam(":password", $hashedPassword);
    $stmt->execute();

    // Check if permissions array is not empty
    if (!empty($permissions)) {
        $query = "INSERT INTO user_permissions (user_id, permission_id) VALUES (:user_id, :permission_id)";
        $user_stmt = $pdo->prepare($query);

        foreach ($permissions as $permission_id) {
            $user_stmt->bindParam(":user_id", $user_id);
            $user_stmt->bindParam(":permission_id", $permission_id);
            $user_stmt->execute();
        }
    }
}

function getAccounts($pdo){
    $query = "SELECT * FROM users";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}
// In utilitiesController.php, add this for updating user permissions
if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_user_permissions"])) {
    $user_id = $_POST["user_id"];
    $permissions = $_POST["permissions"] ?? [];
    
    try {
        // First, delete all existing permissions for this user
        $deleteQuery = "DELETE FROM user_permissions WHERE user_id = :user_id";
        $deleteStmt = $pdo->prepare($deleteQuery);
        $deleteStmt->bindParam(":user_id", $user_id);
        $deleteStmt->execute();
        
        // Then insert the new permissions
        if (!empty($permissions)) {
            $insertQuery = "INSERT INTO user_permissions (user_id, permission_id) VALUES (:user_id, :permission_id)";
            $insertStmt = $pdo->prepare($insertQuery);
            
            foreach ($permissions as $permission_id) {
                $insertStmt->bindParam(":user_id", $user_id);
                $insertStmt->bindParam(":permission_id", $permission_id);
                $insertStmt->execute();
            }
        }
        
        // Clear the edit session
        unset($_SESSION['edit_user']);
        unset($_SESSION['user_permissions']);
        
        header("location: ../views/utilities/settings_utilities.php");
        exit();
    } catch (PDOException $e) {
        die("Query failed: ". $e->getMessage());
    }
}

