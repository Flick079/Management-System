document.addEventListener("DOMContentLoaded", function () {
    if (showModal !== "undefined" && showModal) {
        let dateModal = new bootstrap.Modal(document.getElementById("dateModal"));
        dateModal.show();
    }


    document.getElementById("saveDate").addEventListener("click", function () {
        $.post("../../app/controllers/dashboard.php", $("#dateForm"), function () {
            location.reload();
        })
    })
})




//for edit button in employees main page

document.querySelectorAll(".edit-btn").forEach(button => {
    button.addEventListener("click", function (event) {
        event.preventDefault(); // Prevent form submission

        let modal = this.closest(".modal"); // Get the closest modal
        let inputs = modal.querySelectorAll("input:not([type='hidden']), select"); //responsible for one click on edit button

        // Determine if all fields are currently disabled
        let allDisabled = Array.from(inputs).every(input => input.hasAttribute("disabled"));

        // Toggle the disabled attribute for all inputs and selects
        inputs.forEach(input => {
            if (allDisabled) {
                input.removeAttribute("disabled"); // Enable inputs
            } else {
                input.setAttribute("disabled", "true"); // Disable inputs
            }
        });

        // Ensure all options inside select elements are enabled
        let selects = modal.querySelectorAll("select");
        selects.forEach(select => {
            let options = select.querySelectorAll("option");
            options.forEach(option => {
                option.removeAttribute("disabled"); // Enable all options
            });
        });

        // Change button text based on the toggle state
        this.textContent = allDisabled ? "Cancel Edit" : "Edit";
    });
});


//for viewing the id

function openIDCard(employeeId) {
    window.open(`../../views/employees/employee_id.php?employee_id=${employeeId}`, '_blank', 'width=400,height=600');
}

//for expanding images

function openImageModal(imageSrc) {
    document.getElementById('expandedImage').src = imageSrc;
    var imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
    imageModal.show();
}