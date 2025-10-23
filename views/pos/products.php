<?php
require_once __DIR__ . '/../../middleware/user_exists.php';
require_once __DIR__ . '/../../middleware/verify.php';
require_once __DIR__ . '/../../controllers/productsController.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="../../public/css/products.css">
    <link rel="stylesheet" href="../../public/css/bootstrap-icons-1.11.0/bootstrap-icons.min.css">
    <script defer src="../../public/js/bootstrap.bundle.min.js"></script>
    <title>
        Products Management
    </title>
    <link rel="icon" type="image/png" href="../../public/images/the_lagoon_logo.png">
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <div class="content">
        <div class="content-header d-flex justify-content-between">
            <h4>Products</h4>
            <div class="btns">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add_product_modal">
                    <i class="bi bi-plus"></i>
                    Add product
                </button>
                <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#expiry_report_modal">
                    <i class="bi bi-calendar-exclamation"></i>
                    Expiry Report
                </button>
                <!-- <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#discard_batches_modal">
                    <i class="bi bi-calendar-exclamation"></i>
                    Returned Batches
                </button> -->
            </div>
        </div>
        <div class="content-body">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Product Image</th>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Reorder Point</th>
                        <th>Category</th>
                        <th>Unit</th>
                        <th>Expiration</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($products as $product): 
                        $isLowStock = $product["stock"] < $product["reorder_point"];
                        $rowClass = '';
                        
                        if (!empty($product["expiration_date"])) {
                            $expDate = new DateTime($product["expiration_date"]);
                            $today = new DateTime();
                            $interval = $today->diff($expDate);
                            $daysRemaining = $interval->days;
                            
                            if ($interval->invert) { // Already expired
                                $rowClass = 'expired';
                            } elseif ($daysRemaining <= 30) { // Expiring soon
                                $rowClass = 'expiring-soon';
                            }
                        } elseif ($isLowStock) {
                            $rowClass = 'low-stock';
                        }
                    ?>
                        <tr class="<?php echo $rowClass ?>">
                            <td class="d-flex justify-content-center">
                                <img src="<?php echo htmlspecialchars($product["image"]); ?>" class="img-thumbnail employee-img" 
                                style="height: 110px; max-width: 130px; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#expand_product_picture_<?php echo htmlspecialchars($product["product_id"]) ?>">
                            </td>
                            <td><?php echo htmlspecialchars($product["product_name"]) ?></td>
                            <td><?php echo number_format($product["price"], 2) ?></td>
                            <td>
                                <?php echo htmlspecialchars($product["stock"]) ?>
                                <?php if ($isLowStock): ?>
                                    <i class="bi bi-exclamation-triangle-fill text-danger animate-pulse" title="Low stock!"></i>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($product["reorder_point"]) ?></td>
                            <td><?php echo htmlspecialchars($product["category_name"]) ?></td>
                            <td><?php echo htmlspecialchars($product["measurement"]) ?></td>
                            <td>
                                <?php if(!empty($product["expiration_date"])): ?>
                                    <?php 
                                        $expDate = new DateTime($product["expiration_date"]);
                                        $today = new DateTime();
                                        $interval = $today->diff($expDate);
                                        $daysRemaining = $interval->days;
                                        
                                        if ($interval->invert): ?>
                                        <span class="text-danger">
                                            Expired <?php echo $expDate->format('M d, Y') ?>
                                        </span>
                                    <?php elseif($daysRemaining <= 30): ?>
                                        <span class="text-warning d-flex flex-column">
                                            <?php echo $expDate->format('M d, Y') ?>
                                            <small class="batch-badge"><?php echo $daysRemaining ?> days left</small>
                                        </span>
                                    <?php else: ?>
                                        <?php echo $expDate->format('M d, Y') ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">Non-perishable</span>
                                <?php endif; ?>
                            </td>
