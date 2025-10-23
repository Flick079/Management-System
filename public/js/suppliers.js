document.addEventListener('DOMContentLoaded', function () {
    // Handle order deletion without requiring expiration dates
    document.querySelectorAll('[id^="deleteOrder_"]').forEach(button => {
        button.addEventListener('click', function () {
            const orderId = this.id.split('_')[1];
            const form = document.getElementById(`orderForm_${orderId}`);

            if (confirm('Are you sure you want to delete this order? This cannot be undone.')) {
                // Create a hidden input for delete action
                const deleteInput = document.createElement('input');
                deleteInput.type = 'hidden';
                deleteInput.name = 'delete_order_btn';
                deleteInput.value = '1';
                form.appendChild(deleteInput);

                // Remove required attributes from expiration dates
                document.querySelectorAll('.expiration-date').forEach(input => {
                    input.required = false;
                });

                form.submit();
            }
        });
    });

    // Make expiration dates required when submitting normally (for fulfillment)
    document.querySelectorAll('[name="fulfill_order_btn"]').forEach(button => {
        button.addEventListener('click', function (e) {
            const form = this.closest('form');
            let allValid = true;

            // Validate expiration dates
            document.querySelectorAll('.expiration-date').forEach(input => {
                if (!input.value) {
                    input.reportValidity();
                    allValid = false;
                }
            });

            if (!allValid) {
                e.preventDefault();
            }
        });
    });

    // Fix for the remove-item button in Create Order Modal
    document.querySelector('#order_items_table').addEventListener('click', function (event) {
        // Check if the clicked element or its parent is a remove-item button
        const removeButton = event.target.closest('.remove-item');
        if (removeButton) {
            // Get the row and remove it
            const row = removeButton.closest('tr');
            if (row) {
                row.remove();
            }
        }
    });
});