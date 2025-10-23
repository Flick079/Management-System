<?php
require_once __DIR__ . '/../models/EmployeeModel.php';

$employees = getEmployee($pdo);
$qr_employee_id = isset($_GET["qr_employee_id"]) ? $_GET["qr_employee_id"] : '';


if (isset($_GET["filter_name_btn"]) && !empty($_GET["qr_employee_id"])) {
    $qr_employee_id = $_GET["qr_employee_id"];
    $attendances = getAttendance($pdo, $qr_employee_id);
} else {
    $attendances = [];
}
