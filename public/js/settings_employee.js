document.querySelectorAll(".edit-btn").forEach(button => {
    button.addEventListener("click", function (event) {
        event.preventDefault();

        let modal = this.closest(".modal");
        let inputs = modal.querySelectorAll("input:not([type='hidden'])"); //responsible for one click on edit button


        let allDisabled = Array.from(inputs).every(input => input.hasAttribute("disabled"));

        inputs.forEach(input => {
            if (allDisabled) {
                input.removeAttribute("disabled");
            } else {
                input.setAttribute("disabled", "true");
            }
        })
        this.textContent = allDisabled ? "Cancel Edit" : "Edit";
    })
})

// for errors to keep the modal open

// document.addEventListener("DOMContentLoaded", function () {
//     <? php if (isset($_SESSION["keep_modal_open"]) && $_SESSION["keep_modal_open"]): ?>
//         var addRoleModal = new bootstrap.Modal(document.getElementById("add_role_modal"));
//     addRoleModal.show();
//     <? php unset($_SESSION["keep_modal_open"]) ?>
//     <? php endif; ?>
// })