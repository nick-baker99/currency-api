<?php 
    require_once("../generalFunctions.php");
    // Define parameters
    define('PARAMS', array('cur', 'action'));

    // Define update error codes
    define('ERRORS', array(
        '2000' => 'Action not regognized or is missing', 
        '2100' => 'Currency code in wrong format or is missing',
        '2200' => 'Currency code not found for update',
        '2300' => 'No rate listed for this currency',
        '2400' => 'Cannot update base currency',
        '2500' => 'Error in service'));

    // Function checks for errors in the GET
    function checkUpdateErrors() {
        // If there are less than or more than 2 parameters
        if(count(array_intersect(PARAMS, array_keys($_GET))) < 2 || count(array_intersect(PARAMS, array_keys($_GET))) > 2) {
            return "2000";
        // If the currency code is not in the correct format
        }else if($_GET["cur"] == NULL || strlen($_GET["cur"]) != 3 || !ctype_upper($_GET["cur"])) {
            return "2100";
        // If the currency is in the list of currencies the services provides
        }else if($_GET["cur"] !== NULL && !checkCurrency() && $_GET["action"] != "put") {
            return "2200";
        // Check if the rates API returns a rate for a given currency
        }else if(!checkListedRate()) {
            return "2300";
        // Check if the base currency is trying to be updated
        }else if($_GET["cur"] == "GBP") {
            return "2400";
        }
    }

    // This function generates the output
    function update() {
        // Create a new xml file
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><action></action>');
        // Add the type attributes which the user has requested
        $xml->addAttribute("type", $_GET["action"]);

        // Add the date and time of when the rates were last updated
        $xml->addChild("at", getDateTime(simplexml_load_file("../currencyRates.xml")));

        // load the XML containing all the currencies and rates
        $currenciesXml = simplexml_load_file("../currencyRates.xml");

        // Find the currencies info from the XML 
        $result = $currenciesXml->xpath("/currencies/currency[code='".$_GET["cur"]."']");
        // Call the FIXER API to get the currency rates
        $rates = callApi();

        // Add the rate of the given currency
        $xml->addChild("rate", number_format($rates->{$_GET["cur"]}/$rates->GBP, 5));

        // If the action is POST
        if($_GET["action"] == "post") {
            // find the conversion rate that is currently stored in the XML file
            $xml->addChild("old_rate", $result[0]->attributes()["rate"]);

            // Update the current rate in the XML file
            $currenciesXml = updateCurrRate($currenciesXml, ($rates->{$_GET["cur"]}/$rates->GBP));
            // Save the output XML
            $currenciesXml->asXML("../currencyRates.xml");
        }else if($_GET["action"] == "put") {
            // Add a currency to the currency list
            addCurrency($result, $rates);
        }else if($_GET["action"] == "del") {
            // Delete a currency from the currency list
            delCurrency();
        }
        // If action is DEL
        if($_GET["action"] == "del") {
            // Just add the code of the currency to the XML
            $xml->addChild("code", $_GET["cur"]);
        }else {
            // If PUT or POST add the info of the currency to the XML
            $curr = $xml->addChild("curr");
            $curr->addChild("code", $_GET["cur"]);
            $curr->addChild("name", $result[0]->curr);
            $curr->addChild("loc", $result[0]->loc);
        }

        // Display the XML and exit
        displayXml($xml);
        exit();
    }

    // Delete a currency from the list of currencies the API returns
    function delCurrency() {
        // Read the list of currencies from the text file
        $f = fopen("../currencies.txt", "r");
        $currs = fgetcsv($f);
        fclose($f);
        // Loop through the currencies and remove the currency if it exists in it
        for($i = 0; $i < count($currs); $i++) {
            if($currs[$i] == $_GET["cur"]) {
                unset($currs[$i]);
            }
        }
        // Sort the currencies and put them back in the currency list file
        sort($currs);
        $f = fopen("../currencies.txt", "w");
        fputcsv($f, $currs);
        fclose($f);
    }

    // Function adds a currency to the currency list
    function addCurrency($result, $rates) {
        $f = fopen("../currencies.txt", "r");
        $currs = fgetcsv($f);
        fclose($f);
        
        // If the currency is not already in the list
        if(!in_array($_GET["cur"], $currs)) {
            // Add it
            array_push($currs, $_GET["cur"]);
        }
        // Sort the list and add it to the file
        sort($currs);
        $f = fopen("../currencies.txt", "w");
        fputcsv($f, $currs);
        fclose($f);

    }

    // Update the rate attribute of the given currency in the XML file
    function updateCurrRate($xml, $rate) {
        $xml->xpath("/currencies/currency[code='".$_GET["cur"]."']")[0]->attributes()["rate"] = number_format($rate, 5);
        return $xml;
    }

    // Function makes a call to the rates API and returns an array of currencies and their rates
    function callApi() {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, "http://data.fixer.io/api/latest?access_key=657a3fa6c43b4133cbd66d2622ace618");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $apiResult = json_decode(curl_exec($ch));

        //$apiResult = json_decode(file_get_contents("http://data.fixer.io/api/latest?access_key=657a3fa6c43b4133cbd66d2622ace618"));
        $rates = $apiResult->rates; 

        return $rates;
    }

    // Generates an XML output error
    function generateUpdateError($errorNm) {
        // Generate XML file
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><action></action>');
        $error = $xml->addChild('error');
        
        $error->addChild('code', $errorNm);
        $error->addChild('msg', ERRORS[$errorNm]);

        $xmlDoc = new DOMDocument('1.0');

        // Display XML
        displayXml($xml);
        exit();
    }

    // Check if the currency is in the currency rates XML file
    function checkListedRate() {
        $xml = simplexml_load_file("../currencyRates.xml");
    
        $result = $xml->xpath("/currencies/currency[code='".$_GET["cur"]."']");
    
        if($result == NULL) {
            return false;
        }else {
            return true;
        }
    }

    // Check if the given currency is in the list of currencies the services offers
    function checkCurrency() {
        $f = fopen("../currencies.txt", "r");
        $currencies = fgetcsv($f);
        fclose($f);

        if(in_array($_GET["cur"], $currencies)) {
            return true;
        }else {
            return false;
        }
    }
?>