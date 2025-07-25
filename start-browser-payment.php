<?php
/*
 * Unified Browser Payment Flow
 * Steps:
 * 0. Required query params
 * 1. Parse input
 * 2. Validate apiOperation
 * 3. Call INITIATE_BROWSER_PAYMENT
 * 4. Return result
 */

header('Content-Type: application/json');
include '_bootstrap.php';

try {
    // Step 0: Required query params
    $orderId = requiredQueryParam('orderId');
    $transactionId = requiredQueryParam('transactionId');

    // Step 1: Parse input
    $rawInput = file_get_contents('php://input');
    $initPayload = json_decode($rawInput, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON in request body']);
        exit;
    }

    // Step 2: Validate apiOperation
    if (
        !isset($initPayload['apiOperation']) ||
        strtoupper($initPayload['apiOperation']) !== 'INITIATE_BROWSER_PAYMENT'
    ) {
        http_response_code(400);
        echo json_encode(['error' => 'Expected apiOperation: INITIATE_BROWSER_PAYMENT']);
        exit;
    }

    // Step 3: Call INITIATE_BROWSER_PAYMENT
    error_log("Step 1: Initiate Browser Payment");
    error_log("Payload: " . json_encode($initPayload));

    $response = proxyCall($apiBasePath, $initPayload, 'PUT');
    error_log("DEBUG: Response: " . json_encode($response));

    // === Step 4: Return Result ===
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    error_log("EXCEPTION: " . $e->getMessage());
    error_log($e->getTraceAsString());
    echo json_encode([
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ]);
}
