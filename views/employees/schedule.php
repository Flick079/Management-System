<?php
require_once __DIR__ . '/../../middleware/verify.php';
require_once __DIR__ . '/../../controllers/scheduleController.php';

// Display any schedule conflicts from the previous submission
if (isset($_SESSION['schedule_conflicts'])) {
    echo '<div class="alert alert-danger">';
    echo '<h5>Schedule Conflicts Detected:</h5>';
    echo '<ul>';
    foreach ($_SESSION['schedule_conflicts'] as $conflict) {
        echo '<li>' . htmlspecialchars($conflict) . '</li>';
    }
    echo '</ul>';
    echo '</div>';
    unset($_SESSION['schedule_conflicts']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Schedule</title>
    <link rel="stylesheet" href="../../public/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="../../public/css/bootstrap-icons-1.11.0/bootstrap-icons.min.css">
    <style>
        /* Custom styles */
/* Base Tables */
.schedule-table {
    width: 100%;
    overflow-x: auto;
}

/* Weekly Schedule Layout */
.weekly-schedule th.day-header {
    width: 14%;
    min-width: 120px;
}

.weekly-schedule th.employee-header {
    width: 15%;
    min-width: 150px;
}

.shift-cell {
    height: 80px;
    vertical-align: top;
}

/* Shift Styling */
.shift-info {
    font-size: 0.85rem;
    padding: 3px;
    border-radius: 3px;
    background-color: #e9ecef;
    margin-bottom: 2px;
}

.no-shift {
    color: #6c757d;
    font-style: italic;
}

.shift-actions {
    display: flex;
    gap: 5px;
    justify-content: flex-end;
}

.shift-actions .btn {
    padding: 2px 5px;
    font-size: 0.7rem;
}

.edit-shift-btn {
    color: #0d6efd;
    border-color: #0d6efd;
}

.delete-shift-btn {
    color: #dc3545;
    border-color: #dc3545;
}

/* Leave Notification */
#leaveToast .toast-header {
    background-color: #dc3545;
    color: white;
}

#leaveToast .toast-body {
    background-color: #f8d7da;
    color: #721c24;
    font-weight: 500;
}

.leave-day {
    color: #dc3545;
    font-weight: bold;
}

/* On-leave indicator */
.on-leave-indicator {
    color: #dc3545;
    font-weight: bold;
    padding: 3px;
    border-radius: 3px;
    background-color: #f8d7da;
    margin-bottom: 2px;
    display: block;
}

/* Attendance Status */
.attendance-status {
    display: inline-block;
    padding: 2px 5px;
    border-radius: 3px;
    font-size: 0.7rem;
    margin-top: 2px;
}

.attendance-present {
    background-color: #d1e7dd;
    color: #0f5132;
}

.attendance-absent {
    background-color: #f8d7da;
    color: #842029;
}

.attendance-no-record {
    background-color: #e2e3e5;
    color: #41464b;
}

/* Status Colors */
.day-status.text-info {
    color: #0dcaf0;
    font-weight: 500;
}

.day-status.text-success {
    color: #198754;
    font-weight: 500;
}

.day-status.text-danger {
    color: #dc3545;
    font-weight: 500;
}

.day-status.text-warning {
    color: #ffc107;
    font-weight: 500;
}

/* Time Indicator */
.time-indicator {
    font-size: 0.65rem;
    display: block;
    color: #6c757d;
}

/* Legend Section */
.schedule-legend {
    margin-top: 15px;
    padding: 10px;
    background-color: #f8f9fa;
    border-radius: 5px;
}

.legend-item {
    display: inline-block;
    margin-right: 15px;
}

