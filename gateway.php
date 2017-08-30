<?php

header('Content-Type: application/json');
error_reporting('all');

$merchantId = getenv('GATEWAY_MERCHANT_ID');
$password = getenv('GATEWAY_API_PASSWORD');

$gatewayUrl = 'https://test-gateway.mastercard.com/api/rest/version/43/merchant/' . $merchantId;

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
    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    http_response_code($code);
    print_r($response);
}

// GET requests will create a new session with the gateway
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    doRequest($gatewayUrl . '/session', 'POST', null, $headers);
}
// POST requests will process a payment for an updated session
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = 'TEST-' . srand();
    $txnId = 'TEST-' . srand();
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

    doRequest($url, 'PUT', json_encode($data), $headers);
}
else {
    http_response_code(400);
    echo 'No action matching this request';
}

?>
