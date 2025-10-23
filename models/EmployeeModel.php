<?php
require_once __DIR__ . '/../configs/database.php';


//for employee management
function insertEmployee(
    $pdo,
    $first_name,
    $last_name,
    $dob,
    $email,
    $address,
    $contact_number,
    $position,
    $deductions,
    $image_url,
    $file_name,
    $employee_id
) {

    //for activity_logs
    $action = "New employee added";
    $user = $_SESSION["username"];
    $details = "$user added $first_name $last_name";

    $log_query = "INSERT INTO activity_logs (action, user, details) VALUES (:action, :user, :details);";
    $log_stmt = $pdo->prepare($log_query);
    $log_stmt->bindParam(":action", $action);
    $log_stmt->bindParam(":user", $user);
    $log_stmt->bindParam(":details", $details);
    $log_stmt->execute();



    // Insert the employee first
    $query = "INSERT INTO employees (first_name, last_name, dob,email, address, contact_number, position, image, qr_code, qr_employee_id) 
              VALUES (:first_name, :last_name, :dob,:email, :address, :contact_number,  :position, :image, :qr_code, :qr_employee_id);";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":first_name", $first_name);
    $stmt->bindParam(":last_name", $last_name);
    $stmt->bindParam(":dob", $dob);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":address", $address);
    $stmt->bindParam(":contact_number", $contact_number);
    $stmt->bindParam(":position", $position, PDO::PARAM_INT);
    $stmt->bindParam(":image", $image_url);
    $stmt->bindParam(":qr_code", $file_name);
    $stmt->bindParam(":qr_employee_id", $employee_id);

    $stmt->execute();

    // Get the last inserted employee ID
    $employee_id = $pdo->lastInsertId();

    // Insert deductions if any were selected
    if (!empty($deductions)) {
        $query = "INSERT INTO employee_deductions (employee_id, deduction_id) VALUES (:employee_id, :deduction_id)";
        $stmt = $pdo->prepare($query);

        foreach ($deductions as $deduction) {
            $stmt->bindParam(":employee_id", $employee_id);
            $stmt->bindParam(":deduction_id", $deduction);
            $stmt->execute();
        }
    }
}



function getEmployee($pdo)
{
    $query = "SELECT e.*, 
    CONCAT(e.first_name, ' ', e.last_name) AS full_name, e.employee_id,
    p.position, 
    p.position_id, 
    GROUP_CONCAT(d.name SEPARATOR ', ') AS deductions
    FROM employees e
    JOIN positions p ON e.position = p.position_id
    LEFT JOIN employee_deductions ed ON e.employee_id = ed.employee_id
    LEFT JOIN deductions d ON ed.deduction_id = d.deduction_id
    GROUP BY e.employee_id
    ORDER BY full_name";

    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}

function deleteEmployee($pdo, $employee_id, $full_name, $user_id){

    //for activity_logs
    $action = "Employee deleted";
    $user = $_SESSION["username"];
    $details = "$user deleted $full_name with an ID: $employee_id";
    
    $log_query = "INSERT INTO activity_logs (action, user, details) VALUES (:action, :user, :details);";
    $log_stmt = $pdo->prepare($log_query);
    $log_stmt->bindParam(":action", $action);
    $log_stmt->bindParam(":user", $user);
    $log_stmt->bindParam(":details", $details);
    $log_stmt->execute();

    //for checking exising records in archives table
    $check_archive_query = "SELECT archive_id  FROM archives WHERE original_id = :employee_id AND original_table = 'employee'";
    $check_archive_stmt = $pdo->prepare($check_archive_query);
    $check_archive_stmt->bindParam(":employee_id", $employee_id);
    $check_archive_stmt->execute();

    $existing = $check_archive_stmt->fetch(PDO::FETCH_ASSOC);

    //for getting all employee records
    $employee_query = "SELECT * FROM employees WHERE employee_id = :employee_id;";
    $employee_stmt = $pdo->prepare($employee_query);
    $employee_stmt->bindParam(":employee_id", $employee_id);
    $employee_stmt->execute();

    $employee = $employee_stmt->fetch(PDO::FETCH_ASSOC);

    if($existing !== false){

        $update_archive_query = "UPDATE archives SET archived_data = :archived_data, archived_at = NOW(), restored_at = NULL, details = :full_name WHERE archive_id = :archive_id";
        $update_archive_stmt = $pdo->prepare($update_archive_query);
        $update_archive_stmt->bindParam(":archived_data", json_encode($employee));
        $update_archive_stmt->bindParam(":archive_id", $existing["archived_id"]);
        $update_archive_stmt->bindParam(":full_name", $full_name    );
        $update_archive_stmt->execute();
    } else {
        $query2 = "INSERT INTO archives (original_table, original_id, details,archived_data, archived_by, reason)
        VALUES(
            :original_table,
            :employee_id,
            :full_name,
            :archived_data,
            :user_id,  -- ID of admin performing the archive
            :reason  -- Reason for archiving
            );
            ";
        $stmt2 = $pdo->prepare($query2);
        $stmt2->bindValue(":original_table", 'employees');
        $stmt2->bindParam(":employee_id", $employee_id);
        $stmt2->bindParam(":full_name", $full_name);
        $archived_data = json_encode($employee);
        $stmt2->bindParam(":archived_data", $archived_data);
        $stmt2->bindParam(":user_id", $user_id);
        $stmt2->bindValue(":reason", 'Employee left the company');
        $stmt2->execute();


    }


 

    $query = "DELETE FROM employees WHERE employee_id = :employee_id;";
    $stmt1 = $pdo->prepare($query);
    $stmt1->bindParam(":employee_id", $employee_id);
    $stmt1->execute();
}

