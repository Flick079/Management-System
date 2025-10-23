<?php
require_once __DIR__ . '/../../configs/database.php';

if (isset($_GET['employee_id'])) {
    $employee_id = $_GET['employee_id'];

    $query = "SELECT start_date, end_date FROM leave_requests 
              WHERE employee_id = :employee_id AND status = 'Approved'";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":employee_id", $employee_id);
    $stmt->execute();
    $leaves = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $leave_dates = [];

    foreach ($leaves as $leave) {
        $start = new DateTime($leave['start_date']);
        $end = new DateTime($leave['end_date']);
        while ($start <= $end) {
            $leave_dates[] = $start->format('Y-m-d');
            $start->modify('+1 day');
        }
    }

    echo json_encode($leave_dates);
}
