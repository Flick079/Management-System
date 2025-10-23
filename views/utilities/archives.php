<?php 
    require_once __DIR__ . '/../../middleware/verify.php';
    require_once __DIR__ . '/../../controllers/archivesController.php';
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
    <title>Archives</title>
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/sidebar.php' ?>
    <div class="content">
        <div class="content-header">
            <h4>Archives</h4>
        </div>
        <main>
            <div class="container">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Table Name</th>
                            <th>Original ID</th>
                            <th>Details</th>
                            <th>Archived Time</th>
                            <th>Archived By</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($archives as $archive): ?>
                            
                            <tr>
                                <td><?php echo htmlspecialchars($archive["original_table"]) ?></td>
                                <td><?php echo htmlspecialchars($archive["original_id"]) ?></td>
                                <td><?php echo htmlspecialchars($archive["details"]) ?></td>
                                <td><?php echo htmlspecialchars(date(("F-m-y"), strtotime($archive["archived_at"]))) ?></td>
                                <td><?php echo htmlspecialchars($archive["username"]) ?></td>
                                <td>
                                    <form action="../../controllers/archivesController.php" method="POST">
                                        <input type="hidden" name="original_table" value="<?php echo htmlspecialchars($archive["original_table"]) ?>">
                                        <input type="hidden" name="archive_id" value="<?php echo htmlspecialchars($archive["archive_id"]) ?>">
                                        <button class="btn btn-warning" name="archive_btn">
                                            <i class="bi bi-reply"></i>
                                            Retrieve
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>