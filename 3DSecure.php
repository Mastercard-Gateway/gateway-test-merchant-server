<?php

/*
 * Copyright (c) 2016 Mastercard
 */

include '_bootstrap.php';

// proxy PUT requests for INITIATE_AUTHENTICATION
if (intercept('PUT')) {
    $orderId = requiredQueryParam('orderId');
    $transactionId = requiredQueryParam('transactionId');
    $path = "/order/$orderId/transaction/$transactionId";

    proxyCall($path);
}

// proxy POST requests for AUTHENTICATE_PAYER
if (intercept('POST')) {
    $orderId = requiredQueryParam('orderId');
    $transactionId = requiredQueryParam('transactionId');
    $path = "/order/$orderId/transaction/$transactionId";

    proxyCall($path);
}
?>

<html>
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css"
          integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M"
          crossorigin="anonymous">
    <style>
        body {
            padding: 2rem;
        }
    </style>
</head>
<body>

<h1>3DS 2.x API</h1>

<h3>Step 1: Initiate Authentication</h3>
<h5>Sample Request</h5>
<pre><code>PUT <?php echo htmlentities($pageUrl . '?orderId={order-id}&transactionId={txn-id}'); ?>

Content-Type: application/json
Payload:
{
  "apiOperation": "INITIATE_AUTHENTICATION",
  "order": {
    "currency": "SAR",
    "reference": "order-ref-001"
  },
  "session": {
    "id": "SESSION0000000000000000000000"
  },
  "authentication": {
    "purpose": "PAYMENT_TRANSACTION",
    "channel": "PAYER_BROWSER"
  }
}</code></pre>

<h5>Sample Response</h5>
<pre><code>{
  "gatewayResponse": {
    "authentication": {
      "redirectHtml": "&lt;script&gt;...&lt;/script&gt;"
    },
    "order": {
      "authenticationStatus": "AUTHENTICATION_NOT_SUPPORTED",
      "status": "AUTHENTICATION_UNSUCCESSFUL"
    },
    "result": "FAILURE"
  }
}</code></pre>

<hr />

<h3>Step 2: Authenticate Payer</h3>
<h5>Sample Request</h5>
<pre><code>POST <?php echo htmlentities($pageUrl . '?orderId={order-id}&transactionId={txn-id}'); ?>

Content-Type: application/json
Payload:
{
  "apiOperation": "AUTHENTICATE_PAYER"
}</code></pre>

<h5>Sample Response</h5>
<pre><code>{
  "gatewayResponse": {
    "authentication": {
      "summaryStatus": "AUTHENTICATION_SUCCESSFUL",
      "redirectHtml": "&lt;html&gt;...&lt;/html&gt;"
    },
    "order": {
      "status": "AUTHENTICATED"
    },
    "result": "SUCCESS"
  }
}</code></pre>

</body>
</html>