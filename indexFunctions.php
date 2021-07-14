<?php 
    require_once("generalFunctions.php");
    define('PARAMS', array('to', 'from', 'amnt', 'format'));

    define('ERRORS', array(
        '1000' => 'Required parameter is missing', 
        '1100' => 'Parameter is not recognized',
        '1200' => 'Currency type not recognized',
        '1300' => 'Currency amount must be a decimal number',
        '1400' => 'Format must be xml or json',
        '1500' => 'Error in service'));

    // Checks for errors in the GET variable and if there are any return the code
    function checkConversionErrors() {
        // Check if any parameters are missing
        if (count(array_intersect(PARAMS, array_keys($_GET))) < 4) {
            return "1000";
        // Check for unkown parameters
        }else if(count($_GET) > 4) {
            return "1100";
        // Check if the currency given is in the list of currencies the service provides
        }else if(!checkCurrencies()) {
            return "1200";
        // Check if the amount given is a decmial number
        }else if(!checkDecimal()) {
            return "1300";
        // Check if the correct format is given
        }else if($_GET["format"] !== "xml" && $_GET["format"] !== "json") {
            return "1400";
        }else {
            return NULL;
        }
    }
    // Generates the xml or json error for a given error code
    function generateConversionError($errorNm) {
        // If format is XML
        if($_GET['format'] == 'xml') {
            // Create a new XML element
            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><conv></conv>');
            // Add a new child for the error
            $error = $xml->addChild('error');
            
            // Add the error number child
            $error->addChild('code', $errorNm);
            // Add the error message
            $error->addChild('msg', ERRORS[$errorNm]);
            
            // Display the error
            displayXml($xml);
            exit();
        // If format is JSON
        }else {
            // Create an array with the error
            $arr = array("conv" => array("error" => array("code" => $errorNm, "msg" => ERRORS[$errorNm])));
            // Encode the array as JSON and output
            echo json_encode($arr, JSON_PRETTY_PRINT);
            exit();
        }
    }

    // Checks if currencies exists in the xml file
    function checkCurrencies() {
        // Read the XML file containing the currencies and their conversion rates
        $xml = simplexml_load_file("currencyRates.xml");
        
        // find the to and from child nodes
        $result = $xml->xpath("/currencies/currency[code='".$_GET["to"]."']");
        $result2 = $xml->xpath("/currencies/currency[code='".$_GET["from"]."']");
    
        // if either the to or from result is empty return false
        if($result == NULL || $result2 == NULL) {
            return false;
        // If found return true
        }else {
            return true;
        }
    }

    /// Checks if the GET amnt is a decimal
    function checkDecimal() {
        return is_numeric($_GET["amnt"]) && floor($_GET["amnt"]) != $_GET["amnt"];
    }
    
    // Return the rate of the given currency
    function getRate($xml, $toOrFrom) {
        $result = $xml->xpath("/currencies/currency[code='".$_GET[$toOrFrom]."']");
        return $result[0]->attributes()["rate"];
    }

    // Calculates the conversion rate
    function calcRate($from, $to) {
        return number_format((float)$to/(float)$from, 5);
    }

    // Generates and outputs the conversion as XML
    function generateXml() {
        // Load the currency rates XML file
        $currenciesXml = simplexml_load_file("currencyRates.xml");
        // Create a new XML element
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><conv></conv>');

        // Add the date and time child node
        $xml->addChild("at", getDateTime($currenciesXml));
    
        // Calculate and add the rate for the conversion
        $xml->addChild("rate", calcRate(getRate($currenciesXml, "from"), getRate($currenciesXml, "to")));
        
        // add currency info for the to and from currencies
        $xml = addToOrFromXml($xml, $currenciesXml, "from");
        $xml = addToOrFromXml($xml, $currenciesXml, "to");

        // Display the output XML
        displayXml($xml);
        exit();
    }

    // Generates and outputs the conversion as JSON
    function generateJson() {
        // Load currency file
        $currenciesXml = simplexml_load_file("currencyRates.xml");
        // Find to and from
        $fromResult = $currenciesXml->xpath("/currencies/currency[code='".$_GET["from"]."']");
        $toResult = $currenciesXml->xpath("/currencies/currency[code='".$_GET["to"]."']");
        // Calculate the conversion rate
        $rate = calcRate(getRate($currenciesXml, "from"), getRate($currenciesXml, "to"));

        // Define an array of the output
        $convResult = array("conv" => array(
            "at" => getDateTime($currenciesXml), 
            "rate" => $rate,
            "from" => array(
                "code" => $fromResult[0]->code,
                "curr" => $fromResult[0]->curr,
                "loc" => $fromResult[0]->loc,
                "amnt" => $_GET["amnt"]),
            "to" => array(
                "code" => $toResult[0]->code,
                "curr" => $toResult[0]->curr,
                "loc" => $toResult[0]->loc,
                "amnt" => conversion($_GET["amnt"], $rate)
            )));

        // Encode the array into JSON and output
        echo json_encode($convResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    // Adds the to and from info to the xml file
    function addToOrFromXml($xml, $currenciesXml, $toOrFrom) {
        $child = $xml->addChild($toOrFrom);
        $child->addChild("code", $_GET[$toOrFrom]);
        $result = $currenciesXml->xpath("/currencies/currency[code='".$_GET[$toOrFrom]."']");
        $child->addChild("curr", $result[0]->curr);
        $child->addChild("loc", $result[0]->loc);
        if($toOrFrom == "from") {
            $child->addChild("amnt", number_format($_GET["amnt"], 2));
        }else {
            $child->addChild("amnt", conversion($_GET["amnt"], $xml->rate));
        }
        return $xml;
    }

    // Converts the amount from one currency to the other
    function conversion($amnt, $rate) {
        // Multiply the amnt by the converion rate and return the value
        $converted = (float)$amnt * (float)$rate;
        return number_format($converted, 2);
    }
?>