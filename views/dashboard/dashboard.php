<?php
require_once __DIR__ . '/../../controllers/dashboardController.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="../../public/css/bootstrap-icons-1.11.0/bootstrap-icons.min.css">
    <script defer src="../../public/js/bootstrap.bundle.min.js"></script>
    <script defer src="../../public/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Dashboard</title>
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/sidebar.php' ?>
    <div class="content">
        <div class="header-content">
            <h4>Dashboard Overview</h4>
        </div>
        
        <div class="body-content">
            <!-- Sales Section -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5>Sales Overview</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Today's Sales</h6>
                                    <p class="card-text h4">₱<?php echo number_format($todaySales['total_sales'] ?? 0, 2) ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Transactions</h6>
                                    <p class="card-text h4"><?php echo $todaySales['transaction_count'] ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-dark">
                                <div class="card-body">
                                    <h6 class="card-title">Discounts</h6>
                                    <p class="card-text h4">₱<?php echo number_format($todaySales['total_discounts'] ?? 0, 2) ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-secondary text-white">
                                <div class="card-body">
                                    <h6 class="card-title">VAT</h6>
                                    <p class="card-text h4">₱<?php echo number_format($todaySales['total_vat'] ?? 0, 2) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <h6>Recent Transactions</h6>
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Date/Time</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Payment</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentTransactions as $transaction): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($transaction['transaction_id']) ?></td>
                                <td><?php echo date('M d, h:i A', strtotime($transaction['transaction_date'])) ?></td>
                                <td><?php echo $transaction['items_count'] ?></td>
                                <td>₱<?php echo number_format($transaction['total'], 2) ?></td>
                                <td><?php echo ucfirst(str_replace('_', ' ', $transaction['payment_method'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Sales Chart Section -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5>Sales Trends (Last 7 Days)</h5>
                </div>
                <div class="card-body" style="height: 250px;">
                    <canvas id="salesChart" height="200"></canvas>
                </div>
            </div>
            
            <!-- Inventory Section -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5>Inventory Status</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Total Products</h6>
                                    <p class="card-text h4"><?php echo $inventoryStatus['total_products'] ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Total Stock</h6>
                                    <p class="card-text h4"><?php echo $inventoryStatus['total_stock'] ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Low Stock Items</h6>
                                    <p class="card-text h4"><?php echo $inventoryStatus['low_stock_items'] ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <h6>Low Stock Products</h6>
                    <table class="table table-bordered table-sm">
                        <thead class="table-danger">
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Current Stock</th>
                                <th>Reorder Point</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lowStockProducts as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['product_name']) ?></td>
                                <td><?php echo htmlspecialchars($product['category_name']) ?></td>
                                <td><?php echo $product['quantity'] ?></td>
                                <td><?php echo $product['reorder_point'] ?></td>
                                <td>
                                    <?php if ($product['quantity'] == 0): ?>
                                        <span class="badge bg-danger">Out of Stock</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Low Stock</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <h6 class="mt-4">Expiring Inventory</h6>
                    <table class="table table-bordered table-sm">
                        <thead class="table-warning">
                            <tr>
                                <th>Product</th>
                                <th>Batch</th>
                                <th>Quantity</th>
                                <th>Expiration Date</th>
                                <th>Days Left</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($expiringInventory as $batch): 
                                $daysLeft = floor((strtotime($batch['expiration_date']) - time()) / (60 * 60 * 24));
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($batch['product_name']) ?></td>
                                <td><?php echo $batch['batch_id'] ?></td>
                                <td><?php echo $batch['quantity'] ?></td>
                                <td><?php echo date('M d, Y', strtotime($batch['expiration_date'])) ?></td>
                                <td>
                                    <?php if ($daysLeft < 0): ?>
                                        <span class="badge bg-danger">Expired</span>
                                    <?php elseif ($daysLeft <= 7): ?>
                                        <span class="badge bg-warning text-dark"><?php echo $daysLeft ?> days</span>
                                    <?php else: ?>
                                        <span class="badge bg-success"><?php echo $daysLeft ?> days</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Supplier Orders Section -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5>Supplier Orders</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Pending Orders</h6>
                                    <p class="card-text h4"><?php echo $supplierOrders['pending_count'] ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Delivered</h6>
                                    <p class="card-text h4"><?php echo $supplierOrders['delivered_count'] ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Cancelled</h6>
                                    <p class="card-text h4"><?php echo $supplierOrders['cancelled_count'] ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Total Amount</h6>
                                    <p class="card-text h4">₱<?php echo number_format($supplierOrders['total_amount'] ?? 0, 2) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <h6>Recent Orders</h6>
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Supplier</th>
                                <th>Order Date</th>
                                <th>Expected Delivery</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentSupplierOrders as $order): ?>
                            <tr>
                                <td><?php echo $order['order_id'] ?></td>
                                <td><?php echo htmlspecialchars($order['supplier_name']) ?></td>
                                <td><?php echo date('M d, Y', strtotime($order['order_date'])) ?></td>
                                <td><?php echo $order['expected_delivery_date'] ? date('M d, Y', strtotime($order['expected_delivery_date'])) : '--' ?></td>
                                <td>₱<?php echo number_format($order['total_amount'], 2) ?></td>
                                <td>
                                    <?php if ($order['status'] == 'pending'): ?>
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    <?php elseif ($order['status'] == 'delivered'): ?>
                                        <span class="badge bg-success">Delivered</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Cancelled</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Attendance Section -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5>Attendance Overview</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Total Employees</h6>
                                    <p class="card-text h4"><?php echo $todayAttendance['total_employees'] ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Present Today</h6>
                                    <p class="card-text h4"><?php echo $todayAttendance['present_count'] ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-warning text-dark">
                                <div class="card-body">
                                    <h6 class="card-title">Completed Shifts</h6>
                                    <p class="card-text h4"><?php echo $todayAttendance['completed_count'] ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <h6>Recent Attendance</h6>
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Date</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentAttendance as $attendance): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($attendance['qr_employee_id']) ?></td>
                                <td><?php echo date('M d, Y', strtotime($attendance['date'])) ?></td>
                                <td><?php echo $attendance['time_in'] ?? '--' ?></td>
                                <td><?php echo $attendance['time_out'] ?? '--' ?></td>
                                <td><?php echo $attendance['schedule_status'] ?? '--' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Attendance Chart Section -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5>Attendance Trends (Last 7 Days)</h5>
                </div>
                <div class="card-body" style="height: 250px;">
                    <canvas id="attendanceChart" height="200"></canvas>
                </div>
            </div>
            
            <!-- Today's Scheduled Shifts Section -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5>Today's Scheduled Shifts</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Shift</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th>Attendance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($todayScheduledShifts as $shift): 
                                $currentTime = date('H:i:s');
                                $shiftEnded = ($currentTime > $shift['end_time']);
                                $shiftStarted = ($currentTime >= $shift['start_time']);
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($shift['employee_id']) ?></td>
                                <td><?php echo htmlspecialchars($shift['shift_name']) ?></td>
                                <td>
                                    <?php echo date('h:i A', strtotime($shift['start_time'])) ?> - 
                                    <?php echo date('h:i A', strtotime($shift['end_time'])) ?>
                                </td>
                                <td>
                                    <?php if ($shift['schedule_status'] == 'Approved'): ?>
                                        <span class="badge bg-success">Approved</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark"><?php echo $shift['schedule_status'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($shift['time_in']): ?>
                                        <span class="badge bg-success">
                                            In: <?php echo date('h:i A', strtotime($shift['time_in'])) ?>
                                            <?php if ($shift['time_out']): ?>
                                                / Out: <?php echo date('h:i A', strtotime($shift['time_out'])) ?>
                                            <?php endif; ?>
                                        </span>
                                    <?php elseif ($shiftStarted && !$shiftEnded): ?>
                                        <span class="badge bg-warning text-dark">In Progress (Not Checked In)</span>
                                    <?php elseif ($shiftEnded): ?>
                                        <span class="badge bg-danger">Absent</span>
                                    <?php else: ?>
                                        <span class="badge bg-info">Upcoming</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Leave Requests Section -->
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                    <h5>Leave Requests</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Pending Requests</h6>
                                    <p class="card-text h4"><?php echo $pendingLeaveRequests['pending_requests'] ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Approved</h6>
                                    <p class="card-text h4"><?php echo $pendingLeaveRequests['approved_requests'] ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-warning text-dark">
                                <div class="card-body">
                                    <h6 class="card-title">Rejected</h6>
                                    <p class="card-text h4"><?php echo $pendingLeaveRequests['rejected_requests'] ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <h6>Recent Leave Requests</h6>
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Leave Type</th>
                                <th>Dates</th>
                                <th>Days</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentLeaveRequests as $request): 
                                $days = floor((strtotime($request['end_date']) - strtotime($request['start_date'])) / (60 * 60 * 24) + 1)
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($request['full_name']) ?></td>
                                <td><?php echo htmlspecialchars($request['leave_type']) ?></td>
                                <td>
                                    <?php echo date('M d', strtotime($request['start_date'])) ?> - 
                                    <?php echo date('M d, Y', strtotime($request['end_date'])) ?>
                                </td>
                                <td><?php echo $days ?></td>
                                <td>
                                    <?php if ($request['status'] == 'Pending'): ?>
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    <?php elseif ($request['status'] == 'Approved'): ?>
                                        <span class="badge bg-success">Approved</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Rejected</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Sales Chart
    const salesCtx = document.getElementById('salesChart');
    new Chart(salesCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($salesDates) ?>,
            datasets: [
                {
                    label: 'Total Sales',
                    data: <?php echo json_encode($salesAmounts) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    yAxisID: 'y'
                },
                {
                    label: 'Transaction Count',
                    data: <?php echo json_encode($salesCounts) ?>,
                    backgroundColor: 'rgba(255, 159, 64, 0.7)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1,
                    type: 'line',
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Sales Amount (₱)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Transaction Count'
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });

    // Attendance Chart
    const attendanceCtx = document.getElementById('attendanceChart');
    new Chart(attendanceCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($attendanceDates) ?>,
            datasets: [
                {
                    label: 'Total Scheduled',
                    data: <?php echo json_encode($attendanceTotal) ?>,
                    backgroundColor: 'rgba(201, 203, 207, 0.2)',
                    borderColor: 'rgba(201, 203, 207, 1)',
                    borderWidth: 2,
                    tension: 0.1
                },
                {
                    label: 'Present Employees',
                    data: <?php echo json_encode($attendancePresent) ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2,
                    tension: 0.1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Employees'
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });
});
</script>
</body>
</html>