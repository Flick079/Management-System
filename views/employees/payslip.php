<?php
require_once __DIR__ . '/../../middleware/verify.php'; 
require_once __DIR__ . '/../../controllers/payslipController.php';  
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
    <script defer src="../../public/js/payslip.js"></script>
    <title>Payslip</title>
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/sidebar.php'?>
    <div class="content">
        <header>
            <h3>Payslip</h3>
            <div class="filters d-flex align-items-center justify-content-center">
                <form method="GET" action="payslip.php" class="d-flex mb-3">
                    <!-- <div class="mb-3">
                        <label class="form-label">Employee's Name:</label>
                            <select class="form-control" name="employee_id">
                            <?php //foreach($employees as $employee): ?>
                                <option value="<?php //echo htmlspecialchars($employee["qr_employee_id"]); ?>"
                                    <?php //echo (isset($_GET['employee_id']) && $_GET['employee_id'] == $employee["qr_employee_id"]) ? "selected" : ""; ?>>
                                    <?php //echo htmlspecialchars($employee["full_name"]) . " - " . htmlspecialchars($employee["employee_id"]); ?>
                                </option>
                            <?php //endforeach; ?>
                            </select>
                    </div> -->
                    <div class="mb-3 px-3">
                        <label class="form-label">Pay Period Range:</label>
                    <select name="pay_period_range" class="form-control">
                        <option value="1-15" <?php echo (isset($_GET['pay_period_range']) && $_GET['pay_period_range'] == '1-15') ? 'selected' : ''; ?>>1-15</option>
                        <option value="16-end" <?php echo (isset($_GET['pay_period_range']) && $_GET['pay_period_range'] == '16-end') ? 'selected' : ''; ?>>16-End of Month</option>
                    </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Month:</label>
                        <input type="month" name="pay_period_month" class="form-control"
                        value="<?php echo isset($_GET['pay_period_month']) ? htmlspecialchars($_GET['pay_period_month']) : date('Y-m'); ?>">
                    </div>
                    <div class="mb-3 px-3">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </form>
            </div>
        </header>
        <main>
            <div class="container">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Position</th>
                        <th>Pay Period</th>
                        <th>Net Pay</th>
                        <th>Action</th>
                        </tr>
                    </thead>
                    <?php if (!empty($employee_for_payslip)): ?>
                    <tbody>
                        <?php foreach ($employee_for_payslip as $payslip): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($payslip['employee_id']); ?></td>
                                <td><?php echo htmlspecialchars($payslip['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($payslip['position']); ?></td>
                                <td><?php echo htmlspecialchars($pay_period_range); ?></td>
                                <td><?php echo number_format($payslip['net_pay'], 2); ?></td>
                                <td>
                                    <button type="button" class="btn btn-success" name="generate_payslip_btn" onclick="generatePayslip(<?php echo htmlspecialchars($payslip['employee_id']); ?>)">Generate Payslip</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No records found</td>
                    </tr>
                <?php endif; ?>
                </table>
            </div>
        </main>
    </div>
</body>
</html>