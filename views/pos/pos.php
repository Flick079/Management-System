<?php
require_once __DIR__ . '/../../middleware/user_exists.php';
require_once __DIR__ . '/../../middleware/verify.php';
require_once __DIR__ . '/../../controllers/posController.php';

$user_id = $_SESSION["user_id"];
$username = $_SESSION["username"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="../../public/css/pos.css">
    <link rel="stylesheet" href="../../public/css/bootstrap-icons-1.11.0/bootstrap-icons.min.css">
    <script defer src="../../public/js/bootstrap.bundle.min.js"></script>
    <script defer src="../../public/js/pos.js"></script>
    <title>Point of Sale</title>
</head>
<body>

        <div class="content-body">
        <div class="modal fade" id="dateModal" tabindex="-1" aria-labelledby="dateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dateModalLabel">Select Date</h5>
                </div>
                <div class="modal-body">
                    <input type="date" id="selectedDate" class="form-control">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="saveDate">Save</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Payment Modal -->
    <div class="modal fade payment-modal" id="paymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Process Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    
                    <div class="payment-details">
                        <div class="mb-3">
                            <label class="form-label">Amount Due</label>
                            <input type="text" class="form-control" id="amountDue" readonly>
                        </div>
                        <div class="mb-3" id="cashPaymentGroup">
                            <label class="form-label">Amount Tendered</label>
                            <input type="number" class="form-control" id="amountTendered">
                        </div>
                        <div class="mb-3" id="changeGroup" style="display: none;">
                            <label class="form-label">Change</label>
                            <input type="text" class="form-control" id="changeAmount" readonly>
                        </div>
                        <div class="mb-3" id="referenceNumberGroup" style="display: none;">
                            <label class="form-label">Reference Number</label>
                            <input type="text" class="form-control" id="referenceNumber">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="completePayment">Complete Payment</button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container-fluid">
        <div class="pos-header">
            <button class="back-btn" id="backButton">
                <i class="bi bi-arrow-left"></i>
            </button>
            <h1 class="text-center m-0">The Lagoon Resort Finland - POS System</h1>
        </div>
        
        <div class="pos-content">
            <div class="products-section">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="m-0">Products</h3>
                    <div class="w-50">
                        <input type="text" id="search-bar" class="form-control" placeholder="Search products...">
                    </div>
                </div>
                
                <table class="table table-striped products-table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Measurement</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($products): ?>
                            <?php foreach($products as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product["product_name"]) ?></td>
                                <td><?php echo htmlspecialchars($product["category_name"]) ?></td>
                                <td><?php echo htmlspecialchars($product["measurement"]) ?></td>
                                <td>₱<?php echo htmlspecialchars($product["price"]) ?></td>
                                <td><span id="stock-<?php echo $product['product_id']; ?>" data-stock="<?php echo $product['stock']; ?>"><?php echo $product['stock']; ?></span></td>
                                <td>
                                    <div class="quantity-controls" style="display: none;">
                                        <button class="btn btn-sm btn-outline-secondary" onclick="decrementQuantity(<?php echo $product['product_id']; ?>)">
                                            <i class="bi bi-dash"></i>
                                        </button>
                                        <input type="number" id="quantity-<?php echo $product['product_id']; ?>" class="form-control quantity-input" value="1" min="1" max="<?php echo $product['stock']; ?>">
                                        <button class="btn btn-sm btn-outline-secondary" onclick="incrementQuantity(<?php echo $product['product_id']; ?>, <?php echo $product['stock']; ?>)">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                    </div>
                                    <div>
                                    <button class="btn btn-primary add-to-cart" 
        data-id="<?php echo $product['product_id']; ?>"
        data-name="<?php echo $product['product_name']; ?>"
        data-price="<?php echo $product['price']; ?>"
        <?php echo ($product['stock'] <= 0) ? 'disabled' : ''; ?>>
    <i class="bi bi-cart-plus"></i>
</button>
                                    </div>
                                </td>
                                <td>
    <?php 
    $product_units = getProductUnits($pdo, $product["product_id"]);
    echo htmlspecialchars($product["measurement"]); // Show primary unit
    if (count($product_units) > 1): ?>
        <small class="text-muted">(+<?= count($product_units)-1 ?> other units)</small>
    <?php endif; ?>
</td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5"><p class="text-center">No products found.</p></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="cart-section">
                <div class="cart-header">
                    <h3 class="m-0">Customer Cart</h3>
                </div>
                
                <div class="customer-display">
                    <div id="customer-display-items">
                        <!-- Items will be added here -->
                    </div>
                    <hr style="border-color: #555; margin: 5px 0;">
                    <div class="item-line">
                        <span>Total:</span>
                        <span id="customer-display-total">₱0.00</span>
                    </div>
                </div>
                
                <div class="cart-items-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th>Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="cart-items"></tbody>
                    </table>
                </div>
                
                <div class="cart-footer">
                    <div class="total-display">
                        <div class="total-line">
                            <span>Subtotal:</span>
                            <span>₱<span id="subtotal-price">0.00</span></span>
                        </div>
                        <div class="total-line" style="display:none">
                            <span>VAT (1.25%):</span>
                            <span>₱<span id="vat-amount">0.00</span></span>
                        </div>
                        <div class="total-line grand-total">
                            <span>Total:</span>
                            <span>₱<span id="total-price">0.00</span></span>
                        </div>
                    </div>
                    
                    <div class="pos-keyboard">
                        <button class="btn btn-outline-primary" onclick="addManualItem()">Add Item</button>
                        <button class="btn btn-outline-warning" onclick="applyDiscount()">Discount</button>
                        <button class="btn btn-outline-danger" onclick="clearCart()">Clear</button>
                        <button class="btn btn-success" onclick="processPayment()">Pay</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
        </div>


<!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addItemModalLabel">Add Manual Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger" id="addItemError" style="display: none;"></div>
                <form id="addItemForm">
                    <div class="mb-3">
                        <label for="manualItemName" class="form-label">Item Name</label>
                        <input type="text" class="form-control" id="manualItemName" required>
                    </div>
                    <div class="mb-3">
                        <label for="manualItemPrice" class="form-label">Price (₱)</label>
                        <input type="number" class="form-control" id="manualItemPrice" min="0.01" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="manualItemQuantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="manualItemQuantity" min="1" value="1" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Add to Cart</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Discount Modal -->
<div class="modal fade" id="discountModal" tabindex="-1" aria-labelledby="discountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="discountModalLabel">Apply Discount</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger" id="discountError" style="display: none;"></div>
                <form id="discountForm">
                    <div class="mb-3">
                        <label for="discountType" class="form-label">Discount Type</label>
                        <select class="form-select" id="discountType" required>
                            <option value="none">No Discount</option>
                            <option value="senior">Senior Citizen (20%)</option>
                            <option value="employee">Employee (25%)</option>
                            <option value="custom">Custom Discount</option>
                        </select>
                    </div>
                    <div class="mb-3" id="customDiscountField" style="display: none;">
                        <label for="customDiscountValue" class="form-label">Discount Percentage (%)</label>
                        <input type="number" class="form-control" id="customDiscountValue" min="0" max="100" step="0.01">
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Apply Discount</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Unit Selection Modal -->
<div class="modal fade" id="unitSelectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="unitSelectionBody">
                <!-- Units will be populated here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
<!-- Toast container for notifications -->
<script>
            
           const USER_ID = <?php echo json_encode($user_id); ?>;
           const USERNAME = <?php echo json_encode($username); ?>;
</script>
</body>
</html>