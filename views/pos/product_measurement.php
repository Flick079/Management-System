<?php
require_once __DIR__ . '/../../middleware/user_exists.php';
require_once __DIR__ . '/../../middleware/verify.php';
require_once __DIR__ . '/../../controllers/measurementController.php';
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
    <title>Products measurement</title>
</head>
<body>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <div class="content">
        <div class="content-header d-flex justify-content-between">
            <h4>Products Measurement</h4>
            <div class="btns">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add_measurement_modal">
                    <i class="bi bi-plus">
                    </i>
                    Add measurement
                </button>
            </div>
        </div>
        <div class="content-body">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Unit Measurement</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($measurements as $measurement): ?>
                    <tr>
 
                            <td><?php echo htmlspecialchars($measurement["measurement"]) ?></td>
                            <td class="d-flex gap-2">
                                <div class="edit-btn">
                                        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#edit_measurement_modal_<?php echo htmlspecialchars($measurement["unit_id"]) ?>">
                                            <i class="bi bi-pen"></i>
                                        </button>
                                </div>
                                <div class="delete-btn">
                                    <form action="../../controllers/measurementController.php" method="POST">
                                        <input type="hidden" name="measurement_id" value="<?php echo htmlspecialchars($measurement["unit_id"]) ?>">
                                        <button class="btn btn-danger" name="delete_measurement_btn">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>

                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <!-- add measurement modal -->
    <div class="modal fade" id="add_measurement_modal" aria-labelledby="add_measurement_modal" aria-hidden="true" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Add new measurement</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                </div>
                <div class="modal-body">
                    <form action="../../controllers/measurementController.php" method="POST">
                        <div class="mb-3">
                            <label for="" class="form-label">Measurement</label>
                            <input type="text" name="measurement" class="form-control">
                        </div>
                        <div class="mb-3">
                            <button class="btn btn-primary" name="add_measurement_btn">Add</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- end add measurement modal -->
    <?php foreach($measurements as $measurement): ?>
        <!-- edit measurement modal -->
        <div class="modal fade" id="edit_measurement_modal_<?php echo htmlspecialchars($measurement["unit_id"]) ?>" aria-labelledby="edit_measurement_modal_<?php echo htmlspecialchars($measurement["unit_id"]) ?>" aria-hidden="true" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Edit measurement</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                </div>
                <div class="modal-body">
                    <form action="../../controllers/measurementController.php" method="POST">
                        <div class="mb-3">
                            <label for="" class="form-label">Measurement</label>
                            <input type="text" name="measurement" class="form-control" value="<?php echo htmlspecialchars($measurement["measurement"]) ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <button type="button" class="btn btn-primary edit-btn" id="edit-btn">Edit</button>
                        </div>
                        <div class="mb-3">
                            <input type="hidden" name="unit_id" value="<?php echo htmlspecialchars($measurement["unit_id"]) ?>">
                            <button class="btn btn-success" name="edit_measurement_btn">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
    <!-- end edit measurement modal -->
    </div>
    <script>
    document.querySelectorAll(".edit-btn").forEach(button => {
    button.addEventListener("click", function (event) {
        event.preventDefault();

        let modal = this.closest(".modal");
        let inputs = modal.querySelectorAll("input:not([type='hidden'])"); //responsible for one click on edit button


        let allDisabled = Array.from(inputs).every(input => input.hasAttribute("disabled"));

        inputs.forEach(input => {
            if (allDisabled) {
                input.removeAttribute("disabled");
            } else {
                input.setAttribute("disabled", "true");
            }
        })
        this.textContent = allDisabled ? "Cancel Edit" : "Edit";
    })
})


    </script>
</body>
</html>