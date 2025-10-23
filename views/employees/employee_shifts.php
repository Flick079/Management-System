<?php
require_once __DIR__ . '/../../middleware/verify.php';
require_once __DIR__ . '/../../controllers/employeeController.php';
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
    <script defer src="../../public/js/settings_employee.js"></script>
    <title>Employee Shifts</title>
</head>
<body>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <div class="content">
        <div class="content-header">
            <h4>Shifts</h4>
        </div>
        <div class="content-body">
        <div class="container-fluid positions border mt-3">
            <div class="btn-settings d-flex justify-content-end mb-3">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add_shift_modal">
                    <i class="bi bi-plus"></i>
                    Add shift
                </button>
            </div>
            <table class="table table-border">
                <thead class="table-dark">
                    <tr>
                        <th>Shift</th>
                        <th>Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($shifts as $shift): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($shift["shift_name"]); ?></td>
                            <td>Time: <?php echo htmlspecialchars($shift["start_time"]) . ' - ' . htmlspecialchars($shift["end_time"]); ?></td>
                            <td class="d-flex gap-3">
                                <button class="btn btn-warning" name="edit_btn" data-bs-toggle="modal" data-bs-target="#edit_shift_modal_<?php echo htmlspecialchars($shift["shift_id"]) ?>">
                                        <i class="bi bi-pen"></i>
                                </button>

                                <form action="../../controllers/employeeController.php" method="POST">
                                    <input type="hidden" name="start_time" value="<?php echo $shift["start_time"] ?>">
                                    <input type="hidden" name="end_time" value="<?php echo $shift["end_time"] ?>">
                                    <input type="hidden" name="shift_name" value="<?php echo $shift["shift_name"] ?>">
                                    <input type="hidden" name="shift_id" value="<?php echo $shift["shift_id"] ?>">
                                    <button class="btn btn-danger" name="delete_shift_btn">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- add shift modal -->

        <div class="modal fade" aria-labelledby="add_shift_modal" id="add_shift_modal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4>Add shift</h4>
                        <button class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="../../controllers/employeeController.php" method="POST">
                            <div class="mb-3">
                                <label for="" class="form-label">Shift Name</label>
                                <input type="text" name="shift_name" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="" class="form-label">Start time:</label>
                                <input type="time" name="start_time" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="" class="form-label">End time:</label>
                                <input type="time" name="end_time" class="form-control">
                            </div>
                            <div class="mb-3">
                                <button class="btn btn-primary" name="add_shift_btn">Add time</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!--end add shift modal  -->

        <!-- edit shift modal -->
        <?php foreach($shifts as $shift): ?>
            <div class="modal fade" id="edit_shift_modal_<?php echo htmlspecialchars($shift["shift_id"]) ?>" 
            aria-labelledby="edit_shift_modal_<?php echo htmlspecialchars($shift["shift_id"]) ?>" aria-hidden="true" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4>Edit shift</h4>
                            <button class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="../../controllers/employeeController.php" method="POST">
                                <div class="mb-3">
                                    <label action="" class="form-label">shift</label>
                                    <input type="text" name="shift_name" value="<?php echo htmlspecialchars( $shift["shift_name"]) ?>" class="form-control" disabled>
                                </div>
                                <div class="mb-3">
                                    <label action="" class="form-label">Start Time</label>
                                    <input type="time" name="start_time" value="<?php echo htmlspecialchars($shift["start_time"]) ?>" class="form-control" disabled>
                                </div>
                                <div class="mb-3">
                                    <label action="" class="form-label">End Time</label>
                                    <input type="time" name="end_time" value="<?php echo htmlspecialchars($shift["end_time"]) ?>" class="form-control" disabled>
                                </div>
                                <div class="mb-3">
                                    <button type="button" class="btn btn-primary edit-btn">Edit</button>
                                </div>
                                <div class="mb-3">
                                    <input type="hidden" name="shift_id" value="<?php echo htmlspecialchars($shift["shift_id"]) ?>">
                                    <button type="submit" class="btn btn-success" name="edit_shift_btn">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>


        <!--end edit shift modal  -->
        </div>
    </div>
</body>
</html>