<!-- In the products.php file, modify the actions column in the main table -->
<!-- In the products.php file, modify the actions column in the main table -->
<td>
    <div class="btn-group">
        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#add_stock_modal_<?php echo htmlspecialchars($product["product_id"]) ?>">
            <i class="bi bi-plus"></i> Stock
        </button>
        <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#edit_product_modal_<?php echo htmlspecialchars($product["product_id"]) ?>">
            <i class="bi bi-pencil"></i>
        </button>
        <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#view_batches_modal_<?php echo htmlspecialchars($product["product_id"]) ?>">
            <i class="bi bi-box-seam"></i> Batches
        </button>
        <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#view_discarded_batches_modal_<?php echo htmlspecialchars($product["product_id"]) ?>">
            <i class="bi bi-trash"></i> Discarded
        </button>
        <?php if ($isLowStock): ?>
            <button class="btn btn-danger btn-order" data-bs-toggle="modal" data-bs-target="#order_supply_modal_<?php echo htmlspecialchars($product["product_id"]) ?>">
                <i class="bi bi-cart-plus"></i> Order
            </button>
        <?php endif; ?>
        <button class="btn btn-outline-dark" data-bs-toggle="modal" 
                data-bs-target="#manage_units_modal_<?php echo htmlspecialchars($product["product_id"]) ?>">
            <i class="bi bi-rulers"></i> Units
        </button>
    </div>
