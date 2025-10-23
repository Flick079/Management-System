const new_modals = document.querySelectorAll('[id^="actionsModal"]');

new_modals.forEach(modal => {
    const bookingId = modal.getAttribute('data-booking-id');
    if (!bookingId) return; // Skip if no booking ID found

    let selectedRooms = new Set();

    const addRoomBtn = modal.querySelector('.add-room');
    const roomContainer = modal.querySelector('.border.p-2.rounded');

    if (addRoomBtn && roomContainer) {
        addRoomBtn.addEventListener('click', async function () {
            try {
                const response = await fetch(`get_available_rooms.php?booking_id=${bookingId}`);

                // Check if the response is successful (status 200)
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }

                const rooms = await response.json();

                // Check if the response is a valid array
                if (Array.isArray(rooms) && rooms.length > 0) {
                    const roomSelectDiv = document.createElement('div');
                    roomSelectDiv.className = 'mb-3 room-entry';
                    roomSelectDiv.innerHTML = `
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label">Select Additional Room</label>
                            <button type="button" class="btn btn-danger btn-sm remove-room">
                                <i class="bi bi-trash"></i> Remove
                            </button>
                        </div>
                        <select class="form-control room-select">
                            <option value="">Select a Room</option>
                            ${rooms.map(room => ` 
                                <option value="${room.room_id}" 
                                        data-room-number="${room.room_number}"
                                        data-room-type="${room.room_type}"
                                        data-rate="${room.rate}">
                                    ${room.room_number} - ${room.room_type} (₱${parseFloat(room.rate).toFixed(2)})
                                </option>
                            `).join('')}
                        </select>
                        <div class="room-details mt-2" style="display: none;">
                            <div class="alert alert-info">
                                Room: <span class="room-number-display"></span><br>
                                Type: <span class="room-type-display"></span><br>
                                Rate: <span class="room-rate-display"></span>
                            </div>
                        </div>
                    `;

                    roomContainer.insertBefore(roomSelectDiv, addRoomBtn);

                    const roomSelect = roomSelectDiv.querySelector('.room-select');
                    const removeBtn = roomSelectDiv.querySelector('.remove-room');
                    const roomDetails = roomSelectDiv.querySelector('.room-details');

                    // Room selection handler
                    roomSelect.addEventListener('change', function () {
                        if (this.value) {
                            if (selectedRooms.has(this.value)) {
                                alert('This room is already selected.');
                                this.value = ""; // Reset selection
                                roomDetails.style.display = 'none';
                                return;
                            }

                            const selectedOption = this.options[this.selectedIndex];
                            roomDetails.style.display = 'block';
                            roomDetails.querySelector('.room-number-display').textContent = selectedOption.dataset.roomNumber;
                            roomDetails.querySelector('.room-type-display').textContent = selectedOption.dataset.roomType;
                            roomDetails.querySelector('.room-rate-display').textContent = '₱' + parseFloat(selectedOption.dataset.rate).toFixed(2);

                            // Add room to selected set
                            selectedRooms.add(this.value);
                        } else {
                            roomDetails.style.display = 'none';
                        }
                    });

                    // Remove button handler
                    removeBtn.addEventListener('click', function () {
                        selectedRooms.delete(roomSelect.value); // Remove from selected rooms set
                        roomSelectDiv.remove();
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
