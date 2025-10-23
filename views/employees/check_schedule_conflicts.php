<?php
require_once __DIR__ . '/../../configs/database.php';

header('Content-Type: application/json');

if (isset($_GET['employee_id']) && isset($_GET['dates'])) {
    $employee_id = $_GET['employee_id'];
    $dates = explode(',', $_GET['dates']);
    
    $placeholders = implode(',', array_fill(0, count($dates), '?'));
    $query = "SELECT date FROM work_schedule 
              WHERE employee_id = ? AND date IN ($placeholders)";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute(array_merge([$employee_id], $dates));
    $existing = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $conflicts = [];
    foreach ($existing as $date) {
        $conflicts[] = "Employee already has a schedule on " . date('M j, Y', strtotime($date));
    }
    
    echo json_encode($conflicts);
}