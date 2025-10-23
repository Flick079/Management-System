<?php
require_once __DIR__ . '/../../middleware/verify.php';
require_once __DIR__ . '/../../controllers/employeeController.php';
require_once __DIR__ . '/../layouts/sidebar.php';
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
    <script defer src="../../public/js/main.js"></script>
    <title>Employee Management</title>
</head>
<body>
    <div class="content">
        <header class="employee_header">
            <h3>Employee Management</h3>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add_employee_modal">
                <i class="bi bi-person-plus-fill"></i>
                Add Employee
            </button>
        </header>

        <main>
            <div class="container-fluid mt-3">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Employee Picture</th>
                            <th>Employee Name</th>
                            <th>Position</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($employees as $employee): ?>
                            <tr>
                            <td class="d-flex justify-content-center">
                                    <img src="<?php echo htmlspecialchars($employee["image"]); ?>" class="img-thumbnail employee-img" 
                                    style="height: 110px; max-width: 130px; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#expand_employee_picture_<?php echo htmlspecialchars($employee["employee_id"]) ?>">
                                </td>
                                <td><?php echo htmlspecialchars($employee["full_name"]) ?></td>
                                <td><?php echo htmlspecialchars($employee["position"]) ?></td>
                                <td><?php echo checkEmployeeStatus($pdo, $employee["qr_employee_id"]); ?></td>
                                <td class="d-flex justify-content-center align-items-center" style="height: 100%;">
                                    <div class="edit mx-3">
                                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#edit_employee_modal_<?php echo htmlspecialchars($employee["employee_id"])?>">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                    </div>
                                    <div class="delete">
                                        <form action="../../controllers/employeeController.php" method="POST">
                                            <input type="hidden" name="full_name" value="<?php echo htmlspecialchars($employee["full_name"]) ?>">
                                            <input type="hidden" name="employee_id" value="<?php echo htmlspecialchars($employee["employee_id"]) ?>">
                                            <button class="btn btn-danger" name="employee_delete_btn">
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
        </main>
    </div>

    <!-- add employee modal -->
    <div class="modal fade" id="add_employee_modal" aria-labelledby="add_employee_modal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4>Add Employee</h4>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                </div>
                <div class="modal-body">
                    <form action="../../controllers/employeeController.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="" class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="" class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="" class="form-label">Date of Birth</label>
                            <input type="date" name="dob" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="" class="form-label">Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="" class="form-label">Address</label>
                            <input type="text" name="address" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="" class="form-label">Contact Number</label>
                            <input type="tel" name="contact_number" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="" class="form-label">Position</label>
                                <select name="position" class="form-control" id="">
                                    <?php foreach($positions as $position): ?> <br>
                                        <option value="<?php echo htmlspecialchars($position["position_id"]) ?>"><?php echo htmlspecialchars($position["position"]) ?></option>
                                    <?php endforeach; ?>
                                </select>
                        </div>
                        <div class="mb-3">
                            <label for="" class="form-label">Deductions</label>
                            <?php foreach($deductions as $deduction): ?> <br>
                                <input type="checkbox" name="deduction[]"  value="<?php echo htmlspecialchars($deduction["deduction_id"]) ?>">
                                <?php echo htmlspecialchars($deduction["name"]); ?> <br>
                            <?php endforeach; ?>
                        </div>
                        <div class="mb-3">
                            <label for="" class="form-label">Employee Picture</label>
                            <input type="file" name="image" class="form-control">
                        </div>
                        <div class="mb-3">
                            <button class="btn btn-primary" name="add_employee_btn">Add employee</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- edit employee modal -->
    <?php foreach($employees as $employee): ?>
    <div class="modal fade" id="edit_employee_modal_<?php echo htmlspecialchars($employee["employee_id"])?>" aria-labelledby="edit_employee_modal_<?php echo htmlspecialchars($employee["employee_id"])?>" aria-hidden="true" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                    <div class="modal-header">
                        <h4>Edit <?php echo htmlspecialchars($employee["full_name"]); ?></h4>
                        <button class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="../../controllers/employeeController.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="employee_id" value="<?php echo htmlspecialchars($employee["employee_id"]); ?>">

                            <div class="mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($employee["first_name"]) ?>" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($employee["last_name"]) ?>" disabled>
                            </div>
                            <div class="mb-3">
                            <label for="" class="form-label">Date of Birth</label>
                            <input type="date" name="dob" class="form-control" value="<?php echo htmlspecialchars($employee["dob"]) ?>" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($employee["email"]) ?>" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($employee["address"]) ?>" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contact Number</label>
                                <input type="tel" name="contact_number" class="form-control" value="<?php echo htmlspecialchars($employee["contact_number"]) ?>" disabled>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Position</label>
                                <select name="position" class="form-control" disabled>
                                    <?php foreach ($positions as $position): ?>
                                        <option value="<?php echo htmlspecialchars($position["position_id"]); ?>"
                                            <?php echo ($position["position_id"] == $employee["position_id"]) ? "selected" : ""; ?> disabled>
                                            <?php echo htmlspecialchars($position["position"]); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Deductions Section -->
                            <div class="mb-3">
                                <label class="form-label">Deductions</label> <br>
                                <?php 
                                    // Get employee's existing deductions
                                    $existingDeductions = explode(", ", $employee["deductions"] ?? ""); 

                                    foreach ($deductions as $deduction): 
                                        $checked = in_array($deduction["name"], $existingDeductions) ? "checked" : "";
                                ?>
                                    <input type="checkbox" name="deductions[]" value="<?php echo htmlspecialchars($deduction["deduction_id"]); ?>" <?php echo $checked; ?> disabled>
                                    <?php echo htmlspecialchars($deduction["name"]); ?> <br>
                                <?php endforeach; ?>
                            </div>
                            <div class="mb-3 d-flex flex-direction-column">
                                <label for="" class="form-label">Image</label>
                                <div class="container-fluid mt-5">
                                <img src="<?php echo htmlspecialchars($employee["image"])?>" alt="" style="width: 100px;" class="p-0">
                                <input type="file" name="image" class="form-control" disabled>
                                </div>
                            </div>
                            <div class="mb-3">
                                <button type="button" class="btn btn-primary edit-btn">Edit</button>
                            </div>
                            <div class="mb-3">
                                    <input type="hidden" name="full_name" value="<?php echo htmlspecialchars($employee["full_name"]) ?>">
                                    <input type="hidden" name="employee_id" value="<?php echo htmlspecialchars($employee["employee_id"]) ?>">
                                    <button type="submit" class="btn btn-success" name="update_employee_btn">Submit</button>
                            </div>
                            <div class="mb-3">
                            <button type="button" class="btn btn-info" name="view-id_btn" onclick="openIDCard(<?php echo htmlspecialchars($employee['employee_id']); ?>)">View ID</button>
                            </div>
                        </form>
                    </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

    <!-- Image Modal -->
<!-- Image Modal -->
<?php foreach($employees as $employee): ?>
<div class="modal fade" id="expand_employee_picture_<?php echo htmlspecialchars($employee["employee_id"]) ?>" aria-labelledby="expand_employee_picture_<?php echo htmlspecialchars($employee["employee_id"]) ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Employee Picture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img src="<?php echo htmlspecialchars($employee["image"]) ?>" alt="Employee Image" class="img-fluid" style="width: 450px; height: 450px;">
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

</body>
</html>