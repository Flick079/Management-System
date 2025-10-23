<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../../public/icons/bootstrap-icons-1.11.0/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../public/css/style.css">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <title>Document</title>
</head>
<body>
<div class="sidebar">
        <nav class="nav flex-column">
            <div class="logo" style="width: 15rem;">
                <img src="../../public/images/the_lagoon_logo.png" alt="">
                <h5>The Lagoon Resort Finland Inc.</h5>
                <h6>Point of Sale</h6>
            </div>
            <a href="../pos/pos_dashboard.php" class="nav-link nav-1">
                <span class="icon">
                    <i class="bi bi-grid"></i>
                </span>
                <span class="description">
                    Dashboard
                </span>
            </a>
            <a href="../pos/pos.php" class="nav-link nav-1">
                <span class="icon">
                    <i class="bi bi-bag"></i>
                </span>
                <span class="description">
                    POS
                </span>
            </a>
            <a href="#" class="nav-link nav-1" data-bs-toggle="collapse" data-bs-target="#submenu-rooms"
            aria-expanded="false" aria-controls="submenu-rooms">
                <span class="icon">
                    <i class="bi bi-box-seam"></i>
                </span>
                <span class="description">
                    Inventory
                    <i class="bi bi-caret-down-fill"></i>
                </span>
            </a>
            <!-- start -->
            <div class="sub-menu collapse" id="submenu-rooms">
                <a href="../pos/products.php" class="nav-link nav-2">
                    <span class="icon">
                        <i class="bi bi-box2"></i>
                    </span>
                    <span class="description">
                        Products Management
                    </span>
                </a>
                <a href="../pos/product_category.php" class="nav-link nav-2">
                    <span class="icon">
                        <i class="bi bi-grid-1x2-fill"></i>
                    </span>
                    <span class="description">
                        Products Category
                    </span>
                </a>
                <a href="../pos/product_measurement.php" class="nav-link nav-2">
                    <span class="icon">
                        <i class="bi bi-rulers"></i>
                    </span>
                    <span class="description">
                        Products Unit Measurement
                    </span>
                </a>
                
            </div>
            <a href="../pos/transactions.php" class="nav-link nav-1">
                <span class="icon">
                    <i class="bi bi-receipt"></i>
                </span>
                <span class="description">
                    Transactions
                </span>
            </a>
            <a href="../pos/suppliers.php" class="nav-link nav-1">
                <span class="icon">
                    <i class="bi bi-truck"></i>
                </span>
                <span class="description">
                    Suppliers
                </span>
            </a>
             <!-- dropdown (end) -->
           
            <!-- <a href="#" class="nav-link nav-1" data-bs-toggle="collapse" data-bs-target="#submenu-reports"
            aria-expanded="false" aria-controls="submenu-reports">
                <span class="icon">
                    <i class="bi bi-receipt"></i>
                </span>
                <span class="description">
                    Reports
                    <i class="bi bi-caret-down-fill"></i>
                </span>
            </a>

            <div class="sub-menu collapse" id="submenu-reports">
                <a href="#" class="nav-link nav-2">
                    <span class="icon">
                        <i class="bi bi-reception-4"></i>
                    </span>
                    <span class="description">
                        Sales Report
                    </span>
                </a>
                <a href="#" class="nav-link nav-2">
                    <span class="icon">
                        <i class="bi bi-box-seam"></i>
                    </span>
                    <span class="description">
                        Inventory Report
                    </span>
                </a>
            </div> -->
             <!-- dropdown (end) -->
            
            <!-- dropdown (start) Utilities -->
            <!-- <a href="#" class="nav-link nav-1" data-bs-toggle="collapse" data-bs-target="#submenu-utilities"
            aria-expanded="false" aria-controls="submenu-utilities">
                <span class="icon">
                    <i class="bi bi-stack"></i>
                </span>
                <span class="description">
                    Utilities
                    <i class="bi bi-caret-down-fill"></i>
                </span>
            </a>

            <div class="sub-menu collapse" id="submenu-utilities">
                <a href="../utilities/activity_logs.php" class="nav-link nav-2">
                    <span class="icon">
                        <i class="bi bi-patch-check"></i>
                    </span>
                    <span class="description">
                        Activity Logs
                    </span>
                </a>
                <a href="../utilities/archives.php" class="nav-link nav-2">
                    <span class="icon">
                        <i class="bi bi-file-lock"></i>
                    </span>
                    <span class="description">
                        Archive
                    </span>
                </a>
            </div> -->
             <!-- dropdown (end) -->
            <div class="mt-2">
            <a href="" class="nav-link">
                <form action="../../controllers/logoutController.php" method="POST">
                    <span class="icon">
                        <i class="bi bi-file-person"></i>
                    </span>
                    <span class="description"></span>
                    <button class="btn-logout"><?php echo htmlspecialchars($_SESSION["username"]) ?></button>
                </form>
            </a>
            </div>

        </nav>

        

    </div>

    </div>
  

</body>
</html>