function updateEmployee($pdo, $employee_id, $full_name, $first_name, $last_name, $dob, $email, $address, $contact_number, $shift, $position, $deductions, $image_url = null, $qr_code = null)
{

        //for activity_logs
        $action = "Employee updated";
        $user = $_SESSION["username"];
        $details = "$user updated $first_name $last_name with an ID: $employee_id";
    
        $log_query = "INSERT INTO activity_logs (action, user, details) VALUES (:action, :user, :details);";
        $log_stmt = $pdo->prepare($log_query);
        $log_stmt->bindParam(":action", $action);
        $log_stmt->bindParam(":user", $user);
        $log_stmt->bindParam(":details", $details);
        $log_stmt->execute();
    // Build the query dynamically to update only changed fields
    $query = "UPDATE employees 
              SET first_name = :first_name, 
                  last_name = :last_name, 
                  dob = :dob,
                  email = :email, 
                  address = :address, 
                  contact_number = :contact_number, 
                  shift = :shift, 
                  position = :position";

    if ($image_url) {
        $query .= ", image = :image";
    }
    if ($qr_code) {
        $query .= ", qr_code = :qr_code";
    }

    $query .= " WHERE employee_id = :employee_id";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":first_name", $first_name);
    $stmt->bindParam(":last_name", $last_name);
    $stmt->bindParam(":dob", $dob);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":address", $address);
    $stmt->bindParam(":contact_number", $contact_number);
    $stmt->bindParam(":shift", $shift);
    $stmt->bindParam(":position", $position, PDO::PARAM_INT);
    $stmt->bindParam(":employee_id", $employee_id, PDO::PARAM_INT);

    if ($image_url) {
        $stmt->bindParam(":image", $image_url);
    }
    if ($qr_code) {
        $stmt->bindParam(":qr_code", $qr_code);
    }

    $stmt->execute();

    // Update deductions: Remove old deductions and insert new ones
    $stmt = $pdo->prepare("DELETE FROM employee_deductions WHERE employee_id = ?");
    $stmt->execute([$employee_id]);

    if (!empty($deductions)) {
        $query = "INSERT INTO employee_deductions (employee_id, deduction_id) VALUES (:employee_id, :deduction_id)";
        $stmt = $pdo->prepare($query);

        foreach ($deductions as $deduction) {
            $stmt->bindParam(":employee_id", $employee_id, PDO::PARAM_INT);
            $stmt->bindParam(":deduction_id", $deduction, PDO::PARAM_INT);
            $stmt->execute();
        }
    }
}

function getEmployeeById($pdo, $employee_id)
{
    $query = "SELECT e.*, 
              CONCAT(e.first_name, ' ', e.last_name) AS full_name, e.contact_number, e.address,
              p.position 
              FROM employees e
              JOIN positions p ON e.position = p.position_id
              WHERE e.employee_id = :employee_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":employee_id", $employee_id);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
}

//for attendance


// function getEmployeeForQrScan($pdo, $scanned_id)
// {
//     $query = "SELECT first_name FROM employees WHERE qr_employee_id = :scanned_id;";
//     $stmt = $pdo->prepare($query);
//     $stmt->bindParam(":scanned_id", $scanned_id, PDO::PARAM_STR);
//     $stmt->execute();
//     return $stmt->fetch(PDO::FETCH_ASSOC);
// }


// function getEmployeeAttendace($pdo, $scanned_id, $date)
// {
//     $query = "SELECT * FROM attendance WHERE qr_employee_id = :qr_employee_id AND date = :date ORDER BY time_in DESC LIMIT 1";
//     $stmt = $pdo->prepare($query);
//     $stmt->bindParam(":qr_employee_id", $scanned_id);
//     $stmt->bindParam(":date", $date);
//     $stmt->execute();

//     $result = $stmt->fetch(PDO::FETCH_ASSOC);
//     return $result;
// }

// function updateLastAttendance($pdo, $time, $scanned_id, $date)
// {
//     $query = "UPDATE attendance SET time_out = :time_out WHERE qr_employee_id = :qr_employee_id AND date = :date AND time_out IS NULL";
//     $stmt = $pdo->prepare($query);
//     $stmt->bindParam(":time_out", $time);
//     $stmt->bindParam(":qr_employee_id", $scanned_id);
//     $stmt->bindParam(":date", $date);
//     $stmt->execute();
// }

// function updateAttendance($pdo, $scanned_id, $date, $time)
// {
//     $query = "INSERT INTO attendance (qr_employee_id, date, time_in) VALUES (:qr_employee_id, :date, :time_in);";
//     $stmt = $pdo->prepare($query);
//     $stmt->bindParam(":qr_employee_id", $scanned_id);
//     $stmt->bindParam(":date", $date);
//     $stmt->bindParam(":time_in", $time);
//     $stmt->execute();
// }

function getDateFilter($pdo, $specific_date) {
    $query = "SELECT e.qr_employee_id, CONCAT(e.first_name, ' ', e.last_name) AS full_name, 
                     a.time_in, a.time_out, a.date, 
                     TIMESTAMPDIFF(SECOND, a.time_in, a.time_out) AS total_seconds
              FROM attendance a
              JOIN employees e ON e.qr_employee_id = a.qr_employee_id
              WHERE a.date = :date
              ORDER BY a.time_in ASC";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":date", $specific_date);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


//for employee's status

function getEmployeeStatus($pdo, $employee_id) {
    $query = "SELECT time_out, time_in 
              FROM attendance 
              WHERE qr_employee_id = :qr_employee_id 
              AND DATE(date) = CURDATE() 
              ORDER BY time_in DESC 
              LIMIT 1";
              
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":qr_employee_id", $employee_id, PDO::PARAM_STR);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

//get attendance
function getAttendance($pdo, $employee_id) {
    $query = "SELECT 
                date, 
                time_in, 
                time_out,
                CASE 
                    WHEN time_out >= time_in THEN 
                        TIMESTAMPDIFF(SECOND, time_in, time_out)
                    ELSE 
                        TIMESTAMPDIFF(SECOND, time_in, ADDTIME(time_out, '24:00:00'))
                END AS total_seconds
              FROM attendance 
              WHERE qr_employee_id = :qr_employee_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":qr_employee_id", $employee_id);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
//for settings

//positions

function insertPosition($pdo, $position, $salary)
{
    //for activity_logs
    $action = "New position added";
    $user = $_SESSION["username"];
    $details = "$user added $position";
    
    $log_query = "INSERT INTO activity_logs (action, user, details) VALUES (:action, :user, :details);";
    $log_stmt = $pdo->prepare($log_query);
    $log_stmt->bindParam(":action", $action);
    $log_stmt->bindParam(":user", $user);
    $log_stmt->bindParam(":details", $details);
    $log_stmt->execute();    


    $query = "INSERT INTO positions (position, salary) VALUES (:position, :salary);";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":position", $position);
    $stmt->bindParam(":salary", $salary);
    $stmt->execute();
}

