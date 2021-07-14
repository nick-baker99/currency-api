<?php 
    header('Content-Type: text/plain');
    require_once("updatefunctions.php");
    @date_default_timezone_set("GMT"); 

    // Set action to NULL if it isn't set or is empty
    if (!isset($_GET['action']) || empty($_GET['action'])) {
        $_GET['action'] = NULL;
    }

    // Check file exists
    if(!file_exists("../currencyRates.xml")) {
        generateUpdateError("2500");
    }

    // Check the GET for errors
    if(($errNm = checkUpdateErrors()) !== NULL) {
        // Display error
        generateUpdateError($errNm);
    }else {
        // Check if the correct action is given
        if($_GET["action"] == "post" || $_GET["action"] == "put" || $_GET["action"] == "del") {
            // If it is run the update function
            update();
        }else {
            // If its not run an error
            generateUpdateError("2000");
        }
    }
    generateUpdateError("2500");
?>