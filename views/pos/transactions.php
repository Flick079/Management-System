<?php
require_once __DIR__ . '/../../configs/database.php';
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
    <script defer src="../../public/js/transactions.js"></script>
    <title>Transactions</title>
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/sidebar.php' ?>
    <div class="content">
    <div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Transaction History</h1>
        <!-- <button class="btn btn-primary" id="exportBtn">Export to CSV</button> -->
    </div>

    <!-- Search and Filter Controls -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="input-group">
                <span class="input-group-text">From</span>
                <input type="date" class="form-control" id="dateFrom">
            </div>
        </div>
        <div class="col-md-3">
            <div class="input-group">
                <span class="input-group-text">To</span>
                <input type="date" class="form-control" id="dateTo">
            </div>
        </div>
        <div class="col-md-3">
        <select class="form-select" id="discountFilter">
    <option value="all">All Discount Types</option>
    <option value="none">No Discount</option>
    <option value="senior">Senior Citizen</option>  
    <option value="employee">Employee</option>
    <option value="custom">Custom Discount</option>
</select>
        </div>
        <div class="col-md-3" style="display: none;">
            <div class="input-group">
                <input type="hidden" class="form-control" id="searchInput" placeholder="Search...">
                <button class="btn btn-outline-secondary" id="searchBtn">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="table-responsive">
    <table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>Transaction ID</th>
            <th>Date & Time</th>
            <th>Cashier</th>
            <th>Items</th>
            <!-- <th>Subtotal</th> -->
            <!-- <th>VAT</th> -->
            <th>Total</th>
            <th>Discount</th>
            <th>Final Total</th>
            <th>Actions</th>
        </tr>
    </thead>
            <tbody id="transactionsTableBody">
                <!-- Transaction rows will be loaded here -->
            </tbody>
        </table>
    </div>

    <!-- Pagination Controls -->
    <div class="d-flex justify-content-between align-items-center mt-4">
        <div style="color:white">
            <span id="pageInfo">Showing 1 to 10 of 0 entries</span>
        </div>
        <div>
            <nav aria-label="Page navigation">
                <ul class="pagination" id="pagination">
                    <!-- Pagination will be generated here -->
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Transaction Details Modal -->
<div class="modal fade" id="transactionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Transaction Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="transactionDetails">
                <!-- Transaction details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="printReceiptBtn">Print Receipt</button>
            </div>
        </div>
    </div>
</div>



    </div>
</body>
</html>