.legend-indicator {
    display: inline-block;
    width: 15px;
    height: 15px;
    margin-right: 5px;
    vertical-align: middle;
    border-radius: 3px;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .content-header {
        flex-direction: column;
        gap: 10px;
    }
    
    .weekly-schedule th, 
    .weekly-schedule td {
        font-size: 0.8rem;
    }
    
    .shift-info {
        font-size: 0.7rem;
    }
}
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../layouts/sidebar.php' ?>
<div class="content">
    <div class="content-header d-flex justify-content-between align-items-center flex-wrap">
        <h3 class="mb-0">Employee Weekly Schedules</h3>
        <div>
            <input type="week" id="weekPicker" class="form-control" style="width: 200px; display: inline-block;">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add_schedule_modal">
                <i class="bi bi-plus"></i> Assign Schedule
            </button>
        </div>
    </div>
    
    <div class="content-body mt-3">
        <div class="table-responsive schedule-table">
            <table class="table table-bordered weekly-schedule">
                <thead class="table-dark">
                    <tr>
                        <th class="employee-header">Employee</th>
                        <?php 
                        // Get current week dates (Monday to Sunday)
                        $monday = date('Y-m-d', strtotime('monday this week'));
                        for ($i = 0; $i < 7; $i++): 
                            $day = date('Y-m-d', strtotime($monday . " +$i days"));
                            $dayName = date('D', strtotime($day));
                            $dayNum = date('j', strtotime($day));
                        ?>
                        <th class="day-header">
                            <?= $dayName ?><br>
                            <small><?= $dayNum ?></small>
                        </th>
                        <?php endfor; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($employees)): ?>
                        <tr>
                            <td colspan="8" class="text-center">No employees found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($employees as $employee): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($employee['full_name']) ?></strong>
                            </td>
                            <?php 
                            for ($i = 0; $i < 7; $i++): 
                                $day = date('Y-m-d', strtotime($monday . " +$i days"));
                                $hasShift = false;
                            ?>
                            <td class="shift-cell">
                                <?php foreach($schedules as $schedule): ?>
                                    <?php if($schedule['employee_id'] == $employee['employee_id'] && $schedule['date'] == $day): ?>
                                        <div class="shift-info">
    <strong><?= htmlspecialchars($schedule['shift_name']) ?></strong><br>
    <?= date('g:i A', strtotime($schedule['start_time'])) ?> - 
    <?= date('g:i A', strtotime($schedule['end_time'])) ?>
    <div class="shift-actions mt-1">
        <button type="button" class="btn btn-sm btn-outline-primary edit-shift-btn" 
                data-schedule-id="<?= $schedule['schedule_id'] ?>"
                data-employee-id="<?= $schedule['employee_id'] ?>"
                data-shift-id="<?= $schedule['shift_id'] ?>"
                data-date="<?= $schedule['date'] ?>">
            <i class="bi bi-pencil"></i>
        </button>
        <button type="button" class="btn btn-sm btn-outline-danger delete-shift-btn"
                data-schedule-id="<?= $schedule['schedule_id'] ?>">
            <i class="bi bi-trash"></i>
        </button>
    </div>
</div>
                                        <?php $hasShift = true; ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <?php if(!$hasShift): ?>
                                    <div class="no-shift">No shift</div>
                                <?php endif; ?>
                            </td>
                            <?php endfor; ?>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="schedule-legend">
    <h6>Legend:</h6>
    <div class="legend-item">
        <span class="legend-indicator" style="background-color: #d1e7dd;"></span>
        <span>Present</span>
    </div>
    <div class="legend-item">
        <span class="legend-indicator" style="background-color: #f8d7da;"></span>
        <span>Absent</span>
    </div>
    <div class="legend-item">
        <span class="legend-indicator" style="background-color: #e2e3e5;"></span>
        <span>Pending</span>
    </div>
    <div class="legend-item">
        <span class="legend-indicator" style="background-color: #f8d7da; opacity: 0.7;"></span>
        <span>On Leave</span>
    </div>
