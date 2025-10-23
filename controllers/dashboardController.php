<?php
require_once __DIR__ . '/../models/DashboardModel.php';

// Get sales data
$todaySales = getTodaySales($pdo);
$recentTransactions = getRecentTransactions($pdo, 5);

// Get inventory data
$inventoryStatus = getInventoryStatus($pdo);
$lowStockProducts = getLowStockProducts($pdo, 5);
$expiringInventory = getExpiringInventory($pdo, 7);

// Get supplier orders data
$supplierOrders = getSupplierOrdersSummary($pdo);
$recentSupplierOrders = getRecentSupplierOrders($pdo, 5);

// Get attendance data
$todayAttendance = getTodayAttendance($pdo);
$recentAttendance = getRecentAttendance($pdo, 5);
$todayScheduledShifts = getTodayScheduledShifts($pdo);

// Get leave requests data
$pendingLeaveRequests = getLeaveRequestsSummary($pdo);
$recentLeaveRequests = getRecentLeaveRequests($pdo, 5);

// Get chart data
$salesChartData = getSalesDataForChart($pdo, 7);
$attendanceChartData = getAttendanceDataForChart($pdo, 7);

// Prepare data for charts
$salesDates = array_column($salesChartData, 'date');
$salesAmounts = array_column($salesChartData, 'total_sales');
$salesCounts = array_column($salesChartData, 'transaction_count');

$attendanceDates = array_column($attendanceChartData, 'date');
$attendanceTotal = array_column($attendanceChartData, 'total_employees');
$attendancePresent = array_column($attendanceChartData, 'present_count');