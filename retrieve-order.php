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

    header('Content-Type: application/json');

    // log the response
    error_log("=== proxyCall response 1 ===");
    error_log($response);

    // build mobile redirect with full response payload as acsResult
//    doRedirect("gatewaysdk://3dsecure?acsResult=" . urlencode($response));
    <?php
    $customUrl = "gatewaysdk://3dsecure?acsResult=" . urlencode($response);
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8" />
        <title>Redirecting...</title>
        <script>
            // Try to navigate to custom scheme after page loads
            window.onload = function() {
                // Redirect to your custom scheme
                window.location = "<?php echo $customUrl; ?>";

                // Fallback: after 2 seconds, show a message or fallback URL
                setTimeout(function() {
                    document.getElementById('message').style.display = 'block';
                }, 2000);
            };
        </script>
    </head>
    <body>
        <p>Redirecting you to the payment gateway...</p>
        <p id="message" style="display:none;">
            If you are not redirected automatically, <a href="<?php echo $customUrl; ?>">click here</a>.
        </p>
    </body>
    </html>
}

