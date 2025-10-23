<?php
require_once __DIR__ . '/../configs/database.php';

// Function to update booking statuses
function updateBookingStatuses($pdo) {
    $currentDate = date('Y-m-d');
    $currentDateTime = date('Y-m-d H:i:s');
    
    // Update to Ongoing: Check-in date is today or past, status is Confirmed
    $stmt = $pdo->prepare("UPDATE bookings 
                          SET status = 'Ongoing', updated_at = NOW() 
                          WHERE status = 'Confirmed' 
                          AND check_in_date <= ? 
                          AND check_out_date > ?");
    $stmt->execute([$currentDate, $currentDateTime]);
    
    // Update to Completed: Check-out date is today after 2PM
    $afterCheckoutTime = date('Y-m-d 14:00:00');
    $stmt = $pdo->prepare("UPDATE bookings 
                          SET status = 'Completed', updated_at = NOW() 
                          WHERE status = 'Ongoing' 
                          AND check_out_date <= ?");
    $stmt->execute([$afterCheckoutTime]);
    
    // Mark as No Show: Check-in date was yesterday or earlier, status is Confirmed
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $stmt = $pdo->prepare("UPDATE bookings 
                          SET status = 'No show', updated_at = NOW() 
                          WHERE status = 'Confirmed' 
                          AND down_payment_paid = TRUE 
                          AND check_in_date <= ?");
    $stmt->execute([$yesterday]);
}

// Call the function
updateBookingStatuses($pdo);