function updatePosition($pdo, $position_id, $position, $salary){
    $query = "UPDATE positions SET position = :position, salary = :salary WHERE position_id = :position_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":position", $position);
    $stmt->bindParam(":salary", $salary);
    $stmt->bindParam(":position_id", $position_id);
    $stmt->execute();
}
function getPositions($pdo)
{
    $query = "SELECT * FROM positions";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}

function deletePosition($pdo, $position, $position_id)
{
    //for activity_logs
    $action = "Position deleted";
    $user = $_SESSION["username"];
    $details = "$user deleted $position";
    
    $log_query = "INSERT INTO activity_logs (action, user, details) VALUES (:action, :user, :details);";
    $log_stmt = $pdo->prepare($log_query);
    $log_stmt->bindParam(":action", $action);
    $log_stmt->bindParam(":user", $user);
    $log_stmt->bindParam(":details", $details);
    $log_stmt->execute();

    $query = "DELETE FROM positions WHERE position_id = :position_id;";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":position_id", $position_id);
    $stmt->execute();
}

//deductions

function insertDeduction($pdo, $deduction, $amount)
{
    //for activity_logs
    $action = "Deduction added";
    $user = $_SESSION["username"];
    $details = "$user added $deduction with a percentage of: $amount";
        
    $log_query = "INSERT INTO activity_logs (action, user, details) VALUES (:action, :user, :details);";
    $log_stmt = $pdo->prepare($log_query);
    $log_stmt->bindParam(":action", $action);
    $log_stmt->bindParam(":user", $user);
    $log_stmt->bindParam(":details", $details);
    $log_stmt->execute();


    $query = "INSERT INTO deductions (name, percentage) VALUES (:name, :percentage);";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":name", $deduction);
    $stmt->bindParam(":percentage", $amount);
    $stmt->execute();
}

function getDeductions($pdo)
{
    $query = "SELECT * FROM deductions";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}

function deleteDeduction($pdo, $deduction, $deduction_id)
{
    //for activity_logs
    $action = "Deduction deleted";
    $user = $_SESSION["username"];
    $details = "$user deleted $deduction";
            
    $log_query = "INSERT INTO activity_logs (action, user, details) VALUES (:action, :user, :details);";
    $log_stmt = $pdo->prepare($log_query);
    $log_stmt->bindParam(":action", $action);
    $log_stmt->bindParam(":user", $user);
    $log_stmt->bindParam(":details", $details);
    $log_stmt->execute();

    $query = "DELETE FROM deductions WHERE deduction_id = :deduction_id;";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":deduction_id", $deduction_id);
    $stmt->execute();
}

function editDeduction($pdo, $deduction_id, $deduction, $percentage){
        //for activity_logs
        $action = "Deduction edited";
        $user = $_SESSION["username"];
        $details = "$user edited $deduction";
                
        $log_query = "INSERT INTO activity_logs (action, user, details) VALUES (:action, :user, :details);";
        $log_stmt = $pdo->prepare($log_query);
        $log_stmt->bindParam(":action", $action);
        $log_stmt->bindParam(":user", $user);
        $log_stmt->bindParam(":details", $details);
        $log_stmt->execute();

        $query = "UPDATE deductions SET name = :name, percentage = :percentage WHERE deduction_id = :deduction_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":name", $deduction);
        $stmt->bindParam(":percentage", $percentage);
        $stmt->bindParam(":deduction_id", $deduction_id);
        $stmt->execute();
}

//shifts

function insertShift($pdo, $shift_name, $start_time, $end_time)
{
    //for activity_logs
    $action = "Shift added";
    $user = $_SESSION["username"];
    $details = "$user added $shift_name with a schedule of: $start_time - $end_time";
            
    $log_query = "INSERT INTO activity_logs (action, user, details) VALUES (:action, :user, :details);";
    $log_stmt = $pdo->prepare($log_query);
    $log_stmt->bindParam(":action", $action);
    $log_stmt->bindParam(":user", $user);
    $log_stmt->bindParam(":details", $details);
    $log_stmt->execute();

    $query = "INSERT INTO shifts (shift_name, start_time, end_time) VALUES (:shift_name, :start_time, :end_time);";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":shift_name", $shift_name);
    $stmt->bindParam(":start_time", $start_time);
    $stmt->bindParam(":end_time", $end_time);
    $stmt->execute();
}

function getShift($pdo)
{
    $query = "SELECT * FROM shifts";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}

function deleteShift($pdo, $start_time, $end_time, $shift_name,$shift_id)
{
    //for activity_logs
    $action = "Shift added";
    $user = $_SESSION["username"];
    $details = "$user added $shift_name with a schedule of: $start_time - $end_time";
                
    $log_query = "INSERT INTO activity_logs (action, user, details) VALUES (:action, :user, :details);";
    $log_stmt = $pdo->prepare($log_query);
    $log_stmt->bindParam(":action", $action);
    $log_stmt->bindParam(":user", $user);
    $log_stmt->bindParam(":details", $details);
    $log_stmt->execute();

    $query = "DELETE FROM shifts WHERE shift_id = :shift_id;";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":shift_id", $shift_id);
    $stmt->execute();
}

function editShift($pdo, $start_time, $end_time, $shift_name,$shift_id){
    //for activity_logs
    $action = "Shift edited";
    $user = $_SESSION["username"];
    $details = "$user edit $shift_name with a schedule of: $start_time - $end_time";
                
    $log_query = "INSERT INTO activity_logs (action, user, details) VALUES (:action, :user, :details);";
    $log_stmt = $pdo->prepare($log_query);
    $log_stmt->bindParam(":action", $action);
    $log_stmt->bindParam(":user", $user);
    $log_stmt->bindParam(":details", $details);
    $log_stmt->execute();

    $query = "UPDATE shifts SET shift_name = :shift_name, start_time = :start_time, end_time = :end_time WHERE shift_id = :shift_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":shift_name", $shift_name);
    $stmt->bindParam(":start_time", $start_time);
    $stmt->bindParam(":end_time", $end_time);
    $stmt->bindParam(":shift_id", $shift_id);
    $stmt->execute();
}

