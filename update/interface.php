<?php 
    require_once("interfaceFunctions.php");
    require_once("updateFunctions.php");
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="interfaceCss.css">
    <title>Interface</title>
</head>
<body>
    <div class="interface">
    <div class="contents">
        <h2>Form Interface for POST, PUT, DELETE</h2>
            <!-- Interface Form -->
            <form action="#" id="interface-form">
                <label class="get-labels">Action: </label>
                <input type="radio" name="action" value="post" id="post-radio" required>
                <label for="post-radio" class="action">POST</label>
                <input type="radio" name="action" value="put" id="put-radio" required>
                <label for="put-radio" class="action">PUT</label>
                <input type="radio" name="action" value="del" id="del-radio" required>
                <label for="del-radio" class="action">DEL</label>

                <br />
                <label for="cur-list" class="get-labels">Currency: </label>
                <select name="cur" id="cur-list" required>
                <option value="">Select Currency Code</option>
                <?php 
                    // Get a list of all the currencies
                    $currs = getCurrencies(); 
                    // Loop through each currency and create a HTML option for it
                    foreach($currs as $curr) {
                        echo "<option value='$curr'>$curr</option>";
                    }
                ?>
                </select>

                <br />
                <!-- Submit button that executes the update function when clicked -->
                <button id="button" type="button" value="submit" onclick="update()">SUBMIT</button>
            </form>
            <b><label for="display-xml" class="xml-label">Response XML</label></b>
            <textarea name="xml" id="display-xml" class="xml" spellcheck="false"></textarea>
        </div>
    </div>
    <script>
        // Function gets the form values and returns an XML output
        function update() {
            // Check which radio button is checked and set the value to a variable
            if(document.getElementById("post-radio").checked) {
                var action = document.getElementById("post-radio").value;
            }else if(document.getElementById("put-radio").checked) {
                var action = document.getElementById("put-radio").value;
            }else if(document.getElementById("del-radio").checked) {
                var action = document.getElementById("del-radio").value;
            // If none are checked set the variable to empty
            }else {
                var action = "";
            }
            // Get the currency chosen
            var cur = document.getElementById("cur-list").value;

            // Check if the action is not empty
            if(action != "") {
                //Check if the currency is not empty
                if(cur != "") {
                // Create an object of the XMLHttpRequest class
                var xmlhttp = new XMLHttpRequest();
                // Set the readyState change to a function
                xmlhttp.onreadystatechange = function() {
                    // If the request is finished and the response is ready
                    // And if the status is "OK"
                    if(this.readyState == 4 && this.status == 200) {
                        // Display the result of the request in the textarea element
                        document.getElementById("display-xml").innerHTML = this.responseText;
                    }
                };
                // Initialize a new request
                xmlhttp.open("GET", "index.php?cur=" + cur + "&action=" + action, true);
                // Send the request
                xmlhttp.send();
                // If currency is empty send an alert
                }else {
                    alert("Please select a currency");
                }
            // If action is empty send an alert
            }else {
                alert("Please select an action");
            }
        }
    </script>
</body>
</html>