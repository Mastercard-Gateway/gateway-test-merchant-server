<?php
$pageUrl = $_SERVER['PHP_SELF'];
$apiVersion = "64"; // use latest or set dynamically
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Start Browser Payment</title>
    <link rel="stylesheet"
          href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css"
          integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M"
          crossorigin="anonymous">
    <style> body { padding: 2rem; } </style>
</head>
<body>

<h1>Browser Payment API</h1>
<h3>Start Browser Payment (INITIATE_BROWSER_PAYMENT)</h3>

<h5>Sample Request</h5>
<pre><code>PUT <?php echo htmlentities('https://francophone-leaf-52430-c8565a556f27.herokuapp.com/start-browser-payment.php?orderId={order-id}&transactionId={transaction-id}'); ?>

Content-Type: application/json

Payload:
{
  "apiOperation": "INITIATE_BROWSER_PAYMENT",
  "browserPayment": {
    "operation": "PAY",
    "returnUrl": "https://mcdelivery.co.in/"
  },
  "order": {
    "reference": "TEST-SUCCEED",
    "amount": "90.00",
    "currency": "KWD",
    "description": "apmspi test order"
  },
  "sourceOfFunds": {
    "type": "BROWSER_PAYMENT",
    "browserPayment": {
      "type": "KNET"
    }
  },
  "customer": {
    "email": "akash.mali@mastercard.com",
    "firstName": "Akash",
    "lastName": "Mali",
    "phone": "9898989898"
  },
  "billing": {
    "address": {
      "city": "Edinburgh",
      "country": "KWT",
      "postcodeZip": "2000"
    }
  }
}
</code></pre>

<h5>Sample Response</h5>
<pre><code>Content-Type: application/json

{
  "step": "INITIATE_BROWSER_PAYMENT",
  "result": {
    "gatewayResponse": {
      "order": {
        "id": "{order-id}",
        "status": "PENDING"
      },
      "transaction": {
        "id": "{transaction-id}",
        "type": "PAYMENT"
      },
      "browserPayment": {
        "redirectUrl": "https://...",
        "response": "REQUIRES_REDIRECT"
      },
      "result": "SUCCESS"
    }
  }
}
</code></pre>

</body>
</html>
