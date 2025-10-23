<?php

function settingErrors(){
    if(isset($_SESSION["errors"])){
        $errors = $_SESSION["errors"];
    
        foreach($errors as $error){
            echo "<p class='alert alert-danger'>" . $error . "</p>";
        }
        unset($_SESSION["errors"]);
    }
}