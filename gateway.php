<?php

header('Content-Type: application/json');
error_reporting('all');

$merchantId = getenv('GATEWAY_MERCHANT_ID');
$password = getenv('GATEWAY_API_PASSWORD');

$gatewayUrl = 'https://test-gateway.mastercard.com/api/rest/version/43/merchant/' . $merchantId;

// $options = array(
//     'http' => array(
//         'header'  => "Content-type: application/json\r\nAuthorization: Basic " . base64_encode("merchant.$merchantId:$password") . "\r\n"
//     )
// );

$headers = array(
    'Content-type: application/json',
    'Authorization: Basic ' . base64_encode("merchant.$merchantId:$password")
);

$curl = curl_init();
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);


// GET requests will create a new session with the gateway
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $url = $gatewayUrl . '/session';

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');

    $response = curl_exec($curl);
    print_r($result);
    exit;

    // $result = file_get_contents($url, false, $context);
    // print_r($result);
    // exit;
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

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($curl);
    print_r($result);
    exit;
    //
    // $options['http']['method'] = 'PUT';
    // $options['http']['content'] = json_encode($data);
    // $context = stream_context_create($options);
    //
    // $result = file_get_contents($url, false, $context);
    // print_r($result);
    // exit;
}

http_response_code(400);
echo 'No action matching this request';

?>
