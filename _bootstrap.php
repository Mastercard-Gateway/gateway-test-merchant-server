<?php

/*
 * Mastercard Gateway Proxy Bootstrap
 * Licensed under the Apache License, Version 2.0
 */

error_reporting(E_ALL);
ini_set('display_errors', 0); // Set to 1 for local dev
ini_set('log_errors', 1);
ini_set('error_log', 'php://stderr'); // ✅ Heroku-compatible logging

// === ENVIRONMENT VARIABLES ===
$merchantId  = getenv('GATEWAY_MERCHANT_ID');
$password    = getenv('GATEWAY_API_PASSWORD');
$region      = getenv('GATEWAY_REGION');
$apiVersion  = getenv('GATEWAY_API_VERSION');

// === VALIDATION ===
if (!$merchantId || !$password || !$region || !$apiVersion) {
    error(500, 'Missing required environment variables.');
}
if (intval($apiVersion) < 39) {
    error(500, "API Version must be >= 39");
}

// === REGION PREFIX MAPPING ===
$regionMap = [
    "ASIA_PACIFIC" => "ap",
    "EUROPE" => "eu",
    "NORTH_AMERICA" => "na",
    "INDIA" => "in",
    "CHINA" => "cn",
    "MTF" => "mtf",
    "QA01" => "qa01",
    "QA02" => "qa02",
    "QA03" => "qa03",
    "QA04" => "qa04",
    "QA05" => "qa05",
    "QA06" => "qa06",
    "PEAT" => "perf"
];
$prefix = $regionMap[strtoupper($region)] ?? null;

if (!$prefix) {
    error(500, "Invalid region: $region. Valid values: " . implode(', ', array_keys($regionMap)));
}

// === GATEWAY URL ===
$gatewayUrl = "https://${prefix}.gateway.mastercard.com/api/rest/version/${apiVersion}/merchant/${merchantId}";

// === HEADERS ===
$headers = [
    'Content-type: application/json',
    'Authorization: Basic ' . base64_encode("merchant.$merchantId:$password")
];

// === QUERY PARSING ===
$query = [];
parse_str($_SERVER['QUERY_STRING'] ?? '', $query);

// === PAGE URL FOR DEBUGGING ===
$pageUrl = "https://" . ($_SERVER['SERVER_NAME'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '');

// === HELPERS ===

function intercept($method) {
    return strcasecmp($_SERVER['REQUEST_METHOD'], $method) === 0;
}

function doRequest($url, $method, $data = null, $headers = null) {
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);

    if (!empty($data)) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    if (!empty($headers)) {
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    }

    $response = curl_exec($curl);
    if (curl_errno($curl)) {
        $error = curl_error($curl);
        curl_close($curl);
        error_log("CURL ERROR: $error");
        error(500, "Gateway request failed: $error");
    }
    curl_close($curl);
    return $response;
}

function error($code, $message) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode(['error' => $message]);
    exit;
}

function doRedirect($url) {
    header("Location: $url");
    exit;
}

function requiredQueryParam($param) {
    global $query;
    if (!isset($query[$param]) || trim($query[$param]) === '') {
        error(400, "Missing required query param: $param");
    }
    return $query[$param];
}

function getJsonPayload() {
    $input = file_get_contents('php://input');
    if (!empty($input)) {
        json_decode($input);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error(400, 'Could not parse JSON payload');
        }
    }
    return $input;
}

function decodeResponse($response) {
    $decoded = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error(400, 'Could not decode JSON response from gateway');
    }
    return $decoded;
}

function outputJsonResponse($response) {
    global $apiVersion;
    header('Content-Type: application/json');
    $decoded = decodeResponse($response);
    echo json_encode([
        'apiVersion' => $apiVersion,
        'gatewayResponse' => $decoded
    ]);
    exit;
}

/**
 * proxyCall — forwards a request to the Mastercard Gateway
 * Can be used directly or inside a handler (e.g. session.php, auth.php)
 *
 * @param string $path - e.g. "/session" or "/order/{id}/transaction/{id}"
 * @param mixed  $data - array or raw JSON string (optional)
 * @param string $method - GET, POST, PUT, etc. (optional)
 * @return array - Decoded gateway response
 */
function proxyCall($path, $data = null, $method = null) {
    global $headers, $gatewayUrl;

    $httpMethod = $method ?: $_SERVER['REQUEST_METHOD'];
    $jsonBody = is_array($data) ? json_encode($data) : ($data ?? getJsonPayload());

    $response = doRequest($gatewayUrl . $path, $httpMethod, $jsonBody, $headers);
    return decodeResponse($response);
}
