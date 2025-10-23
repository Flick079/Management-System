<?php
require_once __DIR__ . '/../../controllers/inventoryReportController.php';
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
    <script defer src="../../public/js/main.js"></script>
    <title>Inventory Reports</title>
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/sidebar.php' ?>
    <div class="content">
    <div class="header-content d-flex justify-content-between align-items-center">
            <h4>Inventory Reports</h4>
            <div class="d-flex gap-3 align-items-center">
                <form method="GET" class="d-flex gap-2 align-items-center">
                    <div class="btn-group">
                        <a href="?period=day" class="btn btn-sm btn-outline-primary <?php echo $period === 'day' ? 'active' : '' ?>">Daily</a>
                        <a href="?period=week" class="btn btn-sm btn-outline-primary <?php echo $period === 'week' ? 'active' : '' ?>">Weekly</a>
                        <a href="?period=month" class="btn btn-sm btn-outline-primary <?php echo $period === 'month' ? 'active' : '' ?>">Monthly</a>
                        <a href="?period=year" class="btn btn-sm btn-outline-primary <?php echo $period === 'year' ? 'active' : '' ?>">Yearly</a>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <input type="date" name="start_date" class="form-control form-control-sm" 
                                   value="<?php echo $customStart ? htmlspecialchars($customStart) : '' ?>">
                            <span class="input-group-text">to</span>
                            <input type="date" name="end_date" class="form-control form-control-sm" 
                                   value="<?php echo $customEnd ? htmlspecialchars($customEnd) : '' ?>">
                            <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                        </div>
                    </div>
                </form>
                <a href="?export=1&period=<?php echo $period ?>&start_date=<?php echo $customStart ?>&end_date=<?php echo $customEnd ?>" 
                   class="btn btn-success btn-sm">
                    <i class="bi bi-file-earmark-excel"></i> Export
                </a>
            </div>
        </div>
        <div class="body-content mt-4">
            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Products</h5>
                            <p class="card-text h4"><?php echo $overview['total_products'] ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Stock</h5>
                            <p class="card-text h4"><?php echo $overview['total_stock'] ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5 class="card-title">Inventory Value</h5>
                            <p class="card-text h4">₱<?php echo number_format($overview['total_inventory_value'], 2) ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body">
                            <h5 class="card-title">Low Stock Items</h5>
                            <p class="card-text h4"><?php echo $overview['low_stock_items'] ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Low Stock Items -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Low Stock Items (Below Reorder Point)</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead class="table-danger">
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Current Stock</th>
                                <th>Reorder Point</th>
                                <th>Unit</th>
                                <th>Price</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lowStockItems as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['product_name']) ?></td>
                                <td><?php echo htmlspecialchars($item['category_name']) ?></td>
                                <td class="<?php echo $item['stock'] == 0 ? 'text-danger fw-bold' : '' ?>">
                                    <?php echo $item['stock'] ?>
                                </td>
                                <td><?php echo $item['reorder_point'] ?></td>
                                <td><?php echo htmlspecialchars($item['measurement']) ?></td>
                                <td>₱<?php echo number_format($item['price'], 2) ?></td>
                                <td>
                                    <?php if ($item['stock'] == 0): ?>
                                        <span class="badge bg-danger">Out of Stock</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Low Stock</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Inventory by Category -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Inventory by Category</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead class="table-primary">
                            <tr>
                                <th>Category</th>
                                <th>Products</th>
                                <th>Total Stock</th>
                                <th>Total Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inventoryByCategory as $category): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($category['category_name']) ?></td>
                                <td><?php echo $category['product_count'] ?></td>
                                <td><?php echo $category['total_stock'] ?></td>
                                <td>₱<?php echo number_format($category['category_value'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Product Movement -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Product Movement Analysis</h5>
                    <form method="GET" class="d-flex gap-2">
                        <div class="form-group">
                            <label for="days">Last</label>
                            <select name="days" id="days" class="form-select" onchange="this.form.submit()">
                                <option value="7" <?php echo $days == 7 ? 'selected' : '' ?>>7 days</option>
                                <option value="30" <?php echo $days == 30 ? 'selected' : '' ?>>30 days</option>
                                <option value="90" <?php echo $days == 90 ? 'selected' : '' ?>>90 days</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead class="table-info">
                            <tr>
                                <th>Product</th>
                                <th>Current Stock</th>
                                <th>Sold (Qty)</th>
                                <th>Avg Daily Sales</th>
                                <th>Days of Supply</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productMovement as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['product_name']) ?></td>
                                <td><?php echo $product['stock'] ?></td>
                                <td><?php echo $product['sold_quantity'] ?></td>
                                <td><?php echo number_format($product['avg_daily_sales'], 2) ?></td>
                                <td class="<?php echo $product['days_of_supply'] < 14 ? 'text-warning fw-bold' : '' ?>">
                                    <?php echo is_numeric($product['days_of_supply']) ? number_format($product['days_of_supply'], 1) : 'N/A' ?>
                                    <?php if (is_numeric($product['days_of_supply']) && $product['days_of_supply'] < 14): ?>
                                        <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>