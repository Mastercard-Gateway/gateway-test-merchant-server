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
<pre><code>PUT <?php echo htmlentities($pageUrl . '?orderId={order-id}&transactionId={transaction-id}'); ?>

Content-Type: application/json
Payload:
{
  "apiOperation": "INITIATE_AUTHENTICATION",
  "order": {
    "currency": "USD",
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
<pre><code>Content-Type: application/json
Payload:
{
  "apiVersion": "<?php echo $apiVersion; ?>",
  "gatewayResponse": {
    "authentication": {
      "version": "2.1.0",
      "summaryStatus": "CARD_ENROLLED",
      "redirectHtml": "&lt;script&gt;...&lt;/script&gt;"
    },
    "order": {
      "id": "ORDER-ID",
      "status": "PENDING"
    },
    "transaction": {
      "id": "TRANSACTION-ID",
      "type": "AUTHENTICATION"
    },
    "result": "SUCCESS"
  }
}</code></pre>

<hr />

<h3>Step 2: Authenticate Payer</h3>
<h5>Sample Request</h5>
<pre><code>POST <?php echo htmlentities($pageUrl . '?orderId={order-id}&transactionId={transaction-id}'); ?>

Content-Type: application/json
Payload:
{
  "apiOperation": "AUTHENTICATE_PAYER"
}</code></pre>

<h5>Sample Response</h5>
<pre><code>Content-Type: application/json
Payload:
{
  "apiVersion": "<?php echo $apiVersion; ?>",
  "gatewayResponse": {
    "authentication": {
      "summaryStatus": "AUTHENTICATION_SUCCESSFUL",
      "redirectHtml": "&lt;html&gt;...&lt;/html&gt;"
    },
    "order": {
      "id": "ORDER-ID",
      "status": "AUTHENTICATED"
    },
    "transaction": {
      "id": "TRANSACTION-ID",
      "type": "AUTHENTICATION"
    },
    "result": "SUCCESS"
  }
}</code></pre>

</body>
</html>