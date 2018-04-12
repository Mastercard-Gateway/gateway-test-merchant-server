# Gateway Test Merchant Server
This is a sample application to help developers start building mobile applications using the Gateway mobile SDK. [Android] / [iOS]

## Steps for running
1. Obtain an account with your Gateway provider
1. Register with [Heroku]
1. Click this button [![Deploy](https://www.herokucdn.com/deploy/button.png)](https://heroku.com/deploy)
1. Configure the app with your TEST merchant ID and API password
1. Visit the landing page of the newly deployed app for more details

## Disclaimer
All service calls responsible for handling payment information should use best-in-class security practices. This software is intended for **TEST** / **DEVELOPMENT** purposes **ONLY** and is not intended to be used in a production environment. This app will only work with **TEST** merchant IDs (ie. merchant IDs that begin with `TEST`) and should only serve to satisfy the following use cases:
* As a complimentary tool for the sample mobile apps when demonstrating functionality.
* As a stop-gap solution for developers integrating their apps with the mobile SDKs and do not yet have service components in place to support an entire transaction lifecycle.

[Android]: https://github.com/Mastercard-Gateway/gateway-android-sdk
[iOS]: https://github.com/Mastercard-Gateway/gateway-ios-sdk
[Heroku]: https://www.heroku.com
