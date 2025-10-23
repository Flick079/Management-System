<?php
require_once __DIR__ . '/../../middleware/user_exists.php';
require_once __DIR__ . '/../../middleware/verify.php';
require_once __DIR__ . '/../../controllers/posController.php';
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
    <title>POS Dashboard</title>
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/pos_sidebar.php' ?>
    <div class="content">
        <div class="content-header">
            <h4>Dashboard</h4>
        </div>
    </div>
</body>
</html>