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

// proxy PUT requests
if (intercept('PUT')) {
    // build path
    $threeDSId = requiredQueryParam('3DSecureId');
    $path = '/3DSecureId/' . $threeDSId;

    proxyCall($path);
}

// proxy POST requests
if (intercept('POST')) {
    // build path
    $threeDSId = requiredQueryParam('3DSecureId');
    $path = '/3DSecureId/' . $threeDSId;

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
        <h1>3DS API</h1>
        <h3>Check 3DS Enrollment</h3>
        <h5>Sample Request</h5>
        <pre><code>PUT <?php echo htmlentities($pageUrl . '?3DSecureId={3DSecureId}'); ?>

Content-Type: application/json
Payload:
{
    "apiOperation": "CHECK_3DS_ENROLLMENT",
    "3DSecure": {
      "authenticationRedirect": {
        "responseUrl" : "<?php echo htmlentities("https://".$_SERVER['SERVER_NAME']."/3DSecureResult.php?3DSecureId={3DSecureId}"); ?>"
      }
    },
    "order": {
    	"amount": "1.00",
    	"currency": "USD"
    },
    "session": {
    	"id": "SESSION0000000000000000000000"
    }
}</code></pre>

        <h5>Response</h5>
        <pre><code>Content-Type: application/json
Payload:
{
    "apiVersion": "<?php echo $apiVersion; ?>",
    "gatewayResponse": {
        "3DSecure": {
          "summaryStatus": "CARD_ENROLLED"
          "authenticationRedirect": {
            "simple": {
              "htmlBodyContent": "..."
            }
          }
        },
        "3DSecureId": "<?php echo $threeDSId; ?>",
        "merchant": "<?php echo $merchantId; ?>",
        "response": { ... },
        "version": "<?php echo $apiVersion; ?>"
    }
}</code></pre>

    <h1>3DS API</h1>
    <h3>Process ACS Result</h3>
    <h5>Sample Request</h5>
    <pre><code>POST <?php echo htmlentities($pageUrl . '?3DSecureId={3DSId}'); ?>

Content-Type: application/json
Payload:
{
  "apiOperation": "PROCESS_ACS_RESULT",
  "3DSecure": {
    "paRes": "..."
  }
}</code></pre>

    <h5>Response</h5>
    <pre><code>Content-Type: application/json
Payload:
{
"apiVersion": "<?php echo $apiVersion; ?>",
"gatewayResponse": {
    "3DSecure": {
      "summaryStatus": "AUTHENTICATION_SUCCESSFUL"
      "authenticationRedirect": {
        "simple": {
          "htmlBodyContent": "..."
        }
      }
    },
    "3DSecureId": "<?php echo $threeDSId; ?>",
    "merchant": "<?php echo $merchantId; ?>",
    "response": { ... },
    "version": "<?php echo $apiVersion; ?>"
}
}</code></pre>


    </body>
</html>
