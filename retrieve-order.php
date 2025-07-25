<?php

include '_bootstrap.php';

if (intercept('GET')) {
    error_log("=== proxyCall invoked ===");

    $orderId = $_GET['order'] ?? null;
    $transactionId = $_GET['transaction'] ?? null;

    if (!$orderId || !$transactionId) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing order or transaction ID']);
        exit;
    }

    $response = doRequest(
        $gatewayUrl . "/order/$orderId/transaction/$transactionId",
        'GET',
        null,
        $headers
    );

    header('Content-Type: application/json');

    // log the response
    error_log("=== proxyCall response 1 ===");
    error_log($response);

    // build mobile redirect with full response payload as acsResult
    doRedirect("gatewaysdk://3dsecure?acsResult=" . urlencode($response));

}

