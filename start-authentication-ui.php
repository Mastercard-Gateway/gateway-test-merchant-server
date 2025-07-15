<?php
$pageUrl = $_SERVER['PHP_SELF'];
$apiVersion = "64"; // Can be dynamic
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Start Authentication</title>
    <link rel="stylesheet"
          href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css"
          integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M"
          crossorigin="anonymous">
    <style>
        body {
            padding: 2rem;
        }
    </style>
</head>
<body>

<h1>3DSecure API</h1>
<h3>Start Authentication (INITIATE + AUTHENTICATE)</h3>

<h5>Sample Request</h5>
<pre><code>POST <?php echo htmlentities('https://francophone-leaf-52430-c8565a556f27.herokuapp.com/orderId={order-id}&transactionId={transaction-id}'); ?>

Content-Type: application/json
Payload:
{
  "apiOperation": "INITIATE_AUTHENTICATION",
  "session": {
    "id": "SESSION0002590866535M47240905H2"
  }
}
</code></pre>

<h5>Sample Response</h5>
<pre><code>Content-Type: application/json
Payload:
{
  "step": "CHALLENGE_OR_COMPLETION",
  "initiateResult": {
    "apiVersion": "<?php echo $apiVersion; ?>",
    "gatewayResponse": {
      "authentication": {
        "version": "2.1.0",
        "summaryStatus": "CARD_ENROLLED",
        "redirectHtml": "&lt;script&gt;...&lt;/script&gt;"
      },
      "order": {
        "id": "{order-id}",
        "status": "PENDING"
      },
      "transaction": {
        "id": "{transaction-id}",
        "type": "AUTHENTICATION"
      },
      "result": "SUCCESS"
    }
  },
  "authenticateResult": {
    "apiVersion": "<?php echo $apiVersion; ?>",
    "gatewayResponse": {
      "authentication": {
        "summaryStatus": "AUTHENTICATION_SUCCESSFUL",
        "redirectHtml": "&lt;html&gt;...&lt;/html&gt;"
      },
      "order": {
        "id": "{order-id}",
        "status": "AUTHENTICATED"
      },
      "transaction": {
        "id": "{transaction-id}",
        "type": "AUTHENTICATION"
      },
      "result": "SUCCESS"
    }
  }
}
</code></pre>

</body>
</html>
