<?php
require_once __DIR__ . '/../models/AuthModel.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    try {
        if (empty($username) || empty($password)) {
            $_SESSION["errors"] = "Please fill in all the fields!";
            header("location: ../index.php");
            exit();
        }

        $result = getUserByUsername($pdo, $username);
        
        if ($result && password_verify($password, $result["password"])) {
            // Store basic user info in session
            $_SESSION["username"] = $result["username"];
            $_SESSION["user_id"] = $result["user_id"];
            $_SESSION["role_id"] = $result["role_id"];
            
            // Get user permissions
            $permissions = getUserPermissions($pdo, $result["user_id"]);
            $_SESSION["user_permissions"] = $permissions;
            
            // Define all permission-route mappings with priority order
            $priorityRoutes = [
                // Highest priority routes first
                ['permission' => 'view_dashboard', 'route' => '../views/dashboard/dashboard.php'],
                ['permission' => 'manage_rooms', 'route' => '../views/rooms/room_management.php'],
                ['permission' => 'book_rooms', 'route' => '../views/rooms/booking.php'],
                ['permission' => 'view_employees', 'route' => '../views/employees/employee.php'],
                ['permission' => 'view_dtr', 'route' => '../views/employees/dtr.php'],
                ['permission' => 'view_attendance', 'route' => '../views/employees/attendance.php'],
                ['permission' => 'manage_leaves', 'route' => '../views/employees/leaves.php'],
                ['permission' => 'manage_schedules', 'route' => '../views/employees/schedule.php'],
                ['permission' => 'manage_positions', 'route' => '../views/employees/employee_positions.php'],
                ['permission' => 'manage_shifts', 'route' => '../views/employees/employee_shifts.php'],
                ['permission' => 'manage_deductions', 'route' => '../views/employees/employee_deductions.php'],
                ['permission' => 'manage_holidays', 'route' => '../views/employees/holidays.php'],
                ['permission' => 'view_payslips', 'route' => '../views/employees/payslip.php'],
                ['permission' => 'use_qr_scanner', 'route' => '../views/employees/qr_scanner.php'],
                ['permission' => 'view_sales_reports', 'route' => '../views/reports/sales_reports.php'],
                ['permission' => 'view_inventory_reports', 'route' => '../views/reports/inventory_reports.php'],
                ['permission' => 'view_payroll_reports', 'route' => '../views/reports/payroll_reports.php'],
                ['permission' => 'manage_accounts', 'route' => '../views/utilities/accounts.php'],
                ['permission' => 'view_activity_logs', 'route' => '../views/utilities/activity_logs.php'],
                ['permission' => 'manage_archives', 'route' => '../views/utilities/archives.php'],
                ['permission' => 'manage_backups', 'route' => '../views/utilities/backup_and_restore.php'],
                ['permission' => 'view_pos_dashbaord', 'route' => '../views/pos/dashboard.php'],
                ['permission' => 'manage_pos', 'route' => '../views/pos/pos.php'],
                ['permission' => 'manage_products', 'route' => '../views/pos/products.php'],
                ['permission' => 'manage_categories', 'route' => '../views/pos/product_category.php'],
                ['permission' => 'manage_measurement', 'route' => '../views/pos/product_measurements.php'],
                ['permission' => 'manage_transactions', 'route' => '../views/pos/transactions.php'],
                ['permission' => 'manage_suppliers', 'route' => '../views/pos/suppliers.php'],
            ];

            // Default redirect if no permissions match (shouldn't happen)
            $redirectUrl = '../index.php';
            
            // Find the first permitted route
            foreach ($priorityRoutes as $route) {
                if (in_array($route['permission'], $permissions)) {
                    $redirectUrl = $route['route'];
                    break;
                }
            }

            header("location: " . $redirectUrl);
            exit();
        } else {
            $_SESSION["errors"] = "Incorrect username or password!";
            header("location: ../index.php");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        $_SESSION["errors"] = "A system error occurred. Please try again.";
        header("location: ../index.php");
        exit();
    }
} else {
    header("location: ../index.php");
    exit();
}