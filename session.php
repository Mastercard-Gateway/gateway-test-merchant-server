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

// proxy POST requests
if (intercept('POST')) {
    $path = '/session';

    if (array_key_exists('session', $query) && !empty($query['session'])) {
        $path .= '/' . $query['session'];
    }

    proxyCall($path);
}

// proxy PUT requests
if (intercept('PUT')) {
    $sessionId = requiredQueryParam('session');
    $path = '/session/' . $sessionId;

    proxyCall($path);
}

?>

<html>
    <head>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
        <style>
            body {
                padding: 2rem;
            }
        </style>
    </head>
    <body>
        <h1>Session API</h1>
        <h3>Create Session Operation</h3>
        <h5>Sample Request</h5>
        <pre><code>POST <?php echo $pageUrl . "\n"; ?>
Content-Type: application/json</code></pre>
        <h5>Sample Response</h5>
        <pre><code>Content-Type: application/json
Payload:
{
    "merchant": "TEST_12345",
    "result": "SUCCESS",
    "session": {
        "aes256Key": "abcdef12345",
        "authenticationLimit": 5,
        "id": "SESSION0000000000000000000000",
        "updateStatus": "NO_UPDATE",
        "version": "abcdef0123"
    }
}</code></pre>
        <h3>Update Session Operation</h3>
        <h5>Sample Request</h5>
        <pre><code>PUT <?php echo htmlentities($pageUrl . '?session={sessionId}'); ?>

Content-Type: application/json
Authorization: Basic with Username and Password
Payload:
{
    "authentication": {
        "channel": "PAYER_CHANNEL",
        "acceptVersions": "Accepted Versions",
        "purpose": "PAYMENT_TRANSACTION",
        "redirectResponseUrl": "Redirect URL To be Passed"
    },
    "sourceOfFunds": {
        "type": "CARD",
        "provided": {
            "card": {
                "number": "0000000000000008",
                "expiry": {
                    "month": "01",
                    "year": "42"
                },
                "securityCode": 123
            }
        }
    }
}</code></pre>
        <h5>Sample Response</h5>
        <pre><code>Content-Type: application/json
Payload:
{
    "authentication": {
        "acceptVersions": "Accepted Versions",
        "channel": "PAYER_CHANNEL",
        "purpose": "PAYMENT_TRANSACTION",
        "redirectResponseUrl": "Redirect URL Passed"
    },
    "merchant": "OTTU_MER2",
    "session": {
        "id": "SESSION0002676222061H85330310J8",
        "updateStatus": "SUCCESS",
        "version": "1468375202"
    },
    "sourceOfFunds": {
        "provided": {
            "card": {
                "brand": "Card Brand",
                "expiry": {
                    "month": "1",
                    "year": "42"
                },
                "fundingMethod": "DEBIT",
                "number": "0000000000000008",
                "scheme": "Scheme Name",
                "securityCode": 123
            }
        },
        "type": "CARD"
    },
    "version": "abcdef0123"
}
</code></pre>
    </body>
</html>
