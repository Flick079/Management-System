<?php
require_once __DIR__ . '/../models/EmployeeModel.php';
//for managing employees

$employees = getEmployee($pdo);

//for adding employees
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_employee_btn"])) {
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $dob = $_POST["dob"];
    $email = $_POST["email"];
    $address = $_POST["address"];
    $contact_number = $_POST["contact_number"];
    $position = $_POST["position"];
    $deductions = isset($_POST['deduction']) ? $_POST['deduction'] : [];  // Prevent undefined array key error
    $image_url = 'uploads/default.jpeg';

    $employee_id = uniqid();

//image upload

        $target_dir = __DIR__ . '../../public/uploads/employees/';
        $image_name = basename($_FILES["image"]["name"]); // Get only the file name
        $image_path = $target_dir . $image_name; // Full path for moving the file
        $image_url = '../../public/uploads/employees/' . $image_name; // Relative path to store in DB

        move_uploaded_file($_FILES["image"]["tmp_name"], $image_path);
    

    // Generate QR code
    require_once __DIR__ . '/../public/qr/phpqrcode/qrlib.php';
    $qr_text = $employee_id;
    $qr_filename = $employee_id . '.png'; // Only the filename
    $qr_full_path = __DIR__ . '/../public/qr/qrcodes/' . $qr_filename; // Full path for QR generation
    $qr_relative_path = '../../public/qr/qrcodes/' . $qr_filename; // Relative path to store in DB
    
    QRcode::png($qr_text, $qr_full_path, QR_ECLEVEL_L, 4);
  
    
    try {
        if (empty($first_name) || empty($last_name) || empty($email) 
            || empty($address) || empty($contact_number) || empty($position) || empty($image_url)) {
            $_SESSION["error"] = "Please fill in all the fields!";
            echo "error";
        } else {

                // Pass only the file path or file name to the database
                insertEmployee($pdo, $first_name, $last_name, $dob, $email, $address, $contact_number, $position, $deductions,$image_url, $qr_relative_path, $employee_id);
                header("location: ../views/employees/employee.php?success");
                exit();
        }
    } catch (PDOException $e) {
        die("Querasdasdadsy failed: " . $e->getMessage());
    }
}

//for deleting an employee
if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["employee_delete_btn"])){
    $employee_id = $_POST["employee_id"];
    $full_name = $_POST["full_name"];
    $user_id = $_SESSION["user_id"];

    try {
        

        deleteEmployee($pdo, $employee_id, $full_name, $user_id);
        header("location: ../views/employees/employee.php");
        exit();
    } catch (PDOException $e) {
        die("Query failed: " . $e->getMessage());
    }
}

//for updating an employees information

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_employee_btn"])) {
    $employee_id = $_POST["employee_id"];
    $full_name = $_POST["full_name"];
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $dob = $_POST["dob"];
    $email = $_POST["email"];
    $address = $_POST["address"];
    $contact_number = $_POST["contact_number"];
    $shift = $_POST["shift"];
    $position = $_POST["position"];
    $deductions = $_POST["deductions"] ?? [];

    // Default image URL (only update if a new file is uploaded)
    $image_url = null;

    // Check if a new image file was uploaded
//image upload
if (!empty($_FILES['image']['name'])) {
    $target_dir = __DIR__ . '../../public/uploads/employees/';
    $image_name = basename($_FILES["image"]["name"]); // Get only the file name
    $image_path = $target_dir . $image_name; // Full path for moving the file
    $image_url = '../../public/uploads/employees/' . $image_name; // Relative path to store in DB

    move_uploaded_file($_FILES["image"]["tmp_name"], $image_path);
}

    // Check if a new QR code is uploaded/generated
    $qr_code = !empty($_POST["qr_code"]) ? $_POST["qr_code"] : null;

    updateEmployee($pdo, $employee_id, $full_name,$first_name, $last_name, $dob,$email, $address, $contact_number, $shift, $position, $deductions, $image_url, $qr_code);

    header("Location: ../views/employees/employee.php");
    exit();
}
//for employee's status: in, out or no records

