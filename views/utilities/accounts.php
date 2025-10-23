<?php
require_once __DIR__ . '/../../middleware/verify.php';
require_once __DIR__ . '/../../controllers/utilitiesController.php';
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
    <title>Accounts</title>
    <style>
        /* Custom styles for permission checkboxes */
        .permission-item {
            padding: 8px 12px;
            border-radius: 6px;
            margin-bottom: 8px;
            background-color: #f8f9fa;
            transition: all 0.2s ease;
        }
        
        .permission-item:hover {
            background-color: #e9ecef;
        }
        
        .permission-item input[type="checkbox"] {
            margin-right: 10px;
        }
        
        .permission-container {
            max-height: 250px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 10px;
        }
        
        .permission-title {
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        /* Custom checkbox styling */
        .custom-checkbox {
            display: flex;
            align-items: center;
        }
        
        .custom-checkbox input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .custom-checkbox label {
            margin-bottom: 0;
            margin-left: 8px;
            cursor: pointer;
            flex-grow: 1;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <?php require_once '../layouts/sidebar.php' ?>
    <div class="content">
        <div class="container">
            <header class="d-flex justify-content-between">
                <h4>Accounts</h4>
                <div class="btns">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add_account_modal">
                        <i class="bi bi-plus"></i>
                        Add account
                    </button>
                </div>
            </header>
            <main class="mt-3">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Username</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($accounts as $account): ?>
                            <tr>
               
                                <td><?php echo htmlspecialchars($account["username"]) ?></td>
                                <td>
                                    <div class="btns">
                                        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#edit_account_modal<?php echo htmlspecialchars($account["user_id"]) ?>">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </main>
        </div>
    </div>
    
    <!-- add account modal -->
    <div class="modal fade" id="add_account_modal" aria-labelledby="add_account_modal" aria-hidden="true" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Add account</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                </div>
                <div class="modal-body">
                    <form action="../../controllers/accountsController.php" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" name="username" id="username" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" id="password" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control">
                        </div>
                        <div class="mb-3">
                            <div class="permission-title">Permissions</div>
                            <div class="permission-container">
                                <?php foreach($permissions as $permission): ?>
                                <div class="permission-item">
                                    <div class="custom-checkbox">
                                        <input type="checkbox" 
                                               name="permissions[]" 
                                               id="permission_<?php echo htmlspecialchars($permission["permission_id"]) ?>" 
                                               value="<?php echo htmlspecialchars($permission["permission_id"]) ?>">
                                        <label for="permission_<?php echo htmlspecialchars($permission["permission_id"]) ?>">
                                            <?php echo htmlspecialchars($permission["permission_name"]) ?>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="mb-3">
                            <button class="btn btn-primary" name="add_user_btn">Add Account</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- edit account modal -->
<?php foreach($accounts as $account): ?>
    <div class="modal fade" id="edit_account_modal<?php echo htmlspecialchars($account["user_id"]) ?>" aria-labelledby="edit_account_modal<?php echo htmlspecialchars($account["user_id"]) ?>" aria-hidden="true" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Edit account</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                </div>
                <div class="modal-body">
                    <form action="../../controllers/accountsController.php" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" name="username" value="<?php echo htmlspecialchars($account["username"]) ?>" class="form-control" disabled>
                        </div>
<div class="mb-3">
    <div class="permission-title">Permissions</div>
    <div class="permission-container">
        <?php foreach ($permissions as $permission): ?>
            <?php 
                $perm_id = htmlspecialchars($permission["permission_id"]);
                $perm_name = htmlspecialchars($permission["permission_name"]);
                $is_checked = in_array($permission["permission_id"], $user_permissions ?? []); 
            ?>
            <div class="permission-item">
                <div class="custom-checkbox">
                    <input type="checkbox" 
                           name="permissions[]" 
                           id="permission_<?php echo $perm_id ?>" 
                           value="<?php echo $perm_id ?>" 
                           <?php echo $is_checked ? 'checked' : '' ?> 
                           disabled>
                    <label for="permission_<?php echo $perm_id ?>">
                        <?php echo $perm_name ?>
                    </label>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

                        <div class="mb-3">
                            <button class="btn btn-primary" name="edit_user_btn">Edit Account</button>
                            <button class="btn btn-primary" name="edit_user_btn">Update Account</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
    <script>
        // You can add JavaScript functionality here if needed
    </script>
</body>
</html>