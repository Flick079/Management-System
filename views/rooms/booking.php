
<?php

require_once __DIR__ . '/../../middleware/verify.php';
require_once __DIR__ . '/../../controllers/bookingController.php';
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
    <title>Room Booking</title>
    <style>
        /* Custom styling for room display */
.room-badge {
    display: inline-block;
    min-width: 30px;
    text-align: center;
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 0.8rem;
    margin-right: 5px;
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
}
/* Add to your existing styles */
.status-badge {
    font-size: 0.8rem;
    padding: 0.35em 0.65em;
    font-weight: 500;
    border-radius: 0.25rem;
}

.bg-confirmed { background-color: #28a745; }
.bg-pending { background-color: #ffc107; color: #212529; }
.bg-ongoing { background-color: #007bff; }
.bg-completed { background-color: #17a2b8; }
.bg-cancelled { background-color: #dc3545; }
.bg-no-show { background-color: #6c757d; }
    </style>
</head>
<body>
    <?php require_once '../layouts/sidebar.php' ?>
    <div class="content">
        <header class="d-flex justify-content-between">
            <h3>Room Booking</h3>
            <div class="btns">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#new_booking_modal">
                    <i class="bi bi-plus"></i>
                    New Booking
                </button>
            </div>
        </header>
        <main>
            <!-- Booking search form -->
<!-- In the search-container div, add a status filter dropdown -->
<div class="search-container border rounded p-3 mb-4">
    <h4>Search Bookings</h4>
    <form action="" method="GET" class="row g-3">
        <div class="col-md-3">
            <label for="search_term" class="form-label">Search</label>
            <input type="text" class="form-control" id="search_term" name="search_term" 
                   placeholder="Booking ID, Name, or Phone">
        </div>
        <div class="col-md-2">
            <label for="status" class="form-label">Status</label>
            <select class="form-control" id="status" name="status">
    <option value="">All Statuses</option>
    <option value="Confirmed" <?= ($_GET['status'] ?? '') === 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
    <option value="Pending" <?= ($_GET['status'] ?? '') === 'Pending' ? 'selected' : '' ?>>Pending</option>
    <option value="Ongoing" <?= ($_GET['status'] ?? '') === 'Ongoing' ? 'selected' : '' ?>>Ongoing</option>
    <option value="Completed" <?= ($_GET['status'] ?? '') === 'Completed' ? 'selected' : '' ?>>Completed</option>
    <option value="Cancelled" <?= ($_GET['status'] ?? '') === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
    <option value="No show" <?= ($_GET['status'] ?? '') === 'No show' ? 'selected' : '' ?>>No show</option>
</select>
        </div>
        <div class="col-md-2">
            <label for="from_date" class="form-label">From Date</label>
            <input type="date" class="form-control" id="from_date" name="from_date" value="<?= htmlspecialchars($_GET['from_date'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <label for="to_date" class="form-label">To Date</label>
            <input type="date" class="form-control" id="to_date" name="to_date" value="<?= htmlspecialchars($_GET['to_date'] ?? '') ?>">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Search</button>
        </div>
        <div class="col-md-1 d-flex align-items-end">
            <button type="button" class="btn btn-secondary w-100" onclick="resetFilters()">Reset</button>
        </div>
    </form>
</div>


            <!-- booking modal -->

            <div class="modal fade" aria-labelledby="new_booking_modal" id="new_booking_modal" aria-hidden="true" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4>Book a room</h4>
                            <button class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="booking-form" method="POST">
                                <!-- personal details customer -->
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Full name</label>
                                    <input type="text" id="full_name" name="full_name" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="contact_number" class="form-label">Contact Number</label>
                                    <input type="number" id="contact_number" name="contact_number" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="age" class="form-label">Age</label>
                                    <input type="number" id="age" name="age" class="form-control">
                                </div>
                                <div class="mb-3">
    <label for="gender" class="form-label">Gender</label>
    <select name="gender" id="gender" class="form-control" required>
        <option value="">Select Gender</option>
        <option value="Male">Male</option>
        <option value="Female">Female</option>
        <option value="Other">Other</option>
    </select>
</div>
                                <!-- end personal details customer -->
                                <div class="mb-3">
                                    <label for="check_in_date" class="form-label">Check-in Date</label>
                                    <input type="date" id="check_in_date" name="check_in_date" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="check_out_date" class="form-label">Check-out Date</label>
                                    <input type="date" id="check_out_date" name="check_out_date" class="form-control">
                                </div>

<!-- Room Selection (Initially Hidden) -->
<div class="mb-3" id="room-selection" style="display: none;">
    <label for="room_id" class="form-label">Select Room</label>
    <select id="room_id" name="room_id" class="form-control">
        <option value="">Select a Room</option>
    </select>
</div>

<!-- These fields are hidden initially -->
<div id="room-details" style="display: none;">
    <div class="mb-3">
        <label class="form-label">Max Persons Allowed</label>
        <input type="text" id="max_person" class="form-control" readonly>
    </div>
    <div class="mb-3">
        <label class="form-label">Additional Persons</label>
        <div class="input-group">
            <button type="button" id="decrease_person" class="btn btn-danger">-</button>
            <input type="number" id="add_person" name="add_person" class="form-control text-center" value="0" min="0" readonly>
            <button type="button" id="increase_person" class="btn btn-success">+</button>
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label">Additional Fee per Person</label>
        <input type="text" id="additional_fee" class="form-control" readonly>
    </div>
</div>

<!-- Additional Room Section -->
<button type="button" id="add-room" class="btn btn-primary">+ Add Another Room</button>
<div id="additional-rooms"></div>

<!-- Total Cost Calculation -->
<hr>
<div class="mb-3">
    <label class="form-label">Total Cost</label>
    <input type="text" id="total_cost" class="form-control" readonly>
</div>
<div class="mb-3">
    <label class="form-label">Down Payment (50%)</label>
    <input type="text" id="down_payment" class="form-control" readonly>
</div>

<input type="hidden" id="nights" name="nights">
<input type="hidden" id="rooms_data" name="rooms_data">

<div id="error-message" style="color: red;"></div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    <button type="submit" class="btn btn-primary" id="submit-booking">Confirm Booking</button>
</div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>


            <!-- booking modal end -->

            <!-- Bookings list -->
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>    
                            <th>Customer</th>
                            <th>Room</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Total</th>
                            <th>Down Payment (50%)</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php require_once 'show_bookings.php' ?>
                    </tbody>
        </main>

        <!-- Cancel Booking Modal -->
<div class="modal fade" id="cancelBookingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="cancelBookingForm" method="POST" action="cancel_booking.php">
                <input type="hidden" id="cancelBookingId" name="booking_id">
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to cancel this booking?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <button type="submit" class="btn btn-danger">Yes, Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- No Show Modal -->
<div class="modal fade" id="noShowModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="noShowForm" method="POST" action="mark_no_show.php">
                <input type="hidden" id="noShowBookingId" name="booking_id">
                <div class="modal-header">
                    <h5 class="modal-title">Mark as No Show</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to mark this booking as No Show?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <button type="submit" class="btn btn-warning">Yes, Mark as No Show</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add this modal right before the closing </body> tag in booking.php -->
<!-- Additional Charges Modal -->
<div class="modal fade" id="additionalChargesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="additionalChargesForm" method="POST" action="complete_booking.php">
                <input type="hidden" id="bookingIdForCompletion" name="booking_id">
                <div class="modal-header">
                    <h5 class="modal-title">Complete Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                <label class="form-label">Do you want to finish the booking?</label>
                    <div class="mb-3" style="display: none;">

                        <div id="chargesContainer">
                            <div class="charge-item mb-2"style="display: none">
                                <div class="input-group">
                                    <select class="form-control charge-type" name="charge_types[]" required>
                                        <option value="">Select Charge Type</option>
                                        <option value="Extra Bed">Extra Bed (₱500)</option>
                                        <option value="Food/Beverage">Food/Beverage (₱300)</option>
                                        <option value="Room Damage">Room Damage (₱1000-5000)</option>
                                        <option value="Missing Item">Missing Item (₱500-2000)</option>
                                        <option value="Water Activity">Water Activity (₱800)</option>
                                    </select>
                                    <input type="number" class="form-control charge-amount" name="charge_amounts[]" placeholder="Amount" required>
                                    <button type="button" class="btn btn-danger remove-charge">×</button>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="addCharge" class="btn btn-sm btn-secondary mt-2">+ Add Another Charge</button>
                    </div>
                    <div class="mb-3" style="display: none;">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" placeholder="Any additional notes..."></textarea>
                    </div>
                    <div class="mb-3" style="display: none;">
                        <label class="form-label">Total Additional Charges</label>
                        <input type="text" id="totalAdditionalCharges" class="form-control" readonly value="₱0.00">
                    </div>
                    <div class="mb-3" style="display: none;">
                        <label class="form-label">Remaining Balance</label>
                        <input type="text" id="remainingBalance" class="form-control" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Complete Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>
    </div>



    <script>
document.addEventListener("DOMContentLoaded", function () {

    let checkInInput = document.querySelector("input[name='check_in_date']");
    let checkOutInput = document.querySelector("input[name='check_out_date']");
    
    function updateMinDates() {
      let today = new Date().toISOString().split("T")[0];
      checkInInput.setAttribute("min", today);
      
      let checkInValue = checkInInput.value;
      if (checkInValue) {
        checkOutInput.setAttribute("min", checkInValue);
      }
    }
    
    checkInInput.addEventListener("change", function() {
      checkOutInput.value = ""; // Reset check-out date when check-in changes
      updateMinDates();
    });

    updateMinDates();


    
    // Your existing variable declarations
    const checkIn = document.getElementById("check_in_date");
    const checkOut = document.getElementById("check_out_date");
    const roomSelection = document.getElementById("room-selection");
    const roomDetails = document.getElementById("room-details");
    const roomSelect = document.getElementById("room_id");
    const errorMessage = document.getElementById("error-message");
    const maxPersonField = document.getElementById("max_person");
    const additionalFeeField = document.getElementById("additional_fee");
    const addPersonInput = document.getElementById("add_person");
    const decreasePersonBtn = document.getElementById("decrease_person");
    const increasePersonBtn = document.getElementById("increase_person");
    const totalCostField = document.getElementById("total_cost");
    const downPaymentField = document.getElementById("down_payment");
    const addRoomBtn = document.getElementById("add-room");
    const additionalRoomsContainer = document.getElementById("additional-rooms");

    let selectedRooms = [];

    // 1. Add this date validation function
    function validateDates() {
        if (checkIn.value && checkOut.value) {
            const checkInDate = new Date(checkIn.value);
            const checkOutDate = new Date(checkOut.value);
            
            if (checkOutDate <= checkInDate) {
                errorMessage.textContent = "Check-out date must be after check-in date";
                return false;
            }
            errorMessage.textContent = "";
        }
        return true;
    }


    

    // 2. Keep your existing fetchAvailableRooms function but add room_type_id
    function fetchAvailableRooms() {
        if (checkIn.value && checkOut.value && validateDates()) {
            const formData = new FormData();
            formData.append("check_in_date", checkIn.value);
            formData.append("check_out_date", checkOut.value);

            fetch("fetch_available_rooms.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log(data.rooms);
                roomSelect.innerHTML = '<option value="">Select a Room</option>';
                if (data.success) {
                    data.rooms.forEach(room => {
                        let option = document.createElement("option");
                        option.value = room.room_id;
                        option.dataset.roomNumber = room.room_number;
                        option.dataset.name = room.name;
                        option.dataset.rate = room.rate;
                        option.dataset.maxPerson = room.max_person;
                        option.dataset.additionalFee = room.additional_fee;
                        option.dataset.roomTypeId = room.room_type_id; // Changed from roomtypeid to roomTypeId
                        option.textContent = `${room.room_number} - ${room.name} (₱${room.rate}/night)`;
                        roomSelect.appendChild(option);
                    });
                    roomSelection.style.display = "block";
                    errorMessage.textContent = "";
                } else {
                    roomSelection.style.display = "none";
                    errorMessage.textContent = data.error || "No rooms available for the selected dates.";
                }
            })
            .catch(error => {
                console.error("Error fetching rooms:", error);
                errorMessage.textContent = "Error fetching room data";
            });
        }
    }

    // 3. Add this nights calculation function
    function calculateNights(checkInDate, checkOutDate) {
        const oneDay = 24 * 60 * 60 * 1000;
        return Math.round(Math.abs((checkOutDate - checkInDate) / oneDay));
    }

    // 4. Update your calculateTotal function
    function calculateTotal() {
        if (checkIn.value && checkOut.value && validateDates()) {
            const nights = calculateNights(new Date(checkIn.value), new Date(checkOut.value));
            let total = 0;
            
            selectedRooms.forEach(room => {
                if (room) {
                    total += room.rate * nights;
                    if (room.additionalPersons > 0) {
                        total += room.additionalPersons * room.additionalFee * nights;
                    }
                }
            });

            totalCostField.value = `₱${total.toFixed(2)}`;
            downPaymentField.value = `₱${(total / 2).toFixed(2)}`;
            
            // Set the nights value in the hidden field
            document.getElementById("nights").value = nights;
        }
    }

    // Add this right after your calculateTotal() function
function updateRoomDropdowns() {
    // Update all additional room dropdowns
    document.querySelectorAll('.additional-room').forEach((dropdown, index) => {
        const currentValue = dropdown.value;
        const availableRooms = Array.from(roomSelect.options)
            .filter(option => option.value && 
                   !selectedRooms.some((r, i) => r && r.room_id === option.value && i !== index));
        
        dropdown.innerHTML = '<option value="">Select a Room</option>' + 
            availableRooms.map(option => 
                `<option value="${option.value}" 
                        ${option.value === currentValue ? 'selected' : ''}
                        data-room-number="${option.dataset.roomNumber}"
                        data-name="${option.dataset.name}"
                        data-rate="${option.dataset.rate}"
                        data-max-person="${option.dataset.maxPerson}"
                        data-additional-fee="${option.dataset.additionalFee}"
                        data-room-type-id="${option.dataset.roomTypeId}">
                    ${option.textContent}
                </option>`
            ).join('');
    });
}

    // 5. Update your roomSelect event listener
// Find your existing roomSelect event listener and modify it:
    roomSelect.addEventListener("change", function() {
    const selectedOption = roomSelect.options[roomSelect.selectedIndex];
    if (selectedOption.value) {
        maxPersonField.value = selectedOption.dataset.maxPerson || "";
        additionalFeeField.value = selectedOption.dataset.additionalFee || "";
        addPersonInput.value = 0;
        roomDetails.style.display = "block";

        selectedRooms = [{
            room_id: selectedOption.value,
            room_type_id: selectedOption.dataset.roomTypeId,
            rate: parseFloat(selectedOption.dataset.rate),
            additionalPersons: 0,
            additionalFee: parseFloat(selectedOption.dataset.additionalFee)
        }];
    } else {
        roomDetails.style.display = "none";
        selectedRooms = [];
    }
    calculateTotal();
    updateRoomDropdowns(); // Add this line
});

    // 6. Keep your existing increase/decrease person buttons code
    increasePersonBtn.addEventListener("click", function () {
        if (selectedRooms.length > 0) {
            const currentPersons = parseInt(addPersonInput.value) || 0;
            const maxPersons = parseInt(maxPersonField.value) || 0;
            if (currentPersons < maxPersons) {
                addPersonInput.value = currentPersons + 1;
                selectedRooms[0].additionalPersons = currentPersons + 1;
            }
            calculateTotal();
        }
    });

    decreasePersonBtn.addEventListener("click", function () {
        if (selectedRooms.length > 0) {
            const currentPersons = parseInt(addPersonInput.value) || 0;
            if (currentPersons > 0) {
                addPersonInput.value = currentPersons - 1;
                selectedRooms[0].additionalPersons = currentPersons - 1;
            }
            calculateTotal();
        }
    });

    // 7. Update your addRoomBtn event listener
    addRoomBtn.addEventListener("click", function() {
    const additionalRoomDiv = document.createElement("div");
    additionalRoomDiv.classList.add("mb-3", "room-entry");
    const roomIndex = selectedRooms.length;
    
    // Create a new select element with available rooms only
    const availableRooms = Array.from(roomSelect.options)
        .filter(option => option.value && !selectedRooms.some(r => r && r.room_id === option.value));
    
    const optionsHTML = availableRooms.map(option => 
        `<option value="${option.value}" 
                data-room-number="${option.dataset.roomNumber}"
                data-name="${option.dataset.name}"
                data-rate="${option.dataset.rate}"
                data-max-person="${option.dataset.maxPerson}"
                data-additional-fee="${option.dataset.additionalFee}"
                data-room-type-id="${option.dataset.roomTypeId}">
            ${option.textContent}
        </option>`
    ).join('');
    
    additionalRoomDiv.innerHTML = `
        <hr>
    <div class="d-flex justify-content-between align-items-center">
        <label class="form-label">Select Additional Room</label>
        <button type="button" class="btn btn-danger btn-sm remove-room">
            <i class="bi bi-trash"></i> Remove
        </button>
    </div>
        <select class="form-control additional-room">
            <option value="">Select a Room</option>
            ${optionsHTML}
        </select>
        <div class="room-details" style="display: none;">
            <div class="mb-3">
                <label class="form-label">Max Persons Allowed</label>
                <input type="text" class="form-control max-person" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Additional Persons</label>
                <div class="input-group">
                    <button type="button" class="btn btn-danger decrease-person">-</button>
                    <input type="number" class="form-control text-center additional-persons" value="0" min="0" readonly>
                    <button type="button" class="btn btn-success increase-person">+</button>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Additional Fee per Person</label>
                <input type="text" class="form-control additional-fee" readonly>
            </div>
        </div>
    `;

    // Add remove button handler
    const removeBtn = additionalRoomDiv.querySelector(".remove-room");
    removeBtn.addEventListener("click", function() {
        // Remove from DOM
        additionalRoomDiv.remove();
        
        // Remove from selectedRooms array
        selectedRooms.splice(roomIndex, 1);
        
        // Re-index remaining rooms
        document.querySelectorAll(".room-entry").forEach((roomDiv, index) => {
            const roomSelect = roomDiv.querySelector(".additional-room");
            roomSelect.dataset.roomIndex = index;
        });
        
        calculateTotal();
        updateRoomDropdowns();
    });
    
    
    additionalRoomsContainer.appendChild(additionalRoomDiv);
        
        const newRoomSelect = additionalRoomDiv.querySelector(".additional-room");
        const newRoomDetails = additionalRoomDiv.querySelector(".room-details");
        const newMaxPerson = additionalRoomDiv.querySelector(".max-person");
        const newAdditionalFee = additionalRoomDiv.querySelector(".additional-fee");
        const newAddPerson = additionalRoomDiv.querySelector(".additional-persons");
        const newDecreaseBtn = additionalRoomDiv.querySelector(".decrease-person");
        const newIncreaseBtn = additionalRoomDiv.querySelector(".increase-person");
        
        

        newRoomSelect.addEventListener("change", function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                newMaxPerson.value = selectedOption.dataset.maxPerson || "";
                newAdditionalFee.value = selectedOption.dataset.additionalFee || "";
                newAddPerson.value = 0;
                newRoomDetails.style.display = "block";
                
                selectedRooms[roomIndex] = {
                    room_id: selectedOption.value,
                    room_type_id: selectedOption.dataset.roomTypeId, // Fixed: Changed from roomtypeid to roomTypeId
                    rate: parseFloat(selectedOption.dataset.rate),
                    additionalPersons: 0,
                    additionalFee: parseFloat(selectedOption.dataset.additionalFee)
                };
            } else {
                newRoomDetails.style.display = "none";
                selectedRooms[roomIndex] = null;
            }
            calculateTotal();
        });
            // Add event listeners for the +/- buttons
    newIncreaseBtn.addEventListener("click", function() {
        if (selectedRooms[roomIndex]) {
            const currentPersons = parseInt(newAddPerson.value) || 0;
            const maxPersons = parseInt(newMaxPerson.value) || 0;
            if (currentPersons < maxPersons) {
                newAddPerson.value = currentPersons + 1;
                selectedRooms[roomIndex].additionalPersons = currentPersons + 1;
                calculateTotal();
            }
        }
    });
    
    newDecreaseBtn.addEventListener("click", function() {
        if (selectedRooms[roomIndex]) {
            const currentPersons = parseInt(newAddPerson.value) || 0;
            if (currentPersons > 0) {
                newAddPerson.value = currentPersons - 1;
                selectedRooms[roomIndex].additionalPersons = currentPersons - 1;
                calculateTotal();
            }
        }
    });

        
        // Keep your existing +/- button event listeners
    });

    // 8. Add this form submission handler at the bottom
    document.querySelector('#new_booking_modal form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate form
        if (!validateDates() || selectedRooms.length === 0) {
            errorMessage.textContent = "Please complete all required fields";
            return;
        }

        const nights = calculateNights(new Date(checkIn.value), new Date(checkOut.value));
        const roomsData = selectedRooms.filter(room => room !== null).map(room => ({
            room_id: room.room_id,
            room_type_id: room.room_type_id,
            booked_rate: room.rate,
            additional_persons: room.additionalPersons,
            additional_fees: room.additionalPersons * room.additionalFee * nights
        }));

        // Add this line to update the hidden input field
        document.getElementById('rooms_data').value = JSON.stringify(roomsData);

        const formData = new FormData(this);
        formData.append('rooms', JSON.stringify(roomsData));
        formData.append('total_cost', totalCostField.value.replace(/[^0-9.]/g, ''));
        formData.append('down_payment', downPaymentField.value.replace(/[^0-9.]/g, ''));
        formData.append('nights', nights);

        fetch('process_booking.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`Booking created! ID: ${data.booking_id}`);
                location.reload();
            } else {
                errorMessage.textContent = data.message || "Booking failed";
            }
        })
        .catch(error => {
            console.error('Error:', error);
            errorMessage.textContent = "Booking submission failed";
        });
    });

    // 9. Keep your existing event listeners for date changes
    checkIn.addEventListener("change", function() {
        if (validateDates()) {
            fetchAvailableRooms();
            calculateTotal();
        }
    });

    checkOut.addEventListener("change", function() {
        if (validateDates()) {
            fetchAvailableRooms();
            calculateTotal();
        }
    });
});


