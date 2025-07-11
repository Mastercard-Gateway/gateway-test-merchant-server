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

    // Debug: Print initiateResponse
    error_log("INITIATE RESPONSE:\n" . print_r($initiateResponse, true));

    // Step 3: Extract authentication status and gateway code
    $authStatus = $initiateResponse['gatewayResponse']['order']['authenticationStatus'] ?? null;
    $gatewayCode = $initiateResponse['gatewayResponse']['response']['gatewayCode'] ?? null;

    // Debug: Log extracted values
    error_log("AUTHENTICATION STATUS: $authStatus");
    error_log("GATEWAY CODE: $gatewayCode");

    // Optional condition based on status
    if ($authStatus === 'AUTHENTICATION_UNAVAILABLE') {
        echo json_encode([
            'step' => 'INITIATE_AUTHENTICATION_ONLY',
            'message' => 'Authentication is not available for this card',
            'initiateResult' => $initiateResponse
        ]);
        exit;
    }

    // Step 4: AUTHENTICATE_PAYER (POST)
    $authPayload = [ 'apiOperation' => 'AUTHENTICATE_PAYER' ];

    // Debug: Print authPayload
    error_log("AUTH PAYLOAD:\n" . print_r($authPayload, true));

    $authenticateResponse = proxyCall($apiBasePath, $authPayload, 'POST');

    // Debug: Print authenticateResponse
    error_log("AUTHENTICATE RESPONSE:\n" . print_r($authenticateResponse, true));

    // Step 5: Return both responses
    echo json_encode([
        'step' => 'AUTHENTICATE_PAYER',
        'authenticationStatus' => $authStatus,
        'gatewayCode' => $gatewayCode,
        'initiateResult' => $initiateResponse,
        'authenticateResult' => $authenticateResponse
    ]);

} catch (Exception $e) {
    http_response_code(500);
    error_log("EXCEPTION: " . $e->getMessage());
    echo json_encode([
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ]);
}
