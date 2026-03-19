# Gateway Test Merchant Server
This project is a **sample backend application** designed to help developers integrate mobile applications with the **Gateway Mobile SDK**. It provides basic server-side functionality required to support payment flows such as **session creation, authentication, and transaction processing**.

This server is intended for **testing and development purposes** when integrating the mobile SDKs. [Android] / [iOS]

## Table of Contents

* Overview
* Prerequisites
* Deployment
* Disclaimer
* Related Repositories

## Overview
The Gateway Test Merchant Server acts as a temporary backend service that allows developers to test mobile payment integrations without building a full production backend.

It supports the following operations:
* Create payment sessions
* Authenticate transactions
* Handle 3D Secure challenges
* Complete payments

## Prerequisites
Before running or deploying this project, ensure you have:
* A **Gateway TEST merchant account**
* **TEST Merchant ID**
* **API Password**
* Account on **Render**

## Deployment
1. Obtain a **Gateway TEST merchant account** from your Gateway provider.
1. Create an account on [Render]
1. In the Render dashboard, click **New +** and Select **Web Service**
1. Under **configure and deploy your new Web Service** choose **Public Git Repository** as the source and provide the [repository URL](https://github.com/Mastercard-Gateway/gateway-test-merchant-server)
1. Provide the required service configuration:
    * **Name**: A unique name for your web service.
    * **Branch**: The Git branch to build and deploy.
    * **Instance Type**: Free instance is sufficient for this server setup
    * **Environment Variables**: Set environment-specific config and secrets (such as API keys), then read those
        * GATEWAY_MERCHANT_ID
        * GATEWAY_API_PASSWORD
        * GATEWAY_REGION
        * GATEWAY_API_VERSION
1. After completing the configuration, click **Deploy Web Service**.
1. Once deployment is complete, open the **landing page of the deployed application** to view usage instructions and available endpoints.

## Disclaimer
All service calls responsible for handling payment information should use best-in-class security practices. This software is intended for **TEST** / **DEVELOPMENT** purposes **ONLY** and is not intended to be used in a production environment. This app will only work with **TEST** merchant IDs (ie. merchant IDs that begin with `TEST`) and should only serve to satisfy the following use cases:
* As a complimentary tool for the sample mobile apps when demonstrating functionality.
* As a stop-gap solution for developers integrating their apps with the mobile SDKs and do not yet have service components in place to support an entire transaction lifecycle.

[Android]: https://github.com/Mastercard-Gateway/gateway-android-sdk
[iOS]: https://github.com/Mastercard-Gateway/gateway-ios-sdk
[Heroku]: https://www.heroku.com
[Render]: https://dashboard.render.com/login
