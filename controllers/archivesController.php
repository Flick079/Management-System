<?php

require_once __DIR__ . '/../models/UtilitiesModel.php';


$archives = getArchivedRecords($pdo);
if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["archive_btn"])){
    $original_table = $_POST["original_table"];
    $archive_id = $_POST["archive_id"];

    retrieveRecords($pdo, $archive_id, $original_table);
    header("location: ../views/utilities/archives.php");
    exit();
} 