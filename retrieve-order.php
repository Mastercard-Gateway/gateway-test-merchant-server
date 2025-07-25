<?php

include '_bootstrap.php';

if (intercept('GET')) {
    error_log("=== retrieveOrder invoked ===");

    $orderId = $_GET['order'] ?? null;
    $transactionId = $_GET['transaction'] ?? null;

    error_log("=== order === $orderId");
    error_log("=== transactionId === $transactionId");

    if (!$orderId || !$transactionId) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing order or transaction ID']);
        exit;
    }

    $endpoint = "/order/$orderId/transaction/$transactionId";

    $response = doRequest(
        $gatewayUrl . $endpoint,
        'GET',
        null,
        $headers
    );

    error_log("=== retrieveOrder response ===");
    error_log($response);

    // Return a minimal HTML page with JS redirect to the app
    $redirectUrl = "gatewaysdk://3dsecure?acsResult=" . urlencode($response);
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Redirecting...</title>
        <script type="text/javascript">
            window.location.href = <?= json_encode($redirectUrl) ?>;
        </script>
    </head>
    <body>
        <p>Redirecting to app...</p>
    </body>
    </html>
    <?php
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>3DSecure - Retrieve Order</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css"
          integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M"
          crossorigin="anonymous">
    <style>
        body { padding: 2rem; }
    </style>
</head>
<body>
<h1>3DSecure - Retrieve Transaction</h1>
<p>This endpoint accepts <strong>order</strong> and <strong>transaction</strong> as query parameters,</p>
<p>Performs a Mastercard GET API call, and redirects the mobile client with the 3DS result payload.</p>
</body>
</html>