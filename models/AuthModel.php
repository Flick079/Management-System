<?php

require_once __DIR__ . '/../configs/database.php';

function getUserByUsername ($pdo, $username){
    $query = "SELECT * FROM users WHERE username = :username;";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":username", $username);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
}

function getRoles($pdo){
    $query = "SELECT * FROM roles";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}

function getUserPermissions($pdo, $user_id) {
    // Get permissions assigned directly to user
    $sql = "SELECT p.permission_name 
            FROM user_permissions up
            JOIN permissions p ON up.permission_id = p.permission_id
            WHERE up.user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $directPermissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    
    
    // Combine and remove duplicates
    return array_unique(array_merge($directPermissions));
}