//payslip

function getPayslipInformation($pdo, $start_date, $end_date, $pay_period_range) {
    // Get basic employee information
    $query = "SELECT 
                e.employee_id,
                e.qr_employee_id,
                CONCAT(e.first_name, ' ', e.last_name) AS full_name, 
                p.position, 
                p.salary AS daily_rate
              FROM employees e
              JOIN positions p ON e.position = p.position_id
              WHERE EXISTS (
                  SELECT 1 FROM attendance a 
                  WHERE a.qr_employee_id = e.qr_employee_id
                  AND a.date BETWEEN :start_date AND :end_date
              )";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process each employee
    foreach ($employees as &$employee) {
        $employee_id = $employee['employee_id'];
        $dailyRate = $employee['daily_rate'];
        $hourlyRate = $dailyRate / 8; // Calculate hourly rate
        
        // Calculate attendance using the shared function
        [$totalScheduledDays, $presentDays, $leaveDays, $absentDays, $lateHours, $undertimeHours] = calculateAttendance(
            $pdo, $employee_id, $start_date, $end_date
        );

        // Get deductions using the shared function
        $deductions = getEmployeeDeductions($pdo, $employee_id);
        $totalDeductionPercentage = calculateDeductionsPercentage($deductions, $pay_period_range);

        // Get worked days with holiday information using the shared function
        $workedDays = getWorkedDaysWithHolidays($pdo, $employee_id, $start_date, $end_date);

        // Calculate pay components using the shared function
        $payComponents = calculatePayComponents(
            $pdo, $employee_id, $workedDays, $dailyRate, $hourlyRate, $lateHours, $undertimeHours
        );
        // Calculate night differential using the shared function
        $nightDiffPay = calculateNightDifferential($pdo, $employee_id, $start_date, $end_date);

        // Calculate gross and net pay
        $grossPay = $payComponents['regularPay'] + $payComponents['holidayPay'] + 
                    $payComponents['overtimePay'] + $nightDiffPay;
        $totalDeductions = $grossPay * ($totalDeductionPercentage / 100);
        $netPay = $grossPay - $totalDeductions;
        
        // Add all calculated values to the employee array
        $employee['regular_days'] = $payComponents['regularDays'];
        $employee['holiday_days'] = $payComponents['holidayDays'];
        $employee['overtime_hours'] = $payComponents['overtimeHours'];
        $employee['overtime_rate'] = $payComponents['overtimeRate'];
        $employee['regular_pay'] = $payComponents['regularPay'];
        $employee['holiday_pay'] = $payComponents['holidayPay'];
        $employee['night_diff_pay'] = $nightDiffPay;
        $employee['overtime_pay'] = $payComponents['overtimePay'];
        $employee['gross_pay'] = $grossPay;
        $employee['deductions'] = $deductions;
        $employee['total_deduction_percentage'] = $totalDeductionPercentage;
        $employee['total_deductions'] = $totalDeductions;
        $employee['net_pay'] = $netPay;
        $employee['scheduled_days'] = $totalScheduledDays;
        $employee['present_days'] = $presentDays;
        $employee['leave_days'] = $leaveDays;
        $employee['absent_days'] = $absentDays;
        $employee['hourly_rate'] = $hourlyRate;
        $employee['late_hours'] = $payComponents['lateHours'];
        $employee['lateDeduction'] = $payComponents['lateDeduction'];
        $employee['undertime_hours'] = $payComponents['undertimeHours'];
        $employee['undertimeDeduction'] = $payComponents['undertimeDeduction'];
    }

    return $employees;
}


// Generate payslip data

function generatePayslip($pdo, $employee_id, $start_date, $end_date, $pay_period_range) {
    // Activity log
    $action = "Payslip generated";
    $user = $_SESSION["username"];
    $details = "$user generated payslip for employee $employee_id ($start_date to $end_date)";
    logActivity($pdo, $action, $user, $details);

    // Get employee basic info with optimized query
    $query = "SELECT 
                e.employee_id, e.first_name, e.last_name, e.address, e.contact_number,
                p.position, p.salary
              FROM employees e
              JOIN positions p ON e.position = p.position_id
              WHERE e.employee_id = :employee_id";
              
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":employee_id", $employee_id);
    $stmt->execute();
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$employee) {
        return null;
    }

    // Calculate work days and attendance
    [$totalScheduledDays, $presentDays, $leaveDays, $absentDays, $lateHours, $undertimeHours] = calculateAttendance(
        $pdo, $employee_id, $start_date, $end_date
    );
    // Get deductions
    $deductions = getEmployeeDeductions($pdo, $employee_id);
    $totalDeductionPercentage = calculateDeductionsPercentage($deductions, $pay_period_range);

    // Get worked days with holiday information
    $workedDays = getWorkedDaysWithHolidays($pdo, $employee_id, $start_date, $end_date);

    // Calculate payroll components
    $dailyRate = $employee['salary'];
    $hourlyRate = $dailyRate / 8;
    
    // Calculate pay components

    $payComponents = calculatePayComponents(
        $pdo, $employee_id, $workedDays, $dailyRate, $hourlyRate, $lateHours, $undertimeHours
    );

    // Calculate night differential
    $nightDiffPay = calculateNightDifferential($pdo, $employee_id, $start_date, $end_date);
    
    // Calculate gross and net pay
    $grossPay = $payComponents['regularPay'] + $payComponents['holidayPay'] + 
                $payComponents['overtimePay'] + $nightDiffPay;
    $totalDeductions = $grossPay * ($totalDeductionPercentage / 100);
    $netPay = $grossPay - $totalDeductions;

    // Compile all employee data
    return array_merge($employee, [
        'full_name' => $employee['first_name'] . ' ' . $employee['last_name'],
        'regular_days' => $payComponents['regularDays'],
        'holiday_days' => $payComponents['holidayDays'],
        'regular_pay' => $payComponents['regularPay'],
        'holiday_pay' => $payComponents['holidayPay'],
        'overtime_rate' => $payComponents['overtimeRate'],
        'overtime_hours' => $payComponents['overtimeHours'],
        'overtime_pay' => $payComponents['overtimePay'],
        'night_diff_pay' => $nightDiffPay,
        'gross_pay' => $grossPay,
        'total_deductions' => $totalDeductions,
        'net_pay' => $netPay,
        'deductions' => $deductions,
        'total_deduction_percentage' => $totalDeductionPercentage,
        'scheduled_days' => $totalScheduledDays,
        'present_days' => $presentDays,
        'leave_days' => $leaveDays,
        'absent_days' => $absentDays,
        'hourly_rate' => $hourlyRate,
        'daily_rate' => $dailyRate,
        'late_hours' => $payComponents['lateHours'],
        'lateDeduction' => $payComponents['lateDeduction'],
        'undertime_hours' => $payComponents['undertimeHours'],
        'undertimeDeduction' => $payComponents['undertimeDeduction']
    ]);
}

