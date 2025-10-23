document.addEventListener("DOMContentLoaded", function () {
    const html5QrCode = new Html5Qrcode("reader");
    const toggleButton = document.getElementById("toggle-camera");
    const employeeInfo = document.getElementById("employee-info");
    const employeeName = document.getElementById("employee-name");
    const timeStatus = document.getElementById("time-status");
    const timestamp = document.getElementById("timestamp");
    let cameraActive = false;
    let lastScanTime = 0;
    const SCAN_COOLDOWN = 5000; // 5 seconds cooldown between scans

    // Toast elements
    const toastLiveExample = document.getElementById('liveToast');
    const toastBootstrap = new bootstrap.Toast(toastLiveExample, {
        delay: 5000
    });
    const toastMessage = document.getElementById('toast-message');
    const toastIcon = document.getElementById('toast-icon');
    const toastTime = document.getElementById('toast-time');

    // Function to show toast notification
    function showToast(message, type = 'success') {
        toastMessage.textContent = message;
        toastTime.textContent = new Date().toLocaleTimeString();

        if (type === 'success') {
            toastIcon.className = 'fas fa-check-circle text-success me-2';
        } else if (type === 'warning') {
            toastIcon.className = 'fas fa-exclamation-triangle text-warning me-2';
        } else {
            toastIcon.className = 'fas fa-exclamation-circle text-danger me-2';
        }

        toastBootstrap.show();
    }

    // Function to start the camera
    function startCamera() {
        const config = {
            fps: 10,
            qrbox: {
                width: 250,
                height: 250
            },
            aspectRatio: 1.0
        };

        html5QrCode.start(
            { facingMode: "environment" },
            config,
            onScanSuccess,
            onScanError
        ).then(() => {
            cameraActive = true;
            toggleButton.innerHTML = '<i class="fas fa-camera-slash"></i> <span>Stop Camera</span>';
            employeeInfo.style.display = 'none';
        }).catch((err) => {
            console.error("Camera initialization error:", err);
            showToast("Error starting camera. Please check camera permissions.", "error");
        });
    }

    // Function to stop the camera
    function stopCamera() {
        html5QrCode.stop().then(() => {
            cameraActive = false;
            toggleButton.innerHTML = '<i class="fas fa-camera"></i> <span>Start Camera</span>';
        }).catch((err) => {
            console.error("Camera stop error:", err);
            showToast("Error stopping camera", "error");
        });
    }

    // Function to display full-screen greeting
    function showFullScreenGreeting(name, isTimeIn) {
        // Stop camera temporarily
        if (cameraActive) {
            html5QrCode.pause();
        }

        // Create greeting overlay
        const overlay = document.createElement('div');
        overlay.className = 'full-screen-greeting';

        // Different style/message based on time-in or time-out
        if (isTimeIn) {
            overlay.classList.add('time-in');
            overlay.innerHTML = `
                <div class="greeting-content">
                    <div class="greeting-icon">
                        <i class="fas fa-hand-wave animated-icon"></i>
                    </div>
                    <h1 class="animate__animated animate__fadeInUp">Welcome, ${name}!</h1>
                    <p class="animate__animated animate__fadeIn animate__delay-1s">Have a great day at work</p>
                    <div class="time-display animate__animated animate__fadeIn animate__delay-1s">
                        <div class="current-time">${new Date().toLocaleTimeString()}</div>
                        <div class="attendance-status">Time In Recorded</div>
                    </div>
                </div>
            `;
        } else {
            overlay.classList.add('time-out');
            overlay.innerHTML = `
                <div class="greeting-content">
                    <div class="greeting-icon">
                        <i class="fas fa-hand-peace animated-icon"></i>
                    </div>
                    <h1 class="animate__animated animate__fadeInUp">Goodbye, ${name}!</h1>
                    <p class="animate__animated animate__fadeIn animate__delay-1s">Thank you for your work today</p>
                    <div class="time-display animate__animated animate__fadeIn animate__delay-1s">
                        <div class="current-time">${new Date().toLocaleTimeString()}</div>
                        <div class="attendance-status">Time Out Recorded</div>
                    </div>
                </div>
            `;
        }

        // Add to body
        document.body.appendChild(overlay);

        // Remove greeting and resume camera after delay
        setTimeout(() => {
            overlay.classList.add('fade-out');
            setTimeout(() => {
                document.body.removeChild(overlay);
                // Resume camera if it was active
                if (cameraActive) {
                    html5QrCode.resume();
                }
            }, 1000);
        }, 4000);
    }

    // Function to process QR code scan
    function onScanSuccess(decodedText) {
        // Check for cooldown to prevent immediate rescanning
        const currentTime = new Date().getTime();
        if (currentTime - lastScanTime < SCAN_COOLDOWN) {
            console.log("Scan cooldown active");
            showToast("Please wait before scanning again", "warning");
            return;
        }

        // Update last scan time
        lastScanTime = currentTime;

        decodedText = decodedText.trim();
        console.log("Scanned: ", decodedText);

        // Process the scan data
        processQrCode(decodedText);
    }

    // Handle scan error
    function onScanError(errorMessage) {
        console.log("QR Scan Error: ", errorMessage);
    }

    // Function to process QR code data
    function processQrCode(employeeId) {
        // Show scanning animation
        employeeInfo.innerHTML = `
            <div class="text-center w-100 py-3">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Processing...</span>
                </div>
                <p class="mt-2 mb-0">Processing your attendance...</p>
            </div>
        `;
        employeeInfo.style.display = 'block';

        // Send the QR code data to the server
        $.ajax({
            url: '../../views/employees/qr_scanner.php',
            type: 'POST',
            data: {
                scan_qr: true,
                scanned_id: employeeId
            },
            dataType: 'json',
            success: function (response) {
                // Show toast notification
                showToast(response.message, response.status);

                // Update employee info section
                if (response.status === 'success') {
                    const now = new Date();
                    const options = {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    };

                    // Check if this is a time-in event
                    const isTimeIn = response.message.includes('Time-in');

                    // Show full-screen greeting
                    showFullScreenGreeting(response.name, isTimeIn);

                    // Update employee info display for when greeting disappears
                    setTimeout(() => {
                        employeeInfo.innerHTML = `
                            <div class="employee-avatar">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div class="employee-details">
                                <h5 id="employee-name">${response.name}</h5>
                                <p><span id="time-status" class="${isTimeIn ? 'badge bg-success' : 'badge bg-danger'}">${isTimeIn ? 'Time In' : 'Time Out'}</span></p>
                                <p id="timestamp">${now.toLocaleDateString('en-US', options)}</p>
                            </div>
                        `;

                        // Show employee info with animation
                        employeeInfo.style.display = 'flex';

                        // Apply different styles based on time in/out
                        if (isTimeIn) {
                            employeeInfo.style.backgroundColor = 'rgba(25, 135, 84, 0.1)';
                            employeeInfo.style.borderLeft = '4px solid #198754';
                        } else {
                            employeeInfo.style.backgroundColor = 'rgba(220, 53, 69, 0.1)';
                            employeeInfo.style.borderLeft = '4px solid #dc3545';
                        }
                    }, 5000); // Update info after greeting disappears
                } else {
                    // Display error in employee info
                    employeeInfo.innerHTML = `
                        <div class="text-center w-100 py-3">
                            <i class="fas fa-exclamation-circle text-danger" style="font-size: 2rem;"></i>
                            <p class="mt-2 mb-0">${response.message}</p>
                        </div>
                    `;
                    employeeInfo.style.display = 'block';
                    employeeInfo.style.backgroundColor = 'rgba(220, 53, 69, 0.1)';
                    employeeInfo.style.borderLeft = '4px solid #dc3545';

                    // Resume camera after delay for error
                    setTimeout(() => {
                        if (cameraActive) {
                            html5QrCode.resume();
                        }
                    }, 2000);
                }
            },
            error: function (xhr, status, error) {
                showToast("Error processing scan: " + error, "error");
                employeeInfo.style.display = 'none';

                // Resume camera after delay for error
                setTimeout(() => {
                    if (cameraActive) {
                        html5QrCode.resume();
                    }
                }, 2000);
            }
        });
    }

    // Toggle camera on button click
    toggleButton.addEventListener("click", function () {
        if (cameraActive) {
            stopCamera();
        } else {
            startCamera();
        }
    });

    // Start camera automatically when page loads
    setTimeout(() => {
        startCamera();
    }, 1000);
});