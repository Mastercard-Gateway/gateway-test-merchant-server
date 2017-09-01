<?php

/*
 * Copyright (c) 2017, MasterCard International Incorporated
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification, are
 * permitted provided that the following conditions are met:
 *
 * Redistributions of source code must retain the above copyright notice, this list of
 * conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, this list of
 * conditions and the following disclaimer in the documentation and/or other materials
 * provided with the distribution.
 * Neither the name of the MasterCard International Incorporated nor the names of its
 * contributors may be used to endorse or promote products derived from this software
 * without specific prior written permission.
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
 * OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT
 * SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED
 * TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS;
 * OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER
 * IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING
 * IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
 * SUCH DAMAGE.
 */

error_reporting('all');

$merchantId = getenv('GATEWAY_MERCHANT_ID');
$password = getenv('GATEWAY_API_PASSWORD');
$apiVersion = getenv('GATEWAY_API_VERSION');

// default merchant id
if (empty($merchantId)) {
    $merchantId = 'TEST_MERCHANT_ID';
}

$gatewayUrl = 'https://test-gateway.mastercard.com/api/rest/version/' . $apiVersion . '/merchant/' . $merchantId;

$headers = array(
    'Content-type: application/json',
    'Authorization: Basic ' . base64_encode("merchant.$merchantId:$password")
);

function doRequest($url, $method, $data = null, $headers = null) {
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    if ($data) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    if ($headers) {
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    }
    $response = curl_exec($curl);
    curl_close($curl);

    return $response;
}

// GET requests will create a new session with the gateway
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = doRequest($gatewayUrl . '/session', 'POST', null, $headers);

    header('Content-Type: application/json');
    print_r($response);
    exit;
}

// POST requests will process a payment for an updated session
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $orderId = 'TEST-' . bin2hex(openssl_random_pseudo_bytes(5));
    $txnId = 'TEST-' . bin2hex(openssl_random_pseudo_bytes(5));
    $url = $gatewayUrl . '/order/' . $orderId . '/transaction/' . $txnId;

    $input = json_decode(file_get_contents('php://input'), true);
    $data = array(
        'apiOperation' => 'PAY',
        'order' => array(
            'amount' => $input['amount'],
            'currency' => $input['currency']
        ),
        'session' => array(
            'id' => $input['sessionId']
        ),
        'sourceOfFunds' => array(
            'type' => 'CARD'
        ),
        'transaction' => array(
            'frequency' => 'SINGLE'
        )
    );

    $response = doRequest($url, 'PUT', json_encode($data), $headers);

    header('Content-Type: application/json');
    print_r($response);
    exit;
}


$url = "http".(!empty($_SERVER['HTTPS'])?"s":"")."://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];

?>

<html>
    <body>
        <h1>Create / Complete Checkout Session</h1>
        <h3>Create Session</h3>
        <p>Creates a Session with the gateway, and returns relevant data.</p>
        <h4>Request</h4>
<pre>
POST <?php echo $url; ?>

</pre>
        <h4>Response</h4>
        <p>Refer to gateway API docs for full response body documentation:<br/><a href="https://test-gateway.mastercard.com/api/documentation/apiDocumentation/rest-json/version/latest/operation/Session%3a%20Create%20Session.html">Session: Create Session</a></p>
<pre>
Sample Response:
{
    "merchant": "<?php echo $merchantId; ?>",
    "result": "SUCCESS",
    "session": {
        "id": "SESSION00012345678900000",
        "updateStatus": "NO_UPDATE",
        "version": "abcdef0123"
    }
}
</pre>
        <h3>Complete Session</h3>
        <p>Completes a payment after a session has been updated with card holder information</p>
        <h4>Request</h4>
<pre>
PUT <?php echo $url; ?>

Content-Type: application/json
Sample Payload:
{
    "sessionId": "SESSION00012345678900000",
    "amount": "1.00",
    "currency": "USD",
    "orderId": "O-123456", // optional
    "transactionId": "T-123456" // optional
}
</pre>
        <h4>Response</h4>
        <p>Refer to gateway API docs for full response body documentation:<br/><a href="https://test-gateway.mastercard.com/api/documentation/apiDocumentation/rest-json/version/latest/operation/Transaction%3a%20%20Pay.html">Transaction: Pay</a></p>
<pre>
Sample Response:
{
    "authorizationResponse": { ... },
    "gatewayEntryPoint": "WEB_SERVICES_API",
    "merchant": "<?php echo $merchantId; ?>",
    "order": { ... },
    "response": { ... },
    "result": "SUCCESS",
    "sourceOfFunds": { ... },
    "timeOfRecord": "2017-08-30T19:46:50.935Z",
    "transaction": { ... },
    "version": "<?php echo $apiVersion; ?>"
}
</pre>
    </body>
</html>
