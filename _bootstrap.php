<?php

/*
 * Copyright (c) 2016 Mastercard
 * Licensed under the Apache License, Version 2.0
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'php://stderr'); // ✅ Logs to Heroku

// === ENVIRONMENT VARIABLES ===
$merchantId  = getenv('GATEWAY_MERCHANT_ID');
$password    = getenv('GATEWAY_API_PASSWORD');
$region      = getenv('GATEWAY_REGION');
$apiVersion  = getenv('GATEWAY_API_VERSION');

// === REGION MAPPING ===
$prefix = 'mtf';
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
if (isset($regionMap[strtoupper($region)])) {
    $prefix = $regionMap[strtoupper($region)];
} else {
    error(500, "Invalid region provided. Valid values include: " . implode(", ", array_keys($regionMap)));
}

// === API VERSION CHECK ===
if (intval($apiVersion) < 39) {
    error(500, "API Version must be >= 39");
}

// === BUILD GATEWAY URL ===
$gatewayUrl = "https://${prefix}.gateway.mastercard.com/api/rest/version/${apiVersion}/merchant/${merchantId}";

// === AUTH HEADERS ===
$headers = [
    'Content-type: application/json',
    'Authorization: Basic ' . base64_encode("merchant.$merchantId:$password")
];

// === PARSE QUERY ===
$query = [];
parse_str($_SERVER['QUERY_STRING'] ?? '', $query);

// === PAGE URL (for future use) ===
$pageUrl = "https://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

// === COMMON HELPERS ===

function intercept($method) {
    return strcasecmp($_SERVER['REQUEST_METHOD'], $method) === 0;
}

function doRequest($url, $method, $data = null, $headers = null) {
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    if (!empty($data)) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    if (!empty($headers)) {
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    }
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

function error($code, $message) {
    http_response_code($code);
    print_r($message);
    exit;
}

function doRedirect($url) {
    header("Location: $url");
    exit;
}

function requiredQueryParam($param) {
    global $query;
    if (!isset($query[$param]) || empty($query[$param])) {
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
    print_r(json_encode([
        'apiVersion' => $apiVersion,
        'gatewayResponse' => $decoded
    ]));
    exit;
}

/**
 * proxyCall — handles both default proxying and manual API calls.
 *
 * @param string $path   - Gateway API path
 * @param mixed  $data   - JSON-serializable array or raw JSON (optional)
 * @param string $method - HTTP method (GET, POST, PUT, etc.) (optional)
 * @return array - Decoded gateway response
 */
function proxyCall($path, $data = null, $method = null) {
    global $headers, $gatewayUrl;

    $httpMethod = $method ?: $_SERVER['REQUEST_METHOD'];
    $jsonBody = is_array($data) ? json_encode($data) : ($data ?? getJsonPayload());

    $response = doRequest($gatewayUrl . $path, $httpMethod, $jsonBody, $headers);
    return decodeResponse($response);
}
