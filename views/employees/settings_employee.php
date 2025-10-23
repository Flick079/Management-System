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
    <title>Settings Employee</title>
</head>
<body>  
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <div class="content">
        <!-- Positions -->
        <header class="employee_header">
            <h2>Settings</h2>
        </header>
        <div class="container-fluid positions border">
            <h4>Positions</h4>
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

        <div class="container-fluid positions border mt-3">
            <h4>Deductions</h4>
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
                            <td>
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

        <div class="container-fluid positions border mt-3">
            <h4>Shifts</h4>
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
                            <td>
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
    </div>
    <script>
             document.querySelectorAll(".edit-btn").forEach(button => {
            button.addEventListener("click", function (event) {
                event.preventDefault();

                let modal = this.closest(".modal");
                let inputs = modal.querySelectorAll("input:not([type='hidden'])"); //responsible for one click on edit button


                let allDisabled = Array.from(inputs).every(input => input.hasAttribute("disabled"));

                inputs.forEach(input => {
                    if(allDisabled){
                        input.removeAttribute("disabled");
                    } else {
                        input.setAttribute("disabled", "true");
                    }
                })
                this.textContent = allDisabled ? "Cancel Edit" : "Edit";
            })
        })

        // for errors to keep the modal open
        
        document.addEventListener("DOMContentLoaded", function (){
            <?php if(isset($_SESSION["keep_modal_open"]) && $_SESSION["keep_modal_open"]): ?>
                var addRoleModal = new bootstrap.Modal(document.getElementById("add_role_modal"));
                addRoleModal.show();
            <?php unset($_SESSION["keep_modal_open"]) ?>
            <?php endif; ?>
        })
    </script>
</body>
</html>