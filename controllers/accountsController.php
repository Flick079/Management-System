<?php

require_once __DIR__ . '/../models/UtilitiesModel.php';


if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_user_btn"])){
    $username = $_POST["username"];
    $password = $_POST["password"];
    $permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];  // Prevent undefined array key error

    $user_id = uniqid();
    try {

        if (empty($username) || empty($password) ) {
            $_SESSION["error"] = "Please fill in all the fields!";
            echo "error";
        } else {
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                // Pass only the file path or file name to the database
                insertAccount($pdo, $user_id,$username, $hashedPassword,  $permissions);
                header("location: ../views/utilities/accounts.php");
                exit();
        }
    } catch (PDOException $e) {
        die("Query failed: " . $e->getMessage());
    }
}


