<?php
/*
 * Initiate Authentication (3DS 2.x Step 1)
 */

include '_bootstrap.php';
header('Content-Type: application/json');

try {
    if (intercept('PUT')) {
        $orderId = requiredQueryParam('orderId');
        $transactionId = requiredQueryParam('transactionId');
        $apiBasePath = "/order/{$orderId}/transaction/{$transactionId}";

        $rawInput = file_get_contents('php://input');
        $initPayload = json_decode($rawInput, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON in request body']);
            exit;
        }

        if (
            !isset($initPayload['apiOperation']) ||
            strtoupper($initPayload['apiOperation']) !== 'INITIATE_AUTHENTICATION'
        ) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid or missing apiOperation: expected INITIATE_AUTHENTICATION']);
            exit;
        }

        error_log("Step 1: Initiate Authentication");
        $initiateResponse = proxyCall($apiBasePath, $initPayload, 'PUT');

        echo json_encode([
            'step' => 'INITIATE_AUTHENTICATION',
            'gatewayResponse' => $initiateResponse
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    error_log("EXCEPTION: " . $e->getMessage());
    echo json_encode([
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ]);
}
?>