function calculateAttendance($pdo, $employee_id, $start_date, $end_date) {
    // Get scheduled days with shift times
    $scheduledQuery = "SELECT ws.date, s.start_time, s.end_time 
                      FROM work_schedule ws
                      JOIN shifts s ON ws.shift_id = s.shift_id
                      WHERE ws.employee_id = :employee_id
                      AND ws.date BETWEEN :start_date AND :end_date";
    $stmt = $pdo->prepare($scheduledQuery);
    $stmt->execute([':employee_id' => $employee_id, ':start_date' => $start_date, ':end_date' => $end_date]);
    $scheduledDays = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalScheduledDays = count($scheduledDays);

    // Initialize counters
    $presentDays = 0;
    $leaveDays = 0;
    $totalLateMinutes = 0;
    $totalUndertimeMinutes = 0;

    // Check each scheduled day
    foreach ($scheduledDays as $day) {
        // Get attendance record if exists
        $attendanceQuery = "SELECT time_in, time_out FROM attendance a
                          JOIN employees e ON a.qr_employee_id = e.qr_employee_id
                          WHERE e.employee_id = :employee_id
                          AND a.date = :date";
        $stmt = $pdo->prepare($attendanceQuery);
        $stmt->execute([':employee_id' => $employee_id, ':date' => $day['date']]);
        $attendance = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($attendance) {
            $presentDays++;
            
            // Calculate late arrival
            $timeIn = new DateTime($day['date'] . ' ' . $attendance['time_in']);
            $shiftStart = new DateTime($day['date'] . ' ' . $day['start_time']);
            // Apply 10-minute grace period
            $graceStart = clone $shiftStart;
            $graceStart->modify('+10 minutes');
            if ($timeIn > $graceStart) {
                $diff = $timeIn->diff($graceStart);
                $totalLateMinutes += ($diff->h * 60) + $diff->i;
            }

            
            // Calculate undertime (early departure)
            if (!empty($attendance['time_out'])) {
                $timeOut = new DateTime($day['date'] . ' ' . $attendance['time_out']);
                $shiftEnd = new DateTime($day['date'] . ' ' . $day['end_time']);
                // Apply 10-minute grace period
                $graceEnd = clone $shiftEnd;
                $graceEnd->modify('-10 minutes');
            if ($timeOut < $graceEnd) {
                $diff = $graceEnd->diff($timeOut);
                $totalUndertimeMinutes += ($diff->h * 60) + $diff->i;
            }
            }
        }
    }

    // Get leave days
    $leaveQuery = "SELECT start_date, end_date FROM leave_requests
                  WHERE employee_id = :employee_id
                  AND status = 'Approved'
                  AND start_date <= :end_date
                  AND end_date >= :start_date";
    $stmt = $pdo->prepare($leaveQuery);
    $stmt->execute([':employee_id' => $employee_id, ':start_date' => $start_date, ':end_date' => $end_date]);
    $leaveRanges = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $leaveDays = count($leaveRanges);

    $absentDays = $totalScheduledDays - $presentDays - $leaveDays;
    $lateHours = round($totalLateMinutes / 60, 2);
    $undertimeHours = round($totalUndertimeMinutes / 60, 2);

    return [
        $totalScheduledDays, 
        $presentDays, 
        $leaveDays, 
        $absentDays,
        $lateHours,
        $undertimeHours
    ];
}
function getWorkedDaysWithHolidays($pdo, $employee_id, $start_date, $end_date) {
    $query = "SELECT 
                a.date, a.time_in, a.time_out,
                h.holiday_name, h.holiday_type
              FROM attendance a
              JOIN employees e ON a.qr_employee_id = e.qr_employee_id
              LEFT JOIN (
                  SELECT 
                      holiday_name, holiday_type,
                      CONCAT(YEAR(:start_date), '-', LPAD(month, 2, '0'), '-', LPAD(day, 2, '0')) AS holiday_date
                  FROM holidays
                  UNION
                  SELECT 
                      holiday_name, holiday_type,
                      CONCAT(YEAR(:end_date), '-', LPAD(month, 2, '0'), '-', LPAD(day, 2, '0')) AS holiday_date
                  FROM holidays
              ) h ON a.date = h.holiday_date
              WHERE e.employee_id = :employee_id
              AND a.date BETWEEN :start_date AND :end_date
              ORDER BY a.date";
              
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':employee_id' => $employee_id,
        ':start_date' => $start_date,
        ':end_date' => $end_date
    ]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function calculatePayComponents($pdo, $employee_id, $workedDays, $dailyRate, $hourlyRate, $lateHours, $undertimeHours) {
    $regularDays = 0;
    $holidayDays = 0;
    $holidayPay = 0;
    $overtimeHours = 0;
    $overtimeRate = 81;

    foreach ($workedDays as $day) {
        if (!empty($day['holiday_name'])) {
            $holidayDays++;
            $premiumRate = ($day['holiday_type'] === 'special') ? 0.5 : 0.3;
            $holidayPay += $dailyRate * (1 + $premiumRate);
        } else {
            $regularDays++;
        }

        // Overtime calculation
        if (!empty($day['time_out'])) {
            $shiftEnd = getShiftEndTime($pdo, $employee_id, $day['date']);
            if ($shiftEnd) {
                $overtimeMinutes = calculateOvertimeMinutes($day['time_out'], $shiftEnd, $day['date']);
                if ($overtimeMinutes >= 10) {
                    $overtimeHours += $overtimeMinutes / 60;
                }
            }
        }
    }

    

    // Calculate deductions for late arrivals and undertime
    $lateDeduction = $lateHours * $hourlyRate;
    $undertimeDeduction = $undertimeHours * $hourlyRate;
    
    $regularPay = ($regularDays * $dailyRate) - $lateDeduction - $undertimeDeduction;
    $overtimePay = ceil($overtimeHours) * $overtimeRate;
    $holidayPay = round($holidayPay, 2);

    return [
        'regularDays' => $regularDays,
        'holidayDays' => $holidayDays,
        'regularPay' => $regularPay,
        'holidayPay' => $holidayPay,
        'overtimeRate' => $overtimeRate,
        'overtimeHours' => $overtimeHours,
        'overtimePay' => $overtimePay,
        'lateHours' => $lateHours,               // Changed from 'late_hours'
        'lateDeduction' => $lateDeduction,       // Changed from 'lateDeduction'
        'undertimeHours' => $undertimeHours,     // Changed from 'undertime_hours'
        'undertimeDeduction' => $undertimeDeduction // Changed from 'undertimeDeduction'
    ];
}

