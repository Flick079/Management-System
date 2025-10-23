<?php
require_once __DIR__ . '/../../controllers/payrollReportsController.php';

// Check if export to Excel is requested
// Check if export to Excel is requested
if (isset($_GET['export_excel'])) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="payroll_report_'.date('Y-m-d').'.xls"');
    
    // Function to format pay period consistently
    function formatPayPeriod($pay_period, $pay_period_month) {
        if ($pay_period == "1-15") {
            return "1-15";
        } elseif ($pay_period == "16-End") {
            // Get the last day of the month
            $date = new DateTime($pay_period_month . '-01');
            return '16-' . $date->format('t'); // 't' gives last day of month
        } else {
            return "Whole Month";
        }
    }
    
    // Output Excel content
    echo '<table border="1">';
    echo '<tr><th colspan="21" style="text-align:center;">Payroll Report</th></tr>';
    echo '<tr>';
    echo '<th>Payment Date</th>';
    echo '<th>Pay Period</th>';
    echo '<th>Employee ID</th>';
    echo '<th>Shift Type</th>';
    echo '<th>Full Name</th>';
    echo '<th>Position</th>';
    echo '<th>Regular Days</th>';
    echo '<th>Holiday Days</th>';
    echo '<th>Reg. Hrs</th>';
    echo '<th>Reg. Hrs Rate</th>';
    echo '<th>O.T. Hrs</th>';
    echo '<th>O.T. Hrs Rate</th>';
    echo '<th>Night Diff. Rate</th>';
    echo '<th>Regular Pay</th>';
    echo '<th>Holiday Pay</th>';
    echo '<th>O.T. Pay</th>';
    echo '<th>Night Diff. Pay</th>';
    echo '<th>Gross Pay</th>';
    echo '<th>Deductions</th>';
    echo '<th>Total Deductions</th>';
    echo '<th>Net Pay</th>';
    echo '</tr>';
    
    if(!empty($payrolls)) {
        foreach($payrolls as $payroll) {
            $payroll['pay_period_month'] = $pay_period_month; // Ensure it's available
            echo '<tr>';
            echo '<td>'.date("F j, Y").'</td>';
            echo '<td style="mso-number-format:\'@\';">'.htmlspecialchars($payroll['pay_period']).'</td>';
            echo '<td>'.htmlspecialchars($payroll['employee_id']).'</td>';
            // echo '<td>'.htmlspecialchars($payroll["shift_type"]).'</td>';
            echo '<td>'.htmlspecialchars($payroll['full_name']).'</td>';
            echo '<td>'.htmlspecialchars($payroll['position']).'</td>';
            echo '<td>'.htmlspecialchars($payroll['regular_days']).'</td>';
            echo '<td>'.htmlspecialchars($payroll['holiday_days']).'</td>';
            echo '<td>'.htmlspecialchars($payroll['regular_hours']).'</td>';
            echo '<td>₱'.htmlspecialchars($payroll['hourly_rate']).'</td>';
            echo '<td>'.htmlspecialchars($payroll['overtime_hours']).'</td>';
            echo '<td>₱'.htmlspecialchars($payroll['overtime_rate']).'</td>';
            echo '<td>₱51.00</td>';
            echo '<td>₱'.htmlspecialchars($payroll['regular_pay']).'</td>';
            echo '<td>₱'.htmlspecialchars($payroll['holiday_pay']).'</td>';
            echo '<td>₱'.htmlspecialchars($payroll['overtime_pay']).'</td>';
            echo '<td>₱'.htmlspecialchars($payroll['night_diff_pay']).'</td>';
            echo '<td>₱'.htmlspecialchars($payroll['gross_pay']).'</td>';
            
            // Deductions column
            echo '<td>';
            if (!empty($payroll['deductions'])) {
                foreach ($payroll['deductions'] as $deduction) {
                    echo htmlspecialchars($deduction['deduction_name'] . ": " . $deduction['deduction_percentage'] . "%") . "\n";
                }
            } else {
                echo 'No Deductions';
            }
            echo '</td>';
            
            echo '<td>₱'.htmlspecialchars($payroll['total_deductions']).'</td>';
            echo '<td>₱'.htmlspecialchars($payroll['net_pay']).'</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="21">No payroll data found for the selected period</td></tr>';
    }
    
    echo '</table>';
    exit(); // Stop further execution after exporting
}
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
    <title>Payroll Reports</title>
    <style>
        .table-responsive {
            overflow-x: auto;
        }
        table {
            width: 100%;
            white-space: nowrap;
        }
        .main-content{
            width: 100%;
        }
        .export-btn {
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/sidebar.php' ?>
    <div class="content main-content">
        <header>
            <div class="d-flex justify-content-between align-items-center">
                <h3>Payroll Reports</h3>
                <?php if(!empty($payrolls)): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['export_excel' => 1])); ?>" class="btn btn-success export-btn">
                        <i class="bi bi-file-excel"></i> Export to Excel
                    </a>
                <?php endif; ?>
            </div>
            <form action="" method="GET" class="mb-4">
                <div class="filters d-flex justify-content-center">
                    <div class="mb-3">
                        <label for="pay_period" class="form-label">Pay Period Range:</label>
                        <select name="pay_period" id="pay_period" class="form-control">
                            <option value="1-15" <?php echo $pay_period == "1-15" ? "selected" : ""; ?>>1-15</option>
                            <option value="16-End" <?php echo $pay_period == "16-End" ? "selected" : ""; ?>>16-End</option>
                            <option value="Whole Month" <?php echo $pay_period == "Whole Month" ? "selected" : ""; ?>>Whole Month</option>
                        </select>
                    </div>
                    <div class="mb-3 mx-4">
                        <label for="pay_period_month" class="form-label">Month:</label>
                        <input type="month" name="pay_period_month" id="pay_period_month" class="form-control" value="<?php echo htmlspecialchars($pay_period_month); ?>">
                    </div>
                    <div class="mb-3 mx-4">
                        <label for="employee_id" class="form-label">Employee ID (Optional):</label>
                        <input type="text" name="employee_id" id="employee_id" class="form-control" value="<?php echo htmlspecialchars($employee_id); ?>">
                    </div>
                    <div class="mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </div>
            </form>
        </header>
        <main>
            <div class="container">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Payment Date</th>
                                <th>Pay Period</th>
                                <th>Employee ID</th>
                                <!-- <th>Shift Type</th> -->
                                <th>Full Name</th>
                                <th>Position</th>
                                <th>Regular Days</th>
                                <th>Holiday Days</th>
                                <th>Reg. Hrs</th>
                                <th>Reg. Hrs Rate</th>
                                <th>O.T. Hrs</th>
                                <th>O.T. Hrs Rate</th>
                                <th>Night Diff. Rate</th>
                                <th>Regular Pay</th>
                                <th>Holiday Pay</th>
                                <th>O.T. Pay</th>
                                <th>Night Diff. Pay</th>
                                <th>Gross Pay</th>
                                <th>Deductions</th>
                                <th>Total Deductions</th>
                                <th>Net Pay</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($payrolls)): ?>
                                <?php foreach($payrolls as $payroll): ?>
                                <tr>
                                    <td><?php echo date("F j, Y"); ?></td>
                                    <td><?php echo htmlspecialchars($payroll['pay_period']); ?></td>
                                    <td><?php echo htmlspecialchars($payroll['employee_id']); ?></td>
                                    <!-- <td><?php //echo htmlspecialchars($payroll["shift_type"]); ?></td> -->
                                    <td><?php echo htmlspecialchars($payroll['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($payroll['position']); ?></td>
                                    <td><?php echo htmlspecialchars($payroll['regular_days']); ?></td>
                                    <td><?php echo htmlspecialchars($payroll['holiday_days']); ?></td>
                                    <td><?php echo htmlspecialchars($payroll['regular_hours']); ?></td>
                                    <td>₱<?php echo htmlspecialchars($payroll['hourly_rate']); ?></td>
                                    <td><?php echo htmlspecialchars($payroll['overtime_hours']); ?></td>
                                    <td>₱<?php echo htmlspecialchars($payroll['overtime_rate']); ?></td>
                                    <td>₱51.00</td>
                                    <td>₱<?php echo htmlspecialchars($payroll['regular_pay']); ?></td>
                                    <td>₱<?php echo htmlspecialchars($payroll['holiday_pay']); ?></td>
                                    <td>₱<?php echo htmlspecialchars($payroll['overtime_pay']); ?></td>
                                    <td>₱<?php echo htmlspecialchars($payroll['night_diff_pay']); ?></td>
                                    <td>₱<?php echo htmlspecialchars($payroll['gross_pay']); ?></td>
                                    <td>
                                        <?php if (!empty($payroll['deductions'])): ?>
                                            <?php foreach ($payroll['deductions'] as $deduction): ?>
                                                <div><?php echo htmlspecialchars($deduction['deduction_name'] . ": " . $deduction['deduction_percentage'] . "%"); ?></div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div>No Deductions</div>
                                        <?php endif; ?>
                                    </td>
                                    <td>₱<?php echo htmlspecialchars($payroll['total_deductions']); ?></td>
                                    <td>₱<?php echo htmlspecialchars($payroll['net_pay']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="21" class="text-center">No payroll data found for the selected period</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>