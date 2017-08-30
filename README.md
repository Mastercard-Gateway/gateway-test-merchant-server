# Mastercard Gateway Sample Merchant Server [![Deploy](https://www.herokucdn.com/deploy/button.png)](https://heroku.com/deploy)

This is an sample application to help developers start building mobile applications using the Mastercard Gateway mobile SDK.

## Steps for running

* Obtain a test account with [Mastercard Gateway](http://www.mastercard.com/gateway/)
* Register with [Heroku](https://www.heroku.com)
* Click the 'Deploy to Heroku' button above
* Configure the deployed app with your TEST merchant ID and API password

## Integration
After the app is deployed to Heroku, it will be accessible at
```
https://{your-app-name}.herokuapp.com/gateway.php
```

A standard GET request to this url will create a session on the Mastercard Gateway, and will return the session information as a JSON payload. Example response:
```
{
    "merchant": "TEST_MERCHANT_ID",
    "result": "SUCCESS",
    "session": {
        "id": "SESSION000123234345456567",
        "updateStatus": "NO_UPDATE",
        "version": "d87d95c301"
    }
}
```

You may then use the session id to initialize the mobile SDK and send card holder information directly to the gateway. Once this is complete, Simply POST the amount, currency, and session id back to the Heroku app to complete a transaction. Example payload:
```
{
	"amount": "1.00",
	"currency": "USD",
	"session_id": "SESSION000123234345456567"
}
```
