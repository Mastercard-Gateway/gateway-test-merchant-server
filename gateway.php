<?php

header('Content-Type: application/json');
error_reporting('all');

$merchantId = getenv('GATEWAY_MERCHANT_ID');
$password = getenv('GATEWAY_API_PASSWORD');

$gatewayUrl = 'https://test-gateway.mastercard.com/api/rest/version/43/merchant/' . $merchantId;

$options = array(
    'http' => array(
        'header'  => "Content-type: application/json\r\nAuthorization: Basic " . base64_encode("merchant.$merchantId:$password") . "\r\n"
    )
);

// GET requests will create a new session with the gateway
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $url = $gatewayUrl . '/session';

    $options['http']['method'] = 'POST';
    $context = stream_context_create($options);

    $result = file_get_contents($url, false, $context);
    print_r($result);
    exit;
}
// POST requests will process a payment for an updated session
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = 'O' . rand();
    $txnId = 'T' . rand();
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

    $options['http']['method'] = 'PUT';
    $options['http']['content'] = json_encode($data);
    $context = stream_context_create($options);

    $result = file_get_contents($url, false, $context);
    print_r($result);
    exit;
}

http_response_code(400);
echo 'No action matching this request';

?>
