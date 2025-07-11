<?php
/*
 * Unified 3DS 2.x Authentication Flow
 * Performs INITIATE_AUTHENTICATION and AUTHENTICATE_PAYER sequentially
 */

header('Content-Type: application/json');
include '_bootstrap.php';

try {
    // Step 0: Validate required query parameters
    $orderId = requiredQueryParam('orderId');
    $transactionId = requiredQueryParam('transactionId');
    $apiBasePath = "/order/{$orderId}/transaction/{$transactionId}";

    // Step 1: Read and decode JSON input for INITIATE_AUTHENTICATION
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

    // Step 2: INITIATE_AUTHENTICATION (PUT)
    $initiateResponse = proxyCall($apiBasePath, $initPayload, 'PUT');

    // Step 3: Check for summaryStatus
    $summaryStatus = $initiateResponse['gatewayResponse']['authentication']['summaryStatus'] ?? null;

    if ($summaryStatus === 'CARD_NOT_ENROLLED') {
        echo json_encode([
            'step' => 'INITIATE_AUTHENTICATION_ONLY',
            'message' => 'Card is not enrolled for 3DS',
            'initiateResult' => $initiateResponse
        ]);
        exit;
    }

    // Step 4: AUTHENTICATE_PAYER (POST)
    $authPayload = [ 'apiOperation' => 'AUTHENTICATE_PAYER' ];
    $authenticateResponse = proxyCall($apiBasePath, $authPayload, 'POST');

    // Step 5: Return both responses
    echo json_encode([
        'step' => 'AUTHENTICATE_PAYER',
        'summaryStatus' => $summaryStatus,
        'initiateResult' => $initiateResponse,
        'authenticateResult' => $authenticateResponse
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ]);
}
