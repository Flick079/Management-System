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
    <title>Employee Deductions</title>
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <div class="content">
        <div class="content-header">
            <h4>Deductions</h4>
        </div>
        <div class="content-body">
        <div class="container-fluid deductions border mt-3">
            <div class="btn-settings d-flex justify-content-end mb-3">
                <button class="btn btn-primary" data-bs-target="#add_deduction_modal" data-bs-toggle="modal">
                    <i class="bi bi-plus"></i>
                    Add deductions
                </button>
            </div>
            <table class="table table-border">
                <thead class="table-dark">
                    <tr>
                        <th>Deduction</th>
                        <th>Percentage</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($deductions as $deduction): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($deduction["name"]); ?></td>
                            <td>%<?php echo htmlspecialchars($deduction["percentage"]); ?></td>
                            <td class="d-flex gap-3">
                                
                            <button class="btn btn-warning" name="edit_btn" data-bs-toggle="modal" data-bs-target="#edit_deduction_modal_<?php echo htmlspecialchars($deduction["deduction_id"]) ?>">
                                        <i class="bi bi-pen"></i>
                                    </button>

                                <form action="../../controllers/employeeController.php" method="POST">
                                    <input type="hidden" name="deduction" value="<?php echo $deduction["name"] ?>">
                                    <input type="hidden" name="deduction_id" value="<?php echo $deduction["deduction_id"] ?>">
                                    <button class="btn btn-danger" name="delete_deduction_btn">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

                <!-- add deduction modal -->

                <div class="modal fade" id="add_deduction_modal" aria-labelledby="add_deduction_modal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4>Add deduction</h4>
                        <button class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="../../controllers/employeeController.php" method="POST">
                            <div class="mb-3">
                                <label for="" class="form-label">Deduction</label>
                                <input type="text" name="deduction" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="" class="form-label">Amount</label>
                                <input type="text" name="amount" class="form-control">
                            </div>
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary" name="add_deduction_btn">Add deduction</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- modal end -->
        
        <!-- edit deduction modal -->
        <?php foreach($deductions as $deduction): ?>
            <div class="modal fade" id="edit_deduction_modal_<?php echo htmlspecialchars($deduction["deduction_id"]) ?>" 
            aria-labelledby="edit_deduction_modal_<?php echo htmlspecialchars($deduction["deduction_id"]) ?>" aria-hidden="true" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4>Edit deduction</h4>
                            <button class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="../../controllers/employeeController.php" method="POST">
                                <div class="mb-3">
                                    <label action="" class="form-label">deduction</label>
                                    <input type="text" name="deduction" value="<?php echo htmlspecialchars( $deduction["name"]) ?>" class="form-control" disabled>
                                </div>
                                <div class="mb-3">
                                    <label action="" class="form-label">Salary</label>
                                    <input type="number" name="percentage" value="<?php echo htmlspecialchars($deduction["percentage"]) ?>" class="form-control" disabled>
                                </div>
                                <div class="mb-3">
                                    <button type="button" class="btn btn-primary edit-btn">Edit</button>
                                </div>
                                <div class="mb-3">
                                    <input type="hidden" name="deduction_id" value="<?php echo htmlspecialchars($deduction["deduction_id"]) ?>">
                                    <button type="submit" class="btn btn-success" name="edit_deduction_btn">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>


        <!-- end edit deduction modal -->
        </div>
    </div>
</body>
</html>