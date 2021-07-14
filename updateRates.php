<?php 
    header("Content-Type: text/plain");

    // Continuous Loop
    while(true) {
        // Initialize a HTTP request
        $ch = curl_init();

            // Set request options
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, "http://data.fixer.io/api/latest?access_key=657a3fa6c43b4133cbd66d2622ace618");
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_VERBOSE, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            // Save and decode the JSON result
            $apiResult = json_decode(curl_exec($ch));
        
        // Create an empty array to store the currency codes and their conversion rates
        $ccs = [];
        foreach($apiResult->rates as $key => $value) {
            // Add each currency and its rate to the array
            array_push($ccs, $key);
        }

        // Load the XML containing info about all the currencies
        $currenciesXml = simplexml_load_file("currencies.xml");
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><currencies></currencies>');

        $date = new DateTime();
        $xml->addAttribute("timestamp", $date->getTimestamp());

        // Loop over the array of currencies
        for($i = 0; $i < count($ccs); $i++) {
            // Find the child node with a specific currency code
            $result = $currenciesXml->xpath("/currencies/currency[code='".$ccs[$i]."']");

            // If the result is not empty
            if($result != NULL) {
                // Add a currency child node
                $curr = $xml->addChild("currency");

                // Divide the rate of the currency by the rate of the base currency
                $rateGBP = $apiResult->rates->{$ccs[$i]}/$apiResult->rates->GBP;
                $curr->addAttribute("rate", number_format($rateGBP, 5));

                // Add the info about the currency
                $curr->addChild("code", $ccs[$i]);
                $curr->addChild("curr", $result[0]->curr);
                $curr->addChild("loc", $result[0]->loc);
            }
        }
        // Convert the file into a DOMDocument and save
        $xmlDoc = new DOMDocument('1.0');

        $xmlDoc->preserveWhiteSpace = false;
        $xmlDoc->formatOutput = true;

        $xmlDoc->loadXML($xml->asXML());

        $xmlDoc->save('currencyRates.xml');

        // Sleep for two hours and then repeat
        sleep(7200);
    }

?>