<?php
require_once __DIR__ . '/../models/EmployeeModel.php';

$specific_date = isset($_GET["specific_date"]) ? $_GET["specific_date"] : date("Y-m-d");

// Validate date
if (!DateTime::createFromFormat("Y-m-d", $specific_date)) {
    die("Invalid date format!");
}

// Get records
$records = getDateFilter($pdo, $specific_date);