function checkEmployeeStatus($pdo, $employee_id) {
    $status = getEmployeeStatus($pdo, $employee_id);
    
    if (!$status) {
        return "<span class='badge text-bg-secondary'>No record today</span>";
    }

    if ($status['time_out'] === null) {
        // Employee is currently clocked in
        $timeIn = date("h:i A", strtotime($status['time_in']));
        return "<span class='badge text-bg-success'>Clocked in at $timeIn</span>";
    } else {
        // Employee has clocked out
        $timeOut = date("h:i A", strtotime($status['time_out']));
        return "<span class='badge text-bg-warning'>Clocked out at $timeOut</span>";
    }
}

//for employee id logic


//for employee setiings

//positions
$positions = getPositions($pdo);

//for adding positions
if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_position_btn"])){
    $position = $_POST["position"];
    $salary = $_POST["salary"];

    try {
        if(empty($position)){
            $_SESSION["error"] = "Please fill in all the fields!";
        } else {
            insertPosition($pdo, $position, $salary);
            header("location: ../views/employees/employee_positions.php");
            exit();
        }
    } catch (PDOException $e) {
        die("Query failed: ". $e->getMessage());
    }
}

//for editing/updating position

if($_SERVER["REQUEST_METHOD"] && isset($_POST["edit_position_btn"])){
    $position_id = $_POST["position_id"];
    $position = $_POST["position"];
    $salary = $_POST["salary"];
    try {
        updatePosition($pdo, $position_id, $position, $salary);
        header("location: ../views/employees/employee_positions.php");
        exit();

    } catch (PDOException $e) {
        die("Query failed: " . $e->getMessage());
    }
}

//for deleting position
if($_SERVER["REQUEST_METHOD"] && isset($_POST["delete_position_btn"])){
    $position_id = $_POST["position_id"];
    $position = $_POST["position"];

    try {
        
        deletePosition($pdo, $position,$position_id);
        header("location: ../views/employees/employee_positions.php");
        exit();
    } catch (PDOException $e) {
        die("Query failed: " . $e->getMessage());
    }
}

//deductions

$deductions = getDeductions($pdo);

if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_deduction_btn"])){
    $deduction = $_POST["deduction"];
    $amount = $_POST["amount"];

    try {
        if(empty($deduction) || empty($amount)){
            $_SESSION["error"] = "Please fill in all the fields!";
        } else {
            insertDeduction($pdo, $deduction, $amount);
            header("location: ../views/employees/employee_deductions.php");
            exit();
        }
    } catch (PDOException $e) {
        die("Query failed: ". $e->getMessage());
    }
}

if($_SERVER["REQUEST_METHOD"] && isset($_POST["delete_deduction_btn"])){
    $deduction_id = $_POST["deduction_id"];
    $deduction = $_POST["deduction"];

    try {
        
        deleteDeduction($pdo, $deduction,$deduction_id);
        header("location: ../views/employees/employee_deductions.php");
        exit();
    } catch (PDOException $e) {
        die("Query failed: " . $e->getMessage());
    }
}

if($_SERVER["REQUEST_METHOD"] && isset($_POST["edit_deduction_btn"])){
    $deduction_id = $_POST["deduction_id"];
    $deduction = $_POST["deduction"];
    $percentage = $_POST["percentage"];

    try {
        
        editDeduction($pdo, $deduction_id,$deduction, $percentage);
        header("location: ../views/employees/employee_deductions.php");
        exit();
    } catch (PDOException $e) {
        die("Query failed: " . $e->getMessage());
    }
}


//shifts

$shifts = getShift($pdo);

if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_shift_btn"])){
    $shift_name = $_POST["shift_name"];
    $start_time = $_POST["start_time"];
    $end_time = $_POST["end_time"];

    try {
        if(empty($shift_name) || empty($start_time) || empty($end_time)){
            $_SESSION["error"] = "Please fill in all the fields!";
        } else {
            insertShift($pdo, $shift_name, $start_time, $end_time);
            header("location: ../views/employees/employee_shifts.php");
            exit();
        }
    } catch (PDOException $e) {
        die("Query failed: " . $e->getMessage());
    }
}

