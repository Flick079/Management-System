<?php
require_once __DIR__ . '/../../middleware/user_exists.php';
require_once __DIR__ . '/../../middleware/verify.php';
require_once __DIR__ . '/../../controllers/categoryController.php';
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
    <title>Products Category</title>
</head>
<body>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <div class="content">
        <div class="content-header d-flex justify-content-between">
            <h4>Products Category</h4>
            <div class="btns">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add_category_modal">
                    <i class="bi bi-plus">
                    </i>
                    Add category
                </button>
            </div>
        </div>
        <div class="content-body">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Category Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($categories as $category): ?>
                    <tr>
 
                            <td><?php echo htmlspecialchars($category["category_name"]) ?></td>
                            <td class="d-flex gap-2">
                                <div class="edit-btn">
                                        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#edit_category_modal_<?php echo htmlspecialchars($category["category_id"]) ?>">
                                            <i class="bi bi-pen"></i>
                                        </button>
                                </div>
                                <div class="delete-btn">
                                    <form action="../../controllers/categoryController.php" method="POST">
                                        <input type="hidden" name="category_id" value="<?php echo htmlspecialchars($category["category_id"]) ?>">
                                        <button class="btn btn-danger" name="delete_category_btn">
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
    <!-- add category modal -->
    <div class="modal fade" id="add_category_modal" aria-labelledby="add_category_modal" aria-hidden="true" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Add new Category</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                </div>
                <div class="modal-body">
                    <form action="../../controllers/categoryController.php" method="POST">
                        <div class="mb-3">
                            <label for="" class="form-label">Catogory</label>
                            <input type="text" name="category" class="form-control">
                        </div>
                        <div class="mb-3">
                            <button class="btn btn-primary" name="add_category_btn">Add</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- end add category modal -->
    <?php foreach($categories as $category): ?>
        <!-- edit category modal -->
        <div class="modal fade" id="edit_category_modal_<?php echo htmlspecialchars($category["category_id"]) ?>" aria-labelledby="edit_category_modal_<?php echo htmlspecialchars($category["category_id"]) ?>" aria-hidden="true" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Edit Category</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                </div>
                <div class="modal-body">
                    <form action="../../controllers/categoryController.php" method="POST">
                        <div class="mb-3">
                            <label for="" class="form-label">Catogory</label>
                            <input type="text" name="category" class="form-control" value="<?php echo htmlspecialchars($category["category_name"]) ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <button type="button" class="btn btn-primary edit-btn" id="edit-btn">Edit</button>
                        </div>
                        <div class="mb-3">
                            <input type="hidden" name="category_id" value="<?php echo htmlspecialchars($category["category_id"]) ?>">
                            <button class="btn btn-success" name="edit_category_btn">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
    <!-- end edit category modal -->
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