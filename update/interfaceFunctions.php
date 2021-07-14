<?php 
    // Read the currency list file and return and array of currencies
    function getCurrencies() {
        $f = fopen("../apiCurrencyList.txt", "r");
        $currs = fgetcsv($f);
        fclose($f);
        return $currs;
    }
?>