<?php
    header('Content-Type: text/plain');
    require_once("indexFunctions.php");
    @date_default_timezone_set("GMT"); 

    // If the format is not given or is empty
    if (!isset($_GET['format']) || empty($_GET['format'])) {
        // Set the default to XML
        $_GET['format'] = 'xml';
    }

    // Check if the currency rates XML file exists
    if(!file_exists("currencyRates.xml")) {
        // If it doesn't run a service error
        generateConversionError("1500");
    }

    // Check for errors in the GET
    if(($errNm = checkConversionErrors()) !== NULL) {
        // If there's an error generate error XML
        generateConversionError($errNm);
    // If there are no errors
    }else {
        // If the format is XML
        if($_GET["format"] == "xml") {
            // Generate a XML output
            generateXml();
        // If JSON
        }else {
            // Generate a JSON output
            generateJson();
        }
    }
?>