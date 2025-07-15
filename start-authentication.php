<?php
/*
 * Unified 3DS 2.x Authentication Flow
 * Steps:
 * 1. Initiate Authentication
 * 2. Build 3DS2 Transaction (noop in PHP)
 * 3. Authenticate Payer (with full device/browser payload)
 * 4. Return result
 */

header('Content-Type: application/json');
include '_bootstrap.php';

try {
    // Step 0: Required query params
    $orderId = requiredQueryParam('orderId');
    $transactionId = requiredQueryParam('transactionId');
    $apiBasePath = "/order/{$orderId}/transaction/{$transactionId}";

    // Step 1: Parse input
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
        echo json_encode(['error' => 'Expected apiOperation: INITIATE_AUTHENTICATION']);
        exit;
    }

    // === Step 1: INITIATE_AUTHENTICATION ===
    error_log("Step 1: Initiate Authentication");
    error_log("Payload: " . json_encode($initPayload));

    $initiateResponse = proxyCall($apiBasePath, $initPayload, 'PUT');
    error_log("DEBUG: initiateResponse: " . json_encode($initiateResponse));

    $iaData = $initiateResponse['gatewayResponse'] ?? $initiateResponse;
    error_log("DEBUG: gatewayResponse used as iaData: " . json_encode($iaData));

    if (!$iaData || empty($initPayload['session']['id'])) {
        echo json_encode([
            'step' => 'INITIATE_AUTHENTICATION',
            'message' => 'No auth data returned or missing session ID',
            'initiateResult' => $initiateResponse
        ]);
        exit;
    }

    // === Step 2: Build 3DS2 Transaction (noop) ===
    error_log("Step 2: Build 3DS2 Transaction (noop)");

    // === Step 3: AUTHENTICATE_PAYER ===
    error_log("Step 3: Authenticate Payer");

    $authPayload = [
        'session' => [
            'id' => $initPayload['session']['id']
        ],
        'device' => [
            'browser' => 'MOZILLA',
            'browserDetails' => [
                '3DSecureChallengeWindowSize' => 'FULL_SCREEN',
                'acceptHeaders' => 'application/json',
                'colorDepth' => 24,
                'javaEnabled' => true,
                'language' => 'en-US',
                'screenHeight' => 640,
                'screenWidth' => 480,
                'timeZone' => 273
            ],
            'ipAddress' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
        ],
        'apiOperation' => 'AUTHENTICATE_PAYER'
    ];

    // Optional: agreement block (recurring billing)
    // $authPayload['agreement'] = [
    //     'id' => 'HCOAGREMNT2',
    //     'amountVariability' => 'FIXED',
    //     'paymentFrequency' => 'MONTHLY',
    //     'type' => 'OTHER'
    // ];

    error_log("Payload for AUTHENTICATE_PAYER: " . json_encode($authPayload));

    $authenticateResponse = proxyCall($apiBasePath, $authPayload, 'PUT');
    error_log("DEBUG: authenticateResponse: " . json_encode($authenticateResponse));

    $apData = $authenticateResponse['gatewayResponse'] ?? null;

    // === Step 4: Return Result ===
    echo json_encode([
        'step' => $apData ? 'CHALLENGE_OR_COMPLETION' : 'FRICTIONLESS',
        'initiateResult' => $initiateResponse,
        'authenticateResult' => $authenticateResponse
    ]);

} catch (Exception $e) {
    http_response_code(500);
    error_log("EXCEPTION: " . $e->getMessage());
    error_log($e->getTraceAsString());
    echo json_encode([
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ]);
}