function calculateNightDifferential($pdo, $employee_id, $start_date, $end_date) {
    $nightDiffRate = 51; // Fixed rate per night shift
    $nightShiftQuery = "SELECT COUNT(*) as night_shift_count
                       FROM work_schedule ws
                       JOIN shifts s ON ws.shift_id = s.shift_id
                       WHERE ws.employee_id = :employee_id
                       AND ws.date BETWEEN :start_date AND :end_date
                       AND s.shift_name = 'Night Shift'";
    
    $stmt = $pdo->prepare($nightShiftQuery);
    $stmt->execute([
        ':employee_id' => $employee_id,
        ':start_date' => $start_date,
        ':end_date' => $end_date
    ]);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['night_shift_count'] * $nightDiffRate;
}


function getAllPayrollInformation($pdo, $start_date, $end_date, $employee_id = null, $pay_period) {
    // Get employees
    $employees = getEmployeesForPayroll($pdo, $employee_id);
    if (empty($employees)) return [];

    $result = [];
    foreach ($employees as $employee) {
        $empId = $employee['employee_id'];

        // Calculate attendance
        [$totalScheduledDays, $presentDays, $leaveDays, $absentDays, $lateHours, $undertimeHours] = calculateAttendance(
            $pdo, $empId, $start_date, $end_date
        );

        // Get deductions
        $deductions = getEmployeeDeductions($pdo, $empId);
        $totalDeductionPercentage = calculateDeductionsPercentage($deductions, $pay_period);

        // Get worked days with holidays
        $workedDays = getWorkedDaysWithHolidays($pdo, $empId, $start_date, $end_date);

        // Salary rates
        $dailyRate = $employee['salary'];
        $hourlyRate = $dailyRate / 8;

        // Calculate pay components
        $payComponents = calculatePayComponents(
            $pdo, $empId, $workedDays, $dailyRate, $hourlyRate, $lateHours, $undertimeHours
        );

        // Night differential
        $nightDiffPay = calculateNightDifferential($pdo, $empId, $start_date, $end_date);

        // Gross and net pay
        $grossPay = $payComponents['regularPay'] + $payComponents['holidayPay'] +
                    $payComponents['overtimePay'] + $nightDiffPay;
        $totalDeductions = $grossPay * ($totalDeductionPercentage / 100);
        $netPay = $grossPay - $totalDeductions;

        // Filter deductions per pay period
        $filteredDeductions = array_filter($deductions, function($deduction) use ($pay_period) {
            if ($pay_period === "1-15") {
                return in_array($deduction['deduction_name'], ['PhilHealth', 'Pag-IBIG']);
            } elseif ($pay_period === "16-End") {
                return $deduction['deduction_name'] === 'SSS';
            }
            return true;
        });

        $result[] = [
            'employee_id' => $empId,
            'full_name' => $employee['full_name'],
            'position' => $employee['position'],
            'salary' => $employee['salary'],
            'regular_days' => $payComponents['regularDays'],
            'holiday_days' => $payComponents['holidayDays'],
            'regular_pay' => $payComponents['regularPay'],
            'holiday_pay' => $payComponents['holidayPay'],
            'overtime_rate' => $payComponents['overtimeRate'],
            'overtime_hours' => $payComponents['overtimeHours'],
            'overtime_pay' => $payComponents['overtimePay'],
            'night_diff_pay' => $nightDiffPay,
            'gross_pay' => $grossPay,
            'total_deductions' => $totalDeductions,
            'net_pay' => $netPay,
            'deductions' => $filteredDeductions,
            'total_deduction_percentage' => $totalDeductionPercentage,
            'scheduled_days' => $totalScheduledDays,
            'present_days' => $presentDays,
            'leave_days' => $leaveDays,
            'absent_days' => $absentDays,
            'shift_type' => $employee["shift_name"],
            'regular_hours' => $payComponents['regularDays'] * 8,
            'hourly_rate' => $hourlyRate,
            'pay_period' => $pay_period,
            'late_hours' => $payComponents['lateHours'],
            'lateDeduction' => $payComponents['lateDeduction'],
            'undertime_hours' => $payComponents['undertimeHours'],
            'undertimeDeduction' => $payComponents['undertimeDeduction']
        ];
    }

    return $result;
}

