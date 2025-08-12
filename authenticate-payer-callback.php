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

if (intercept('POST')) {
    $orderId = $_GET['order'] ?? null;
    $transactionId = $_GET['transaction'] ?? null;

    try {
        // Step 1: Retrieve transaction
        $transactionUrl = $gatewayUrl . "/order/{$orderId}/transaction/{$transactionId}";
        $transactionResponse = doRequest($transactionUrl, 'GET', null, $headers);

        // Step 2: Redirect
        $redirectUrl = "gatewaysdk://3dsecure?acsResult=" . urlencode($transactionResponse);
        doRedirect($redirectUrl);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>