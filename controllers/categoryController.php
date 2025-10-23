<?php

require_once __DIR__ . '/../models/PosModel.php';

//for adding category
if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_category_btn"])){
    $category = $_POST["category"];

    try {
        
        $errors = [];

        if(empty($category)){
            $errors["empty_input"] = "Please fill in all the fields!";
        }
        if($errors){
            $_SESSION["errors"] = $errors;
            $_SESSION["keep_modal_open"] = true;
            header("location: ../views/products/product_category.php");
            exit();
        } else {
            insertCategory($pdo, $category);
            header("location: ../views/pos/product_category.php");
            exit();
        }

    } catch (PDOException $e) {
        die("Query failed:" . $e->getMessage());
    }
}


//for editing category 
if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["edit_category_btn"])){
    $category_id = $_POST["category_id"];
    $category = $_POST["category"];

    try {
        
        $errors = [];

        if(empty($category)){
            $errors["empty_input"] = "Please fill in all the fields!";
        }
        if($errors){
            $_SESSION["errors"] = $errors;
            $_SESSION["keep_modal_open"] = true;
            header("location: ../views/products/product_category.php");
            exit();
        } else {
            updateCategory($pdo, $category_id, $category);
            header("location: ../views/pos/product_category.php");
            exit();
        }

    } catch (PDOException $e) {
        die("Query failed:" . $e->getMessage());
    }
}

if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_category_btn"])){
    $category_id = $_POST["category_id"];

    try {
        

            deleteCategory($pdo, $category_id);
            header("location: ../views/pos/product_category.php");
            exit();

    } catch (PDOException $e) {
        die("Query failed:" . $e->getMessage());
    }
}

$categories = getCategories($pdo);