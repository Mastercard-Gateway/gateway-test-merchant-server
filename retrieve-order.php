<?php

include '_bootstrap.php';

if (intercept('GET')) {
    error_log("=== proxyCall invoked ===");

    $orderId = $_GET['order'] ?? null;
    $transactionId = $_GET['transaction'] ?? null;

    if (!$orderId || !$transactionId) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing order or transaction ID']);
        exit;
    }

    $response = doRequest(
        $gatewayUrl . "/order/$orderId/transaction/$transactionId",
        'GET',
        null,
        $headers
    );

    // Log for debugging
    error_log("=== proxyCall response ===");
    error_log($response);

    // Escape payload for safe embedding in URL
    $encodedPayload = urlencode($response);
    $redirectUrl = "gatewaysdk://3dsecure?acsResult=" . $encodedPayload;

    // Instead of doRedirect(), print HTML + JS for redirection
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Redirecting...</title>
    </head>
    <body>
        <p>Redirecting to your app...</p>
        <script>
            window.location.href = "<?= $redirectUrl ?>";
        </script>
    </body>
    </html>
    <?php
    exit;
}

?>