</td>
                        </tr>
                        
                        <!-- Expand Product Picture Modal -->
                        <div class="modal fade" id="expand_product_picture_<?php echo htmlspecialchars($product["product_id"]) ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5><?php echo htmlspecialchars($product["product_name"]) ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body text-center">
                                        <img src="<?php echo htmlspecialchars($product["image"]); ?>" class="img-fluid" style="max-height: 70vh;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Add Product Modal -->
        <div class="modal fade" id="add_product_modal" aria-labelledby="add_product_modal" aria-hidden="true" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Add new product</h5>
                        <button class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="../../controllers/productsController.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="" class="form-label">Product Name</label>
                                <input type="text" name="product_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="" class="form-label">Price</label>
                                <input type="number" name="price" class="form-control" step="0.01" min="0" required>
                            </div>
                            <div class="mb-3">
                                <label for="" class="form-label">Initial Stock</label>
                                <input type="number" name="stock" class="form-control" min="0" required>
                            </div>
                            <div class="mb-3">
                                <label for="" class="form-label">Reorder Point</label>
                                <input type="number" name="reorder_point" class="form-control" min="0" required>
                            </div>
                            <div class="mb-3">
                                <label for="" class="form-label">Category</label>
                                <select name="category_id" class="form-control" required>
                                    <?php foreach($categories as $category): ?>
                                        <option value="<?php echo htmlspecialchars($category["category_id"]) ?>">
                                            <?php echo htmlspecialchars($category["category_name"]) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="" class="form-label">Unit of Measurement</label>
                                <select name="unit_id" class="form-control" required>
                                    <?php foreach($measurements as $measurement): ?>
                                        <option value="<?php echo htmlspecialchars($measurement["unit_id"]) ?>">
                                            <?php echo htmlspecialchars($measurement["measurement"]) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="" class="form-label">Expiration Date</label>
                                <input type="date" name="expiration_date" class="form-control" min="<?php echo date('Y-m-d'); ?>">
                                <small class="text-muted">Leave blank for non-perishable products</small>
                            </div>
                            <div class="mb-3">
                                <label for="" class="form-label">Product Image</label>
                                <input type="file" name="image" class="form-control" accept="image/*" required>
                            </div>
                            <div class="mb-3">
                                <button class="btn btn-primary" name="add_product_btn">Add Product</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Expiry Report Modal -->
        <div class="modal fade" id="expiry_report_modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Product Expiry Report</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <ul class="nav nav-tabs" id="expiryTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="expired-tab" data-bs-toggle="tab" data-bs-target="#expired" type="button" role="tab">
                                    Expired
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="expiring-tab" data-bs-toggle="tab" data-bs-target="#expiring" type="button" role="tab">
                                    Expiring Soon (≤30 days)
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content p-3 border border-top-0" id="expiryTabsContent">
                            <div class="tab-pane fade show active" id="expired" role="tabpanel">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr class="table-danger">
                                            <th>Product</th>
                                            <th>Expired On</th>
                                            <th>Days Expired</th>
                                            <th>Stock</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $today = new DateTime();
                                        $expiredProducts = array_filter($products, function($p) use ($today) {
                                            if (empty($p['expiration_date'])) return false;
                                            $expDate = new DateTime($p['expiration_date']);
                                            return $expDate < $today;
                                        });
                                        
                                        if (empty($expiredProducts)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center">No expired products found</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($expiredProducts as $product): 
                                                $expDate = new DateTime($product['expiration_date']);
                                                $daysExpired = $today->diff($expDate)->days;
                                            ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($product['product_name']) ?></td>
                                                    <td><?php echo $expDate->format('M d, Y') ?></td>
                                                    <td><?php echo $daysExpired ?></td>
                                                    <td><?php echo $product['stock'] ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="tab-pane fade" id="expiring" role="tabpanel">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr class="table-warning">
                                            <th>Product</th>
                                            <th>Expires On</th>
                                            <th>Days Remaining</th>
                                            <th>Stock</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $soonProducts = array_filter($products, function($p) use ($today) {
                                            if (empty($p['expiration_date'])) return false;
                                            $expDate = new DateTime($p['expiration_date']);
                                            $diff = $today->diff($expDate);
                                            return !$diff->invert && $diff->days <= 30;
                                        });
                                        
                                        if (empty($soonProducts)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center">No products expiring soon</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($soonProducts as $product): 
                                                $expDate = new DateTime($product['expiration_date']);
                                                $daysRemaining = $today->diff($expDate)->days;
                                            ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($product['product_name']) ?></td>
                                                    <td><?php echo $expDate->format('M d, Y') ?></td>
                                                    <td><?php echo $daysRemaining ?></td>
                                                    <td><?php echo $product['stock'] ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Discarded Batches Modal -->

        <div class="modal fade" id="discard_batches_modal" aria-labelledby="discard_batches_modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Discarded Batches</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                    
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
                                <?php foreach($discardeds as $discarded): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($discarded["batch_id"]) ?></td>
                                        <td><?php echo date("F j, Y", strtotime($discarded["received_date"])); ?></td>
                                        <td><?php echo date("F j, Y", strtotime($discarded["disposal_date"])); ?></td>
                                        <td><?php echo htmlspecialchars($discarded["supplier_name"]) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>                 

                    </div>
                </div>
            </div>
        </div>

        <!-- Add Stock Modal -->
        <?php foreach($products as $product): ?>
            <div class="modal fade" aria-labelledby="add_stock_modal_<?php echo htmlspecialchars($product["product_id"])?>" 
                 id="add_stock_modal_<?php echo htmlspecialchars($product["product_id"])?>" aria-hidden="true" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4>Add Stock to <?php echo htmlspecialchars($product["product_name"]) ?></h4>
                            <button class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="../../controllers/productsController.php" method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Current Stock</label>
                                    <input type="number" value="<?php echo htmlspecialchars($product["stock"]) ?>" class="form-control" disabled>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Quantity to Add</label>
                                    <input type="number" name="added_stock" class="form-control" min="1" required>
                                </div>
                                <!-- <?php //if (!empty($product["expiration_date"])): ?> -->
                                <div class="mb-3">
                                    <label class="form-label">Batch Expiration Date</label>
                                    <input type="date" name="batch_expiration" class="form-control" 
                                           min="<?php echo date('Y-m-d'); ?>" 
                                           value="<?php echo htmlspecialchars($product["expiration_date"]) ?>">
                                    <small class="text-muted">Leave blank to use product's current expiration date</small>
                                </div>
                                <!-- <?php //endif; ?> -->
                                <div class="mb-3">
                                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product["product_id"]) ?>">
                                    <button class="btn btn-primary" name="add_new_stock_btn">Add Stock</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- add batch modal -->
            
<!-- Add this modal after the add stock modal -->
<div class="modal fade" id="batch_details_modal_<?php echo $product['product_id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Inventory Batches for <?php echo htmlspecialchars($product['product_name']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php 
                $batches = getProductBatches($pdo, $product['product_id']);
                if (empty($batches)): ?>
                    <p>No inventory batches found for this product.</p>
                <?php else: ?>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Batch ID</th>
                                <th>Quantity</th>
                                <th>Received Date</th>
                                <th>Expiration Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($batches as $batch): 
                                $status = '';
                                $statusClass = '';
                                if ($batch['expiration_date']) {
                                    $today = new DateTime();
                                    $expDate = new DateTime($batch['expiration_date']);
                                    if ($expDate < $today) {
                                        $status = 'Expired';
                                        $statusClass = 'text-danger';
                                    } else {
                                        $diff = $today->diff($expDate);
                                        if ($diff->days <= 30) {
                                            $status = 'Expiring in ' . $diff->days . ' days';
                                            $statusClass = 'text-warning';
                                        } else {
                                            $status = 'Good';
                                            $statusClass = 'text-success';
                                        }
                                    }
                                } else {
                                    $status = 'Non-perishable';
                                    $statusClass = 'text-muted';
                                }
                            ?>
                                <tr>
                                    <td><?php echo $batch['batch_id'] ?></td>
                                    <td><?php echo $batch['quantity'] ?></td>
                                    <td><?php echo date('M d, Y', strtotime($batch['received_date'])) ?></td>
                                    <td><?php echo $batch['expiration_date'] ? date('M d, Y', strtotime($batch['expiration_date'])) : 'N/A' ?></td>
                                    <td class="<?php echo $statusClass ?>"><?php echo $status ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
            <!-- end add batch modal -->
            <!-- Edit Product Modal -->
        
            <div class="modal fade" id="edit_product_modal_<?php echo htmlspecialchars($product["product_id"]) ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Product</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="../../controllers/productsController.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="product_id" value="<?php echo $product["product_id"] ?>">
                                <div class="mb-3">
                                    <label class="form-label">Product Name</label>
                                    <input type="text" name="product_name" class="form-control" 
                                           value="<?php echo htmlspecialchars($product["product_name"]) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Price</label>
                                    <input type="number" name="price" class="form-control" step="0.01" min="0" 
                                           value="<?php echo htmlspecialchars($product["price"]) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Reorder Point</label>
                                    <input type="number" name="reorder_point" class="form-control" min="0" 
                                           value="<?php echo htmlspecialchars($product["reorder_point"]) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Category</label>
                                    <select name="category_id" class="form-control" required>
                                        <?php foreach($categories as $category): ?>
                                            <option value="<?php echo $category["category_id"] ?>" 
                                                <?php if($category["category_id"] == $product["category_id"]) echo 'selected' ?>>
                                                <?php echo htmlspecialchars($category["category_name"]) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Unit of Measurement</label>
                                    <select name="unit_id" class="form-control" required>
                                        <?php foreach($measurements as $measurement): ?>
                                            <option value="<?php echo $measurement["unit_id"] ?>" 
                                                <?php if($measurement["unit_id"] == $product["unit_id"]) echo 'selected' ?>>
                                                <?php echo htmlspecialchars($measurement["measurement"]) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <!-- In the edit product modal -->
<div class="mb-3">
    <label class="form-label">Unit Prices</label>
    <div class="unit-prices-container">
        <?php 
        $product_units = getProductUnits($pdo, $product["product_id"]);
        foreach ($product_units as $unit): ?>
            <div class="input-group mb-2">
                <span class="input-group-text"><?php echo htmlspecialchars($unit["measurement"]) ?></span>
                <input type="number" name="unit_price[<?php echo $unit["unit_id"] ?>]" 
                       class="form-control" step="0.01" min="0"
                       value="<?php echo $unit["unit_price"] ?? $product["price"] ?>">
                <?php if (!$unit["is_primary"]): ?>
                    <button class="btn btn-outline-danger remove-unit-btn" 
                            type="button" data-unit-id="<?php echo $unit["unit_id"] ?>">
                        <i class="bi bi-trash"></i>
                    </button>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
                                <div class="mb-3">
                                    <label class="form-label">Expiration Date</label>
                                    <input type="date" name="expiration_date" class="form-control" 
                                           value="<?php echo htmlspecialchars($product["expiration_date"]) ?>">
                                    <small class="text-muted">Leave blank for non-perishable products</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Product Image</label>
                                    <input type="file" name="image" class="form-control" accept="image/*">
                                    <small class="text-muted">Current: <?php echo basename($product["image"]) ?></small>
                                </div>
                                <div class="mb-3">
                                    <button type="submit" name="update_product_btn" class="btn btn-primary">Update Product</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Batch Viewing Modal -->
<!-- In products.php -->
<!-- View Batches Modal (simplified without the discarded batches button) -->
<div class="modal fade" id="view_batches_modal_<?php echo htmlspecialchars($product["product_id"]) ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Active Inventory Batches - <?php echo htmlspecialchars($product["product_name"]) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span><strong>Total Active Stock:</strong> <?php echo htmlspecialchars($product["stock"]) ?></span>
                    </div>
                </div>
                
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Batch ID</th>
                            <th>Quantity</th>
                            <th>Received Date</th>
                            <th>Expiration Date</th>
                            <th>Days Remaining</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $batches = getProductBatches($pdo, $product["product_id"]);
                        if (empty($batches)): ?>
                            <tr>
                                <td colspan="7" class="text-center">No active inventory batches found</td>
                            </tr>
                        <?php else: 
                            $today = new DateTime();
                            foreach ($batches as $batch): 
                                $status = '';
                                $statusClass = '';
                                $daysRemaining = null;
                                
                                if (!empty($batch['expiration_date'])) {
                                    $expDate = new DateTime($batch['expiration_date']);
                                    $interval = $today->diff($expDate);
                                    
                                    if ($expDate < $today) {
                                        $status = 'Expired';
                                        $statusClass = 'text-danger';
                                        $daysRemaining = 'Expired ' . $interval->days . ' days ago';
                                    } else {
                                        $daysRemaining = $interval->days;
                                        if ($daysRemaining <= 30) {
                                            $status = 'Expiring Soon';
                                            $statusClass = 'text-warning';
                                        } else {
                                            $status = 'Good';
                                            $statusClass = 'text-success';
                                        }
                                    }
                                } else {
                                    $status = 'Non-perishable';
                                    $statusClass = 'text-muted';
                                    $daysRemaining = 'N/A';
                                }
                        ?>
                            <tr class="<?php echo $status === 'Expired' ? 'table-danger' : '' ?>">
                                <td><?php echo $batch['batch_id'] ?></td>
                                <td><?php echo $batch['quantity'] ?></td>
                                <td><?php echo date('M d, Y', strtotime($batch['received_date'])) ?></td>
                                <td><?php echo !empty($batch['expiration_date']) ? date('M d, Y', strtotime($batch['expiration_date'])) : 'N/A' ?></td>
                                <td><?php echo $daysRemaining ?></td>
                               <!-- In the batches table (inside view_batches_modal) -->
<td class="<?php echo $statusClass ?>">
    <?php echo $status ?>
   
</td>
<td>
<button class="btn btn-sm btn-danger discard-batch-btn" 
                data-bs-toggle="modal" data-bs-target="#discard_batch_modal"
                data-batch-id="<?php echo $batch['batch_id'] ?>">
            <i class="bi bi-trash"></i> Discard
        </button>
</td>

                            </tr>
                        <?php endforeach; 
                        endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Standalone Discarded Batches Modal -->
<div class="modal fade" id="view_discarded_batches_modal_<?php echo htmlspecialchars($product["product_id"]) ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title">Discarded Batches - <?php echo htmlspecialchars($product["product_name"]) ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php 
                $discardedBatches = getDiscardedBatches($pdo, $product["product_id"]);
                if (empty($discardedBatches)): ?>
                    <p>No discarded batches found for this product.</p>
                <?php else: ?>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Batch ID</th>
                                <th>Quantity</th>
                                <th>Received Date</th>
                                <th>Expiration Date</th>
                                <th>Discard Date</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($discardedBatches as $batch): ?>
                                <tr>
                                    <td><?php echo $batch['batch_id'] ?></td>
                                    <td><?php echo $batch['quantity'] ?></td>
                                    <td><?php echo date('M d, Y', strtotime($batch['received_date'])) ?></td>
                                    <td><?php echo $batch['expiration_date'] ? date('M d, Y', strtotime($batch['expiration_date'])) : 'N/A' ?></td>
                                    <td><?php echo date('M d, Y', strtotime($batch['status_change_date'])) ?></td>
                                    <td><?php echo htmlspecialchars($batch['reason']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Discarded Batches Modal -->
<div class="modal fade" id="discarded_batches_modal_<?php echo htmlspecialchars($product["product_id"]) ?>" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title">Discarded Batches - <?php echo htmlspecialchars($product["product_name"]) ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php 
                $discardedBatches = getDiscardedBatches($pdo, $product["product_id"]);
                if (empty($discardedBatches)): ?>
                    <p>No discarded batches found for this product.</p>
                <?php else: ?>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Batch ID</th>
                                <th>Quantity</th>
                                <th>Received Date</th>
                                <th>Expiration Date</th>
                                <th>Discard Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($discardedBatches as $batch): ?>
                                <tr>
                                    <td><?php echo $batch['batch_id'] ?></td>
                                    <td><?php echo $batch['quantity'] ?></td>
                                    <td><?php echo date('M d, Y', strtotime($batch['received_date'])) ?></td>
                                    <td><?php echo $batch['expiration_date'] ? date('M d, Y', strtotime($batch['expiration_date'])) : 'N/A' ?></td>
                                    <td><?php echo date('M d, Y', strtotime($batch['status_change_date'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<!-- Discard Batch Modal -->
<!-- Discard Batch Modal -->
<div class="modal fade" id="discard_batch_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Discard Batch</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../../controllers/productsController.php" method="POST">
                    <input type="hidden" name="batch_id" id="discard_batch_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Discard Reason</label>
                        <select name="discard_reason" class="form-select" required>
                            <option value="">Select a reason...</option>
                            <option value="expired">Expired</option>
                            <option value="damaged">Damaged</option>
                            <option value="returned">Returned to Supplier</option>
                            <option value="spoiled">Spoiled</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="other_reason_container" style="display:none;">
                        <label class="form-label">Specify Reason</label>
                        <input type="text" name="other_reason" class="form-control" placeholder="Enter specific reason">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Additional Notes</label>
                        <textarea name="notes" class="form-control" placeholder="Any additional information"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <button type="submit" name="discard_batch_btn" class="btn btn-danger w-100">
                            <i class="bi bi-trash"></i> Confirm Discard
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Order Supply Modal -->
<div class="modal fade" id="order_supply_modal_<?php echo htmlspecialchars($product["product_id"]) ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Order Supply for <?php echo htmlspecialchars($product["product_name"]) ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../../controllers/productsController.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Current Stock</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($product["stock"]) ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reorder Point</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($product["reorder_point"]) ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity to Order</label>
                        <input type="number" name="order_quantity" class="form-control" 
                               min="1" value="<?php echo max(1, $product["reorder_point"] - $product["stock"]) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Supplier</label>
                        <select name="supplier_id" class="form-control" required>
                            <?php 
                            $product_suppliers = getProductSuppliers($pdo, $product["product_id"]);
                            if (empty($product_suppliers)): ?>
                                <option value="">No suppliers assigned</option>
                            <?php else: ?>
                                <?php foreach($product_suppliers as $supplier): ?>
                                    <option value="<?php echo htmlspecialchars($supplier["supplier_id"]) ?>">
                                        <?php echo htmlspecialchars($supplier["supplier_name"]) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Expected Delivery Date</label>
                        <input type="date" name="expected_delivery" class="form-control" 
                               min="<?php echo date('Y-m-d', strtotime('+1 day')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Unit Price</label>
                        <input type="number" name="unit_price" class="form-control" 
                               step="0.01" min="0.01" value="<?php echo htmlspecialchars($product["price"]) ?>" required>
                    </div>
                    <div class="mb-3">
                        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product["product_id"]) ?>">
                        <button type="submit" name="create_supply_order_btn" class="btn btn-danger">
                            <i class="bi bi-cart-plus"></i> Create Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Add this modal after the other modals in products.php -->
<div class="modal fade" id="manage_units_modal_<?php echo htmlspecialchars($product["product_id"]) ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manage Units for <?php echo htmlspecialchars($product["product_name"]) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>Current Units</h6>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Unit</th>
                            <th>Conversion Factor</th>
                            <th>Price</th>
                            <th>Primary</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $product_units = getProductUnits($pdo, $product["product_id"]);
                        foreach ($product_units as $unit): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($unit["measurement"]) ?></td>
                                <td><?php echo htmlspecialchars($unit["conversion_factor"]) ?></td>
                                <td>
                                    <?php if ($unit["is_primary"]): ?>
                                        <span class="badge bg-primary">Primary</span>
                                    <?php else: ?>
                                        <form method="POST" action="../../controllers/productsController.php" style="display:inline;">
                                            <input type="hidden" name="product_id" value="<?php echo $product["product_id"] ?>">
                                            <input type="hidden" name="mapping_id" value="<?php echo $unit["mapping_id"] ?>">
                                            <button type="submit" name="set_primary_unit_btn" class="btn btn-sm btn-outline-primary">
                                                Make Primary
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!$unit["is_primary"]): ?>
                                        <form method="POST" action="../../controllers/productsController.php" style="display:inline;">
                                            <input type="hidden" name="mapping_id" value="<?php echo $unit["mapping_id"] ?>">
                                            <button type="submit" name="remove_unit_btn" class="btn btn-sm btn-danger">
                                                Remove
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <hr>
                
                <h6>Add New Unit</h6>
                <form method="POST" action="../../controllers/productsController.php">
                    <input type="hidden" name="product_id" value="<?php echo $product["product_id"] ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Unit of Measurement</label>
                        <select name="unit_id" class="form-control" required>
                            <?php 
                            // Get all units not already assigned to this product
                            $assigned_unit_ids = array_column($product_units, 'unit_id');
                            $all_units = getMeasurement($pdo);
                            foreach ($all_units as $unit): 
                                if (!in_array($unit["unit_id"], $assigned_unit_ids)): ?>
                                    <option value="<?php echo htmlspecialchars($unit["unit_id"]) ?>">
                                        <?php echo htmlspecialchars($unit["measurement"]) ?>
                                    </option>
                                <?php endif; 
                            endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Conversion Factor</label>
                        <input type="number" name="conversion_factor" class="form-control" 
                               step="0.0001" min="0.0001" value="1.0000" required>
                        <small class="text-muted">
                            How many of this unit equals 1 of the primary unit. 
                            Example: If primary is "bottle" and this is "glass", 
                            enter how many glasses make 1 bottle.
                        </small>
                    </div>
                    <div class="mb-3">
                        <label for="" class="form-label">Price</label>
                        <input type="number" name="price" class="form-control">
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_primary" class="form-check-input" id="is_primary_<?php echo $product["product_id"] ?>">
                        <label class="form-check-label" for="is_primary_<?php echo $product["product_id"] ?>">Set as primary unit</label>
                    </div>
                    
                    <div class="mb-3">
                        <button type="submit" name="add_unit_btn" class="btn btn-primary">Add Unit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
        <?php endforeach; ?>
    </div>
    <script>
// JavaScript to set the batch ID when discard button is clicked
document.querySelectorAll('.discard-batch-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('discard_batch_id').value = this.dataset.batchId;
    });
});

