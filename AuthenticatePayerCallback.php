<?php

include '_bootstrap.php';

if (intercept('POST')) {
    $threeDSecureId = requiredQueryParam('3DSecureId');
    $orderId = requiredQueryParam('orderId');
    $transactionId = requiredQueryParam('transactionId');

    try {
        // Step 1: AUTHENTICATE_PAYER
        $payload = [ 'apiOperation' => 'AUTHENTICATE_PAYER' ];

        $authResponse = doRequest(
            $gatewayUrl . '/3DSecureId/' . $threeDSecureId,
            'POST', // ✅ Use POST
            json_encode($payload),
            $headers
        );

        $parsed = json_decode($authResponse, true);
        $summaryStatus = $parsed['3DSecure']['summaryStatus']
            ?? $parsed['gatewayResponse']['authentication']['summaryStatus']
            ?? 'UNKNOWN';

        // Step 2: Retrieve transaction
        $transactionUrl = $gatewayUrl . "/merchant/{$merchantId}/order/{$orderId}/transaction/{$transactionId}";
        $transactionResponse = doRequest($transactionUrl, 'GET', null, $headers);

        // Step 3: Parse NVP transaction data
        $transactionData = [];
        if (!empty($transactionResponse) && strpos($transactionResponse, '=') !== false) {
            parse_str(str_replace("\n", "&", trim($transactionResponse)), $transactionData);
        }

        $transactionStatus = $transactionData['transaction.status'] ?? 'UNKNOWN';
        $amount = $transactionData['transaction.amount'] ?? '';
        $currency = $transactionData['transaction.currency'] ?? '';
        $authCode = $transactionData['transaction.authorizationCode'] ?? '';

        // Step 4: Redirect to mobile app
        $params = [
            'status' => $summaryStatus,
            'txnStatus' => $transactionStatus,
            'amount' => $amount,
            'currency' => $currency,
            'authCode' => $authCode,
            'orderId' => $orderId,
            'transactionId' => $transactionId
        ];

        $redirectUrl = "gatewaysdk://3dsecure?" . http_build_query($params);
        doRedirect($redirectUrl);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>