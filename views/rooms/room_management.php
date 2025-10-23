<?php
require_once __DIR__ . '/../../middleware/verify.php';
require_once __DIR__ . '/../../controllers/roomManagementController.php';
require_once __DIR__ . '/../errors/room_errors.php';
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
    <title>Room Management</title>
</head>
<body>
    <?php require_once '../layouts/sidebar.php' ?>
    <div class="content">
        <header class="d-flex justify-content-between">
            <h3>Room Management</h3>
            <div class="btns">
                <!-- <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add_room_modal">
                    <i class="bi bi-plus"></i>
                    Add a room
                </button> -->
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#add_room_type_modal">
                    <i class="bi bi-plus"></i>
                    Add room type
                </button>
            </div>
        </header>
        <main>


            <!-- add room type modal -->

            <div class="modal fade" id="add_room_type_modal" aria-labelledby="add_room_type_modal" aria-hidden="true" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>New room type</h3>
                            <button class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="../../controllers/roomManagementController.php" method="POST">
                                <?php
                                    roomErrors();
                                ?>
                                <div class="mb-3">
                                    <label for="" class="form-label">Room Type</label>
                                    <input type="text" name="room_type" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="" class="form-label">Rate</label>
                                    <input type="number" name="rate" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="" class="form-label">Additional fee per person</label>
                                    <input type="number" name="add_fee" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="" class="form-label">Maximum person(s)</label>
                                    <input type="number" name="max_per" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="" class="form-label">Caption</label>
                                    <textarea class="form-control" name="caption" id="caption" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <button class="btn btn-success" name="add_room_type_btn">Add</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end  -->
        </main>
        <?php foreach($room_types as $room_type): ?>
            <div class="room-types border m-3 p-4">
                <div class="container-fluid">
                    <div class="header-container">
                        <div class="mb-1 d-flex justify-content-between align-items-center">
                            <h4><?php echo htmlspecialchars($room_type["name"]) ?></h4>
                            <div class="btns d-flex gap-1">
                                <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#edit_room_type_modal_<?php echo htmlspecialchars($room_type["room_type_id"]) ?>">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add_room_modal<?php echo htmlspecialchars($room_type["room_type_id"]) ?>">
                                    <i class="bi bi-plus"></i>
                                </button>
                                <form action="../../controllers/roomManagementController.php" method="POST">
                                    <input type="hidden" name="room_type_name" value="<?php echo htmlspecialchars($room_type["name"]) ?>">
                                    <input type="hidden" name="room_type_id" value="<?php echo htmlspecialchars($room_type["room_type_id"]) ?>">
                                    <button class="btn btn-danger" name="delete_room_type_btn">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <hr>
                        <div class="mb-1 d-flex">
                            <label for="" class="form-label px-2">Rate: </label>
                            <h5>₱<?php echo htmlspecialchars($room_type["rate"]) ?></h5>
                        </div>
                        <div class="mb-1 d-flex">
                            <label for="" class="form-label px-2">Max person: </label>
                            <h5><?php echo htmlspecialchars($room_type["max_person"]) ?></h5>
                        </div>
                        <div class="mb-1 d-flex">
                            <label for="" class="form-label px-2">Additional person: </label>
                            <h5>₱<?php echo htmlspecialchars($room_type["additional_fee"]) ?></h5>
                        </div>
                    </div>
                    <div class="body-container">
                        <div class="cards d-flex flex-wrap">
    
                            <?php foreach($rooms as $room): ?>
                                <?php if($room["room_type_id"] === $room_type["room_type_id"]): ?>
                                    <div class="card m-3">
                                        <h4 class="text-center"><?php echo htmlspecialchars($room["room_number"]) ?></h4>
                                        <img src="<?php echo htmlspecialchars($room["image"]) ?>" class="img-thumbnail" style="width: 250px;" alt="">
                                        <div class="card-body">
                                            <div class="btns">
                                                <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#edit_room_modal_<?php echo htmlspecialchars($room["room_id"]) ?>">
                                                    <i class="bi bi-pen"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach;?>

                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
            <!-- add room modal -->
        <?php foreach($room_types as $room_type): ?>
            <div class="modal fade" id="add_room_modal<?php echo htmlspecialchars($room_type["room_type_id"]) ?>" 
                aria-labelledby="add_room_modal<?php echo htmlspecialchars($room_type["room_type_id"]) ?>" 
                aria-hidden="true" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4>Add <?php echo htmlspecialchars($room_type["name"]) ?></h4>
                            <button class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="../../controllers/roomManagementController.php" method="POST" enctype="multipart/form-data">
                            <?php roomErrors(); ?>
                                <div class="mb-3">
                                    <label for="" class="form-label">Room Number</label>
                                    <input type="text" name="room_number" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="" class="form-label">Image</label>
                                    <input type="file" name="room_image" class="form-control">
                                </div>
                                <div class="mb-3 d-flex align-items-center">
                                    <label for="" class="form-label mx-2">Set as active</label>
                                    <input type="checkbox" name="room_status" class="">
                                </div>
                                <div class="mb-3">
                                    <input type="hidden" name="room_type_id" value="<?php echo htmlspecialchars($room_type["room_type_id"]) ?>">
                                    <button class="btn btn-success" name="add_room_btn">Add</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach ?>
            <!-- end add room modal -->
            
            <!-- edit room type modal -->
            <?php foreach($room_types as $room_type): ?>
                <div class="modal fade" id="edit_room_type_modal_<?php echo htmlspecialchars($room_type["room_type_id"]) ?>"
                    aria-labelledby="edit_room_type_modal_<?php echo htmlspecialchars($room_type["room_type_id"]) ?>" aria-hidden="true"
                    tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4>Edit <?php echo htmlspecialchars($room_type["name"]) ?></h4>
                                <button class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                            </div>
                            <div class="modal-body">
                                <form action="../../controllers/roomManagementController.php" method="POST" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label for="" class="form-label">Room Type</label>
                                        <input type="text" name="room_type" class="form-control"
                                        value="<?php echo htmlspecialchars($room_type["name"]) ?>" disabled>
                                    </div>
                                    <div class="mb-3">
                                        <label for="" class="form-label">Rate</label>
                                        <input type="number" name="rate" class="form-control"
                                        value="<?php echo htmlspecialchars($room_type["rate"]) ?>" disabled>
                                    </div>
                                    <div class="mb-3">
                                        <label for="" class="form-label">Additional Fee Per Person</label>
                                        <input type="number" name="add_fee" class="form-control"
                                        value="<?php echo htmlspecialchars($room_type["additional_fee"]) ?>" disabled>
                                    </div>
                                    <div class="mb-3">
                                        <label for="" class="form-label">Maximum person(s)</label>
                                        <input type="number" name="max_person" class="form-control"
                                        value="<?php echo htmlspecialchars($room_type["max_person"]) ?>" disabled>
                                    </div>
                                    <div class="mb-3">
                                        <label for="" class="form-label">Caption</label>
                                        <input type="text" name="caption" class="form-control"
                                        value="<?php echo htmlspecialchars($room_type["caption"]) ?>" disabled>
                                    </div>
                                    <div class="mb-3">
                                        <button type="button" class="btn btn-primary edit-btn">Edit</button>
                                    </div>
                                    <div class="mb-3">
                                        <input type="hidden" name="room_type_id" value="<?php echo htmlspecialchars($room_type["room_type_id"]) ?>">
                                        <button type="submit" id="update_btn" name="update_btn" class="btn btn-success">Submit</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>


            <!-- end edit room type modal -->

            <!-- edit room modal  -->
        <?php foreach($rooms as $room): ?>
            <div class="modal fade" id="edit_room_modal_<?php echo htmlspecialchars($room["room_id"]) ?>"
                aria-labelledby="edit_room_modal_<?php echo htmlspecialchars($room["room_id"]) ?>" aria-hidden="true" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4>Edit Room Number <?php echo htmlspecialchars($room["room_id"]) ?></h4>
                            <button class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="../../controllers/roomManagementController.php" method="POST" enctype="multipart/form-data">
                            <?php roomErrors(); ?>
                                <div class="mb-3">
                                    <label for="" class="form-label">Room Number</label>
                                    <input type="text" name="room_number" class="form-control" value="<?php echo htmlspecialchars($room["room_number"]) ?>" disabled>
                                </div>
                                <div class="mb-3">
                                    <label for="" class="form-label">Image</label>
                                    <img src="<?php echo htmlspecialchars($room["image"]) ?>" style="width: 250px;" alt="">
                                    <input type="file" name="room_image" class="form-control" disabled>
                                </div>
                                <div class="mb-3 d-flex align-items-center">
    <label for="" class="form-label mx-2">Set as active</label>
    <input type="checkbox" name="room_status" class="" value="1" 
        <?php echo ($room["room_status"] == 1) ? 'checked' : ''; ?> disabled>
