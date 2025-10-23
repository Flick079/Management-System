<?php
require_once __DIR__ . '/../../middleware/user_exists.php';
require_once __DIR__ . '/../../middleware/verify.php';
require_once __DIR__ . '/../../controllers/suppliersController.php';

// Get supplier details if viewing specific supplier
$current_supplier = null;
if (isset($_GET['id'])) {
    $current_supplier = getSupplierById($pdo, $_GET['id']);
    $supplier_products = $current_supplier ? getSupplierProducts($pdo, $_GET['id']) : [];
    $discardeds = $current_supplier ? getDiscardedBatch($pdo, $_GET['id']) : [];
}

// Get products for dropdowns
$all_products = getProducts($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="../../public/css/suppliers.css">
    <link rel="stylesheet" href="../../public/css/bootstrap-icons-1.11.0/bootstrap-icons.min.css">
    <script defer src="../../public/js/bootstrap.bundle.min.js"></script>
    <script defer src="../../public/js/suppliers.js"></script>
    <title>Suppliers Management</title>
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <div class="content">
        <div class="content-header d-flex justify-content-between">
            <h4><?php echo isset($current_supplier) ? htmlspecialchars($current_supplier['supplier_name']) : 'Suppliers'; ?></h4>
            <div class="btns">
                <?php if (isset($current_supplier)): ?>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#create_order_modal">
                        <i class="bi bi-cart-plus"></i>
                        Create Order
                    </button>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#add_product_modal">
                        <i class="bi bi-plus"></i>
                        Add Product
                    </button>
                    <a href="suppliers.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Back to Suppliers
                    </a>
                <?php else: ?>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add_supplier_modal">
                        <i class="bi bi-plus"></i>
                        Add Supplier
                    </button>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (isset($_SESSION["success_message"])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION["success_message"]; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION["success_message"]); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION["error_message"])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION["error_message"]; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION["error_message"]); ?>
        <?php endif; ?>
        
        <div class="content-body">
            <?php if (isset($current_supplier)): ?>
                <!-- Supplier Details View -->
                <div class="supplier-details">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Contact Person:</strong> <?php echo htmlspecialchars($current_supplier['contact_person']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($current_supplier['email']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($current_supplier['phone']); ?></p>
                            <p><strong>Address:</strong> <?php echo htmlspecialchars($current_supplier['address']); ?></p>
                        </div>
                    </div>
                </div>
                
                <h5>Supplier Products</h5>
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($supplier_products as $product): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                         class="product-img-thumb img-thumbnail">
                                </td>
                                <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                <td>₱<?php echo htmlspecialchars($product['price']); ?></td>
                                <td><?php echo htmlspecialchars($product['stock']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#remove_product_modal_<?php echo $product['product_id']; ?>">
                                        <i class="bi bi-trash"></i> Remove
                                    </button>
                                </td>
                            </tr>
                            
                            <!-- Remove Product Modal -->
                            <div class="modal fade" id="remove_product_modal_<?php echo $product['product_id']; ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Remove Product</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to remove <?php echo htmlspecialchars($product['product_name']); ?> from this supplier?</p>
                                            <form action="../../controllers/suppliersController.php" method="POST">
                                                <input type="hidden" name="supplier_id" value="<?php echo $current_supplier['supplier_id']; ?>">
                                                <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                                <div class="mb-3">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" name="remove_product_supplier_btn" class="btn btn-danger">Remove</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <h5 class="mt-4">Recent Orders</h5>
                <?php 
                $supplier_orders = getSupplierOrders($pdo, $current_supplier['supplier_id']);
                if (!empty($supplier_orders)): ?>
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Expected Delivery</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($supplier_orders as $order): ?>
                                <tr>
                                    <td><?php echo $order['order_id']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                    <td><?php echo $order['expected_delivery_date'] ? date('M d, Y', strtotime($order['expected_delivery_date'])) : 'N/A'; ?></td>
                                    <td><?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $order['status'] === 'delivered' ? 'success' : 
                                                 ($order['status'] === 'cancelled' ? 'danger' : 'warning'); 
                                        ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#view_order_modal_<?php echo $order['order_id']; ?>">
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                    </td>
                                </tr>   
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <table class="table">
    <thead class="table-dark">
        <tr>
            <th>Batch ID</th>
            <th>Received Date</th>
            <th>Discard Date</th>
            <th>Supplier</th>
        </tr>
    </thead>
    <tbody>
        <?php if(!empty($discardeds)): ?>
            <?php foreach($discardeds as $discarded): ?>
                <tr>
                    <td><?php echo htmlspecialchars($discarded["batch_id"]) ?></td>
                    <td><?php echo date("F j, Y", strtotime($discarded["received_date"])); ?></td>
                    <td><?php echo date("F j, Y", strtotime($discarded["disposal_date"])); ?></td>
                    <td><?php echo htmlspecialchars($discarded["supplier_name"]) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="4" class="text-center">No discarded batches found</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
                <?php else: ?>
                    <div class="alert alert-info">No orders found for this supplier.</div>
                <?php endif; ?>
                
            <?php else: ?>
                <!-- Main Suppliers Listing -->
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Supplier ID</th>
                            <th>Supplier Name</th>
                            <th>Contact Person</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($suppliers as $supplier): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($supplier["supplier_id"]); ?></td>
                                <td><?php echo htmlspecialchars($supplier["supplier_name"]); ?></td>
                                <td><?php echo htmlspecialchars($supplier["contact_person"]); ?></td>
                                <td><?php echo htmlspecialchars($supplier["email"]); ?></td>
                                <td><?php echo htmlspecialchars($supplier["phone"]); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="suppliers.php?id=<?php echo $supplier["supplier_id"]; ?>" 
                                           class="btn btn-sm btn-info" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <button class="btn btn-sm btn-warning" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#edit_supplier_modal_<?php echo $supplier["supplier_id"]; ?>"
                                                title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#delete_supplier_modal_<?php echo $supplier["supplier_id"]; ?>"
                                                title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Edit Supplier Modal -->
                            <div class="modal fade" id="edit_supplier_modal_<?php echo $supplier["supplier_id"]; ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Supplier</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="../../controllers/suppliersController.php" method="POST">
                                                <input type="hidden" name="supplier_id" value="<?php echo $supplier["supplier_id"]; ?>">
                                                <div class="mb-3">
                                                    <label class="form-label">Supplier Name</label>
                                                    <input type="text" name="supplier_name" class="form-control" 
                                                           value="<?php echo htmlspecialchars($supplier["supplier_name"]); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Contact Person</label>
                                                    <input type="text" name="contact_person" class="form-control" 
                                                           value="<?php echo htmlspecialchars($supplier["contact_person"]); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Email</label>
                                                    <input type="email" name="email" class="form-control" 
                                                           value="<?php echo htmlspecialchars($supplier["email"]); ?>">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Phone</label>
                                                    <input type="text" name="phone" class="form-control" 
                                                           value="<?php echo htmlspecialchars($supplier["phone"]); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Address</label>
                                                    <textarea name="address" class="form-control" required><?php echo htmlspecialchars($supplier["address"]); ?></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <button type="submit" name="update_supplier_btn" class="btn btn-primary">Update</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Delete Supplier Modal -->
                            <div class="modal fade" id="delete_supplier_modal_<?php echo $supplier["supplier_id"]; ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Confirm Delete</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to delete supplier <?php echo htmlspecialchars($supplier["supplier_name"]); ?>?</p>
                                            <form action="../../controllers/suppliersController.php" method="POST">
                                                <input type="hidden" name="supplier_id" value="<?php echo $supplier["supplier_id"]; ?>">
                                                <div class="mb-3">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" name="delete_supplier_btn" class="btn btn-danger">Delete</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- Add Supplier Modal -->
        <div class="modal fade" id="add_supplier_modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Supplier</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="../../controllers/suppliersController.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Supplier Name</label>
                                <input type="text" name="supplier_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contact Person</label>
                                <input type="text" name="contact_person" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" required></textarea>
                            </div>
                            <div class="mb-3">
                                <button type="submit" name="add_supplier_btn" class="btn btn-primary">Add Supplier</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (isset($current_supplier)): ?>
            <!-- Add Product Modal -->
            <div class="modal fade" id="add_product_modal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add Product to Supplier</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="../../controllers/suppliersController.php" method="POST">
                                <input type="hidden" name="supplier_id" value="<?php echo $current_supplier['supplier_id']; ?>">
                                <div class="mb-3">
                                    <label class="form-label">Product</label>
                                    <select name="product_id" class="form-control" required>
                                        <option value="">Select Product</option>
                                        <?php foreach ($all_products as $product): ?>
                                            <option value="<?php echo $product['product_id']; ?>">
                                                <?php echo htmlspecialchars($product['product_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <button type="submit" name="add_product_supplier_btn" class="btn btn-primary">Add Product</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Create Order Modal -->
            <div class="modal fade" id="create_order_modal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Create New Order</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="../../controllers/suppliersController.php" method="POST">
                                <input type="hidden" name="supplier_id" value="<?php echo $current_supplier['supplier_id']; ?>">
                                <div class="mb-3">
                                    <label class="form-label">Expected Delivery Date</label>
                                    <input type="date" name="expected_delivery_date" class="form-control" 
                                           min="<?php echo date('Y-m-d'); ?>">
                                </div>
                                
                                <h5>Order Items</h5>
                                <table class="table" id="order_items_table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Unit Price</th>
                                            <th>Quantity</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($supplier_products as $index => $product): ?>
                                            <tr>
                                                <td>
                                                    <?php echo htmlspecialchars($product['product_name']); ?>
                                                    <input type="hidden" name="product_id[]" value="<?php echo $product['product_id']; ?>">
                                                </td>
                                                <td>
                                                    <input type="number" name="unit_price[]" class="form-control" 
                                                           step="0.01" min="0" value="<?php echo $product['price']; ?>">
                                                </td>
                                                <td>
                                                    <input type="number" name="quantity[]" class="form-control" min="0" value="0">
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-danger remove-item">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                
                                <div class="mb-3">
                                    <button type="submit" name="create_order_btn" class="btn btn-primary">Create Order</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- View Order Modals -->
            <?php foreach ($supplier_orders as $order): 
                $order_details = getOrderDetails($pdo, $order['order_id']);
            ?>
                <div class="modal fade" id="view_order_modal_<?php echo $order['order_id']; ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Order #<?php echo $order['order_id']; ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <p><strong>Supplier:</strong> <?php echo htmlspecialchars($order_details['supplier_name']); ?></p>
                                        <p><strong>Contact:</strong> <?php echo htmlspecialchars($order_details['contact_person']); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Order Date:</strong> <?php echo date('M d, Y', strtotime($order_details['order_date'])); ?></p>
                                        <p><strong>Status:</strong> 
                                            <span class="badge bg-<?php 
                                                echo $order_details['status'] === 'delivered' ? 'success' : 
                                                     ($order_details['status'] === 'cancelled' ? 'danger' : 'warning'); 
                                            ?>">
                                                <?php echo ucfirst($order_details['status']); ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                
                                <h5>Order Items</h5>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Unit Price</th>
                                            <th>Quantity</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($order_details['items'] as $item): ?>
                                            <tr>
                                                <td>
                                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                                         class="product-img-thumb img-thumbnail me-2">
                                                    <?php echo htmlspecialchars($item['product_name']); ?>
                                                </td>
                                                <td>₱<?php echo number_format($item['unit_price'], 2); ?></td>
                                                <td><?php echo $item['quantity']; ?></td>
                                                <td>₱<?php echo number_format($item['unit_price'] * $item['quantity'], 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3" class="text-end">Total:</th>
                                            <th>₱<?php echo number_format($order_details['total_amount'], 2); ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                                
<!-- Replace the fulfill order form in the view_order_modal with this -->
<!-- Replace the form in the view_order_modal with this -->
<?php if ($order_details['status'] === 'pending'): ?>
    <form id="orderForm_<?php echo $order['order_id']; ?>" action="../../controllers/suppliersController.php" method="POST" class="mt-3">
        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
        
        <h5>Receive Items</h5>
        <table class="table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Expiration Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_details['items'] as $item): ?>
                    <tr>
                        <td>
                            <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                 class="product-img-thumb img-thumbnail me-2">
                            <?php echo htmlspecialchars($item['product_name']); ?>
                            <input type="hidden" name="product_ids[]" value="<?php echo $item['product_id']; ?>">
                            <input type="hidden" name="quantities[]" value="<?php echo $item['quantity']; ?>">
                        </td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>
                            <input type="date" name="expiration_dates[]" class="form-control expiration-date" 
                                   min="<?php echo date('Y-m-d'); ?>" required>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="d-flex justify-content-between">
            <button type="submit" name="fulfill_order_btn" class="btn btn-success">
                <i class="bi bi-check-circle"></i> Mark as Received
            </button>
            <button type="button" id="deleteOrder_<?php echo $order['order_id']; ?>" class="btn btn-danger">
                <i class="bi bi-trash"></i> Delete Order
            </button>
        </div>
    </form>
<?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>