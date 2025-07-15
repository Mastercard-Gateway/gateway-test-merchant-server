<?php
/*
 * Authenticate Payer (3DS 2.x Step 3)
 */

include '_bootstrap.php';
header('Content-Type: application/json');

try {
    if (intercept('POST')) {
        $orderId = requiredQueryParam('orderId');
        $transactionId = requiredQueryParam('transactionId');
        $apiBasePath = "/order/{$orderId}/transaction/{$transactionId}";

        error_log("Step 3: Authenticate Payer");
        $authPayload = ['apiOperation' => 'AUTHENTICATE_PAYER'];

        $authenticateResponse = proxyCall($apiBasePath, $authPayload, 'POST');

        echo json_encode([
            'step' => 'AUTHENTICATE_PAYER',
            'gatewayResponse' => $authenticateResponse
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
