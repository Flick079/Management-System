<?php
require_once __DIR__ . '/../../models/AuthModel.php';
// Check if user is logged in and has permissions
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_permissions'])) {
    header("location: ../../index.php");
    exit();
}

// Permission check function
function hasPermission($permissionName) {
    return isset($_SESSION['user_permissions']) && in_array($permissionName, $_SESSION['user_permissions']);
}
?>

<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../public/icons/bootstrap-icons-1.11.0/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../public/css/style.css">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <title>The Lagoon Resort</title>
</head>
<body>
<div class="sidebar">
    <nav class="nav flex-column">
        <div class="logo" style="width: 15rem;">
            <img src="../../public/images/the_lagoon_logo.png" alt="The Lagoon Resort Logo">
            <h5>The Lagoon Resort Finland Inc.</h5>
        </div>

    <!-- Dashboard -->
    <?php if (hasPermission('view_dashboard')): ?>
    <a href="../dashboard/dashboard.php" class="nav-link nav-1">
        <span class="icon"><i class="bi bi-grid"></i></span>
        <span class="description">Dashboard</span>
    </a>
    <?php endif; ?>
    
    <!-- QR Scanner -->
    <?php if (hasPermission('use_qr_scanner')): ?>
    <a href="../employees/qr_scanner.php" class="nav-link nav-1">
        <span class="icon"><i class="bi bi-qr-code-scan"></i></span>
        <span class="description">QR Scanner</span>
    </a>
    <?php endif; ?>
    

    
    <!-- Payslip -->
    <?php if (hasPermission('view_payslips')): ?>
    <a href="../employees/payslip.php" class="nav-link nav-1">
        <span class="icon"><i class="bi bi-receipt"></i></span>
        <span class="description">Payslip</span>
    </a>
    <?php endif; ?>
    
    <!-- Employee Information Section -->
    <?php if (hasPermission('view_employees') || hasPermission('view_dtr') || hasPermission('view_attendance') || 
              hasPermission('manage_leaves') || hasPermission('manage_schedules') || hasPermission('manage_positions') || 
              hasPermission('manage_shifts') || hasPermission('manage_deductions') || hasPermission('manage_holidays')): ?>
    <a href="#" class="nav-link nav-1" data-bs-toggle="collapse" data-bs-target="#submenu-employee" aria-expanded="false">
        <span class="icon"><i class="bi bi-clipboard"></i></span>
        <span class="description">
            Employee Information <i class="bi bi-caret-down-fill"></i>
        </span>
    </a>
    <div class="sub-menu collapse" id="submenu-employee">
        <?php if (hasPermission('view_employees')): ?>
        <a href="../employees/employee.php" class="nav-link nav-2">
            <span class="icon"><i class="bi bi-clipboard-check"></i></span>
            <span class="description">Employee Masterfile</span>
        </a>
        <?php endif; ?>
        
        <?php if (hasPermission('view_dtr')): ?>
        <a href="../employees/dtr.php" class="nav-link nav-2">
            <span class="icon"><i class="bi bi-clock"></i></span>
            <span class="description">Date and Time Record</span>
        </a>
        <?php endif; ?>
        
        <?php if (hasPermission('view_attendance')): ?>
        <a href="../employees/attendance.php" class="nav-link nav-2">
            <span class="icon"><i class="bi bi-calendar-event"></i></span>
            <span class="description">Attendance</span>
        </a>
        <?php endif; ?>
        
        <?php if (hasPermission('manage_leaves')): ?>
        <a href="../employees/leaves.php" class="nav-link nav-2">
            <span class="icon"><i class="bi bi-send"></i></span>
            <span class="description">Leave Requests</span>
        </a>
        <?php endif; ?>
        
        <?php if (hasPermission('manage_schedules')): ?>
        <a href="../employees/schedule.php" class="nav-link nav-2">
            <span class="icon"><i class="bi bi-calendar-week-fill"></i></span>
            <span class="description">Employee Schedules</span>
        </a>
        <?php endif; ?>
        
        <?php if (hasPermission('manage_positions')): ?>
        <a href="../employees/employee_positions.php" class="nav-link nav-2">
            <span class="icon"><i class="bi bi-person-lines-fill"></i></span>
            <span class="description">Employee Positions</span>
        </a>
        <?php endif; ?>
        
        <?php if (hasPermission('manage_shifts')): ?>
        <a href="../employees/employee_shifts.php" class="nav-link nav-2">
            <span class="icon"><i class="bi bi-hourglass-split"></i></span>
            <span class="description">Employee Shifts</span>
        </a>
        <?php endif; ?>
        
        <?php if (hasPermission('manage_deductions')): ?>
        <a href="../employees/employee_deductions.php" class="nav-link nav-2">
            <span class="icon"><i class="bi bi-clipboard-minus"></i></span>
            <span class="description">Employee Deductions</span>
        </a>
        <?php endif; ?>
        
        <?php if (hasPermission('manage_holidays')): ?>
        <a href="../employees/holidays.php" class="nav-link nav-2">
            <span class="icon"><i class="bi bi-calendar2-x"></i></span>
            <span class="description">Holidays</span>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <!-- Reports Section -->
    <?php if (hasPermission('view_sales_reports') || hasPermission('view_inventory_reports') || hasPermission('view_payroll_reports')): ?>
    <a href="#" class="nav-link nav-1" data-bs-toggle="collapse" data-bs-target="#submenu-reports" aria-expanded="false">
        <span class="icon"><i class="bi bi-receipt"></i></span>
        <span class="description">
            Reports <i class="bi bi-caret-down-fill"></i>
        </span>
    </a>
    <div class="sub-menu collapse" id="submenu-reports">
        <?php if (hasPermission('view_sales_reports')): ?>
        <a href="../reports/sales_reports.php" class="nav-link nav-2">
            <span class="icon"><i class="bi bi-reception-4"></i></span>
            <span class="description">Sales Report</span>
        </a>
        <?php endif; ?>
        
        <?php if (hasPermission('view_inventory_reports')): ?>
        <a href="../reports/inventory_reports.php" class="nav-link nav-2">
            <span class="icon"><i class="bi bi-box-seam"></i></span>
            <span class="description">Inventory Report</span>
        </a>
        <?php endif; ?>
        
        <?php if (hasPermission('view_payroll_reports')): ?>
        <a href="../reports/payroll_reports.php" class="nav-link nav-2">
            <span class="icon"><i class="bi bi-table"></i></span>
            <span class="description">Payroll Reports</span>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <!-- Utilities Section -->
    <?php if (hasPermission('manage_accounts') || hasPermission('view_activity_logs') || hasPermission('manage_archives') || 
              hasPermission('manage_backups') || hasPermission('manage_settings')): ?>
    <a href="#" class="nav-link nav-1" data-bs-toggle="collapse" data-bs-target="#submenu-utilities" aria-expanded="false">
        <span class="icon"><i class="bi bi-stack"></i></span>
        <span class="description">
            Utilities <i class="bi bi-caret-down-fill"></i>
        </span>
    </a>
    <div class="sub-menu collapse" id="submenu-utilities">
        <?php if (hasPermission('manage_accounts')): ?>
        <a href="../utilities/accounts.php" class="nav-link nav-2">
            <span class="icon"><i class="bi bi-people"></i></span>
            <span class="description">Accounts</span>
        </a>
        <?php endif; ?>
        
        <?php if (hasPermission('view_activity_logs')): ?>
        <a href="../utilities/activity_logs.php" class="nav-link nav-2">
            <span class="icon"><i class="bi bi-patch-check"></i></span>
            <span class="description">Activity Logs</span>
        </a>
        <?php endif; ?>
        
        <?php if (hasPermission('manage_archives')): ?>
        <a href="../utilities/archives.php" class="nav-link nav-2">
            <span class="icon"><i class="bi bi-file-lock"></i></span>
            <span class="description">Archive</span>
        </a>
        <?php endif; ?>
        
        <?php if (hasPermission('manage_backups')): ?>
        <a href="../utilities/backup_and_restore.php" class="nav-link nav-2">
            <span class="icon"><i class="bi bi-clouds"></i></span>
            <span class="description">Backup and Restore</span>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- POS Dashboard -->
    <?php if (hasPermission('view_pos_dashboard')): ?>
    <a href="../pos/pos_dashboard.php" class="nav-link nav-1">
        <span class="icon"><i class="bi bi-grid"></i></span>
        <span class="description">Dashboard</span>
    </a>
    <?php endif; ?>

    <!-- POS Management -->
    <?php if (hasPermission('manage_pos')): ?>
    <a href="../pos/pos.php" class="nav-link nav-1">
        <span class="icon"><i class="bi bi-bag"></i></span>
        <span class="description">POS</span>
    </a>
    <?php endif; ?>

    <!-- Inventory Section -->
    <?php if (hasPermission('manage_products') || hasPermission('manage_categories') || hasPermission('manage_measurement')): ?>
    <a href="#" class="nav-link nav-1" data-bs-toggle="collapse" data-bs-target="#submenu-inventory" aria-expanded="false">
        <span class="icon"><i class="bi bi-box-seam"></i></span>
        <span class="description">Inventory <i class="bi bi-caret-down-fill"></i></span>
    </a>
    <div class="sub-menu collapse" id="submenu-inventory">
        <?php if (hasPermission('manage_products')): ?>
        <a href="../pos/products.php" class="nav-link nav-2">
            <span class="icon"><i class="bi bi-box2"></i></span>
            <span class="description">Products Management</span>
        </a>
        <?php endif; ?>

        <?php if (hasPermission('manage_categories')): ?>
        <a href="../pos/product_category.php" class="nav-link nav-2">
            <span class="icon"><i class="bi bi-grid-1x2-fill"></i></span>
            <span class="description">Products Category</span>
        </a>
        <?php endif; ?>
        
        <?php if (hasPermission('manage_measurement')): ?>
        <a href="../pos/product_measurement.php" class="nav-link nav-2">
            <span class="icon"><i class="bi bi-rulers"></i></span>
            <span class="description">Products Unit Measurement</span>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Transactions -->
    <?php if (hasPermission('manage_transactions')): ?>
    <a href="../pos/transactions.php" class="nav-link nav-1">
        <span class="icon"><i class="bi bi-receipt"></i></span>
        <span class="description">Transactions</span>
    </a>
    <?php endif; ?>

    <!-- Suppliers -->
    <?php if (hasPermission('manage_suppliers')): ?>
    <a href="../pos/suppliers.php" class="nav-link nav-1">
        <span class="icon"><i class="bi bi-truck"></i></span>
        <span class="description">Suppliers</span>
    </a>
    <?php endif; ?>

        <!-- Rooms Section -->
        <?php if (hasPermission('manage_rooms') || hasPermission('book_rooms')): ?>
    <a href="#" class="nav-link nav-1" data-bs-toggle="collapse" data-bs-target="#submenu-rooms" aria-expanded="false">
        <span class="icon"><i class="bi bi-house"></i></span>
        <span class="description">
            Rooms <i class="bi bi-caret-down-fill"></i>
        </span>
    </a>
    <div class="sub-menu collapse" id="submenu-rooms">
        <?php if (hasPermission('manage_rooms')): ?>
        <a href="../rooms/room_management.php" class="nav-link nav-2">
            <span class="icon"><i class="bi bi-house"></i></span>
            <span class="description">Room Management</span>
        </a>
        <?php endif; ?>
        <?php if (hasPermission('book_rooms')): ?>
        <a href="../rooms/booking.php" class="nav-link nav-2">
            <span class="icon"><i class="bi bi-book"></i></span>
            <span class="description">Booking</span>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- User Account/Logout Section - Made Collapsible -->
    <a href="#" class="nav-link nav-1" data-bs-toggle="collapse" data-bs-target="#submenu-user" aria-expanded="false">
        <span class="icon"><i class="bi bi-person-circle"></i></span>
        <span class="description">
            <?php echo htmlspecialchars($_SESSION["username"] ?? 'User'); ?> <i class="bi bi-caret-down-fill"></i>
        </span>
    </a>
    <div class="sub-menu collapse" id="submenu-user">
        <a href="#" class="nav-link nav-2">
            <form action="../../controllers/logoutController.php" method="POST">
                <span class="icon"><i class="bi bi-box-arrow-right"></i></span>
                <span class="description">
                    <button type="submit" class="btn-logout">Logout</button>
                </span>
            </form>
        </a>
    </div>
    </nav>
</div>
</body>
</html>