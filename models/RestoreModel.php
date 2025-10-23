<?php 

require_once __DIR__ . '/../configs/database.php';

function restoreDatabase() {
    $host = 'localhost';
    $user = 'root';
    $password = '';
    $dbName = 'your_database_name';

    if (!isset($_FILES['sql_file']) || $_FILES['sql_file']['error'] !== UPLOAD_ERR_OK) {
        echo "File upload failed.";
        return;
    }

    $sqlFilePath = $_FILES['sql_file']['tmp_name'];

    try {
        // Connect to database
        $pdo = new PDO("mysql:host=$host;dbname=$dbName", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Read the SQL file
        $sql = file_get_contents($sqlFilePath);
        if (!$sql) {
            echo "Failed to read the SQL file.";
            return;
        }

        // Execute SQL statements
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;"); // Disable foreign key checks
        $pdo->exec($sql);
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;"); // Re-enable foreign key checks

        echo "Database restored successfully!";
    } catch (PDOException $e) {
        echo "Error restoring database: " . $e->getMessage();
    }
}
