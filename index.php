<?php

/*
 * Copyright (c) 2016 Mastercard
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

include '_bootstrap.php';
?>
<html>
    <head>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
        <style>
            body {
                padding: 3rem;
            }
        </style>
    </head>
    <body>
        <h1>Gateway Test Merchant Server</h1>
        <p>This is an sample application to help developers start building mobile applications using the Gateway mobile SDK.</p>
        <h3>Available APIs</h3>
        <ul>
            <li><a href="./session.php">Session API</a></li>
            <li><a href="./transaction.php">Transaction API</a></li>
            <li><a href="./3DSecure.php">3DSecure API</a></li>
        </ul>
    </body>
</html>
