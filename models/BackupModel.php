<?php

require_once __DIR__ . '/../configs/database.php';


function backupDatabase() {
        $host = 'localhost';
        $user = 'root';
        $password = '';
        $dbName = 'resort_db';
    
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbName", $user, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
            // Get all table names
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
            $backupSQL = "";
            foreach ($tables as $table) {
                // Get CREATE TABLE statement
                $createTableStmt = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
                $backupSQL .= $createTableStmt['Create Table'] . ";\n\n";
    
                // Get table data
                $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($rows as $row) {
                    $values = array_map([$pdo, 'quote'], array_values($row));
                    $backupSQL .= "INSERT INTO `$table` VALUES (" . implode(", ", $values) . ");\n";
                }
                $backupSQL .= "\n\n";
            }
    
            // Set the filename
            $backupFile = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    
            // Send file as download
            header('Content-Type: application/sql');
            header('Content-Disposition: attachment; filename="' . $backupFile . '"');
            echo $backupSQL;
            exit;
    
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
    
