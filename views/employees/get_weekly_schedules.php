<?php

// get_weekly_schedules.php
require_once __DIR__ . '/../../configs/database.php';
require_once __DIR__ . '/../../models/EmployeeModel.php';


header('Content-Type: application/json');
try {
    // Test database connection first
    $pdo->query("SELECT 1")->fetch();
    error_log("Database connection successful");
try {
    // Validate input
    if (!isset($_GET['week_start'])) {
        throw new Exception('week_start parameter is required');
    }

    $week_start = $_GET['week_start'];
    
    // Validate date format
    if (!DateTime::createFromFormat('Y-m-d', $week_start)) {
        throw new Exception('Invalid date format. Use YYYY-MM-DD');
    }

    // Calculate week range (Monday to Sunday)
    $monday = new DateTime($week_start);
    if ($monday->format('N') != 1) { // If not Monday
        $monday->modify('last monday');
    }
    $sunday = clone $monday;
    $sunday->modify('+6 days');

    // Get data
    $employees = getEmployee($pdo);
    $schedules = getSchedulesForWeek($pdo, $monday->format('Y-m-d'), $sunday->format('Y-m-d'));
    $leaves = getLeaveForWeek($pdo, $monday->format('Y-m-d'), $sunday->format('Y-m-d'));
   $attendance = getAttendanceForWeek($pdo, $monday->format('Y-m-d'), $sunday->format('Y-m-d'));

    echo json_encode([
        'success' => true,
        'week_start' => $monday->format('Y-m-d'),
        'week_end' => $sunday->format('Y-m-d'),
        'employees' => $employees,
        'schedules' => $schedules,
        'leaves' => $leaves,
        'attendance' => $attendance
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTrace()
    ]);
}
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    // Rest of error handling...
}