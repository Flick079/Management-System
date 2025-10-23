let cart = [];
let transactionId = Math.floor(Math.random() * 90000) + 10000;

// DOM elements
const cartItemsEl = document.getElementById('cart-items');
const customerDisplayItemsEl = document.getElementById('customer-display-items');
const customerDisplayTotalEl = document.getElementById('customer-display-total');
const subtotalPriceEl = document.getElementById('subtotal-price');
const vatAmountEl = document.getElementById('vat-amount');
const totalPriceEl = document.getElementById('total-price');
const userId = USER_ID;
const userName = USERNAME;
let currentDiscountType = 'none';
let currentDiscountAmount = 0;
// Add this near the top of your pos.js file (with other global variables)
function showToast(message, type = 'info') {
    const toastContainer = document.createElement('div');
    toastContainer.className = `toast show align-items-center text-white bg-${type}`;
    toastContainer.style.position = 'fixed';
    toastContainer.style.bottom = '20px';
    toastContainer.style.right = '20px';
    toastContainer.style.zIndex = '1000';

    toastContainer.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;

    document.body.appendChild(toastContainer);

    // Auto-remove after 3 seconds
    setTimeout(() => {
        toastContainer.remove();
    }, 3000);
}

// Discount rates
const discountTypes = {
    'none': { rate: 0, name: 'No Discount' },
    'senior': { rate: 20, name: 'Senior Citizen (20%)' },
    'student': { rate: 10, name: 'Student (10%)' },
    'pwd': { rate: 15, name: 'PWD (15%)' },
    'employee': { rate: 25, name: 'Employee (25%)' },
    'custom': { rate: 0, name: 'Custom Discount' }
};

// Initialize the POS
document.addEventListener("DOMContentLoaded", function () {
    // Date modal
    if (!localStorage.getItem("posDate")) {
        var dateModal = new bootstrap.Modal(document.getElementById('dateModal'));
        dateModal.show();
    }

    // Add to cart buttons
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', () => {
            const id = parseInt(button.getAttribute('data-id'));
            const name = button.getAttribute('data-name');
            const price = parseFloat(button.getAttribute('data-price'));
            const stock = parseInt(button.getAttribute('data-stock'));
            addToCart(id, name, price, stock);
        });
    });

    // Search functionality
    document.getElementById('search-bar').addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase();
        const productItems = document.querySelectorAll('.products-table tbody tr');

        productItems.forEach(item => {
            const productName = item.querySelector('td').textContent.toLowerCase();
            const productCategory = item.querySelectorAll('td')[1].textContent.toLowerCase();
            const productMeasurement = item.querySelectorAll('td')[2].textContent.toLowerCase();
            if (productName.includes(searchTerm) || productCategory.includes(searchTerm) || productMeasurement.includes(searchTerm)) {
                item.style.display = 'table-row';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // Save date button
    document.getElementById("saveDate").addEventListener("click", function () {
        const selectedDate = document.getElementById("selectedDate").value;
        if (selectedDate) {
            localStorage.setItem("posDate", selectedDate);
            var dateModal = bootstrap.Modal.getInstance(document.getElementById('dateModal'));
            dateModal.hide();
        } else {
            alert("Please select a date.");
        }
    });

    // Back button
    document.getElementById('backButton').addEventListener('click', function () {
        window.location.href = 'products.php';
    });

    // Amount tendered calculation
    document.getElementById('amountTendered').addEventListener('input', function () {
        const amountDue = parseFloat(document.getElementById('amountDue').value.replace('₱', '')) || 0;
        const amountTendered = parseFloat(this.value) || 0;

        if (amountTendered >= amountDue) {
            document.getElementById('changeGroup').style.display = 'block';
            document.getElementById('changeAmount').value = '₱' + (amountTendered - amountDue).toFixed(2);
        } else {
            document.getElementById('changeGroup').style.display = 'none';
        }
    });

    // Complete payment button
    document.getElementById('completePayment').addEventListener('click', completePayment);

    // Add manual item form submit
    document.getElementById('addItemForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const name = document.getElementById('manualItemName').value;
        const price = parseFloat(document.getElementById('manualItemPrice').value);
        const quantity = parseInt(document.getElementById('manualItemQuantity').value);

        if (!name || isNaN(price) || price <= 0 || isNaN(quantity) || quantity <= 0) {
            document.getElementById('addItemError').textContent = "Please fill all fields with valid values.";
            document.getElementById('addItemError').style.display = "block";
            return;
        }

        addManualItemToCart(name, price, quantity);
        var addItemModal = bootstrap.Modal.getInstance(document.getElementById('addItemModal'));
        addItemModal.hide();
    });

    // Apply discount form submit
    document.getElementById('discountForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const discountType = document.getElementById('discountType').value;
        let discountValue = discountTypes[discountType].rate;

        if (discountType === 'custom') {
            discountValue = parseFloat(document.getElementById('customDiscountValue').value);
            if (isNaN(discountValue) || discountValue < 0 || discountValue > 100) {
                document.getElementById('discountError').textContent = "Please enter a valid discount percentage (0-100).";
                document.getElementById('discountError').style.display = "block";
                return;
            }
        }

        applyDiscountToCart(discountType, discountValue);
        currentDiscountType = discountType;
        currentDiscountAmount = discountValue;
        var discountModal = bootstrap.Modal.getInstance(document.getElementById('discountModal'));
        discountModal.hide();
    });

    // Show custom discount field when custom is selected
    document.getElementById('discountType').addEventListener('change', function () {
        const customField = document.getElementById('customDiscountField');
        if (this.value === 'custom') {
            customField.style.display = 'block';
        } else {
            customField.style.display = 'none';
        }
    });
});

