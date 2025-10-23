<?php 
require_once __DIR__ . '/../models/EmployeeModel.php';
date_default_timezone_set('Asia/Manila'); // Change this to your timezone

// Time-in/Time-out Logging via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['scan_qr'])) {
    try {
        $scanned_id = trim($_POST['scanned_id']);
        $date = date("Y-m-d"); // Correct format for MySQL DATE column
        $time = date("H:i:s");
        
        // Check if employee exists
        $employee = getEmployeeForQrScan($pdo, $scanned_id);
        if (!$employee) {
            echo json_encode([
                "status" => "error", 
                "message" => "Invalid QR code or employee not found"
            ]);
            exit;
        }
        
        $employee_name = $employee['first_name'];
    
        // Get the latest attendance record for the employee today
        $last_record = getEmployeeAttendace($pdo, $scanned_id, $date);
        
        if ($last_record) {
            // If last record has no time_out, update it with time_out
            if ($last_record['time_out'] === null) {
                // Check if at least 1 minute has passed since time_in (prevent accidental double scans)
                $time_in = strtotime($last_record['time_in']);
                $current_time = strtotime($time);
                $time_diff = $current_time - $time_in;
                
                if ($time_diff < 60) { // Less than 1 minute
                    echo json_encode([
                        "status" => "warning", 
                        "message" => "Please wait before scanning again, $employee_name"
                    ]);
                    exit;
                }
                
                updateLastAttendance($pdo, $time, $scanned_id, $date);
                echo json_encode([
                    "status" => "success", 
                    "message" => "Goodbye, $employee_name! Time-out recorded.", 
                    "name" => $employee_name
                ]);
            } else {
                // Check when the last time-out was recorded
                $time_out = strtotime($last_record['time_out']);
                $current_time = strtotime($time);
                $time_diff = $current_time - $time_out;
                
                if ($time_diff < 300) { // Less than 5 minutes
                    echo json_encode([
                        "status" => "warning", 
                        "message" => "You have already timed out today, $employee_name"
                    ]);
                    exit;
                }
                
                // Allow a new time-in if significant time has passed since last time-out
                updateAttendance($pdo, $scanned_id, $date, $time);
                echo json_encode([
                    "status" => "success", 
                    "message" => "Welcome back, $employee_name! Time-in recorded.", 
                    "name" => $employee_name
                ]);
            }
        } else {
            // No time-in recorded today, insert a new time-in
            updateAttendance($pdo, $scanned_id, $date, $time);
            echo json_encode([
                "status" => "success", 
                "message" => "Welcome, $employee_name! Time-in recorded.", 
                "name" => $employee_name
            ]);
        }
        exit;
    } catch (PDOException $e) {
        echo json_encode([
            "status" => "error", 
            "message" => "Database error. Please try again or contact support."
        ]);
        // Log the actual error for administrators
        error_log("Database error in QR scanner: " . $e->getMessage());
        exit;
    }
}

function getEmployeeForQrScan($pdo, $scanned_id)
{
    $query = "SELECT employee_id, first_name, last_name FROM employees WHERE qr_employee_id = :scanned_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":scanned_id", $scanned_id, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getEmployeeAttendace($pdo, $scanned_id, $date)
{
    $query = "SELECT * FROM attendance WHERE qr_employee_id = :qr_employee_id AND date = :date ORDER BY time_in DESC LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":qr_employee_id", $scanned_id);
    $stmt->bindParam(":date", $date);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
}

function updateLastAttendance($pdo, $time, $scanned_id, $date)
{
    $query = "UPDATE attendance SET time_out = :time_out WHERE qr_employee_id = :qr_employee_id AND date = :date AND time_out IS NULL";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":time_out", $time);
    $stmt->bindParam(":qr_employee_id", $scanned_id);
    $stmt->bindParam(":date", $date);
    $stmt->execute();
}

function updateAttendance($pdo, $scanned_id, $date, $time)
{
    $query = "INSERT INTO attendance (qr_employee_id, date, time_in) VALUES (:qr_employee_id, :date, :time_in)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":qr_employee_id", $scanned_id);
    $stmt->bindParam(":date", $date);
    $stmt->bindParam(":time_in", $time);
    $stmt->execute();
}