<?php

/*
 * Copyright (c) 2016 Mastercard
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

include '_bootstrap.php';

// proxy PUT requests
if (intercept('PUT')) {
    // build path
    $orderId = requiredQueryParam('order');
    $txnId = requiredQueryParam('transaction');
    $path = '/order/' . $orderId . '/transaction/' . $txnId;

    proxyCall($path);
}

?>

<html>
    <head>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
        <style>
            body {
                padding: 2rem;
            }
        </style>
    </head>
    <body>
        <h1>Transaction API</h1>
        <h3>PAY Operation</h3>
        <h5>Sample Request</h5>
        <pre><code>PUT <?php echo htmlentities($pageUrl . '?order={orderId}&transaction={txnId}'); ?>

Content-Type: application/json
Payload:
{
    "apiOperation": "PAY",
    "order": {
    	"amount": "1.00",
    	"currency": "USD"
    },
    "session": {
    	"id": "SESSION0000000000000000000000"
    },
    "sourceOfFunds": {
    	"type": "CARD"
    },
    "transaction": {
    	"frequency": "SINGLE"
    }
}</code></pre>

        <h5>Response</h5>
        <pre><code>Content-Type: application/json
Payload:
{
    "apiVersion": "<?php echo $apiVersion; ?>",
    "gatewayResponse": {
        "authorizationResponse": { ... },
        "gatewayEntryPoint": "WEB_SERVICES_API",
        "merchant": "<?php echo $merchantId; ?>",
        "order": { ... },
        "response": { ... },
        "result": "SUCCESS",
        "sourceOfFunds": { ... },
        "timeOfRecord": "2017-01-01T00:00:00.000Z",
        "transaction": { ... },
        "version": "<?php echo $apiVersion; ?>"
    }
}</code></pre>
    </body>
</html>
