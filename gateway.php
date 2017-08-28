<?php

header('Content-Type: application/json');
error_reporting('all');

$merchantId = getenv('GATEWAY_MERCHANT_ID');
$password = getenv('GATEWAY_API_PASSWORD');

$gatewayUrl = 'https://test-gateway.mastercard.com/api/rest/version/43/merchant/' . $merchantId;

// GET requests will create a new session with the gateway
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $url = $gatewayUrl . '/session';
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\nAuthorization: Basic " . base64_encode("merchant.$merchantId:$password") . "\r\n",
            'method'  => 'POST',
            'content' => ""
        )
    );
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    var_dump($result);
}

?>