</div>

                                <div class="mb-3">
                                    <button type="button" class="btn btn-warning edit-btn">Edit</button>
                                </div>
                                <div class="mb-3">
                                    <input type="hidden" name="room_type_id" value="<?php echo htmlspecialchars($room["room_type_id"]) ?>">
                                    <input type="hidden" name="room_id" value="<?php echo htmlspecialchars($room["room_id"]) ?>">
                                    <button class="btn btn-success" name="update_room_btn">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
            <!-- end edit room modal -->
    <script>
        document.addEventListener("DOMContentLoaded", function (){
            <?php if(isset($_SESSION["keep_modal_open"]) && $_SESSION["keep_modal_open"]): ?>
                var addRoomTypeModal = new bootstrap.Modal(document.getElementById("add_room_type_modal") || document.getElementById("add_room_modal<?php echo htmlspecialchars($room_type["room_type_id"]) ?>"));
                addRoomTypeModal.show();
            <?php unset($_SESSION["keep_modal_open"]) ?>
            <?php endif; ?>
        })

        document.addEventListener("DOMContentLoaded", function () {
            <?php if(isset($_SESSION["keep_modal_open2"]) && $_SESSION["keep_modal_open2"]): ?>
                var addRoomModal = new bootstrap.Modal(document.getElementById("add_modal<?php echo htmlspecialchars($room_type["room_type_id"]) ?>"))
                addRoomModal.show();
            <?php unset($_SESSION["keep_modal_open2"]) ?>
            <?php endif;?>
        })


        //for edit button in rooms main page

document.querySelectorAll(".edit-btn").forEach(button => {
    button.addEventListener("click", function (event) {
        event.preventDefault(); // Prevent form submission

        let modal = this.closest(".modal"); // Get the closest modal
        let inputs = modal.querySelectorAll("input:not([type='hidden'])"); //responsible for one click on edit button

        // Determine if all fields are currently disabled
        let allDisabled = Array.from(inputs).every(input => input.hasAttribute("disabled"));

        // Toggle the disabled attribute for all inputs and selects
        inputs.forEach(input => {
            if (allDisabled) {
                input.removeAttribute("disabled"); // Enable inputs
            } else {
                input.setAttribute("disabled", "true"); // Disable inputs
            }
        });

        // Ensure all options inside select elements are enabled
        // let selects = modal.querySelectorAll("select");
        // selects.forEach(select => {
        //     let options = select.querySelectorAll("option");
        //     options.forEach(option => {
        //         option.removeAttribute("disabled"); // Enable all options
        //     });
        // });

        // Change button text based on the toggle state
        this.textContent = allDisabled ? "Cancel Edit" : "Edit";
    });
});
    </script>
</body>
</html>