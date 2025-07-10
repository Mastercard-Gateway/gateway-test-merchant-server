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
<pre><code>Content-Type: application/json
Payload:
{
  "apiVersion": "<?php echo $apiVersion; ?>",
  "gatewayResponse": {
    "authentication": {
      "acceptVersions": "3DS1,3DS2",
      "channel": "PAYER_BROWSER",
      "purpose": "PAYMENT_TRANSACTION",
      "redirect": {
        "customized": {
          "3DS": {
            "methodPostData": "e30=",
            "methodUrl": "https://mtf.gateway.mastercard.com/acs/mastercard/v2/empty"
          }
        }
      },
      "redirectHtml": "&lt;script id=\"initiate-authentication-script\"&gt;&lt;/script&gt;",
      "version": "2.1.0"
    },
    "merchant": "<?php echo $merchantId; ?>",
    "order": {
      "authenticationStatus": "AUTHENTICATION_NOT_SUPPORTED",
      "status": "AUTHENTICATION_UNSUCCESSFUL",
      "id": "8b4cae9e-73da-48e6-950b-90a13b558c00"
    },
    "transaction": {
      "id": "92fbafe1-b62d-4815-a553-b3d049daf1e7",
      "type": "AUTHENTICATION"
    },
    "response": {
      "gatewayCode": "DECLINED",
      "gatewayRecommendation": "PROCEED"
    },
    "result": "FAILURE",
    "version": "<?php echo $apiVersion; ?>"
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
<pre><code>Content-Type: application/json
Payload:
{
  "apiVersion": "<?php echo $apiVersion; ?>",
  "gatewayResponse": {
    "authentication": {
      "summaryStatus": "AUTHENTICATION_SUCCESSFUL",
      "redirectHtml": "&lt;html&gt;...&lt;/html&gt;"
    },
    "merchant": "<?php echo $merchantId; ?>",
    "order": {
      "id": "8b4cae9e-73da-48e6-950b-90a13b558c00",
      "status": "AUTHENTICATED"
    },
    "transaction": {
      "id": "92fbafe1-b62d-4815-a553-b3d049daf1e7",
      "type": "AUTHENTICATION"
    },
    "result": "SUCCESS",
    "version": "<?php echo $apiVersion; ?>"
  }
}</code></pre>

</body>
</html>