</div>
    </div>

    <!-- Add Schedule Modal -->
    <div class="modal fade" id="add_schedule_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="scheduleForm" class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Assign Schedule</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Employee</label>
                            <select name="employee_id" id="employee_select" class="form-select" required>
                                <option value="">Select Employee</option>
                                <?php foreach($employees as $emp): ?>
                                    <option value="<?= $emp['employee_id'] ?>"><?= htmlspecialchars($emp['full_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Week Start Date</label>
                            <input type="date" name="week_start" id="week_start" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="table-responsive mt-3">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Day</th>
                                    <th>Date</th>
                                    <th>Assign Shift</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                    foreach ($daysOfWeek as $index => $day):
                                ?>
                                <tr class="schedule-day" data-day-index="<?= $index ?>">
                                    <td><?= $day ?></td>
                                    <td class="day-date"></td>
                                    <td>
                                        <select name="shifts[<?= $index ?>]" class="form-select shift-select">
                                            <option value="">No Shift</option>
                                            <?php foreach($shifts as $shift): ?>
                                                <option value="<?= $shift['shift_id'] ?>">
                                                    <?= $shift['shift_name'] ?> 
                                                    (<?= date('g:i A', strtotime($shift['start_time'])) ?> - 
                                                    <?= date('g:i A', strtotime($shift['end_time'])) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td class="day-status"></td>
                                </tr>
                                
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div id="formErrors" class="alert alert-danger d-none mt-3">
                        <h5 class="alert-heading">Schedule Conflicts</h5>
                        <ul id="errorList"></ul>
                    </div>
                    
                    <div id="successMessage" class="alert alert-success d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="submitBtn" class="btn btn-primary">Save Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Schedule Modal -->
<div class="modal fade" id="edit_schedule_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editScheduleForm" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Edit Schedule</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="schedule_id" id="edit_schedule_id">
                <input type="hidden" name="action" value="update_schedule">
                
                <div class="mb-3">
                    <label class="form-label">Employee</label>
                    <select name="employee_id" id="edit_employee_select" class="form-select" required>
                        <option value="">Select Employee</option>
                        <?php foreach($employees as $emp): ?>
                            <option value="<?= $emp['employee_id'] ?>"><?= htmlspecialchars($emp['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" id="edit_date" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Shift</label>
                    <select name="shift_id" id="edit_shift_select" class="form-select" required>
                        <option value="">Select Shift</option>
                        <?php foreach($shifts as $shift): ?>
                            <option value="<?= $shift['shift_id'] ?>">
                                <?= $shift['shift_name'] ?> 
                                (<?= date('g:i A', strtotime($shift['start_time'])) ?> - 
                                <?= date('g:i A', strtotime($shift['end_time'])) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div id="editFormErrors" class="alert alert-danger d-none mt-3">
                    <ul id="editErrorList"></ul>
                </div>
                
                <div id="editSuccessMessage" class="alert alert-success d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" id="editSubmitBtn" class="btn btn-primary">Update Schedule</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="delete_schedule_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Delete Schedule</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this schedule?</p>
                <p>This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirmDeleteBtn" class="btn btn-danger" data-schedule-id="">Delete</button>
            </div>
        </div>
    </div>
</div>


<!-- Toast Notification -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="leaveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-danger text-white">
            <strong class="me-auto">Schedule Conflict</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="toastMessage">
            <!-- Message will be inserted here -->
        </div>
    </div>
</div>

<script src="../../public/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const employeeSelect = document.getElementById("employee_select");
    const weekStartInput = document.getElementById("week_start");
    const weekPicker = document.getElementById("weekPicker");
    const scheduleForm = document.getElementById("scheduleForm");
    const leaveToast = new bootstrap.Toast(document.getElementById('leaveToast'));
    const formErrors = document.getElementById("formErrors");
    
    // Store leave dates and holidays
    let leaveDates = [];
    let holidays = [];
    
    // Format date as YYYY-MM-DD
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    
    // Calculate dates for the week
    function calculateWeekDates(startDate) {
        const dates = [];
        const start = new Date(startDate);
        
        for (let i = 0; i < 7; i++) {
            const date = new Date(start);
            date.setDate(start.getDate() + i);
            dates.push(date);
        }
        
        return dates;
    }
    
    // Update date cells in the table
    function updateDateCells(startDate) {
        const weekDates = calculateWeekDates(startDate);
        const dayRows = document.querySelectorAll('.schedule-day');
        
        dayRows.forEach((row, index) => {
            const dateCell = row.querySelector('.day-date');
            const statusCell = row.querySelector('.day-status');
            const date = weekDates[index];
            
            dateCell.textContent = formatDate(date);
            dateCell.dataset.date = formatDate(date);
            
            // Reset status
            statusCell.textContent = '';
            statusCell.className = 'day-status';
            
            // Check if date is in leaveDates
            if (leaveDates.includes(formatDate(date))) {
                statusCell.textContent = 'On Leave';
                statusCell.classList.add('text-danger', 'leave-day');
            }
            
            // Check if date is weekend (Saturday/Sunday) or holiday
            if (date.getDay() === 0 || date.getDay() === 6) {
                statusCell.textContent = 'Weekend';
                statusCell.classList.add('text-muted');
            } else if (holidays.includes(formatDate(date))) {
                statusCell.textContent = 'Holiday';
                statusCell.classList.add('text-warning');
            }
        });
    }
    
    // Update the weekly schedule view based on selected week
    async function updateWeeklyScheduleView(weekStartDate) {
        try {
            console.log("Updating schedule for week starting:", weekStartDate);
            
            // Calculate Monday of the selected week
            const monday = new Date(weekStartDate);
            monday.setDate(monday.getDate() - (monday.getDay() || 7) + 1);
            
            // Update table headers with new dates
            updateTableHeaders(monday);
            
            const url = `get_weekly_schedules.php?week_start=${formatDate(monday)}`;
            console.log("Fetching from:", url);

            const response = await fetch(url);
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error("Server response not OK:", errorText);
                throw new Error(`Server error: ${response.status} ${response.statusText}`);
            }
            
            const data = await response.json();
            console.log("Received data:", data);
            
            if (!data.success) {
                throw new Error(data.error || 'Invalid response from server');
            }
            
            updateScheduleTable(data, monday);
            
            // Set up event handlers for the newly created edit and delete buttons
            setupButtonEventHandlers();
            
        } catch (error) {
            console.error('Detailed error:', error);
            showError(`Failed to load schedule data: ${error.message}`);
        }
    }

    // Function to update table headers with new dates
    function updateTableHeaders(monday) {
        const dayHeaders = document.querySelectorAll('.weekly-schedule th.day-header');
        
        for (let i = 0; i < 7; i++) {
            const date = new Date(monday);
            date.setDate(monday.getDate() + i);
            
            const dayName = date.toLocaleDateString('en-US', { weekday: 'short' });
            const dayNum = date.getDate();
            
            // Update the header content
            dayHeaders[i].innerHTML = `${dayName}<br><small>${dayNum}</small>`;
        }
    }

    // Update the schedule table with new data
    function updateScheduleTable(data, monday) {
        const tbody = document.querySelector('.weekly-schedule tbody');
        
        if (data.employees.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center">No employees found</td></tr>';
            return;
        }
        
        // Process leave data
        const leaveDatesMap = new Map();
        
        if (data.leaves && data.leaves.length > 0) {
            data.leaves.forEach(leave => {
                const employeeId = String(leave.employee_id);
                const startDate = new Date(leave.start_date);
                const endDate = new Date(leave.end_date);
                
                const currentDate = new Date(startDate);
                while (currentDate <= endDate) {
                    const dateKey = `${employeeId}_${formatDate(currentDate)}`;
                    leaveDatesMap.set(dateKey, true);
                    currentDate.setDate(currentDate.getDate() + 1);
                }
            });
        }
        
        // Create a mapping between qr_employee_id and employee_id
        const employeeIdMap = new Map();
        data.employees.forEach(employee => {
            if (employee.qr_employee_id) {
                employeeIdMap.set(String(employee.qr_employee_id), String(employee.employee_id));
            }
        });
        
        // Process attendance data with proper ID mapping
        const attendanceMap = new Map();
        
        if (data.attendance && data.attendance.length > 0) {
            data.attendance.forEach(record => {
                const qrEmployeeId = String(record.qr_employee_id);
                // Look up the corresponding employee_id using the mapping
                // If no mapping exists, fall back to the qr_employee_id
                const employeeId = employeeIdMap.get(qrEmployeeId) || qrEmployeeId;
                const date = record.date;
                const timeIn = record.time_in;
                const timeOut = record.time_out;
                
                const dateKey = `${employeeId}_${date}`;
                console.log(`Adding attendance record: ${dateKey} (mapped from QR ID: ${qrEmployeeId})`);
                
                attendanceMap.set(dateKey, {
                    present: timeIn !== null,
                    timeIn: timeIn,
                    timeOut: timeOut
                });
            });
        }
        
        // Debug log the attendance map keys
        console.log("All attendance map keys after mapping:");
        for (const key of attendanceMap.keys()) {
            console.log(key);
        }
        
        let html = '';
        
        data.employees.forEach(employee => {
            html += `<tr>
                <td><strong>${escapeHtml(employee.full_name)}</strong></td>`;
            
            for (let i = 0; i < 7; i++) {
                const date = new Date(monday);
                date.setDate(monday.getDate() + i);
                const dateStr = formatDate(date);
                
                // Find schedules for this employee on this date
                const shifts = data.schedules.filter(s => 
                    String(s.employee_id) == String(employee.employee_id) && s.date == dateStr
                );
                
                // Check if employee is on leave for this date
                const leaveKey = `${String(employee.employee_id)}_${dateStr}`;
                const isOnLeave = leaveDatesMap.has(leaveKey);
                
                // Check attendance status with proper employee ID
                const attendanceKey = `${String(employee.employee_id)}_${dateStr}`;
                console.log(`Looking up attendance for ${attendanceKey}`);
                
                const attendanceRecord = attendanceMap.get(attendanceKey);
                
                html += `<td class="shift-cell">`;
                
                if (isOnLeave) {
                    html += `<div class="on-leave-indicator">On Leave</div>`;
                    
                    if (shifts.length > 0) {
                        shifts.forEach(shift => {
                            html += `<div class="shift-info" style="text-decoration: line-through;">
                                <strong>${escapeHtml(shift.shift_name)}</strong><br>
                                ${formatTime(shift.start_time)} - ${formatTime(shift.end_time)}
                            </div>`;
                        });
                    }
                } else if (shifts.length > 0) {
                    shifts.forEach(shift => {
                        html += `<div class="shift-info">
                            <strong>${escapeHtml(shift.shift_name)}</strong><br>
                            ${formatTime(shift.start_time)} - ${formatTime(shift.end_time)}
                            <div class="shift-actions mt-1">
                                <button type="button" class="btn btn-sm btn-outline-primary edit-shift-btn" 
                                        data-schedule-id="${shift.schedule_id}"
                                        data-employee-id="${shift.employee_id}"
                                        data-shift-id="${shift.shift_id}"
                                        data-date="${shift.date}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger delete-shift-btn"
                                        data-schedule-id="${shift.schedule_id}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>`;
                        
                        // Add attendance status for this shift
                        const currentDate = new Date();
                        const shiftDate = new Date(dateStr);
                        
                        if (attendanceRecord !== undefined) {
                            // Attendance record exists
                            if (attendanceRecord.present) {
                                html += `<div class="attendance-status attendance-present">Present</div>`;
                                html += `<div class="time-indicator">In: ${formatTime(attendanceRecord.timeIn)}</div>`;
                                
                                if (attendanceRecord.timeOut) {
                                    html += `<div class="time-indicator">Out: ${formatTime(attendanceRecord.timeOut)}</div>`;
                                }
                            } else {
                                html += `<div class="attendance-status attendance-absent">Absent</div>`;
                            }
                        } else {
                            // No attendance record
                            if (shiftDate < currentDate && shiftDate.toDateString() !== currentDate.toDateString()) {
                                html += `<div class="attendance-status attendance-absent">Absent</div>`;
                            } else if (shiftDate.toDateString() === currentDate.toDateString()) {
                                html += `<div class="attendance-status attendance-no-record">Pending</div>`;
                            } else {
                                // Future date
                                html += `<div class="attendance-status attendance-no-record">Upcoming</div>`;
                            }
                        }
                    });
                } else {
                    html += `<div class="no-shift">No shift</div>`;
                }
                
                html += `</td>`;
            }
            
            html += `</tr>`;
        });
        
        tbody.innerHTML = html;
    }

    // Setup event handlers for edit and delete buttons
    function setupButtonEventHandlers() {
        // Edit buttons
        document.querySelectorAll('.edit-shift-btn').forEach(button => {
            button.addEventListener('click', function() {
                const scheduleId = this.dataset.scheduleId;
                const employeeId = this.dataset.employeeId;
                const shiftId = this.dataset.shiftId;
                const date = this.dataset.date;
                
                // Populate the edit form
                document.getElementById('edit_schedule_id').value = scheduleId;
                document.getElementById('edit_employee_select').value = employeeId;
                document.getElementById('edit_date').value = date;
                document.getElementById('edit_shift_select').value = shiftId;
                
                // Show the modal
                const editModal = new bootstrap.Modal(document.getElementById('edit_schedule_modal'));
                editModal.show();
            });
        });
        
        // Delete buttons
        document.querySelectorAll('.delete-shift-btn').forEach(button => {
            button.addEventListener('click', function() {
                const scheduleId = this.dataset.scheduleId;
                
                // Set the schedule ID in the confirmation button
                document.getElementById('confirmDeleteBtn').dataset.scheduleId = scheduleId;
                
                // Show the modal
                const deleteModal = new bootstrap.Modal(document.getElementById('delete_schedule_modal'));
                deleteModal.show();
            });
        });
    }

    // Helper functions
    function escapeHtml(str) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    function formatTime(timeStr) {
        const time = new Date(`1970-01-01T${timeStr}`);
        return time.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
    }
    
    // Display error message
    function showError(message) {
        document.getElementById('toastMessage').textContent = message;
        leaveToast.show();
    }
    
    // Fetch leave dates from server
    async function fetchLeaveDates(employeeId) {
        try {
            const response = await fetch(`get_leave_dates.php?employee_id=${employeeId}`);
            if (!response.ok) throw new Error('Failed to fetch leave dates');
            return await response.json();
        } catch (error) {
            console.error('Error fetching leave dates:', error);
            return [];
        }
    }
    
    // Fetch holidays from server
    async function fetchHolidays() {
        try {
            const response = await fetch('get_holidays.php');
            if (!response.ok) throw new Error('Failed to fetch holidays');
            return await response.json();
        } catch (error) {
            console.error('Error fetching holidays:', error);
            return [];
        }
    }
    
    // Check for schedule conflicts
    async function checkScheduleConflicts(employeeId, dates) {
        try {
            const response = await fetch(`check_schedule_conflicts.php?employee_id=${employeeId}&dates=${dates.join(',')}`);
            if (!response.ok) throw new Error('Failed to check schedule conflicts');
            return await response.json();
        } catch (error) {
            console.error('Error checking schedule conflicts:', error);
            return [];
        }
    }
    
    // Validate shift change
    async function validateShiftChange(selectElement) {
        const row = selectElement.closest('.schedule-day');
        const dateCell = row.querySelector('.day-date');
        const statusCell = row.querySelector('.day-status');
        
        if (!employeeSelect.value || !weekStartInput.value || !selectElement.value) {
            return;
        }
        
        // Show validating status
        statusCell.textContent = 'Checking availability...';
        statusCell.className = 'day-status text-info';
        
        try {
            const response = await fetch(`check_shift_availability.php?employee_id=${employeeSelect.value}&shift_id=${selectElement.value}&date=${dateCell.dataset.date}`);
            
            if (!response.ok) throw new Error('Validation failed');
            
            const result = await response.json();
            
            if (result.available) {
                statusCell.textContent = 'Available';
                statusCell.className = 'day-status text-success';
            } else {
                statusCell.textContent = result.message || 'Not available';
                statusCell.className = 'day-status text-danger';
            }
        } catch (error) {
            console.error('Validation error:', error);
            statusCell.textContent = 'Validation error';
            statusCell.className = 'day-status text-warning';
        }
    }
    
    // Validate form via AJAX
    async function validateFormViaAJAX(formData) {
        try {
            const response = await fetch('../../controllers/scheduleController.php', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) throw new Error('Network response was not ok');
            
            return await response.json();
        } catch (error) {
            console.error('Error during validation:', error);
            return { success: false, errors: ['An error occurred during validation'] };
        }
    }
    
    // Handle form submission
    async function handleFormSubmit(e) {
        e.preventDefault();
        
        // Disable submit button
        document.getElementById('submitBtn').disabled = true;
        
        // Hide previous messages
        document.getElementById('formErrors').classList.add('d-none');
        document.getElementById('successMessage').classList.add('d-none');
        
        // Collect form data
        const formData = new FormData(document.getElementById('scheduleForm'));
        formData.append('validate_only', 'true');
        
        // Perform AJAX validation
        const result = await validateFormViaAJAX(formData);
        
        // Re-enable submit button
        document.getElementById('submitBtn').disabled = false;
        
        if (result.success) {
            // If validation passed, submit the actual form
            await submitFinalForm(formData);
        } else {
            // Show errors in the modal
            const errorList = document.getElementById('errorList');
            errorList.innerHTML = '';
            
            result.errors.forEach(error => {
                const li = document.createElement('li');
                li.textContent = error;
                errorList.appendChild(li);
            });
            
            document.getElementById('formErrors').classList.remove('d-none');
            
            // Scroll to errors
            document.getElementById('formErrors').scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }
    }
    
    // Submit final form
    async function submitFinalForm(formData) {
        formData.delete('validate_only');
        
        try {
            document.getElementById('submitBtn').disabled = true;
            
            const response = await fetch('../../controllers/scheduleController.php', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) throw new Error('Network response was not ok');
            
            const result = await response.json();
            
            if (result.success) {
                // Show success message
                const successMessage = document.getElementById('successMessage');
                successMessage.textContent = 'Schedule assigned successfully!';
                successMessage.classList.remove('d-none');
                
                // Disable form and close after delay
                document.getElementById('submitBtn').disabled = true;
                setTimeout(() => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('add_schedule_modal'));
                    modal.hide();
                    location.reload();
                }, 1500);
            } else {
                // Show errors if final submission failed
                const errorList = document.getElementById('errorList');
                errorList.innerHTML = '';
                
                result.errors.forEach(error => {
                    const li = document.createElement('li');
                    li.textContent = error;
                    errorList.appendChild(li);
                });
                
                document.getElementById('formErrors').classList.remove('d-none');
            }
        } catch (error) {
            console.error('Error during submission:', error);
            
            const errorList = document.getElementById('errorList');
            errorList.innerHTML = '<li>An error occurred during submission. Please try again.</li>';
            document.getElementById('formErrors').classList.remove('d-none');
        } finally {
            document.getElementById('submitBtn').disabled = false;
        }
    }
    
    function getDateOfISOWeek(week, year) {
        const simple = new Date(year, 0, 1 + (week - 1) * 7);
        const dow = simple.getDay();
        const ISOweekStart = simple;
        if (dow <= 4)
            ISOweekStart.setDate(simple.getDate() - simple.getDay() + 1);
        else
            ISOweekStart.setDate(simple.getDate() + 8 - simple.getDay());
        return ISOweekStart;
    }

    // Event listeners
    employeeSelect.addEventListener('change', async function() {
        if (this.value && weekStartInput.value) {
            leaveDates = await fetchLeaveDates(this.value);
            updateDateCells(weekStartInput.value);
        }
    });
    
    weekStartInput.addEventListener('change', async function() {
        if (this.value && employeeSelect.value) {
            leaveDates = await fetchLeaveDates(employeeSelect.value);
            updateDateCells(this.value);
        }
    });
    
    weekPicker.addEventListener('change', function() {
        const [year, week] = this.value.split('-W');
        const monday = getDateOfISOWeek(week, year);
        updateWeeklyScheduleView(monday);
    });

    // Initialize on page load
    const today = new Date();
    
    // Set default week picker with current week
    const year = today.getFullYear();
    const weekNum = getWeekNumber(today)[1];
    weekPicker.value = `${year}-W${String(weekNum).padStart(2, '0')}`;
    
    // Set default week start to next Monday if empty
    if (!weekStartInput.value) {
        const nextMonday = new Date(today);
        nextMonday.setDate(today.getDate() + (1 + 7 - today.getDay()) % 7);
        weekStartInput.valueAsDate = nextMonday;
    }
    
    // Initial update of weekly schedule
    updateWeeklyScheduleView(today);
    
    document.querySelectorAll('.shift-select').forEach(select => {
        select.addEventListener('change', function() {
            validateShiftChange(this);
        });
    });
    
    scheduleForm.addEventListener('submit', handleFormSubmit);
    
    // Initialize holidays
    fetchHolidays().then(data => {
        holidays = data;
        if (weekStartInput.value) {
            updateDateCells(weekStartInput.value);
        }
    });
    
    // Helper function to get week number
    function getWeekNumber(d) {
        d = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()));
        d.setUTCDate(d.getUTCDate() + 4 - (d.getUTCDay()||7));
        const yearStart = new Date(Date.UTC(d.getUTCFullYear(),0,1));
        const weekNo = Math.ceil(( ( (d - yearStart) / 86400000) + 1)/7);
        return [d.getUTCFullYear(), weekNo];
    }

    // Handle Edit Form Submit
    document.getElementById('editScheduleForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Disable submit button
        document.getElementById('editSubmitBtn').disabled = true;
        
        // Hide previous messages
        document.getElementById('editFormErrors').classList.add('d-none');
        document.getElementById('editSuccessMessage').classList.add('d-none');
        
        // Collect form data
        const formData = new FormData(this);
        
        try {
            const response = await fetch('../../controllers/scheduleController.php', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) throw new Error('Network response was not ok');
            
            const result = await response.json();
            
            if (result.success) {
                // Show success message
                const successMessage = document.getElementById('editSuccessMessage');
                successMessage.textContent = 'Schedule updated successfully!';
                successMessage.classList.remove('d-none');
                
                // Reload the page after delay
                setTimeout(() => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('edit_schedule_modal'));
                    modal.hide();
                    location.reload();
                }, 1500);
            } else {
                // Show errors if update failed
                const errorList = document.getElementById('editErrorList');
                errorList.innerHTML = '';
                
                result.errors.forEach(error => {
                    const li = document.createElement('li');
                    li.textContent = error;
                    errorList.appendChild(li);
                });
                
                document.getElementById('editFormErrors').classList.remove('d-none');
            }
        } catch (error) {
            console.error('Error during update:', error);
            
            const errorList = document.getElementById('editErrorList');
            errorList.innerHTML = '<li>An error occurred during update. Please try again.</li>';
            document.getElementById('editFormErrors').classList.remove('d-none');
        } finally {
            document.getElementById('editSubmitBtn').disabled = false;
        }
    });
    
    // Handle Delete Confirmation
    document.getElementById('confirmDeleteBtn').addEventListener('click', async function() {
    const scheduleId = this.dataset.scheduleId;
    this.disabled = true;
    
    try {
        const formData = new FormData();
        formData.append('schedule_id', scheduleId);
        formData.append('action', 'delete_schedule');
        
        const response = await fetch('../../controllers/scheduleController.php', {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error("Server error response:", errorText);
            throw new Error(`Server responded with status: ${response.status}`);
        }
        
        const result = await response.json();
        console.log("Delete response:", result);
        
        if (result.success) {
            // Close modal and reload
            bootstrap.Modal.getInstance(document.getElementById('delete_schedule_modal')).hide();
            
            // Show toast confirmation
            document.getElementById('toastMessage').textContent = 'Schedule deleted successfully!';
            leaveToast.show();
            
            // Reload after delay
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            throw new Error(result.error || 'Failed to delete schedule');
        }
    } catch (error) {
        console.error('Error during deletion:', error);
        document.getElementById('toastMessage').textContent = 'Error: ' + error.message;
        leaveToast.show();
    } finally {
        this.disabled = false;
    }
});
    
    // Initial setup of any buttons that might be in the HTML on page load
    setupButtonEventHandlers();
});
</script>
</body>
</html>