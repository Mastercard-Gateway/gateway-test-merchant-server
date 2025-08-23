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

      if (
          !isset($initPayload['apiOperation']) ||
          strtoupper($initPayload['apiOperation']) !== 'INITIATE_AUTHENTICATION'
      ) {
          http_response_code(400);
          echo json_encode(['error' => 'Expected apiOperation: INITIATE_AUTHENTICATION']);
          exit;
      }

      if (!isset($initPayload['authentication'])) {
          $initPayload['authentication'] = [
              'purpose' => 'PAYMENT_TRANSACTION',
              'channel' => 'PAYER_BROWSER'
          ];
      }

      $amount = null;
      if (isset($initPayload['order']['amount'])) {
          $amount = $initPayload['order']['amount'];
          unset($initPayload['order']['amount']);
      }

      $devicePayload = null;
      if (isset($initPayload['device'])) {
          $devicePayload = $initPayload['device'];
          unset($initPayload['device']);
      }

      // === Step 1: INITIATE_AUTHENTICATION ===
      error_log("Step 1: Initiate Authentication");
      error_log("Payload: " . json_encode($initPayload));

      $initiateResponse = proxyCall($apiBasePath, $initPayload, 'PUT', true);
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

      $recommendation = $iaData['response']['gatewayRecommendation'] ?? null;
      error_log("Recommendation: " . json_encode($recommendation));

      $status = $iaData['transaction']['authenticationStatus'] ?? null;
      error_log("Authentication Status: " . json_encode($status));

      if (isset($recommendation)) {
        switch($recommendation) {
          case 'PROCEED':
            $strippedStatus = preg_replace('/^AUTHENTICATION_/', '', $status);
            error_log("Stripped Status: " . json_encode($strippedStatus));

            switch ($strippedStatus) {
              case 'AVAILABLE':
                break;
              default:
                echo json_encode($iaData);
                exit;
            }
            break;

          default:
          echo json_encode($iaData);
          exit;
        }
      } else {
        echo json_encode($iaData);
        exit;
      }

      // === Step 2: Build 3DS2 Transaction (noop) ===
      error_log("Step 2: Build 3DS2 Transaction (noop)");

      // === Step 3: AUTHENTICATE_PAYER ===
      error_log("Step 3: Authenticate Payer");

      if (isset($devicePayload)) {
        $devicePayload['ipAddress'] = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
      } else {
        $devicePayload = [ // Fallback if $devicePayload not available
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
            ];
      }

      $authPayload = [
          'session' => [
              'id' => $initPayload['session']['id']
          ],
          'order' => [
            'currency' => $initPayload['order']['currency'],
            'amount' => $amount
          ],
          'apiOperation' => 'AUTHENTICATE_PAYER',
          'device' => $devicePayload,
          'authentication' => [
                    'redirectResponseUrl' => "https://francophone-leaf-52430-c8565a556f27.herokuapp.com/authenticate-payer-callback.php?order={$orderId}&transaction={$transactionId}"
          ]
      ];

      error_log("Payload for AUTHENTICATE_PAYER: " . json_encode($authPayload));

      $authenticateResponse = proxyCall($apiBasePath, $authPayload, 'PUT', true);
      error_log("DEBUG: authenticateResponse: " . json_encode($authenticateResponse));

      // === Step 4: Return Result ===
      error_log("Step 4: Return Result");
      echo json_encode($authenticateResponse);
      exit;
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
      <title>Start Authentication</title>
      <link rel="stylesheet"
            href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css"
            integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M"
            crossorigin="anonymous">
      <style>
          body {
              padding: 2rem;
          }
      </style>
  </head>

  <body>

  <h1>3DSecure API</h1>
  <h3>Start Authentication (INITIATE + AUTHENTICATE)</h3>

  <h5>Sample Request</h5>
  <pre><code>PUT <?php echo htmlentities('https://francophone-leaf-52430-c8565a556f27.herokuapp.com/start-authentication?orderId={order-id}&transactionId={transaction-id}'); ?>
Content-Type: application/json
Payload:
{
  "apiOperation": "INITIATE_AUTHENTICATION",
  "session": {
    "id": "SESSION0002677199564H1362874H71"
  },
  "authentication": {
    "purpose": "PAYMENT_TRANSACTION",
    "channel": "PAYER_BROWSER"
  }
}</code></pre>

  <h5>Sample Response</h5>
  <pre><code>Content-Type: application/json
Payload:
{
  "step": "CHALLENGE_OR_COMPLETION",
  "initiateResult": {
    "apiVersion": "<?php echo $apiVersion; ?>",
    "gatewayResponse": {
      "authentication": {
        "version": "2.1.0",
        "summaryStatus": "CARD_ENROLLED",
        "redirectHtml": "&lt;script&gt;...&lt;/script&gt;"
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
        "redirectHtml": "&lt;html&gt;...&lt;/html&gt;"
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
}</code></pre>
  </body>
</html>