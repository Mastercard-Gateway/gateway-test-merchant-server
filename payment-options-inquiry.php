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
    $path = '/paymentOptionsInquiry';
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
        <h1>Payment Options Inquiry API</h1>
        <h5>Sample Request</h5>
        <pre><code>POST <?php echo $pageUrl . "\n"; ?>
Content-Type: application/json</code></pre>
        <h5>Sample Response</h5>
        <pre><code>Content-Type: application/json
Payload:
{
    "merchant": "TEST_12345",
    "paymentTypes": {
        "browserPayment": [
            {
                "currencies": [
                    {
                        "currency": "USD"
                    }
                ],
                "displayName": "USDWITHREFUND",
                "logoUrl": "Merchant Logo URL in Specific Format",
                "maxAmount": 1000000000000,
                "minAmount": 1,
                "standardPayerData": [
                    {
                        "fieldName": "EMAIL",
                        "presence": "OPTIONAL"
                    },
                    {
                        "fieldName": "PHONE",
                        "presence": "OPTIONAL"
                    }
                ],
                "supportedCountries": [
                    {
                        "country": "USA"
                    }
                ],
                "type": "USD"
            }
        ]
    },
    "result": "SUCCESS",
    "supportedPaymentOperations": [
        {
            "supportedPaymentOperation": "PURCHASE"
        }
    ]
}</code></pre>
    </body>
</html>