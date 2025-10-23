<?php
require_once __DIR__ . '/../../configs/database.php';
require_once __DIR__ . '/../../models/EmployeeModel.php';

header('Content-Type: application/json');

$response = ['available' => false];

if (isset($_GET['employee_id']) && isset($_GET['shift_id']) && isset($_GET['date'])) {
    $employee_id = $_GET['employee_id'];
    $shift_id = $_GET['shift_id'];
    $date = $_GET['date'];
    
    // Only check if employee is on leave
    if (hasApprovedLeave($pdo, $employee_id, $date)) {
        $response['message'] = 'Employee is on leave this day';
        echo json_encode($response);
        exit;
    }
    
    // Only check if employee already has a schedule (don't care about others)
    if (hasExistingSchedule($pdo, $employee_id, $date)) {
        $response['message'] = 'Employee already has a schedule this day';
        echo json_encode($response);
        exit;
    }
    
    // If checks pass, it's available
    $response['available'] = true;
    $response['message'] = 'Available';
}

echo json_encode($response);