<?php

/*
 * Copyright (c) 2016 Mastercard
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $threeDSecureId = requiredQueryParam('3DSecureId');

    // Step 1: Directly call AUTHENTICATE_PAYER (no need to decode pares)
    $authenticatePayload = array(
        'apiOperation' => 'AUTHENTICATE_PAYER'
    );

    $authResponse = doRequest(
        $gatewayUrl . '/3DSecureId/' . $threeDSecureId,
        'PUT',
        json_encode($authenticatePayload),
        $headers
    );

    // Step 2: Parse response to get summaryStatus
    $parsed = json_decode($authResponse, true);
    $summaryStatus = $parsed['3DSecure']['summaryStatus']
        ?? $parsed['gatewayResponse']['authentication']['summaryStatus']
        ?? 'UNKNOWN';

    // Step 3: Redirect mobile app with final result
    doRedirect("gatewaysdk://3dsecure?status=" . urlencode($summaryStatus));
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
    <h1>3DSecure - Authenticate Payer</h1>
    <p>This script receives the Issuer response and directly calls <strong>AUTHENTICATE_PAYER</strong> using the 3DSecureId.<br/>
    The result is then passed to the mobile app via a custom deep link.</p>
</body>
</html>