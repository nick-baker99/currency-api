<?php 
    // This functions converts a simplexml file into a DOMDocument to make it more readable
    function displayXml($xml) {
        $xmlDoc = new DOMDocument('1.0');
        
        $xmlDoc->preserveWhiteSpace = false;
        $xmlDoc->formatOutput = true;

        $xmlDoc->loadXML($xml->asXML());

        echo $xmlDoc->saveXML();
        exit();
    }

    // Function gets the timestamp from the XML file and returns it as a Date and Time format
    function getDateTime($xml) {
        // Get the timestamp of when the XML was last updated
        $timestamp = $xml->xpath("/currencies/@timestamp");
        // Conver to date and time
        return date("d M Y H:i", (int)$timestamp[0]);
    }
?>