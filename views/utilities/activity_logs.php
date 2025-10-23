<?php
require_once __DIR__ . '/../../controllers/utilitiesController.php';
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
    <title>Activity Logs</title>
</head>
<body>
    <?php require_once '../layouts/sidebar.php'?>
    <div class="content">
        <header>
            <h4>Activity Logs</h4>
        </header>
        <main>
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Action</th>
                        <th>User</th>
                        <th>Details</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($logs as $log): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($log["action"]) ?></td>
                            <td><?php echo htmlspecialchars($log["user"]) ?></td>
                            <td><?php echo htmlspecialchars($log["details"]) ?></td>
                            <td><?php echo htmlspecialchars($log["activity_time"]) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <!-- Previous Page Link -->
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1 ?>" <?php echo ($page <= 1) ? 'tabindex="-1" aria-disabled="true"' : '' ?>>Previous</a>
                    </li>
                    
                    <!-- Page Number Links -->
                    <?php 
                    // Show a reasonable number of pages around current page
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    // Always show first page if not in initial range
                    // if ($start_page > 1) {
                    //     echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                    //     if ($start_page > 2) {
                    //         echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    //     }
                    // }
                    
                    for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <!-- <li class="page-item <?php echo ($page == $i) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?php echo $i ?>"><?php echo $i ?></a>
                        </li> -->
                    <?php endfor; 
                    
                    // Always show last page if not in final range
                    // if ($end_page < $total_pages) {
                    //     if ($end_page < $total_pages - 1) {
                    //         echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    //     }
                    //     echo '<li class="page-item"><a class="page-link" href="?page='.$total_pages.'">'.$total_pages.'</a></li>';
                    // }
                    ?>
                    
                    <!-- Next Page Link -->
                    <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1 ?>" <?php echo ($page >= $total_pages) ? 'tabindex="-1" aria-disabled="true"' : '' ?>>Next</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>