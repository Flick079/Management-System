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
    <title>Back up and Restore</title>
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/sidebar.php' ?>
    <div class="content">
        <header>
            <h4>Backup and Restore</h4>
        </header>
        <main>
            <div class="container d-flex flex-column justify-content-center align-items-center">
                <div class="container-fluid border shadow" style="width: 41rem; height:13rem;">
                    <!-- Backup and Restore Forms -->
                    <div class="d-flex flex-column gap-3" style="max-width: 600px; margin: 0 auto;">
                        <!-- Backup Database Form -->
                        <div>
                            <form action="../../controllers/backupController.php" method="GET">
                                <button class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2 mt-3"
                                        name="action" value="backup_data" style="height: 3rem;">
                                    <i class="bi bi-database-fill-up"></i>
                                    Backup Database
                                </button>
                            </form>
                        </div>
                    <!-- Restore Database Form -->
                    <div>
                        <form action="../../controllers/restoreController.php" method="POST" enctype="multipart/form-data">
                            <div class="input-group">
                                <input type="file" name="sql_file" accept=".sql" class="form-control" id="sqlFileInput">
                                <button class="btn btn-secondary d-flex align-items-center justify-content-center gap-2" 
                                        name="action" value="restore" style="height: 3rem; min-width: 150px;">
                                    <i class="bi bi-database-fill-add"></i>
                                    Restore Database
                                </button>
                            </div>
                        </form>
                    </div>
                    <!-- Backup System Form -->
                    <div>
                        <form action="../../controllers/backupSystemController.php" method="GET">
                            <button class="btn btn-success w-100 d-flex align-items-center justify-content-center gap-2" 
                                    name="action" value="backup_system" style="height: 3rem;">
                                <i class="bi bi-floppy-fill"></i>
                                Backup System
                            </button>
                        </form>
                    </div>
                </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>