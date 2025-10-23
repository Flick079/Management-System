<?php
require_once __DIR__ . '/../../middleware/verify.php';
require_once __DIR__ . '/../../controllers/attendanceController.php';
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
    <title>Attendance</title>
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/sidebar.php' ?>
    <div class="content">
        <div class="header">
            <h3>Attendance</h3>
            <form action="attendance.php" method="GET">
            <div class="container-fluid d-flex justify-content-between align-items-center">
                <div class="mb-3">
                    <label for="" class="form-label">Employee List</label>
                    <select name="qr_employee_id" id="qr_employee_id" class="form-control">
                    <?php foreach ($employees as $employee): ?>
                        <option value="<?php echo htmlspecialchars($employee["qr_employee_id"]) ?>"
                            <?php echo (isset($_GET['qr_employee_id']) && $_GET['qr_employee_id'] == $employee["qr_employee_id"]) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($employee["full_name"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                </div>
                <div class="btn">
                    <button class="btn btn-primary" name="filter_name_btn">Filter</button>
                </div>
                </div>
                </form>
            </div>
            <div class="container">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th> Date: </th>
                        <th> Time-in: </th>
                        <th> Time-out: </th>
                        <th> Total hours: </th>
                    </tr>
                </thead>
                <tbody>
    <?php foreach($attendances as $attendance): ?>
        <?php 
            $total_hours = floor(abs($attendance["total_seconds"]) / 3600);
            $total_mins = floor((abs($attendance["total_seconds"]) % 3600) / 60);
        ?>
        <tr>
            <td><?php echo date("F j, Y", strtotime($attendance["date"])) ?></td>
            <td><?php echo htmlspecialchars($attendance["time_in"]) ?></td>
            <td>
                <?php echo htmlspecialchars($attendance["time_out"]) ?>
                <?php if($attendance["total_seconds"] < 0): ?>
                    <span class="text-muted small">(next day)</span>
                <?php endif; ?>
            </td>
            <td><?php echo $total_hours ?> hrs <?php echo $total_mins ?> mins</td>
        </tr>
    <?php endforeach; ?>
</tbody>
            </table>
        </div>
        </div>
</body>
</html>