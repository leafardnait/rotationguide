# Magekc_Rsppayment

Magento Rising Sun payment gateway.

## Description

Rising	Sun	Payment	Gateway	API, credit card form show in checkout with credit card #, expiration, cvc 

## Process

Integration	with RSP’s Payment	Gateway	requires the following steps:
1.) RSP	will provide the API Manual	and	gateway	credentials to the merchant.
2.) Merchant should	provide	their server IP address	and	need to	be whitelisted on the gateway.
3.) You	will need to integrate with	the	gateway	by following the instructions of	this GATEWAY	API.		
Request	data should	be	sent using HTTPS POST method to	the	correct	gateway	service	URL.	
4.) Once your merchant account has been	approved, you will be issued with a	live account and	gateway	 credentials.	
```bash
    Merchant Backend URL

    Live :	https://backend.rsppayment.com
    Demo :https://devbackend.rsppayment.com

    Gateway	Service	URL

    Live :https://secure.rsppayment.com/services.rsp
    Demo :https://devsecure.rsppayment.com/services.rsp
```
Install
=======

1. Go to Magento2 root folder

2. Copy the extension to app/code:

3. Enter following commands to enable module:

    ```bash
    php bin/magento module:enable Magekc_Rsppayment --clear-static-content
    php bin/magento setup:upgrade
    ```
4. Enable and configure Rsp in Magento Admin under Stores/Configuration/Payment Methods/Rising Sun payment