// Quantity controls
function incrementQuantity(id, max) {
    const input = document.getElementById(`quantity-${id}`);
    if (parseInt(input.value) < max) {
        input.value = parseInt(input.value) + 1;
    }
}

function decrementQuantity(id) {
    const input = document.getElementById(`quantity-${id}`);
    if (parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
    }
}

// Cart functions
// Modify the addToCart function to properly check stock
async function addToCart(id, name, price, stock) {
    // First get available units for this product
    try {
        const response = await fetch(`../../views/pos/get_product_units.php?product_id=${id}`);
        const units = await response.json();

        if (units.length === 0) {
            showToast('No units available for this product', 'error');
            return;
        }

        // If only one unit, add directly
        if (units.length === 1) {
            addToCartWithUnit(id, name, price, stock, units[0]);
            return;
        }

        // Show unit selection modal
        const modalBody = document.getElementById('unitSelectionBody');
        modalBody.innerHTML = '';

        // In pos.js, update the unit button creation
        units.forEach(unit => {
            const unitBtn = document.createElement('button');
            unitBtn.className = 'btn btn-outline-primary w-100 mb-2';
            unitBtn.innerHTML = `
        ${unit.measurement} - ₱${parseFloat(unit.price).toFixed(2)}
        ${unit.is_primary ? '<span class="badge bg-primary">Primary</span>' : ''}
        ${unit.conversion_factor !== '1.0000' ?
                    `<small class="text-muted">(${unit.conversion_factor} ${unit.measurement} = 1 ${units.find(u => u.is_primary).measurement})</small>` : ''}
    `;
            unitBtn.onclick = () => {
                addToCartWithUnit(id, name, unit.price, stock, unit);
                bootstrap.Modal.getInstance(document.getElementById('unitSelectionModal')).hide();
            };
            modalBody.appendChild(unitBtn);
        });

        const unitModal = new bootstrap.Modal(document.getElementById('unitSelectionModal'));
        unitModal.show();
    } catch (error) {
        console.error('Error getting units:', error);
        showToast('Error getting product units', 'error');
    }
}
// In pos.js, modify the addToCartWithUnit function
// In pos.js, modify the addToCartWithUnit function
// 1. Fix for addToCartWithUnit function - ensure proper object structure
function addToCartWithUnit(id, name, price, stock, unit) {
    const quantityInput = document.getElementById(`quantity-${id}`);
    const quantity = parseFloat(quantityInput.value) || 1;

    if (quantity <= 0) {
        showToast('Invalid quantity!', 'error');
        return;
    }

    // Calculate primary quantity with precise decimals - ensure accurate conversion
    const conversionFactor = parseFloat(unit.conversion_factor);
    const primaryQty = quantity * conversionFactor;

    console.log('Debug - Unit Info:', {
        unit_id: unit.unit_id,
        conversion_factor: conversionFactor,
        primaryQty: primaryQty
    });

    // Check available stock (in primary units) with decimal precision
    fetch(`../../views/pos/get_stock.php?product_id=${id}`)
        .then(response => response.json())
        .then(data => {
            const availableStock = parseFloat(data.stock) || 0;

            // Compare with 4 decimal places precision
            if (primaryQty > availableStock + 0.0001) {
                showToast(`Not enough stock available! (Need ${primaryQty.toFixed(4)} primary units, have ${availableStock.toFixed(4)})`, 'error');
                return;
            }

            // Add to cart with complete and consistent unit info
            let existingItem = cart.find(item =>
                item.id === id && item.unit_id === unit.unit_id
            );

            if (!existingItem) {
                // Create new cart item with consistent property names
                cart.push({
                    id,
                    name,
                    price: parseFloat(unit.price),
                    quantity,
                    primaryQty,
                    stock: availableStock,
                    originalStock: availableStock,
                    originalPrice: parseFloat(unit.price),
                    discountApplied: false,
                    // Ensure these exact property names are used consistently
                    unit_id: parseInt(unit.unit_id),
                    unit_name: unit.measurement,
                    conversion_factor: conversionFactor,
                    is_primary: unit.is_primary === "1" || unit.is_primary === true
                });
            } else {
                existingItem.quantity += quantity;
                existingItem.primaryQty += primaryQty;
            }

            console.log('Debug - Cart after add:', cart);
            updateCart();
        })
        .catch(error => {
            console.error('Error checking stock:', error);
            showToast('Error checking product availability', 'error');
        });
}