document.addEventListener('DOMContentLoaded', function() {
    // Handle modal stacking issue
    var discardedModals = document.querySelectorAll('[id^="discarded_batches_modal_"]');
    discardedModals.forEach(function(modal) {
        modal.addEventListener('hidden.bs.modal', function () {
            // When this modal closes, make sure the parent modal is still there
            var parentModal = document.getElementById('view_batches_modal_' + modal.id.split('_').pop());
            if (parentModal) {
                var parentModalInstance = bootstrap.Modal.getInstance(parentModal);
                if (parentModalInstance) {
                    parentModalInstance._backdrop._config.isStatic = false;
                }
            }
        });
    });
});
document.addEventListener('DOMContentLoaded', function() {
    // Handle discard button clicks
    document.querySelectorAll('.discard-batch-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('discard_batch_id').value = this.dataset.batchId;
        });
    });

    // Show/hide other reason field based on selection
    const reasonSelect = document.querySelector('select[name="discard_reason"]');
    const otherReasonContainer = document.getElementById('other_reason_container');
    
    if (reasonSelect && otherReasonContainer) {
        reasonSelect.addEventListener('change', function() {
            otherReasonContainer.style.display = this.value === 'other' ? 'block' : 'none';
        });
    }
});
</script>
</body>
</html>