// Function to show cancel modal with booking ID
function showCancelModal(bookingId) {
    var modal = new bootstrap.Modal(document.getElementById('cancelBookingModal'));
    document.getElementById('cancelBookingId').value = bookingId;
    modal.show();
}

// Function to show no show modal with booking ID
function showNoShowModal(bookingId) {
    var modal = new bootstrap.Modal(document.getElementById('noShowModal'));
    document.getElementById('noShowBookingId').value = bookingId;
    modal.show();
}

// Handle form submissions
document.getElementById('cancelBookingForm').addEventListener('submit', function(e) {
    if (!confirm('Are you sure you want to cancel this booking?')) {
        e.preventDefault();
    }
});

document.getElementById('noShowForm').addEventListener('submit', function(e) {
    if (!confirm('Are you sure you want to mark this booking as No Show?')) {
        e.preventDefault();
    }
});


//from show bookings


document.addEventListener("DOMContentLoaded", function() {
    // Handle form submissions for all edit modals
    document.querySelectorAll('[id^="actionsModal"] form').forEach(form => {
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const modal = this.closest('.modal');
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        try {
            // Show loading state
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
            submitBtn.disabled = true;
            
            const response = await fetch('update_booking.php', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            
            const result = await response.json();
            
            if (result.success) {
                // Show success message
                alert(result.message);
                // Close the modal and refresh the page
                const modalInstance = bootstrap.Modal.getInstance(modal);
                modalInstance.hide();
                location.reload();
            } else {
                // Show specific error message
                alert(result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred: ' + error.message);
        } finally {
            // Reset button state
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    });
});
    // Room management functionality
    const new_modals = document.querySelectorAll('[id^="actionsModal"]');

    new_modals.forEach(modal => {
        const modalId = modal.id;
        const bookingId = modalId.replace('actionsModal', '');
        if (!bookingId) return;

        // Initialize selectedRooms with the rooms that are already assigned
        const initialRooms = modal.querySelectorAll('[data-room-id]');
        const selectedRooms = new Set();
        initialRooms.forEach(room => {
            const roomId = room.getAttribute('data-room-id');
            if (roomId) selectedRooms.add(roomId);
        });

        const addRoomBtn = modal.querySelector('.add-room');
        const roomContainer = modal.querySelector('.border.p-2.rounded');
        const form = modal.querySelector('form');

        // Enhanced event delegation for ALL remove buttons (both existing and new)
        roomContainer.addEventListener('click', function(e) {
            const removeBtn = e.target.closest('.btn-outline-danger, .btn-danger.btn-sm');
            if (removeBtn) {
                const roomDiv = removeBtn.closest('[data-room-id]') || removeBtn.closest('.room-entry');
                
                if (roomDiv) {
                    const roomId = roomDiv.getAttribute('data-room-id');
                    if (roomId) {
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'removed_rooms[]';
                        hiddenInput.value = roomId;
                        form.appendChild(hiddenInput);
                        selectedRooms.delete(roomId);
                    }
                    roomDiv.remove();
                }
            }
        });

        // Handle additional persons changes for existing rooms
        roomContainer.addEventListener('change', function(e) {
            if (e.target.classList.contains('additional-persons')) {
                const roomDiv = e.target.closest('[data-room-id]');
                if (roomDiv) {
                    const display = roomDiv.querySelector('.additional-persons-display');
                    if (display) {
                        display.textContent = e.target.value;
                    }
                }
            }
        });

        if (addRoomBtn && roomContainer) {
            addRoomBtn.addEventListener('click', async function() {
                try {
                    const checkInDate = modal.querySelector('input[name="check_in_date"]').value;
                    const checkOutDate = modal.querySelector('input[name="check_out_date"]').value;
                    
                    const response = await fetch(`get_bookings.php?booking_id=${bookingId}&check_in_date=${checkInDate}&check_out_date=${checkOutDate}`);

                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }

                    const rooms = await response.json();

                    if (Array.isArray(rooms) && rooms.length > 0) {
                        const roomEntryDiv = document.createElement('div');
                        roomEntryDiv.className = 'room-entry mb-3 p-3 border rounded';
                        roomEntryDiv.innerHTML = `
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">Add New Room</h6>
                                <button type="button" class="btn btn-danger btn-sm remove-room">
                                    <i class="bi bi-trash"></i> Remove
                                </button>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Select Room</label>
                                        <select class="form-control room-select" name="room_ids[]" required>
                                            <option value="">Select a Room</option>
                                            ${rooms.map(room => {
                                                if (selectedRooms.has(room.room_id.toString())) return '';
                                                return `<option value="${room.room_id}" 
                                                        data-room-number="${room.room_number}"
                                                        data-room-type="${room.room_type}"
                                                        data-rate="${room.rate}"
                                                        data-max-persons="${room.max_person}">
                                                    ${room.room_number} - ${room.room_type} (₱${parseFloat(room.rate).toFixed(2)})
                                                </option>`;
                                            }).join('')}
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Additional Persons</label>
                                        <input type="number" class="form-control additional-persons" 
                                            name="additional_persons[]" min="0" value="0" required>
                                        <small class="text-muted max-persons-hint">Maximum: 0</small>
                                    </div>
                                </div>
                            </div>     
                            <div class="room-details" style="display: none;">
                                <div class="alert alert-info">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            Room: <span class="room-number-display"></span><br>
                                            Type: <span class="room-type-display"></span><br>
                                            Rate: <span class="room-rate-display"></span>
                                        </div>
                                        <div>
                                            Max Persons: <span class="max-persons-display"></span><br>
                                            Additional Persons: <span class="additional-persons-display">0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;

                        roomContainer.insertBefore(roomEntryDiv, addRoomBtn);

                        const roomSelect = roomEntryDiv.querySelector('.room-select');
                        const removeBtn = roomEntryDiv.querySelector('.remove-room');
                        const roomDetails = roomEntryDiv.querySelector('.room-details');
                        const additionalPersonsInput = roomEntryDiv.querySelector('.additional-persons');
                        const maxPersonsHint = roomEntryDiv.querySelector('.max-persons-hint');

                        roomSelect.addEventListener('change', function() {
                            if (this.value) {
                                const selectedOption = this.options[this.selectedIndex];
                                const maxPersons = parseInt(selectedOption.dataset.maxPersons) || 0;
                                
                                additionalPersonsInput.max = maxPersons;
                                additionalPersonsInput.value = 0;
                                maxPersonsHint.textContent = `Maximum: ${maxPersons}`;
                                
                                roomDetails.style.display = 'block';
                                roomDetails.querySelector('.room-number-display').textContent = selectedOption.dataset.roomNumber;
                                roomDetails.querySelector('.room-type-display').textContent = selectedOption.dataset.roomType;
                                roomDetails.querySelector('.room-rate-display').textContent = '₱' + parseFloat(selectedOption.dataset.rate).toFixed(2);
                                roomDetails.querySelector('.max-persons-display').textContent = maxPersons;
                                roomDetails.querySelector('.additional-persons-display').textContent = 0;

                                selectedRooms.add(this.value);
                            } else {
                                roomDetails.style.display = 'none';
                            }
                        });

                        additionalPersonsInput.addEventListener('change', function() {
                            const maxPersons = parseInt(roomSelect.options[roomSelect.selectedIndex]?.dataset.maxPersons) || 0;
                            if (this.value > maxPersons) {
                                this.value = maxPersons;
                            }
                            if (this.value < 0) {
                                this.value = 0;
                            }
                            if (roomDetails.style.display === 'block') {
                                roomDetails.querySelector('.additional-persons-display').textContent = this.value;
                            }
                        });

                        removeBtn.addEventListener('click', function() {
                            if (roomSelect.value) {
                                selectedRooms.delete(roomSelect.value);
                            }
                            roomEntryDiv.remove();
                        });
                    } else {
                        alert('No available rooms found');
                    }
                } catch (error) {
                    console.error('Error fetching rooms:', error);
                    alert('Error loading available rooms');
                }
            });
        }
    });
});

function confirmDelete(bookingId) {
    if (confirm('Are you sure you want to delete this booking?')) {
        window.location.href = `delete_booking.php?booking_id=${bookingId}`;
    }
}

function resetFilters() {
    document.getElementById('search_term').value = '';
    document.getElementById('status').value = '';
    document.getElementById('from_date').value = '';
    document.getElementById('to_date').value = '';
    window.location = window.location.pathname; // Reload without query params
}

// Add this to your existing JavaScript in booking.php
function showAdditionalChargesModal(bookingId, totalCost, downPayment) {
    // Convert the amounts to numbers by removing currency symbols and commas
    const total = parseFloat(totalCost.replace(/[^0-9.]/g, ''));
    const paid = parseFloat(downPayment.replace(/[^0-9.]/g, ''));
    const remaining = total - paid;
    
    document.getElementById('bookingIdForCompletion').value = bookingId;
    document.getElementById('remainingBalance').value = `₱${remaining.toFixed(2)}`;
    
    // Reset the charges container
    document.getElementById('chargesContainer').innerHTML = `
        <div class="charge-item mb-2">
            <div class="input-group">
                <select class="form-control charge-type" name="charge_types[]" required>
                    <option value="">Select Charge Type</option>
                    <option value="Extra Bed">Extra Bed (₱500)</option>
                    <option value="Food/Beverage">Food/Beverage (₱300)</option>
                    <option value="Room Damage">Room Damage (₱1000-5000)</option>
                    <option value="Missing Item">Missing Item (₱500-2000)</option>
                    <option value="Water Activity">Water Activity (₱800)</option>
                </select>
                <input type="number" class="form-control charge-amount" name="charge_amounts[]" placeholder="Amount" required>
                <button type="button" class="btn btn-danger remove-charge">×</button>
            </div>
        </div>
    `;
    
    document.getElementById('totalAdditionalCharges').value = '₱0.00';
    
    const modal = new bootstrap.Modal(document.getElementById('additionalChargesModal'));
    modal.show();
}

// Add event listener for adding more charge items
document.addEventListener('click', function(e) {
    if (e.target && e.target.id === 'addCharge') {
        const chargesContainer = document.getElementById('chargesContainer');
        const newChargeItem = document.createElement('div');
        newChargeItem.className = 'charge-item mb-2';
        newChargeItem.innerHTML = `
            <div class="input-group">
                <select class="form-control charge-type" name="charge_types[]" required>
                    <option value="">Select Charge Type</option>
                    <option value="Extra Bed">Extra Bed (₱500)</option>
                    <option value="Food/Beverage">Food/Beverage (₱300)</option>
                    <option value="Room Damage">Room Damage (₱1000-5000)</option>
                    <option value="Missing Item">Missing Item (₱500-2000)</option>
                    <option value="Water Activity">Water Activity (₱800)</option>
                </select>
                <input type="number" class="form-control charge-amount" name="charge_amounts[]" placeholder="Amount" required>
                <button type="button" class="btn btn-danger remove-charge">×</button>
            </div>
        `;
        chargesContainer.appendChild(newChargeItem);
    }
    
    // Handle remove charge button
    if (e.target && e.target.classList.contains('remove-charge')) {
        if (document.querySelectorAll('.charge-item').length > 1) {
            e.target.closest('.charge-item').remove();
            calculateTotalCharges();
        }
    }
    
    // Auto-fill amount based on charge type selection
    if (e.target && e.target.classList.contains('charge-type')) {
        const chargeType = e.target.value;
        const amountInput = e.target.closest('.input-group').querySelector('.charge-amount');
        
        // Set default amounts based on charge type
        switch(chargeType) {
            case 'Extra Bed':
                amountInput.value = '500';
                break;
            case 'Food/Beverage':
                amountInput.value = '300';
                break;
            case 'Room Damage':
                amountInput.value = '1000';
                break;
            case 'Missing Item':
                amountInput.value = '500';
                break;
            case 'Water Activity':
                amountInput.value = '800';
                break;
            default:
                amountInput.value = '';
        }
        
        calculateTotalCharges();
    }
    
    // Recalculate when amount changes
    if (e.target && e.target.classList.contains('charge-amount')) {
        calculateTotalCharges();
    }
});

function calculateTotalCharges() {
    let total = 0;
    document.querySelectorAll('.charge-amount').forEach(input => {
        const amount = parseFloat(input.value) || 0;
        total += amount;
    });
    
    document.getElementById('totalAdditionalCharges').value = `₱${total.toFixed(2)}`;
    
    // Update remaining balance
    const remainingBalance = parseFloat(document.getElementById('remainingBalance').value.replace(/[^0-9.]/g, '')) || 0;
    const newRemaining = remainingBalance + total;
    document.getElementById('remainingBalance').value = `₱${newRemaining.toFixed(2)}`;
}
</script>



</body>
</html>