function getEmployeesForPayroll($pdo, $employee_id = null) {
    $query = "SELECT
                e.employee_id,
                CONCAT(e.first_name, ' ', e.last_name) AS full_name,
                p.position,
                p.salary,
                s.shift_name
              FROM employees e
              LEFT JOIN positions p ON p.position_id = e.position
              LEFT JOIN shifts s ON s.shift_id = e.shift
              WHERE 1=1";
    
    if ($employee_id) {
        $query .= " AND e.employee_id = :employee_id";
    }
    
    $stmt = $pdo->prepare($query);
    if ($employee_id) {
        $stmt->bindParam(":employee_id", $employee_id);
    }
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getEmployeeDeductions($pdo, $employee_id) {
    $query = "SELECT 
                d.name AS deduction_name,
                d.percentage AS deduction_percentage
              FROM employee_deductions ed
              JOIN deductions d ON ed.deduction_id = d.deduction_id
              WHERE ed.employee_id = :employee_id";
              
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":employee_id", $employee_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function calculateDeductionsPercentage($deductions, $pay_period) {
    $philhealth = $pagibig = $sss = 0;
    
    foreach ($deductions as $deduction) {
        switch ($deduction["deduction_name"]) {
            case "PhilHealth": $philhealth = $deduction["deduction_percentage"]; break;
            case "Pag-IBIG": $pagibig = $deduction["deduction_percentage"]; break;
            case "SSS": $sss = $deduction["deduction_percentage"]; break;
        }
    }
    
    switch ($pay_period) {
        case "1-15": return $philhealth + $pagibig;
        case "16-end": return $sss;
        default: return $philhealth + $pagibig + $sss;
    }
}

function getShiftEndTime($pdo, $employee_id, $date) {
    $query = "SELECT s.end_time 
              FROM work_schedule ws
              JOIN shifts s ON ws.shift_id = s.shift_id
              WHERE ws.employee_id = :employee_id
              AND ws.date = :date";
              
    $stmt = $pdo->prepare($query);
    $stmt->execute([':employee_id' => $employee_id, ':date' => $date]);
    return $stmt->fetchColumn();
}

function calculateOvertimeMinutes($time_out, $shift_end, $date) {
    $timeOut = new DateTime($date . ' ' . $time_out);
    $shiftEnd = new DateTime($date . ' ' . $shift_end);
    
    if ($timeOut > $shiftEnd) {
        $diff = $timeOut->diff($shiftEnd);
        return ($diff->h * 60) + $diff->i;
    }
    return 0;
}

function logActivity($pdo, $action, $user, $details) {
    $query = "INSERT INTO activity_logs (action, user, details) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$action, $user, $details]);
}

//for leave

function insertLeave($pdo, $employee_id, $leave_type, $start_date, $end_date, $reason, $full_name){
    //for activity_logs
    $action = "Leave requested";
    $user = $_SESSION["username"];
    $details = "$user requested a leave for $full_name";

    $log_query = "INSERT INTO activity_logs (action, user, details) VALUES (:action, :user, :details);";
    $log_stmt = $pdo->prepare($log_query);
    $log_stmt->bindParam(":action", $action);
    $log_stmt->bindParam(":user", $user);
    $log_stmt->bindParam(":details", $details);
    $log_stmt->execute();


    $query = "INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, reason) VALUES
                (:employee_id, :leave_type, :start_date, :end_date, :reason)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":employee_id", $employee_id);
    $stmt->bindParam(":leave_type", $leave_type);
    $stmt->bindParam(":start_date", $start_date);
    $stmt->bindParam(":end_date", $end_date);
    $stmt->bindParam(":reason", $reason);
    $stmt->execute();
}

function getLeave($pdo){
    $query = "SELECT lr.*, CONCAT(e.first_name, ' ', e.last_name) as full_name 
    FROM leave_requests lr
    JOIN employees e ON e.employee_id = lr.employee_id
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}

function approveLeave($pdo, $leave_id, $full_name){
    //for activity_logs
    $action = "Approved leave";
    $user = $_SESSION["username"];
    $details = "$user approved a leave for $full_name";

    $log_query = "INSERT INTO activity_logs (action, user, details) VALUES (:action, :user, :details);";
    $log_stmt = $pdo->prepare($log_query);
    $log_stmt->bindParam(":action", $action);
    $log_stmt->bindParam(":user", $user);
    $log_stmt->bindParam(":details", $details);
    $log_stmt->execute();

    $query = "UPDATE leave_requests SET status = :status WHERE leave_id = :leave_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":status", 'Approved');
    $stmt->bindParam(":leave_id", $leave_id);
    $stmt->execute();
}

function rejectLeave($pdo, $leave_id, $full_name){
    //for activity_logs
    $action = "Approved leave";
    $user = $_SESSION["username"];
    $details = "$user approved a leave for $full_name";

    $log_query = "INSERT INTO activity_logs (action, user, details) VALUES (:action, :user, :details);";
    $log_stmt = $pdo->prepare($log_query);
    $log_stmt->bindParam(":action", $action);
    $log_stmt->bindParam(":user", $user);
    $log_stmt->bindParam(":details", $details);
    $log_stmt->execute();


    $query = "UPDATE leave_requests SET status = :status WHERE leave_id = :leave_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":status", 'Rejected');
    $stmt->bindParam(":leave_id", $leave_id);
    $stmt->execute();
}


//schedule

    function assignSchedule($pdo, $employee_id, $shift_id, $date) {
        $query = "INSERT INTO work_schedule (employee_id, shift_id, date) VALUES (:employee_id, :shift_id, :date)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":employee_id", $employee_id);
        $stmt->bindParam(":shift_id", $shift_id);
        $stmt->bindParam(":date", $date);
        $stmt->execute();
    }

    function getSchedules($pdo) {
        $query = "SELECT ws.schedule_id, ws.employee_id, ws.shift_id, ws.date, 
                         s.shift_name, s.start_time, s.end_time, 
                         CONCAT(e.first_name, ' ', e.last_name) AS full_name
                  FROM work_schedule ws
                  JOIN shifts s ON ws.shift_id = s.shift_id
                  JOIN employees e ON ws.employee_id = e.employee_id
                  ORDER BY ws.date DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

function hasApprovedLeave($pdo, $employee_id, $date) {
    $query = "SELECT * FROM leave_requests
              WHERE employee_id = :employee_id 
              AND :date BETWEEN start_date AND end_date 
              AND status = 'Approved'";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":employee_id", $employee_id);
    $stmt->bindParam(":date", $date);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC); // return one record if exists
}


function hasExistingSchedule($pdo, $employee_id, $date) {
    $query = "SELECT * FROM work_schedule 
              WHERE employee_id = :employee_id AND date = :date";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":employee_id", $employee_id);
    $stmt->bindParam(":date", $date);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC); // returns existing schedule if any
}

function isShiftTaken($pdo, $shift_id, $date) {
    $query = "SELECT COUNT(*) FROM work_schedule 
              WHERE shift_id = :shift_id AND date = :date";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":shift_id", $shift_id);
    $stmt->bindParam(":date", $date);
    $stmt->execute();
    return $stmt->fetchColumn() > 0;
}

/**
 * Get a specific schedule by ID
 */