if($_SERVER["REQUEST_METHOD"] && isset($_POST["delete_shift_btn"])){
    $shift_id = $_POST["shift_id"];
    $shift_name = $_POST["shift_name"];
    $start_time = $_POST["start_time"];
    $end_time = $_POST["end_time"];

    try {
        
        deleteShift($pdo, $start_time, $end_time, $shift_name,$shift_id);
        header("location: ../views/employees/employee_shifts.php");
        exit();
    } catch (PDOException $e) {
        die("Query failed: " . $e->getMessage());
    }
}

if($_SERVER["REQUEST_METHOD"] && isset($_POST["edit_shift_btn"])){
    $shift_id = $_POST["shift_id"];
    $shift_name = $_POST["shift_name"];
    $start_time = $_POST["start_time"];
    $end_time = $_POST["end_time"];

    try {
        
        editShift($pdo, $start_time, $end_time, $shift_name,$shift_id);
        header("location: ../views/employees/employee_shifts.php");
        exit();
    } catch (PDOException $e) {
        die("Query failed: " . $e->getMessage());
    }
}
//holidays
$holidays = getHolidays($pdo);

//for adding holidays

if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_holiday_btn"])){
    $holiday_name = trim($_POST["holiday_name"]);
    $month = (int)$_POST["month"];
    $day = (int)$_POST["day"];
    $type = $_POST["type"];

    try {
        $errors = [];
        
        // Validate inputs
        if(empty($holiday_name)){
            $errors["holiday_name"] = "Holiday name is required";
        }
        if($month < 1 || $month > 12){
            $errors["month"] = "Invalid month";
        }
        if($day < 1 || $day > 31){
            $errors["day"] = "Invalid day";
        }
        if(!in_array($type, ['regular', 'special'])){
            $errors["type"] = "Invalid holiday type";
        }
        
        // Check for duplicate holidays
        $checkQuery = "SELECT id FROM holidays WHERE holiday_name = ? AND month = ? AND day = ?";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute([$holiday_name, $month, $day]);
        if($checkStmt->rowCount() > 0){
            $errors["duplicate"] = "This holiday already exists";
        }

        if(!empty($errors)){
            $_SESSION["errors"] = $errors;
            $_SESSION["form_data"] = $_POST;
            header("Location: ../views/employees/holidays.php");
            exit();
        }
        
        insertHoliday($pdo, $holiday_name, $month, $day, $type);
        $_SESSION["success"] = "Holiday added successfully";
        header("Location: ../views/employees/holidays.php");
        exit();
    } catch (PDOException $e) {
        error_log("Error adding holiday: " . $e->getMessage());
        $_SESSION["errors"]["database"] = "Error saving holiday";
        header("Location: ../views/employees/holidays.php");
        exit();
    }
}


if($_SERVER["REQUEST_METHOD"] && isset($_POST["delete_holiday_btn"])){
    $holiday_id = $_POST["holiday_id"];
    $holiday_name = $_POST["holiday_name"];



    try {
        
        deleteHoliday($pdo, $holiday_id, $holiday_name);
        header("location: ../views/employees/holidays.php");
        exit();
    } catch (PDOException $e) {
        die("Query failed: " . $e->getMessage());
    }
}
// For editing holidays
if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["edit_holiday_btn"])){
    $holiday_id = $_POST["holiday_id"];
    $holiday_name = trim($_POST["holiday_name"]);
    $month = (int)$_POST["month"];
    $day = (int)$_POST["day"];
    $type = $_POST["type"];

    try {
        $errors = [];
        
        // Validation (same as add)
        if(empty($holiday_name)){
            $errors["holiday_name"] = "Holiday name is required";
        }
        // ... other validations
        
        if(!empty($errors)){
            $_SESSION["errors"] = $errors;
            header("Location: ../views/employees/holidays.php");
            exit();
        }
        
        updateHoliday($pdo, $holiday_id, $holiday_name, $month, $day, $type);
        $_SESSION["success"] = "Holiday updated successfully";
        header("Location: ../views/employees/holidays.php");
        exit();
    } catch (PDOException $e) {
        error_log("Error updating holiday: " . $e->getMessage());
        $_SESSION["errors"]["database"] = "Error updating holiday";
        header("Location: ../views/employees/holidays.php");
        exit();
    }
}