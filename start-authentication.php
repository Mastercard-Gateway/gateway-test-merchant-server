<?php
/*
 * Unified 3DS 2.x Authentication Flow
 * Steps:
 * 1. Initiate Authentication
 * 2. Build 3DS2 Transaction (noop in PHP)
 * 3. Authenticate Payer (with full device/browser payload)
 * 4. Return result
 */

include '_bootstrap.php';

// --------------------------------------
// Handle POST (authentication flow)
// --------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

        $iaData = $initiateResponse['gatewayResponse'] ?? $initiateResponse;
        if (!$iaData || empty($initPayload['session']['id'])) {
            echo json_encode([
                'step' => 'INITIATE_AUTHENTICATION',
                'message' => 'No auth data returned or missing session ID',
                'initiateResult' => $initiateResponse
            ]);
            exit;
        }

        // === Step 2: Build 3DS2 Transaction (noop in PHP) ===
        error_log("Step 2: Build 3DS2 Transaction (noop)");

        // === Step 3: AUTHENTICATE_PAYER ===
        error_log("Step 3: Authenticate Payer");
        $authPayload = [
            'apiOperation' => 'AUTHENTICATE_PAYER',
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
            ]
        ];
        $authenticateResponse = proxyCall($apiBasePath, $authPayload, 'PUT');

        // === Step 4: Return Result ===
        echo json_encode([
            'step' => 'CHALLENGE_OR_COMPLETION',
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

    exit;
}

// --------------------------------------
// Handle GET (HTML Sample Page)
// --------------------------------------

$pageUrl = $_SERVER['PHP_SELF'];
$apiVersion = "64"; // can be dynamic
?>
<?php
$pageUrl = $_SERVER['PHP_SELF'];
$apiVersion = "64"; // can be dynamic
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Start Authentication</title>
    <link rel="stylesheet"
          href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css"
          integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M"
          crossorigin="anonymous">
    <style>
        body {
            padding: 2rem;
        }
        pre {
            background-color: #f8f9fa;
            padding: 1rem;
            border: 1px solid #dee2e6;
            border-radius: 0.3rem;
        }
    </style>
</head>
<body>

<h3>Start Authentication (Initiate + Authenticate)</h3>

<h5>Sample Request</h5>
<pre>
POST <?php echo htmlentities($pageUrl); ?>?orderId={order-id}&transactionId={transaction-id}
Content-Type: application/json

Payload:
{
  "apiOperation": "INITIATE_AUTHENTICATION",
  "session": {
    "id": "SESSION_ID_HERE"
  }
}
</pre>

<h5>Sample Response</h5>
<pre>
Content-Type: application/json

Payload:
{
  "step": "CHALLENGE_OR_COMPLETION",
  "initiateResult": {
    "apiVersion": "<?php echo $apiVersion; ?>",
    "gatewayResponse": {
      "authentication": {
        "version": "2.1.0",
        "summaryStatus": "CARD_ENROLLED",
        "redirectHtml": "<script>...</script>"
      },
      "order": {
        "id": "{order-id}",
        "status": "PENDING"
      },
      "transaction": {
        "id": "{transaction-id}",
        "type": "AUTHENTICATION"
      },
      "result": "SUCCESS"
    }
  },
  "authenticateResult": {
    "apiVersion": "<?php echo $apiVersion; ?>",
    "gatewayResponse": {
      "authentication": {
        "summaryStatus": "AUTHENTICATION_SUCCESSFUL",
        "redirectHtml": "<html>...</html>"
      },
      "order": {
        "id": "{order-id}",
        "status": "AUTHENTICATED"
      },
      "transaction": {
        "id": "{transaction-id}",
        "type": "AUTHENTICATION"
      },
      "result": "SUCCESS"
    }
  }
}
</pre>

</body>
</html>
