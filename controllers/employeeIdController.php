<?php
require_once __DIR__ . '/../models/EmployeeModel.php';


if (!isset($_GET['employee_id'])) {
    die("Employee ID is required.");
}

$employee_id = $_GET['employee_id'];

// Get complete employee data
$employee = getEmployeeById($pdo, $employee_id);

if (!$employee) {
    die("Employee not found.");
}

// QR code path
$qr_code_path = "../../public/qr/qrcodes/{$employee_id}.png";
