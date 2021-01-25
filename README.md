# kaching
Backend software for Kaching Wallet

## Features
* Single Role
    * Subscriber
            
## Units Tested
* Contact
    * mobile searchable [x]
    * default wallet [X]
    * balance, deposit, withdraw, transfer [x]
    * multi-wallet [x]
        * GenX
        * PCSO

## Features Tested
* Auto-register subscriber
    * zero balance [x]
    * exclusive PH mobile [x]
    * token-based API access [x]   
    * OTP for deposit and withdrawal [x]
     
## Deployment
* Heroku
    * Postgres [x]
    
## TODO
* Wallets
    * On-demand
    * Dynamic
    * OTP from database notification to SMS
    * App/Models/Transaction inherit from Bavix\Wallet\Models\Transaction
    
