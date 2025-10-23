<?php
require_once __DIR__ . '/../../controllers/employeeScannerController.php';

// Only send JSON header for AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['scan_qr'])) {
    header('Content-Type: application/json');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Attendance Scanner</title>
    <link rel="stylesheet" href="../../public/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/css/qr_scanner_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script defer src="https://unpkg.com/html5-qrcode"></script>
    <script defer src="../../public/js/bootstrap.bundle.min.js"></script>
    <script defer src="../../public/js/qr_scanner.js"></script>
</head>
<body>
    <div class="scanner-container">
        <div class="scanner-header">
            <img src="../../public/images/the_lagoon_logo.png" class="logo" alt="Company Logo">
            <h3 class="text-primary">Employee Attendance System</h3>
        </div>
        
        <div class="scanner-body">
            <div id="reader-wrapper">
                <div id="reader"></div>
                <div class="scanner-overlay">
                    <div class="scan-area"></div>
                </div>
            </div>
            
            <button class="btn-camera-toggle" id="toggle-camera">
                <i class="fas fa-camera-slash"></i> <span>Stop Camera</span>
            </button>
            
            <p class="status-message">
                <i class="fas fa-info-circle"></i> 
                Scan your employee QR code to record attendance
            </p>
            
            <div id="employee-info" class="employee-info">
                <div class="employee-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="employee-details">
                    <h5 id="employee-name">Employee Name</h5>
                    <p><span id="time-status" class="badge">Status</span></p>
                    <p id="timestamp">Timestamp</p>
                </div>
            </div>
        </div>
        
        <div class="scanner-footer">
            <p class="current-time" id="current-time"></p>
            <p class="company-name">The Lagoon Company &copy; 2025</p>
        </div>
    </div>
    
    <!-- Toast notification -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i id="toast-icon" class="fas fa-check-circle me-2"></i>
                <strong class="me-auto">Attendance System</strong>
                <small id="toast-time">now</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toast-message">
                Scan successful!
            </div>
        </div>
    </div>
    
    <script>
        // Update current time in footer
        function updateClock() {
            const now = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
            document.getElementById('current-time').textContent = now.toLocaleDateString('en-US', options);
        }
        
        setInterval(updateClock, 1000);
        updateClock();
    </script>
</body>
</html>