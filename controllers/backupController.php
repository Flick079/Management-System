<?php

require_once __DIR__ . '/../models/BackupModel.php';

if(isset($_GET["action"])){
    $action = $_GET["action"];

    switch($action){
        case 'backup_data':
            backupDatabase();
            break;
        case 'restore':
            echo "Not implemented yet!";
            break;
        case 'backup_system':
            echo "Not implemented yet!";
            break;
        default:
            echo "Invalid action";
    }
}