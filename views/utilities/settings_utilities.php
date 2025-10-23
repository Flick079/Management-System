<?php
require_once __DIR__ . '/../../middleware/verify.php';
require_once __DIR__ . '/../../controllers/utilitiesController.php';
require_once __DIR__ . '/../../views/errors/utilities_errors.php';
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
    <title>Setiings Utilities</title>
</head>
<body>
    <?php require_once '../layouts/sidebar.php' ?>
    <div class="content">
        <header>
            <h4>Settings</h4>
        </header>
        <?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($_SESSION['success_message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($_SESSION['error_message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

        <main>
            <div class="container-fluid role border">
                <div class="role-headers d-flex justify-content-between my-3">
                    <h4>Roles</h4>
                </div>
                <div class="btns d-flex justify-content-end mb-3">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add_role_modal">
                            <i class="bi bi-plus"></i>
                            Add Role
                        </button>
                </div>
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($roles)): ?>
                            <?php foreach($roles as $role): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($role["role"]) ?></td>
                                    <td>
                                        <div class="btns d-flex gap-3">
                                            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#edit_role_modal_<?php echo htmlspecialchars($role["role_id"]) ?>">
                                                <i class="bi bi-pen"></i>
                                            </button>
                                            <form action="../../controllers/utilitiesController.php" method="POST">
                                                <input type="hidden" name="role" value="<?php echo htmlspecialchars($role["role"]) ?>">
                                                <input type="hidden" name="role_id" value="<?php echo htmlspecialchars($role["role_id"]) ?>">
                                                <button class="btn btn-danger" name="delete_role_btn">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                <?php endforeach; ?>
                                </tr>
                        <?php else: ?>
                                <tr>
                                    <td class="text-center">No roles found!</td>
                                </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!--permissions container  -->
            <div class="container-fluid access border">
                <div class="role-headers d-flex justify-content-between my-3">
                    <h4>Permission</h4>
                </div>
                <div class="btns d-flex justify-content-end mb-3">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add_permission_modal">
                            <i class="bi bi-plus"></i>
                            Add permissions
                        </button>
                </div>
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Permissions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($permissions)): ?>
                            <?php foreach($permissions as $permission): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($permission["permission_name"]) ?></td>
                                    <td>
                                    <div class="btns d-flex gap-3">
                                            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#edit_permission_modal_<?php echo htmlspecialchars($permission["permission_id"]) ?>">
                                                <i class="bi bi-pen"></i>
                                            </button>
                                            <form action="../../controllers/utilitiesController.php" method="POST">
                                                <input type="hidden" name="permission_name" value="<?php echo htmlspecialchars($permission["permission_name"]) ?>">
                                                <input type="hidden" name="permission_id" value="<?php echo htmlspecialchars($permission["permission_id"]) ?>">
                                                <button class="btn btn-danger" name="delete_permission_btn">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                    </div>
                                    </td>
                                <?php endforeach; ?>
                                </tr>
                        <?php else: ?>
                                <tr>
                                    <td class="text-center">No permission found!</td>
                                </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- end permissions container -->
        </main>


        
            <!-- add role modal -->
            <div class="modal fade" id="add_role_modal" aria-labelledby="add_role_modal" aria-hidden="true" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4>Add role</h4>
                            <button class="btn btn-close" data-bs-dismiss="modal" aria-lable="close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="../../controllers/utilitiesController.php" method="POST">
                                <?php
                                    settingErrors();
                                ?>
                                <div class="mb-3">
                                    <label for="" class="form-label">Role</label>
                                    <input type="text" name="role" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <button class="btn btn-primary" name="add_role_btn">Add role</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- end add role modal -->


            <!--edit role modal  -->
        <?php foreach($roles as $role): ?>
            <div class="modal fade" id="edit_role_modal_<?php echo htmlspecialchars($role["role_id"]) ?>" 
                aria-labelledby="edit_role_modal_<?php echo htmlspecialchars($role["role_id"]) ?>" aria-hidden="true" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4>Add role</h4>
                            <button class="btn btn-close" data-bs-dismiss="modal" aria-lable="close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="../../controllers/utilitiesController.php" method="POST">
                                <?php
                                    settingErrors();
                                ?>
                                <div class="mb-3">
                                    <label for="" class="form-label">Role</label>
                                    <input type="text" name="role" value="<?php echo htmlspecialchars($role["role"]) ?>" class="form-control" disabled>
                                </div>
                                <div class="mb-3">
                                    <button type="button" class="btn btn-success edit-btn" name="add_role_btn">Edit</button>
                                </div>
                                <div class="mb-3">
                                    <input type="hidden" name="role_id" value="<?php echo htmlspecialchars($role["role_id"]) ?>" class="form-control">
                                    <button class="btn btn-primary" name="edit_role_btn">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
            <!-- end edit role modal -->

        <!-- add permissions modal -->
        
        <div class="modal fade" id="add_permission_modal" aria-labelledby="add_permission_modal" aria-hidden="true" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4>Add permission</h4>
                            <button class="btn btn-close" data-bs-dismiss="modal" aria-lable="close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="../../controllers/utilitiesController.php" method="POST">
                                <?php
                                    settingErrors();
                                ?>
                                <div class="mb-3">
                                    <label for="" class="form-label">Permission</label>
                                    <input type="text" name="permission_name" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <button class="btn btn-primary" name="add_permission_btn">Add permission</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        
        <!-- end add permissions modal -->

        <!--edit permission modal  -->
        <?php foreach($permissions as $permission): ?>
            <div class="modal fade" id="edit_permission_modal_<?php echo htmlspecialchars($permission["permission_id"]) ?>" 
                aria-labelledby="edit_permission_modal_<?php echo htmlspecialchars($permission["permission_id"]) ?>" aria-hidden="true" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4>Add permission</h4>
                            <button class="btn btn-close" data-bs-dismiss="modal" aria-lable="close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="../../controllers/utilitiesController.php" method="POST">
                                <?php
                                    settingErrors();
                                ?>
                                <div class="mb-3">
                                    <label for="" class="form-label">Permission</label>
                                    <input type="text" name="permission_name" value="<?php echo htmlspecialchars($permission["permission_name"]) ?>" class="form-control" disabled>
                                </div>
                                <div class="mb-3">
                                    <button type="button" class="btn btn-success edit-btn" name="edit_permission_btn">Edit</button>
                                </div>
                                <div class="mb-3">
                                    <input type="hidden" name="permission_id" value="<?php echo htmlspecialchars($permission["permission_id"]) ?>" class="form-control">
                                    <button class="btn btn-primary" name="edit_permission_btn">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
            <!-- end edit role modal -->
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