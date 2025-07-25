<?php

include '_bootstrap.php';

if (intercept('POST')) {
    $path = '/paymentOptionsInquiry';
    proxyCall($path);
}
