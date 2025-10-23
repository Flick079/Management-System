<?php
require_once __DIR__ . '/../configs/database.php';

if(!isset($_SESSION["user_id"])){
    header("location: ../../index.php");
    exit();
} 