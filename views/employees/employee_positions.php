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
    <title>Employee Positions</title>
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <div class="content">
        <div class="content-header">
            <h4>Positions</h4>
        </div>
        <div class="content-body">
            <div class="container">
            <div class="btn-settings d-flex justify-content-end mb-3">
                <button class="btn btn-primary" data-bs-target="#add_position_modal" data-bs-toggle="modal">
                    <i class="bi bi-plus"></i>
                    Add position
                </button>
            </div>
            <table class="table table-border">
                <thead class="table-dark">
                    <tr>
                        <th>Position</th>
                        <th>Salary</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($positions as $position): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($position["position"]); ?></td>
                            <td>₱<?php echo htmlspecialchars($position["salary"]); ?></td>
                            <td class="d-flex gap-3">

                                    <button class="btn btn-warning" name="edit_btn" data-bs-toggle="modal" data-bs-target="#edit_position_modal_<?php echo htmlspecialchars($position["position_id"]) ?>">
                                        <i class="bi bi-pen"></i>
                                    </button>

                                <form action="../../controllers/employeeController.php" method="POST">
                                        <input type="hidden" name="position" value="<?php echo $position["position"] ?>">
                                        <input type="hidden" name="position_id" value="<?php echo $position["position_id"] ?>">
                                        <button class="btn btn-danger" name="delete_position_btn">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- add position modal -->

        <div class="modal fade" id="add_position_modal" aria-labelledby="add_position_modal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4>Add position</h4>
                        <button class="btn-close" data-bs-dismiss="modal" aria-label></button>
                    </div>
                    <div class="modal-body">
                        <form action="../../controllers/employeeController.php" method="POST">
                            <div class="mb-3">
                                <label for="" class="form-label">Position</label>
                                <input type="text" name="position" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="" class="form-label">Salary</label>
                                <input type="number" name="salary" class="form-control">
                            </div>
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary" name="add_position_btn">Add position</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- modal end -->

        <!-- edit position modal -->
        <?php foreach($positions as $position): ?>
            <div class="modal fade" id="edit_position_modal_<?php echo htmlspecialchars($position["position_id"]) ?>" 
            aria-labelledby="edit_position_modal_<?php echo htmlspecialchars($position["position_id"]) ?>" aria-hidden="true" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4>Edit position</h4>
                            <button class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="../../controllers/employeeController.php" method="POST">
                                <div class="mb-3">
                                    <label action="" class="form-label">Position</label>
                                    <input type="text" name="position" value="<?php echo htmlspecialchars( $position["position"]) ?>" class="form-control" disabled>
                                </div>
                                <div class="mb-3">
                                    <label action="" class="form-label">Salary</label>
                                    <input type="number" name="salary" value="<?php echo htmlspecialchars($position["salary"]) ?>" class="form-control" disabled>
                                </div>
                                <div class="mb-3">
                                    <button type="button" class="btn btn-primary edit-btn">Edit</button>
                                </div>
                                <div class="mb-3">
                                    <input type="hidden" name="position_id" value="<?php echo htmlspecialchars($position["position_id"]) ?>">
                                    <button type="submit" class="btn btn-success" name="edit_position_btn">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- end edit position modal -->

            </div>
        </div>
    </div>
    
</body>
</html>