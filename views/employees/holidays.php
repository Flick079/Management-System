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
    <script defer src="../../public/js/holidays.js"></script>
    <title>Holidays</title>
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <div class="content">
        <div class="content-header">
            <h4>Holidays</h4>
        </div>
        <div class="content-body">
            <div class="container">
                <?php if (isset($_SESSION['errors'])): ?>
                    <div class="alert alert-danger">
                        <?php 
                            foreach ($_SESSION['errors'] as $error) {
                                echo htmlspecialchars($error) . "<br>";
                            }
                            unset($_SESSION['errors']);
                        ?>
                    </div>
                <?php endif; ?>
                
                <div class="btn-settings d-flex justify-content-end mb-3">
                    <button class="btn btn-primary" data-bs-target="#add_holiday_modal" data-bs-toggle="modal">
                        <i class="bi bi-plus"></i>
                        Add holiday
                    </button>
                </div>
                
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Holiday</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($holidays as $holiday): 
                            $currentYear = date('Y');
                            $displayDate = date('F j', strtotime("$currentYear-{$holiday['month']}-{$holiday['day']}"));
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($holiday["holiday_name"]); ?></td>
                                <td><?php echo htmlspecialchars($displayDate); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $holiday['holiday_type'] === 'regular' ? 'primary' : 'warning'; ?>">
                                        <?php echo ucfirst(htmlspecialchars($holiday["holiday_type"])); ?>
                                    </span>
                                </td>
                                <td class="d-flex gap-2">
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" 
                                            data-bs-target="#edit_holiday_modal_<?php echo htmlspecialchars($holiday["id"]) ?>">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    
                                    <form action="../../controllers/employeeController.php" method="POST" 
                                          onsubmit="return confirm('Are you sure you want to delete this holiday?');">
                                        <input type="hidden" name="holiday_id" value="<?php echo $holiday["id"] ?>">
                                        <button class="btn btn-danger btn-sm" name="delete_holiday_btn">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Add Holiday Modal -->
            <div class="modal fade" id="add_holiday_modal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add New Holiday</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="../../controllers/employeeController.php" method="POST" id="addHolidayForm">
                                <div class="mb-3">
                                    <label class="form-label">Holiday Name</label>
                                    <input type="text" name="holiday_name" class="form-control" required>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Month</label>
                                        <select name="month" class="form-control" required>
                                            <?php for($i=1; $i<=12; $i++): ?>
                                                <option value="<?= $i ?>" <?= $i == date('n') ? 'selected' : '' ?>>
                                                    <?= date('F', mktime(0, 0, 0, $i, 1)) ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Day</label>
                                        <select name="day" class="form-control" required>
                                            <?php for($i=1; $i<=31; $i++): ?>
                                                <option value="<?= $i ?>" <?= $i == date('j') ? 'selected' : '' ?>>
                                                    <?= $i ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Holiday Type</label>
                                    <select name="type" class="form-control" required>
                                        <option value="regular">Regular Holiday</option>
                                        <option value="special">Special Non-Working Holiday</option>
                                    </select>
                                </div>
                                
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary" name="add_holiday_btn">Save Holiday</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Holiday Modals -->
            <?php foreach($holidays as $holiday): ?>
                <div class="modal fade" id="edit_holiday_modal_<?php echo htmlspecialchars($holiday["id"]) ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Holiday</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form action="../../controllers/employeeController.php" method="POST">
                                    <input type="hidden" name="holiday_id" value="<?php echo htmlspecialchars($holiday["id"]) ?>">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Holiday Name</label>
                                        <input type="text" name="holiday_name" class="form-control" 
                                               value="<?php echo htmlspecialchars($holiday["holiday_name"]) ?>" required>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Month</label>
                                            <select name="month" class="form-control" required>
                                                <?php for($i=1; $i<=12; $i++): ?>
                                                    <option value="<?= $i ?>" <?= $i == $holiday['month'] ? 'selected' : '' ?>>
                                                        <?= date('F', mktime(0, 0, 0, $i, 1)) ?>
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Day</label>
                                            <select name="day" class="form-control" required>
                                                <?php for($i=1; $i<=31; $i++): ?>
                                                    <option value="<?= $i ?>" <?= $i == $holiday['day'] ? 'selected' : '' ?>>
                                                        <?= $i ?>
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Holiday Type</label>
                                        <select name="type" class="form-control" required>
                                            <option value="regular" <?= $holiday['holiday_type'] == 'regular' ? 'selected' : '' ?>>Regular Holiday</option>
                                            <option value="special" <?= $holiday['holiday_type'] == 'special' ? 'selected' : '' ?>>Special Non-Working Holiday</option>
                                        </select>
                                    </div>
                                    
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary" name="edit_holiday_btn">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>