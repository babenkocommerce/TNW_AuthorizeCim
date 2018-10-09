# Magento 2 Authorize.net CIM

[![CircleCI](https://circleci.com/gh/PowerSync/TNW_AuthorizeCim.svg?style=svg&circle-token=52899cb3d7dc3d27fccc718aca591d8fe5183e13)](https://circleci.com/gh/PowerSync/TNW_AuthorizeCim)

Accept and store customer information, including payment methods, with
Authorize.net's Customer Information Manager(CIM). 

* Securely accept customer payments using the Accept.js tokenization when
collecting all payments.
* Provide customers option of storing payment information for future 
transactions.
* Stored customer card information can be used for orders created in the
frontend or backend.
* New payments can be authorize or authorize and capture.
* Authorized payments can be captured online during invoice creation.
* Full and partial refund support when creating credit memos.
* Support for Multiple Shipping Addresses
* Supports for Magento InstantPurchase

## Installation
In your Magento 2 root directory run  
`composer require tnw/module-authorizenetcim`  
`bin/magento setup:upgrade`

## Configuration
The configuration can be found in the Magento 2 admin panel under  
Store->Configuration->Sales->Payment Methods->Authorize.net CIM  

#### Accept.js and Test mode
Test Mode requires your checkout and admin pages use the HTTPS protocol even
in your staging development environments. The correct version of Accetp.js,
https://jstest.authorize.net/v1/Accept.js, is loaded when the module is in test
mode, but it will error if you attempt to use it over HTTP. You do NOT need a
valid SSL certificate for testing. 

## License
[GNU General Public License v3.0](https://choosealicense.com/licenses/gpl-3.0/)
