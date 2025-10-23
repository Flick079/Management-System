<?php

require_once __DIR__ . '/../models/BackupSystemModel.php';

if($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["action"])){
    $action = $_GET["action"];

    if($action === "backup_system"){
        backupSystem();
    }
}