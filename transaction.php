<?php

/*
 * Copyright (c) 2017, MasterCard International Incorporated
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification, are
 * permitted provided that the following conditions are met:
 *
 * Redistributions of source code must retain the above copyright notice, this list of
 * conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, this list of
 * conditions and the following disclaimer in the documentation and/or other materials
 * provided with the distribution.
 * Neither the name of the MasterCard International Incorporated nor the names of its
 * contributors may be used to endorse or promote products derived from this software
 * without specific prior written permission.
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
 * OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT
 * SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED
 * TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS;
 * OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER
 * IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING
 * IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
 * SUCH DAMAGE.
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
}</code></pre>
    </body>
</html>
