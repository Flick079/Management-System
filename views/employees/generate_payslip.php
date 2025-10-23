<?php
require_once __DIR__ . '/../../middleware/verify.php';
require_once __DIR__ . '/../../controllers/generatePayslipController.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/generate_payslip.css">
    <title>Generate Payslip</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .payslip {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .payslip-header {
            display: flex;
            justify-content: space-evenly;
            align-items: baseline;
            font-weight: bold;
            background-color: white;
            color: #333;
            text-align: end;
            padding: 0;
        }
        .payslip-header img {
            height: 34px;
            width: 49px;
            vertical-align: middle;
            margin: 0%;
        }
        .id-number {
            text-align: left;

            font-style: italic;
            background-color: white;
            color: #333;
        }
        .payslip-content {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .salary-details{
            margin: 0;
            padding: 0;
        }
        .deductions{
            margin: 0;
            padding: 0;
        }
        .salary-details, .deductions {
            flex: 1;
            min-width: 300px;
        }
        h3 {
            border-bottom: 1px solid #ddd;

   
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
        }
        .label {
            font-weight: bold;
        }
        .holiday-pay {
            color: #d9534f;
        }
        .overtime-pay {
            color: #5bc0de;
        }
        .night-diff-pay {
            color: #5cb85c;
        }
        .total {
            font-size: 18px;
            font-weight: bold;
            border-top: 2px solid #333;
        }
        @media print{
            #print-btn{
                display: none;
            }
        }

        .total-deviation{
            color: #e74c3c;
        }
    </style>
</head>
<body>
    <div class="payslip">
        <div class="payslip-header">
            <img src="../../public/images/the_lagoon_logo.png" alt="Company Logo">
            The Lagoon Resort Finland Inc.
        </div>

        <div class="id-number">ID: <?php echo htmlspecialchars($employee_id); ?></div>
        <hr>
        <div class="payslip-content">
            <div class="salary-details">
                <h3>Personal Information</h3>
                <div class="detail-row">
                    <span class="label">Full Name:</span>
                    <span class="value"><?php echo htmlspecialchars($employee["full_name"]) ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Address:</span>
                    <span class="value"><?php echo htmlspecialchars($employee["address"]) ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Contact Number:</span>
                    <span class="value"><?php echo htmlspecialchars($employee["contact_number"]) ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Position:</span>
                    <span class="value"><?php echo htmlspecialchars($employee["position"]) ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Pay Period:</span>
                    <span class="value"><?php echo htmlspecialchars($formattedStart. " to " . $formattedEnd) ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Pay Date:</span>
                    <span class="value"><?php echo htmlspecialchars(date("F, j Y")) ?></span>
                </div>

                <h3>Earnings</h3>
                <div class="detail-row">
                    <span class="label">Daily Rate:</span>
                    <span class="value">₱<?php echo number_format($employee["salary"], 2); ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Days of Work:</span>
                    <span class="value"><?php echo htmlspecialchars($employee["scheduled_days"]); ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Days Present:</span>
                    <span class="value"><?php echo htmlspecialchars($employee["present_days"]); ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Regular Days:</span>
                    <span class="value"><?php echo htmlspecialchars($employee["regular_days"]); ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Leave Days:</span>
                    <span class="value"><?php echo htmlspecialchars($employee["leave_days"]); ?></span>
                </div>
                <!-- Late Arrivals -->
<div class="detail-row time-deviation late">
    <span class="label">Late Arrival Hours:</span>
    <span class="value">
        ₱<?php echo isset($employee["lateDeduction"]) ? number_format($employee["lateDeduction"], 2) : '0.00'; ?>  
        (<?php  echo isset($employee["late_hours"]) ? number_format($employee["late_hours"], 2): '0.00'; ?>hrs)
    </span>
</div>

<!-- Early Departures -->
<div class="detail-row time-deviation undertime">
    <span class="label">Undertime:</span>
    <span class="value">
        ₱<?php echo isset($employee["undertimeDeduction"]) ? number_format($employee["undertimeDeduction"], 2) : '0.00'; ?>  
        (<?php  echo isset($employee["undertime_hours"]) ? number_format($employee["undertime_hours"], 2): '0.00'; ?>hrs)
    </span>
</div>

<!-- Total Time Adjustments -->
<div class="detail-row total-deviation">
    <span class="label">Total Time Adjustments:</span>
    <span class="value">
        -₱<?php 
            $late = isset($employee["lateDeduction"]) ? $employee["lateDeduction"] : 0;
            $undertime = isset($employee["undertimeDeduction"]) ? $employee["undertimeDeduction"] : 0;
            echo number_format($late + $undertime, 2); 
        ?>
    </span>
</div>
                <div class="detail-row">
                    <span class="label">Regular Pay:</span>
                    <span class="value">₱<?php echo number_format($employee["regular_pay"], 2); ?></span>
                </div>
                
                <?php //if($employee["holiday_days"] > 0): ?>
                <!-- <div class="detail-row holiday-pay">
                    <span class="label">Holiday Days:</span>
                    <span class="value"><?php //echo htmlspecialchars($employee["holiday_days"]); ?></span>
                </div> -->
                <div class="detail-row holiday-pay">
                    <span class="label">Holiday Pay:</span>
                    <span class="value">₱<?php echo number_format($employee["holiday_pay"], 2); ?> 
                    (<?php echo htmlspecialchars($employee["holiday_days"]); ?>d)</span>
                </div>
                <?php //endif; ?>
                
                <?php //if($employee["overtime_hours"] > 0): ?>
                <div class="detail-row overtime-pay">
                    <span class="label">Overtime Rate:</span>
                    <span class="value">₱<?php echo number_format($employee["overtime_rate"], 2); ?></span>
                </div>
                <!-- <div class="detail-row overtime-pay">
                    <span class="label">Overtime Hours:</span>
                    <span class="value"><?php //echo number_format($employee["overtime_hours"], 2); ?> hrs</span>
                </div> -->
                <div class="detail-row overtime-pay">
                    <span class="label">Overtime Pay:</span>
                    <span class="value">₱<?php echo number_format($employee["overtime_pay"], 2);?> 
                    (<?php echo number_format(customRoundTime($employee["overtime_hours"])); ?>
                    hrs)</span>
                </div>
                <?php //endif; ?>
                <?php //if($employee["night_diff_pay"] > 0): ?>
                <div class="detail-row night-diff-pay">
                    <span class="label">Night Differential Rate:</span>
                    <span class="value">₱51</span>
                </div>
                <!-- <div class="detail-row night-diff-pay">
                    <span class="label">Night Shift Times:</span>
                    <span class="value"><?php //echo number_format($employee["night_diff_pay"] /51); ?></span>
                </div> -->
                <?php //if($employee["night_diff_pay"] > 0): ?>
                <div class="detail-row night-diff-pay">
                    <span class="label">Night Differential:</span>
                    <span class="value">₱<?php echo number_format($employee["night_diff_pay"], 2); ?>
                    (<?php echo number_format($employee["night_diff_pay"] /51); ?>d)
                </span>
                </div>
                <?php //endif; ?>
                <div class="detail-row">
                    <span class="label">Total Hours:</span>
                    <span class="value"><?php echo htmlspecialchars(($employee["regular_days"] + $employee["holiday_days"]) * 8); ?> hrs</span>
                </div>
                <div class="detail-row">
                    <span class="label">Gross Pay:</span>
                    <span class="value">₱<?php echo number_format($employee["gross_pay"], 2); ?></span>
                </div>
            </div>
    
            <div class="deductions">
                <h3>Deductions (<?php echo $employee["total_deduction_percentage"]; ?>%)</h3>
                <?php if(!empty($employee["deductions"])): ?>
                    <?php foreach($employee["deductions"] as $deduction): ?>
                        <?php if(($pay_period_range === "1-15" && ($deduction["deduction_name"] === "PhilHealth" || $deduction["deduction_name"] === "Pag-IBIG")) || 
                              ($pay_period_range === "16-end" && ($deduction["deduction_name"] === "SSS"))): ?>
                            <div class="detail-row">
                                <span class="label"><?php echo htmlspecialchars($deduction["deduction_name"]); ?>:</span>
                                <span class="value"><?php echo $deduction["deduction_percentage"]; ?>%</span>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    
                    <div class="detail-row">
                        <span class="label">Total Deductions:</span>
                        <span class="value">₱<?php echo number_format($employee["total_deductions"], 2); ?></span>
                    </div>
                <?php endif; ?>
                
                <div class="detail-row total">
                    <span class="label">Net Pay:</span>
                    <span class="value">₱<?php echo number_format($employee["net_pay"], 2); ?></span>
                </div>
            </div>
        </div>
    <div class="print-btn-container">
        <button class="print-btn" onclick="window.print();">Print Payslip</button>
    </div>
    </div>

</body>
</html>