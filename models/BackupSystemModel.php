<?php

require_once __DIR__ . '/../configs/database.php';

function backupSystem() {
    $rootPath = realpath('../../'); // Adjust to the system's root directory
    $backupFolder = '../../backups/';
    $backupFile = $backupFolder . 'system_backup_' . date('Y-m-d_H-i-s') . '.zip';

    if (!file_exists($backupFolder)) {
        mkdir($backupFolder, 0777, true); // Create backups directory if not exists
    }

    $zip = new ZipArchive();
    if ($zip->open($backupFile, ZipArchive::CREATE) === TRUE) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);
                
                // Exclude specific files or folders (optional)
                if (strpos($relativePath, 'backups') === false) {
                    $zip->addFile($filePath, $relativePath);
                }
            }
        }

        $zip->close();
        echo "System backup completed. Saved as " . basename($backupFile);
    } else {
        echo "Failed to create system backup.";
    }
}