function getScheduleById($pdo, $schedule_id) {
    $query = "SELECT * FROM work_schedule WHERE schedule_id = :schedule_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":schedule_id", $schedule_id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Update an existing schedule
 */
function updateSchedule($pdo, $schedule_id, $employee_id, $shift_id, $date) {
    $query = "UPDATE work_schedule 
              SET employee_id = :employee_id, 
                  shift_id = :shift_id, 
                  date = :date 
              WHERE schedule_id = :schedule_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":schedule_id", $schedule_id);
    $stmt->bindParam(":employee_id", $employee_id);
    $stmt->bindParam(":shift_id", $shift_id);
    $stmt->bindParam(":date", $date);
    return $stmt->execute();
}


// To this:
function deleteSchedule($pdo, $schedule_id) {
    $query = "DELETE FROM work_schedule WHERE schedule_id = :schedule_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":schedule_id", $schedule_id);
    return $stmt->execute();
}

//holidays
function getHolidays($pdo) {
    $query = "SELECT id, holiday_name, month, day, holiday_type 
              FROM holidays 
              ORDER BY month, day";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function insertHoliday($pdo, $holiday_name, $month, $day, $type){
    //for activity_logs
    $action = "Holiday inserted";
    $user = $_SESSION["username"];
    $details = "$user added a holiday $holiday_name on $month - $day";

    $log_query = "INSERT INTO activity_logs (action, user, details) VALUES (:action, :user, :details);";
    $log_stmt = $pdo->prepare($log_query);
    $log_stmt->bindParam(":action", $action);
    $log_stmt->bindParam(":user", $user);
    $log_stmt->bindParam(":details", $details);
    $log_stmt->execute();

    // Calculate current year's date for backward compatibility
    $currentYear = date('Y');
    $holiday_date = "$currentYear-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($day, 2, '0', STR_PAD_LEFT);
    
    $query = "INSERT INTO holidays (holiday_name, month, day, holiday_type, holiday_date) 
              VALUES (:holiday_name, :month, :day, :type, :holiday_date)";

    // Calculate current year's date for backward compatibility
    $currentYear = date('Y');
    $holiday_date = "$currentYear-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($day, 2, '0', STR_PAD_LEFT);

    $stmt = $pdo->prepare($query);
    $stmt->bindParam("holiday_name", $holiday_name);
    $stmt->bindParam("month", $month);
    $stmt->bindParam("day", $day);
    $stmt->bindParam("type", $type);
    $stmt->bindParam("holiday_date", $holiday_date);
    $stmt->execute();
}

function updateHoliday($pdo, $id, $holiday_name, $month, $day, $type) {
    // Activity log


    $query = "UPDATE holidays 
              SET holiday_name = :holiday_name, 
                  month = :month, 
                  day = :day, 
                  holiday_type = :type 
              WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->bindParam(":holiday_name", $holiday_name);
    $stmt->bindParam(":month", $month, PDO::PARAM_INT);
    $stmt->bindParam(":day", $day, PDO::PARAM_INT);
    $stmt->bindParam(":type", $type);
    $stmt->execute();
}
function deleteHoliday($pdo, $holiday_id, $holiday_name) {
    // First get holiday info for logging
    $query = "SELECT holiday_name, month, day FROM holidays WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$holiday_id]);
    $holiday = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($holiday) {


        $query = "DELETE FROM holidays WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$holiday_id]);
        return true;
    }
    return false;
}

function getHolidaysForPeriod($pdo, $start_date, $end_date) {
    $start_year = date('Y', strtotime($start_date));
    $end_year = date('Y', strtotime($end_date));
    
    // Get all holidays (month/day combinations)
    $query = "SELECT 
                id, holiday_name, holiday_type, month, day,
                CONCAT(:year, '-', LPAD(month, 2, '0'), '-', LPAD(day, 2, '0')) AS holiday_date
              FROM holidays
              WHERE 
                (CONCAT(:start_year, '-', LPAD(month, 2, '0'), '-', LPAD(day, 2, '0')) BETWEEN :start_date AND :end_date) OR
                (CONCAT(:end_year, '-', LPAD(month, 2, '0'), '-', LPAD(day, 2, '0')) BETWEEN :start_date AND :end_date)
              ORDER BY month, day";
              
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":start_date", $start_date);
    $stmt->bindParam(":end_date", $end_date);
    $stmt->bindParam(":start_year", $start_year);
    $stmt->bindParam(":end_year", $end_year);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

//new


function getSchedulesForWeek($pdo, $monday, $sunday) {
    $stmt = $pdo->prepare("
        SELECT s.*, sh.shift_name, sh.start_time, sh.end_time, 
               CONCAT(e.first_name, ' ', e.last_name) as full_name, e.employee_id
        FROM work_schedule s
        JOIN shifts sh ON s.shift_id = sh.shift_id
        JOIN employees e ON s.employee_id = e.employee_id
        WHERE s.date BETWEEN ? AND ?
        ORDER BY CONCAT(e.first_name, ' ', e.last_name), s.date
    ");
    $stmt->execute([$monday, $sunday]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function  getLeaveForWeek($pdo, $weekStart, $weekEnd){
    $leaveQuery = "SELECT employee_id, start_date, end_date FROM leave_requests 
              WHERE status = 'Approved' AND 
              ((start_date BETWEEN :start_date AND :end_date) OR 
               (end_date BETWEEN :start_date AND :end_date) OR 
               (start_date <= :start_date AND end_date >= :end_date))";
$leaveStmt = $pdo->prepare($leaveQuery);
$leaveStmt->bindParam(':start_date', $weekStart);
$leaveStmt->bindParam(':end_date', $weekEnd); // weekEnd would be $weekStart + 6 days
$leaveStmt->execute();
return $leaves = $leaveStmt->fetchAll(PDO::FETCH_ASSOC);
}


//

function getAttendanceForWeek($pdo, $startDate, $endDate) {
    $query = "SELECT a.qr_employee_id, e.employee_id, a.date, a.time_in, a.time_out 
              FROM attendance a 
              LEFT JOIN employees e ON a.qr_employee_id = e.qr_employee_id 
              WHERE a.date BETWEEN :start_date AND :end_date";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':start_date', $startDate);
    $stmt->bindParam(':end_date', $endDate);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}