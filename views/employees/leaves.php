<?php
require_once __DIR__ . '/../../middleware/verify.php';
require_once __DIR__ . '/../../controllers/leaveController.php';
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
    <title>Employee Leaves</title>
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/sidebar.php' ?>
    <div class="content">
        <div class="content-header d-flex justify-content-between">
            <h3>Leaves</h3>
            <div class="btns">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add_leave_modal">
                    <i class="bi bi-plus"></i>
                    Request leave
                </button>
            </div>
        </div>
        <div class="content-body">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Employee Name</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($leaves as $leave): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($leave["full_name"]) ?></td>
                            <td><?php echo date('F j, y', strtotime($leave["start_date"])) ?></td>
                            <td><?php echo date('F j, y', strtotime($leave["end_date"])) ?></td>
                            <td><?php echo htmlspecialchars($leave["reason"]) ?></td>
                            <td>
                            <span class="badge <?php echo htmlspecialchars($leave["status"]) === "Approved" ? 'bg-success' : 'bg-danger'; ?>">
                            <?php echo htmlspecialchars($leave["status"]); ?>
                            </span>
                            </td>
                            
                            <td class="d-flex gap-2">
                                <div class="approve">
                                    <form action="../../controllers/leaveController.php" method="POST">
                                        <input type="hidden" name="full_name" value="<?php echo htmlspecialchars($leave["full_name"]) ?>">
                                        <input type="hidden" name="leave_id" value="<?php echo htmlspecialchars($leave["leave_id"]) ?>">
                                        <button class="btn btn-success" name="approve_btn">
                                            <i class="bi bi-hand-thumbs-up"></i>
                                            Approve
                                        </button>
                                    </form>
                                </div>
                                <div class="reject">
                                    <form action="../../controllers/leaveController.php" method="POST">
                                        <input type="hidden" name="full_name" value="<?php echo htmlspecialchars($leave["full_name"]) ?>">
                                        <input type="hidden" name="leave_id" value="<?php echo htmlspecialchars($leave["leave_id"]) ?>">
                                        <button class="btn btn-danger" name="reject_btn">
                                            <i class="bi bi-hand-thumbs-down"></i>
                                            Reject
                                        </button>
                                  </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- add leave modal -->
            <div class="modal fade" id="add_leave_modal" aria-labelledby="add_leave_modal" aria-hidden="true" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4>Add leave</h4>
                            <button class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="../../controllers/leaveController.php" method="POST">
                                <div class="mb-3">
                                    <label for="" class="form-label">Employee Name</label>
                                    <select name="employee_id" id="" class="form-control">
                                        <?php foreach($employees as $employee): ?>
                                            <option value="<?php echo htmlspecialchars($employee["employee_id"]) ?>">
                                                <?php echo htmlspecialchars($employee["full_name"]) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="" class="form-label">Leave Type</label>
                                    <select name="leave_type" id="" class="form-control">
                                        <option value="Sick Leave">Sick Leave</option>
                                        <option value="Vacation Leave">Vacation Leave</option>
                                        <option value="Emergency Leave">Emergency Leave</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="" class="form-label">Start Date</label>
                                    <input type="date" name="start_date" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="" class="form-label">End Date</label>
                                    <input type="date" name="end_date" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="" class="form-label">Reason</label>
                                    <textarea name="reason" id="" class="form-control"></textarea>
                                </div>
                                <div class="mb-3">
                                    <input type="hidden" name="full_name" value="<?php echo htmlspecialchars($employee["full_name"]) ?>">
                                    <button class="btn btn-primary" name="add_leave_btn">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <!-- end add leave modal -->
    </div>
</body>
</html>