function updateStock(id, change) {
    const stockElement = document.getElementById(`stock-${id}`);
    if (!stockElement) return;

    const currentStock = parseInt(stockElement.getAttribute('data-stock'));
    const newStock = currentStock + change;

    stockElement.setAttribute('data-stock', newStock);
    stockElement.textContent = newStock;

    const addButton = document.querySelector(`.add-to-cart[data-id="${id}"]`);
    if (addButton) {
        addButton.disabled = newStock <= 0;
    }
}

function removeFromCart(index, returnStock = true) {
    const item = cart[index];

    if (returnStock && item.id > 0) {
        updateStock(item.id, item.quantity);
    }

    cart.splice(index, 1);
    updateCart();
    // Removed the toast notification
}

function updateCart() {
    cartItemsEl.innerHTML = '';
    customerDisplayItemsEl.innerHTML = '';

    let subtotal = 0;

    cart.forEach((item, index) => {
        // Calculate item total using original price (not discounted)
        const itemTotal = item.originalPrice * item.quantity;
        subtotal += itemTotal;

        // Main cart display
        const row = document.createElement('tr');
        row.className = 'cart-item';
        row.innerHTML = `
            <td>
                ${item.name}
                ${!item.is_primary ? `<small class="text-muted"> (${item.unit_name})</small>` : ''}
            </td>
            <td>₱${item.originalPrice.toFixed(2)}</td>
            <td>
                <button class="btn btn-sm btn-outline-secondary" onclick="decrementCartItem(${index})">
                    <i class="bi bi-dash"></i>
                </button>
                <span class="quantity-display">${item.quantity}</span>
                <button class="btn btn-sm btn-outline-secondary" onclick="incrementCartItem(${index}, ${item.stock}, ${item.conversion_factor})">
                    <i class="bi bi-plus"></i>
                </button>
            </td>
            <td>₱${itemTotal.toFixed(2)}</td>
            <td>
                <button class="btn btn-sm btn-danger" onclick="removeFromCart(${index})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        cartItemsEl.appendChild(row);

        // Customer display
        const displayRow = document.createElement('div');
        displayRow.className = 'item-line';
        displayRow.innerHTML = `
            <span>${item.name} ${!item.is_primary ? `(${item.unit_name})` : ''} x${item.quantity}</span>
            <span>₱${itemTotal.toFixed(2)}</span>
        `;
        customerDisplayItemsEl.appendChild(displayRow);
    });


    // Calculate totals
    const vat = (subtotal * 0.0112) / 100;
    const totalBeforeDiscount = subtotal;
    const discountAmount = currentDiscountAmount > 0 ? totalBeforeDiscount * (currentDiscountAmount / 100) : 0;
    const total = totalBeforeDiscount - discountAmount;

    // Update display
    subtotalPriceEl.textContent = subtotal.toFixed(2);
    vatAmountEl.textContent = vat.toFixed(2);
    totalPriceEl.textContent = total.toFixed(2);
    customerDisplayTotalEl.textContent = `₱${total.toFixed(2)}`;

    // Update discount badge
    const discountBadge = document.getElementById('discountBadge');
    if (discountBadge) {
        discountBadge.style.display = currentDiscountAmount > 0 ? 'inline-block' : 'none';
    }
}
function incrementCartItem(index, maxStock) {
    const item = cart[index];
    if (item.quantity < maxStock) {
        item.quantity++;
        if (item.id > 0) {
            updateStock(item.id, -1);
        }
        updateCart();
    }
}

function decrementCartItem(index) {
    const item = cart[index];
    if (item.quantity > 1) {
        item.quantity--;
        if (item.id > 0) {
            updateStock(item.id, 1);
        }
        updateCart();
    } else {
        // If quantity would go to 0, remove the item
        removeFromCart(index);
    }
}
function clearCart() {
    if (cart.length === 0) return;

    if (confirm('Are you sure you want to clear the cart?')) {
        // Return all items to stock
        cart.forEach(item => {
            if (item.id > 0) { // Only return stock for actual inventory items
                updateStock(item.id, item.quantity);
            }
        });

        cart = [];
        updateCart();
        //   showToast('Cart cleared', 'info');
    }
}

function addManualItem() {
    // Reset form fields
    document.getElementById('manualItemName').value = '';
    document.getElementById('manualItemPrice').value = '';
    document.getElementById('manualItemQuantity').value = '1';
    document.getElementById('addItemError').style.display = 'none';

    // Show modal
    const addItemModal = new bootstrap.Modal(document.getElementById('addItemModal'));
    addItemModal.show();
}

function addManualItemToCart(name, price, quantity) {
    cart.push({
        id: -1 * (Date.now()), // Use timestamp for unique negative ID
        name: name,
        price: price,
        originalPrice: price,
        quantity: quantity,
        stock: 999,
        originalStock: 999,
        discountApplied: false
    });

    updateCart();
    //  showToast(`${name} added to cart`, 'success');
}

function applyDiscount() {
    if (cart.length === 0) {
        //  showToast('Cart is empty!', 'error');
        return;
    }

    // Reset form and error message
    document.getElementById('discountType').value = 'none';
    document.getElementById('customDiscountValue').value = '';
    document.getElementById('customDiscountField').style.display = 'none';
    document.getElementById('discountError').style.display = 'none';

    // Show discount modal
    const discountModal = new bootstrap.Modal(document.getElementById('discountModal'));
    discountModal.show();
}

function applyDiscountToCart(discountType, discountValue) {
    currentDiscountType = discountType;
    currentDiscountAmount = discountValue;
    updateCart();

    const discountName = discountType === 'custom'
        ? `${discountValue}% discount`
        : discountTypes[discountType].name;

    showToast(`Applied ${discountName} to order total`, 'success');
}
function processPayment() {
    if (cart.length === 0) {
        showToast('Cart is empty!', 'error');
        return;
    }

    const total = parseFloat(totalPriceEl.textContent);

    // Show payment modal
    const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
    document.getElementById('amountDue').value = `₱${total.toFixed(2)}`;
    document.getElementById('amountTendered').value = '';
    document.getElementById('changeAmount').value = '';
    document.getElementById('cashPaymentGroup').style.display = 'block';
    document.getElementById('changeGroup').style.display = 'none';

    paymentModal.show();
}
async function generateUniqueTransactionId() {
    let isUnique = false;
    let newId;
    let attempts = 0;
    const maxAttempts = 5;

    while (!isUnique && attempts < maxAttempts) {
        attempts++;
        newId = Math.floor(Math.random() * 90000) + 10000;

        try {
            const response = await fetch(`../../views/pos/check_transaction_id.php?transaction_id=${newId}`);
            const data = await response.json();

            if (data.exists === false) {
                isUnique = true;
            }
        } catch (error) {
            console.error('Error checking transaction ID:', error);
            // Fallback to random if check fails
            if (attempts >= maxAttempts) {
                isUnique = true;
            }
        }
    }

    return newId;
}

// 2. Fix for completePayment function - ensure data is structured correctly for PHP
async function completePayment() {
    try {
        // Calculate totals
        const subtotal = parseFloat(subtotalPriceEl.textContent);
        const vat = parseFloat(vatAmountEl.textContent);
        const totalBeforeDiscount = subtotal + vat;
        const discountAmount = totalBeforeDiscount * (currentDiscountAmount / 100);
        const total = totalBeforeDiscount - discountAmount;
        const amountTendered = parseFloat(document.getElementById('amountTendered').value) || 0;

        // Generate a new transaction ID
        transactionId = await generateUniqueTransactionId();

        // Debug cart contents before sending
        console.log('Debug - Cart before payment:', JSON.parse(JSON.stringify(cart)));

        // Prepare payment details with explicit property mapping
        const paymentDetails = {
            transaction_id: transactionId.toString(),
            cashier_id: userId,
            cashier_name: userName,
            subtotal: subtotal,
            vat: vat,
            total: total,
            payment_method: 'cash',
            amount_tendered: amountTendered,
            change_amount: amountTendered - total,
            discount_type: currentDiscountType,
            discount_percentage: currentDiscountAmount,
            discount_amount: discountAmount,
            items: cart.map(item => {
                // Create a new clean object to ensure consistent structure
                return {
                    product_id: item.id > 0 ? item.id : null,
                    product_name: item.name + " (" + item.unit_name + ")",
                    quantity: parseFloat(item.quantity),
                    price: parseFloat(item.price),
                    original_price: parseFloat(item.originalPrice),
                    discount_applied: item.discountApplied ? true : false,
                    unit_id: parseInt(item.unit_id) || null,
                    conversion_factor: parseFloat(item.conversion_factor) || 1.0
                };
            })
        };

        console.log('Debug - Payment details:', JSON.stringify(paymentDetails));

        // Send transaction to server
        const response = await fetch('../../views/pos/save_transaction.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(paymentDetails)
        });

        // First check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Non-JSON response:', text);
            throw new Error(`Server returned unexpected response: ${text.substring(0, 100)}...`);
        }

        const data = await response.json();
        console.log('Debug - Server response:', data);

        if (!response.ok) {
            console.error('Error response:', data);
            throw new Error(data.message || `Transaction failed with status ${response.status}`);
        }

        // Success case
        const paymentModal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
        paymentModal.hide();

        openReceiptWindow(paymentDetails);

        // Reset cart
        cart = [];
        currentDiscountType = 'none';
        currentDiscountAmount = 0;
        updateCart();

        // Generate new transaction ID
        transactionId = Math.floor(Math.random() * 90000) + 10000;

        showToast('Transaction completed successfully!', 'success');

    } catch (error) {
        console.error('Transaction failed:', error);

        // More user-friendly error messages
        let errorMessage = error.message;
        if (errorMessage.includes('Unexpected token')) {
            errorMessage = 'Server returned an invalid response. Please check server logs.';
        } else if (errorMessage.includes('status 400')) {
            errorMessage = 'Invalid request data. Please try again.';
        } else if (errorMessage.includes('status 500')) {
            errorMessage = 'Server error occurred. Please contact support.';
        }

        showToast(`Error: ${errorMessage}`, 'danger');
    }
}

function debugCartUnitData() {
    console.log('===== CART UNIT DATA DEBUG =====');
    cart.forEach((item, index) => {
        console.log(`Item ${index}: ${item.name}`);
        console.log('  - unit_id:', item.unit_id);
        console.log('  - conversion_factor:', item.conversion_factor);
        console.log('  - quantity:', item.quantity);
        console.log('  - primaryQty:', item.primaryQty);
    });
    console.log('===============================');
}

async function getProductBatchesForCart(productId, quantity) {
    try {
        const response = await fetch(`../../views/pos/get_batches.php?product_id=${productId}`);
        if (!response.ok) throw new Error('Failed to get batch info');
        const batches = await response.json();

        // Simulate FIFO deduction to determine which batches will be used
        let remaining = quantity;
        const usedBatches = [];

        for (const batch of batches) {
            if (remaining <= 0) break;
            const deduct = Math.min(remaining, batch.quantity);
            remaining -= deduct;
            usedBatches.push({
                batch_id: batch.batch_id,
                quantity_deducted: deduct
            });
        }

        return usedBatches;
    } catch (error) {
        console.error('Error getting batches:', error);
        return null;
    }
}
function clearCart() {
    if (cart.length === 0) return;

    if (confirm('Are you sure you want to clear the cart?')) {
        cart.forEach(item => {
            if (item.id > 0) {
                updateStock(item.id, item.quantity);
            }
        });

        cart = [];
        currentDiscountType = 'none'; // Reset discount
        currentDiscountAmount = 0;    // Reset discount amount
        updateCart();
        showToast('Cart cleared', 'info');
    }
}
function openReceiptWindow(paymentDetails) {
    // Convert the paymentDetails to URL parameters
    const queryString = Object.keys(paymentDetails).map(key => {
        if (key === 'items') {
            return `items=${encodeURIComponent(JSON.stringify(paymentDetails[key]))}`;
        }
        return `${encodeURIComponent(key)}=${encodeURIComponent(paymentDetails[key])}`;
    }).join('&');

    window.open(`receipt_template.php?${queryString}`, 'ReceiptWindow', 'width=400,height=600');
}
async function saveTransaction(paymentDetails) {
    try {
        // Transform keys to match database columns
        const transactionData = {
            transaction_id: paymentDetails.transactionId, // Changed from transactionId
            cashier_id: USER_ID, // Replace with actual cashier ID from session
            subtotal: paymentDetails.subtotal,
            vat: paymentDetails.vat,
            total: paymentDetails.total,
            payment_method: paymentDetails.method, // Changed from method
            amount_tendered: paymentDetails.amountTendered, // Changed from amountTendered
            change_amount: paymentDetails.change, // Changed from change
            items: paymentDetails.items.map(item => ({
                product_id: item.id > 0 ? item.id : null,
                product_name: item.name,
                quantity: item.quantity,
                price: item.price,
                original_price: item.originalPrice, // Changed from originalPrice
                discount_applied: item.discountApplied ? 1 : 0,// Changed from discountApplied
                unit_id: item.unit_id || null,                      // ✅ Include unit_id
                conversion_factor: parseFloat(item.conversion_factor) || 1.0 // ✅ Include conversion_factor
            }))
        };

        const response = await fetch('../../views/pos/save_transaction.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(transactionData)
        });

        // ... rest of your error handling ...
    } catch (error) {
        console.error('Save failed:', error);
        showToast(`Save error: ${error.message}`, 'danger');
        return false;
    }
}

//ui fixes

