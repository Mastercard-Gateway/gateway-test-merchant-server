<?php
/*
 * Unified 3DS 2.x Authentication Flow (PHP equivalent of Kotlin steps)
 * Steps:
 * 1. Initiate Authentication
 * 2. Build 3DS2 Transaction (conceptual - no-op in PHP)
 * 3. Authenticate Payer
 * 4. Return response
 */

header('Content-Type: application/json');
include '_bootstrap.php';

try {
    // Step 0: Validate required query parameters
    $orderId = requiredQueryParam('orderId');
    $transactionId = requiredQueryParam('transactionId');
    $apiBasePath = "/order/{$orderId}/transaction/{$transactionId}";

    // Step 1: Read and validate JSON input for INITIATE_AUTHENTICATION
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

    // === 1. Initiate Authentication ===
    error_log("Step 1: Initiate Authentication");
    $initiateResponse = proxyCall($apiBasePath, $initPayload, 'PUT');
    $iaData = $initiateResponse['gatewayResponse'] ?? null;

    if (!$iaData) {
        echo json_encode([
            'step' => 'INITIATE_AUTHENTICATION',
            'message' => 'No authentication data returned, proceeding without 3DS',
            'initiateResult' => $initiateResponse
        ]);
        exit;
    }

    // === 2. Build 3DS Transaction ===
    // (No actual logic needed in PHP, this step is conceptual)
    error_log("Step 2: Build 3DS2 Transaction");

    // === 3. Authenticate Payer ===
    error_log("Step 3: Authenticate Payer");
    $authPayload = [ 'apiOperation' => 'AUTHENTICATE_PAYER' ];
    $authenticateResponse = proxyCall($apiBasePath, $authPayload, 'POST');
    $apData = $authenticateResponse['gatewayResponse'] ?? null;

    if (!$apData) {
        echo json_encode([
            'step' => 'AUTHENTICATE_PAYER',
            'message' => 'Frictionless flow detected, no challenge required',
            'initiateResult' => $initiateResponse,
            'authenticateResult' => $authenticateResponse
        ]);
        exit;
    }

    // === 4. Return full flow data ===
    echo json_encode([
        'step' => 'CHALLENGE_OR_COMPLETION',
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
