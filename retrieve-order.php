<?php

/*
 * Copyright (c) 2016 Mastercard
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * http://www.apache.org/licenses/LICENSE-2.0
 */

include '_bootstrap.php';

if (intercept('POST')) {
    error_log("=== retrieveOrder invoked ===");

    // Get required query parameters
    $orderId = $_GET['order'] ?? null;
    $transactionId = $_GET['transaction'] ?? null;

    if (!$orderId || !$transactionId) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing order or transaction ID']);
        exit;
    }

    // Construct the endpoint path
    $endpoint = "/order/$orderId/transaction/$transactionId";

    // Perform the GET request
    $response = doRequest(
        $gatewayUrl . $endpoint,
        'GET',
        null,
        $headers
    );

    header('Content-Type: application/json');

    // Log response for debugging
    error_log("=== retrieveOrder response ===");
    error_log($response);

    // Redirect back to app with acsResult payload
    doRedirect("gatewaysdk://3dsecure?acsResult=" . urlencode($response));
}

// Fallback HTML UI
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
