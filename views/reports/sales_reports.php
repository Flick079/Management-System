<?php
require_once __DIR__ . '/../../controllers/salesReportController.php';
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
    <title>Sales Report</title>
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/sidebar.php' ?>
    <div class="content">
    <div class="header-content d-flex justify-content-between align-items-center">
            <h4>Sales Reports</h4>
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
                            <h5 class="card-title">Total Sales</h5>
                            <p class="card-text h4">₱<?php echo number_format($summary['total_sales'], 2) ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Transactions</h5>
                            <p class="card-text h4"><?php echo $summary['total_transactions'] ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total VAT</h5>
                            <p class="card-text h4">₱<?php echo number_format($summary['total_vat'], 2) ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body">
                            <h5 class="card-title">Total Discounts</h5>
                            <p class="card-text h4">₱<?php echo number_format($summary['total_discounts'], 2) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Methods Breakdown -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Payment Methods</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <p>Cash: ₱<?php echo number_format($summary['cash_sales'], 2) ?></p>
                        </div>
                        <div class="col-md-3">
                            <p>Credit Card: ₱<?php echo number_format($summary['credit_card_sales'], 2) ?></p>
                        </div>
                        <div class="col-md-3">
                            <p>GCash: ₱<?php echo number_format($summary['gcash_sales'], 2) ?></p>
                        </div>
                        <div class="col-md-3">
                            <p>Bank Transfer: ₱<?php echo number_format($summary['bank_transfer_sales'], 2) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Products -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Top Selling Products</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity Sold</th>
                                <th>Total Sales</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topProducts as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['product_name']) ?></td>
                                <td><?php echo $product['total_quantity'] ?></td>
                                <td>₱<?php echo number_format($product['total_sales'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Detailed Transactions -->
            <div class="card">
                <div class="card-header">
                    <h5>Transaction Details</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Subtotal</th>
                                <th>VAT</th>
                                <th>Discount</th>
                                <th>Total</th>
                                <th>Payment Method</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sales as $sale): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($sale['transaction_id']) ?></td>
                                <td><?php echo date('M d, Y h:i A', strtotime($sale['transaction_date'])) ?></td>
                                <td><?php echo $sale['items_count'] ?> (<?php echo $sale['total_quantity'] ?>)</td>
                                <td>₱<?php echo number_format($sale['subtotal'], 2) ?></td>
                                <td>₱<?php echo number_format($sale['vat'], 2) ?></td>
                                <td>
                                    <?php if ($sale['discount_type'] != 'none'): ?>
                                        <?php echo ucfirst($sale['discount_type']) ?>: ₱<?php echo number_format($sale['discount_amount'], 2) ?>
                                    <?php else: ?>
                                        None
                                    <?php endif; ?>
                                </td>
                                <td>₱<?php echo number_format($sale['total'], 2) ?></td>
                                <td><?php echo ucfirst(str_replace('_', ' ', $sale['payment_method'])) ?></td>
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