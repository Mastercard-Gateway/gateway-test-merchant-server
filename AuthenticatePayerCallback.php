<?php

/*
 * Copyright (c) 2016 Mastercard
 */

include '_bootstrap.php';

if (intercept('POST')) {
    $threeDSecureId = requiredQueryParam('3DSecureId');
    $orderId = requiredQueryParam('orderId');
    $transactionId = requiredQueryParam('transactionId');

    // Step 1: Call AUTHENTICATE_PAYER
    $payload = array(
        'apiOperation' => 'AUTHENTICATE_PAYER'
    );

    $authResponse = doRequest(
        $gatewayUrl . '/3DSecureId/' . $threeDSecureId,
        'PUT',
        json_encode($payload),
        $headers
    );

    // Step 2: Parse summaryStatus
    $parsed = json_decode($authResponse, true);
    $summaryStatus = $parsed['3DSecure']['summaryStatus']
        ?? $parsed['gatewayResponse']['authentication']['summaryStatus']
        ?? 'UNKNOWN';

    // Step 3: Call Retrieve Transaction (NVP format expected)
    $transactionUrl = $gatewayUrl . "/merchant/{$merchantId}/order/{$orderId}/transaction/{$transactionId}";
    $transactionResponse = doRequest(
        $transactionUrl,
        'GET',
        null,
        $headers
    );

    // Step 4: Parse NVP response
    parse_str(str_replace("\n", "&", trim($transactionResponse)), $transactionData);
    $transactionStatus = $transactionData['transaction.status'] ?? 'UNKNOWN';
    $amount = $transactionData['transaction.amount'] ?? '';
    $currency = $transactionData['transaction.currency'] ?? '';
    $authCode = $transactionData['transaction.authorizationCode'] ?? '';

    // Step 5: Redirect to mobile app with result
    $redirectUrl = "gatewaysdk://3dsecure?"
        . "status=" . urlencode($summaryStatus)
        . "&txnStatus=" . urlencode($transactionStatus)
        . "&amount=" . urlencode($amount)
        . "&currency=" . urlencode($currency)
        . "&authCode=" . urlencode($authCode)
        . "&orderId=" . urlencode($orderId)
        . "&transactionId=" . urlencode($transactionId);

    doRedirect($redirectUrl);
}
?>

<html>
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css"
          integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M"
          crossorigin="anonymous">
    <style>
        body {
            padding: 2rem;
        }
    </style>
</head>
<body>
    <h1>3DSecure - Authenticate Payer Callback</h1>
    <p>This page handles the POST response from the ACS server (issuer). It calls <code>AUTHENTICATE_PAYER</code>, retrieves transaction details,
       and redirects the result to the mobile app via a deep link with transaction info.</p>
</body>
</html>