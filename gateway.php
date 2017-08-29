<?php

header('Content-Type: application/json');
error_reporting('all');

$merchantId = getenv('GATEWAY_MERCHANT_ID');
$password = getenv('GATEWAY_API_PASSWORD');

$gatewayUrl = 'https://test-gateway.mastercard.com/api/rest/version/43/merchant/' . $merchantId;

$options = array(
    'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\nAuthorization: Basic " . base64_encode("merchant.$merchantId:$password") . "\r\n"
    )
);

// GET requests will create a new session with the gateway
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $options['http']['method'] = 'POST';
    $context = stream_context_create($options);

    $url = $gatewayUrl . '/session';
    $result = file_get_contents($url, false, $context);

    var_dump($result);
    exit;
}
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    var_dump($HTTP_RAW_POST_DATA);
    exit;
    
    $input = json_decode(file_get_contents('php://input'));
    $data = array(
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

    $orderId = uniqid("", true);
    $txnId = uniqid("", true);
    $url = $gatewayUrl . '/order/' . $orderId . '/transaction/' . $txnId;
    $result = file_get_contents($url, false, $context);

    var_dump($result);
    exit;
}

http_response_code(400);
echo 'No action matching this request';

?>
