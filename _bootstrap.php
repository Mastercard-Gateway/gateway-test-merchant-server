<?php

/*
 * Copyright (c) 2017, MasterCard International Incorporated
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification, are
 * permitted provided that the following conditions are met:
 *
 * Redistributions of source code must retain the above copyright notice, this list of
 * conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, this list of
 * conditions and the following disclaimer in the documentation and/or other materials
 * provided with the distribution.
 * Neither the name of the MasterCard International Incorporated nor the names of its
 * contributors may be used to endorse or promote products derived from this software
 * without specific prior written permission.
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
 * OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT
 * SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED
 * TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS;
 * OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER
 * IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING
 * IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
 * SUCH DAMAGE.
 */

error_reporting('all');

// pull environment vars
$merchantId = getenv('GATEWAY_MERCHANT_ID');
$password = getenv('GATEWAY_API_PASSWORD');
$region = getenv('GATEWAY_REGION');
$apiVersion = getenv('GATEWAY_API_VERSION');

// default merchant id
if (empty($merchantId)) {
    $merchantId = 'TEST_MERCHANT_ID';
}

// init region prefix
$regionPrefix = 'test';
if (strcasecmp($region, 'North America') == 0) {
    $regionPrefix = 'na';
} else if (strcasecmp($region, 'Asia Pacific') == 0) {
    $regionPrefix = 'ap';
} else if (strcasecmp($region, 'Europe') == 0) {
    $regionPrefix = 'eu';
}

// build api endpoint url
$gatewayUrl = 'https://' . $regionPrefix . '-gateway.mastercard.com/api/rest/version/' . $apiVersion . '/merchant/' . $merchantId;

// parae query string
$query = array();
parse_str($_SERVER['QUERY_STRING'], $query);

// build auth headers
$headers = array(
    'Content-type: application/json',
    'Authorization: Basic ' . base64_encode("merchant.$merchantId:$password")
);

// construct page url
$pageUrl = "http".(!empty($_SERVER['HTTPS'])?"s":"")."://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
$docsUrl = "https://$regionPrefix-gateway.mastercard.com/api/documentation/apiDocumentation/rest-json/version/latest/api.html";

function intercept($method) {
    return strcasecmp($_SERVER['REQUEST_METHOD'], $method) == 0;
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

function requiredQueryParam($param) {
    global $query;

    if (!array_key_exists($param, $query) || empty($query[$param])) {
        error(400, 'Missing required query param: ' . $param);
    }

    return $query[$param];
}

function getJsonPayload() {
    $input = file_get_contents('php://input');
    if (!empty($input)) {
        json_decode($input);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error(400, 'Could not parse json payload');
        }
    }

    return $input;
}

function outputJsonResponse($response) {
    header('Content-Type: application/json');
    print_r($response);
    exit;
}

function proxyCall($path) {
    global $headers, $gatewayUrl;

    // get json payload from request
    $payload = getJsonPayload();

    // proxy authenticated request
    $response = doRequest($gatewayUrl . $path, $_SERVER['REQUEST_METHOD'], $payload, $headers);

    // output response
    outputJsonResponse($response);
}
