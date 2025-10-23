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

    checkInInput.addEventListener("change", function () {
        checkOutInput.value = ""; // Reset check-out date when check-in changes
        updateMinDates();
    });

    updateMinDates();
});


//for date selection

document.getElementById("check_in").addEventListener("change", checkDates);
document.getElementById("check_out").addEventListener("change", checkDates);

function checkDates() {
    const checkIn = document.getElementById("check_in").value;
    const checkOut = document.getElementById("check_out").value;
    const availableRooms = document.getElementById("available-rooms");

    if (checkIn && checkOut) {
        availableRooms.style.display = "block";
    } else {
        availableRooms.style.display = "none";
    }
}