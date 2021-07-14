# ATWD1 Assignment
Solution to the assignment in my Advanced Topics in Web Development module. The task was to produce a Restful API (web service) based microservice for currency conversion with full CRUD functionality as well as a client component to test the app.

## Installation
Put the 'atwd1' folder into the 'htdocs' folder for XXAMP.

Once you have put this folder into your XXAMP folder, make sure you are running Apache.

Run the following in your browser to begin updating the currency rates:
```
http://localhost/atwd1/assignment/updateRates.php
```

## Task A Usage
Example request:
```
http://localhost/atwd1/assignment/?from=GBP&to=JPY&amnt=10.35&format=xml
```

## Task B Usage
Example POST request:
```
http://localhost/atwd1/assignment/update/?cur=USD&action=post
```

Example PUT request:
```
http://localhost/atwd1/assignment/update/?cur=USD&action=put
```

Example DELETE request:
```
http://localhost/atwd1/assignment/update/?cur=GBP&action=del
```

## Task C Usage
To open the interface run the following in the browser:
```
http://localhost/atwd1/assignment/update/interface.php
```

## Task D
The critical evaluation can be found in the "Critical Evaluation.docx" file.