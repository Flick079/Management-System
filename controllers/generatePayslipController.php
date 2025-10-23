<?php

require_once __DIR__ . '/../models/EmployeeModel.php';


// In generatePayslipController.php
$employee_id = $_GET['employee_id'];
$pay_period_range = isset($_GET["pay_period_range"]) ? $_GET["pay_period_range"] : '1-15';
$pay_period_month = isset($_GET["pay_period_month"]) ? $_GET["pay_period_month"] : date('Y-m');

// Extract year and month
list($year, $month) = explode('-', $pay_period_month);

if ($pay_period_range === '1-15') {
    $start_date = "$year-$month-01";
    $end_date = "$year-$month-15";
} else {
    $start_date = "$year-$month-16";
    $end_date = date("Y-m-t", strtotime("$year-$month-01")); // Get the last day of the month
}


// Get complete employee data with holiday calculations
$employee = generatePayslip($pdo, $employee_id, $start_date, $end_date, $pay_period_range);
    // Convert start date to readable format
    $startDate = new DateTime($start_date);
    $formattedStart = $startDate->format('F j');
    
    // Convert end date to readable format
    $endDate = new DateTime($end_date);
    $formattedEnd = $endDate->format('j, Y');

$date_today = date('Y-m-d');
function customRoundTime($hours) {
    // Separate hours and minutes
    $wholeHours = floor($hours); // Extract whole hours
    $minutes = ($hours - $wholeHours) * 60; // Convert decimal to minutes

    // Apply custom rounding condition
    if ($minutes >= 10) {
        return $wholeHours + 1; // Round up to the next hour
    } else {
        return $wholeHours; // Round down and keep the same hour
    }
}