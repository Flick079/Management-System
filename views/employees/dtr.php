<?php
require_once __DIR__ . '/../../middleware/verify.php';
require_once __DIR__ . '/../../controllers/dtrController.php';
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
    <title>Date and Time Record</title>
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/sidebar.php' ?>

    <div class="content">
        <header>
            <div class="container-fluid d-flex justify-content-between">
                <h3>Date and Time Record</h3>
                <form action="dtr.php" method="GET" class="d-flex">
                <input type="date" name="specific_date" class="form-control mx-2" 
                value="<?php echo isset($_GET['specific_date']) ? htmlspecialchars($_GET['specific_date']) : date('Y-m-d'); ?>">
                    <button class="btn btn-primary">Filter</button>
                </form>
            </div>
        </header>
        <main>
            <table class="table table-bordered mt-3">
                <thead class="table-dark">
                    <tr>
                        <th>Employee Name</th>
                        <th>Time in</th>
                        <th>Time out</th>
                        <th>Total Hours</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($records as $record): ?>
                        <?php $total_hours = floor($record["total_seconds"] / 3600) ?>
                        <?php $total_mins = floor(($record["total_seconds"] % 3600) / 60) ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record["full_name"]) ?></td>
                            <td><?php echo htmlspecialchars($record["time_in"]) ?></td>
                            <td><?php echo htmlspecialchars($record["time_out"]) ?></td>
                            <td> <?php echo $total_hours ?> hrs <?php echo $total_mins ?> mins</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>
    <script>
//           setInterval(function() {
//     location.reload();
//   }, 2000); // Refresh every 10 seconds
    </script>
</body>
</html>