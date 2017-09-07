<?php include '_bootstrap.php'; ?>
<html>
    <head>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
        <style>
            body {
                padding: 3rem;
            }
        </style>
    </head>
    <body>
        <h1>Mastercard Gateway Sample Merchant Server</h1>
        <p>This is an sample application to help developers start building mobile applications using the Mastercard Gateway mobile SDK.</p>
        <h3>Available APIs</h3>
        <ul>
            <li><a href="./session.php">Session API</a></li>
            <li><a href="./transaction.php">Transaction API</a></li>
        </ul>
        <p>* If you are using the sample app, include the following url in your configuration:</p>
        <pre><?php echo htmlentities($pageUrl); ?></pre>
    </body>
</html>
