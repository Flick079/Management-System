<?php
require_once __DIR__ . '/../../middleware/verify.php';
require_once __DIR__ . '/../../controllers/employeeIdController.php';

// Check for employee ID parameter

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/employee_id_style.css">
    <script src="../../public/js/main.js"></script>
    <title>Employee ID - The Lagoon Resort</title>
    <style>
    </style>
</head>
<body>
    <div class="id-card">
        <div class="id-header">
            <img src="../../public/images/the_lagoon_logo.png" alt="The Lagoon Resort Logo">
            The Lagoon Resort Finland Inc.
        </div>
        
        <div class="id-number">ID: <?php echo htmlspecialchars($employee_id); ?></div>
        
        <div class="id-content">
            <div class="id-photo-container">
                <img src="<?php echo htmlspecialchars($employee['image']); ?>" class="id-photo" alt="Employee Photo">
            </div>
            
            <div class="employee-name">
                <?php echo htmlspecialchars($employee['full_name']); ?>
            </div>
            
            <div class="id-info">
                <p>
                    <span class="label">Position:</span>
                    <span class="value"><?php echo htmlspecialchars($employee['position']); ?></span>
                </p>
                <p>
                    <span class="label">Address:</span>
                    <span class="value"><?php echo htmlspecialchars($employee['address']); ?></span>
                </p>
                <p>
                    <span class="label">Contact #:</span>
                    <span class="value"><?php echo htmlspecialchars($employee['contact_number']); ?></span>
                </p>
            </div>
            
            <div class="id-qr">
                <img src="<?php echo htmlspecialchars($employee["qr_code"]) ?>" alt="QR Code">
            </div>
            
            <div class="print-btn-container">
                <button class="print-btn" onclick="window.print();">Print ID Card</button>
            </div>
        </div>
        
        <div class="id-footer">Valid Only for Employees</div>
    </div>
</body>
</html>