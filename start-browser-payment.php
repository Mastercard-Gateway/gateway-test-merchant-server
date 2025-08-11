<?php

/*
 * Copyright (c) 2025 Mastercard
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

include '_bootstrap.php';

// proxy POST and PUT requests
if (intercept('PUT')) {
    header('Content-Type: application/json');

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
}
?>

<html>
    <head>
        <meta charset="UTF-8">
        <title>Start Browser Payment</title>
        <link rel="stylesheet"
            href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css"
            integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M"
            crossorigin="anonymous">
        <style> body { padding: 2rem; } </style>
    </head>

    <body>

    <h1>Browser Payment API</h1>
    <h3>Start Browser Payment (INITIATE_BROWSER_PAYMENT)</h3>

    <h5>Sample Request</h5>
    <pre><code>PUT <?php echo htmlentities('https://francophone-leaf-52430-c8565a556f27.herokuapp.com/start-browser-payment.php?orderId={order-id}&transactionId={transaction-id}'); ?>

    Content-Type: application/json

    Payload:
    {
    "apiOperation": "INITIATE_BROWSER_PAYMENT",
    "browserPayment": {
        "operation": "PAY",
        "returnUrl": "https://mcdelivery.co.in/"
    },
    "order": {
        "reference": "TEST-SUCCEED",
        "amount": "90.00",
        "currency": "KWD",
        "description": "apmspi test order"
    },
    "sourceOfFunds": {
        "type": "BROWSER_PAYMENT",
        "browserPayment": {
        "type": "KNET"
        }
    },
    "customer": {
        "email": "akash.mali@mastercard.com",
        "firstName": "Akash",
        "lastName": "Mali",
        "phone": "9898989898"
    },
    "billing": {
        "address": {
        "city": "Edinburgh",
        "country": "KWT",
        "postcodeZip": "2000"
        }
    }
    }
    </code></pre>

    <h5>Sample Response</h5>
    <pre><code>Content-Type: application/json

    {
    "step": "INITIATE_BROWSER_PAYMENT",
    "result": {
        "gatewayResponse": {
        "order": {
            "id": "{order-id}",
            "status": "PENDING"
        },
        "transaction": {
            "id": "{transaction-id}",
            "type": "PAYMENT"
        },
        "browserPayment": {
            "redirectUrl": "https://...",
            "response": "REQUIRES_REDIRECT"
        },
        "result": "SUCCESS"
        }
    }
    }
    </code></pre>

    </body>
</html>