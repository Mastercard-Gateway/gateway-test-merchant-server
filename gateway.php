<?php

error_reporting('all');

$merchantId = getenv('GATEWAY_MERCHANT_ID');
$password = getenv('GATEWAY_API_PASSWORD');
$apiVersion = getenv('GATEWAY_API_VERSION');

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
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $response = doRequest($gatewayUrl . '/session', 'POST', null, $headers);
    header('Content-Type: application/json');
    print_r($response);
}
// POST requests will process a payment for an updated session
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            'id' => $input['session_id']
        ),
        'sourceOfFunds' => array(
            'type' => 'CARD'
        )
    );

    $response = doRequest($url, 'PUT', json_encode($data), $headers);

    header('Content-Type: application/json');
    print_r($response);
}
else {
    http_response_code(400);
    echo 'No action matching this request';
}

?>
