# PushAuth php code
PHP push authorization


=======


[![Latest Stable Version](https://poser.pugx.org/pushauth/pushauth/v/stable)](https://packagist.org/packages/pushauth/pushauth)
[![Total Downloads](https://poser.pugx.org/pushauth/pushauth/downloads)](https://packagist.org/packages/pushauth/pushauth)
[![License](https://poser.pugx.org/pushauth/pushauth/license)](https://packagist.org/packages/pushauth/pushauth)

### Requirements
PHP 5.6 and later.

### Composer
You can install the bindings via Composer. Run the following command:

```bash
composer require pushauth/pushauth-php
```

To use the bindings, use Composer's autoload:
```php
require_once('vendor/autoload.php');
```

### Manual Installation

If you do not wish to use Composer, you can download the latest release. Then, to use the bindings, include the init.php file.

```php 
require_once('/path/to/pushauth-php/pushahuth.php');
```

### Dependencies

The bindings require the following extension in order to work properly:

* curl, although you can use your own non-cURL client if you prefer
* json
* mbstring (Multibyte String)

If you use Composer, these dependencies should be handled automatically. If you install manually, you'll want to make sure that these extensions are available.

### Getting Started

Simple usage looks like:

```php
use PushAuth\PushAuth;

//Setting your Public & Private keys
$authRequest = new PushAuth('publicKey', 'privateKey');
```

### Sending Push Request

And waiting responce from client until 30 sec:

```php
$request = $authRequest->to('client@yourfirm.com')
                       ->mode('push')
                       ->response(false)
                       ->send();

if ($authRequest->isAccept()) {
                                //Make logIn action...
                            } else {
                                //Make Access Denied action...
                            }
```

Or custom wait 10 seconds response with self check:

```php
$request = $authRequest->to('client@yourfirm.com')
                       ->mode('push')
                       ->response(true)
                       ->send();

$sec = 1;
while ($sec <= 10) {
 if ($authRequest->isAccept()) { //Make LogIn action }
$sec++;
sleep(1);
}

if ($authRequest->isAccept() == false) { //Make Access Denied action }
if ($authRequest->isAccept() == Null) { //No answer from client }  
```

### Sending Push Code

Special security code to client device:

```php
$request = $authRequest->to('client@yourfirm.com')
                       ->mode('code')
                       ->code('123-456')
                       ->send();
```

### Sending Routing Push Request

To all clients together and wait response:

```php
$request = $authRequest->to([
                                ['1'=>'client.one@yourfirm.com'],
                                ['1'=>'client.two@yourfirm.com'],
                                ['1'=>'client.three@yourfirm.com']
                                ])
                                ->response(false)
                                ->send();
```

All clients recieve push and request will be true only if all clients answering true.


To all clients by order:

```php
$request = $authRequest->to([
                                ['1'=>'client.one@yourfirm.com'],
                                ['2'=>'client.two@yourfirm.com'],
                                ['3'=>'client.three@yourfirm.com']
                                ])
                       ->send();

if ($authRequest->isAccept()) { //Make LogIn action }
if ($authRequest->isAccept() == false) { //Make Access Denied action }
if ($authRequest->isAccept() == Null) { //No answer from client }
```
                            
The first client receive push and the next client will receive push only if previous answer true. All request will be true, only if all clients answered true.


### Retrieve request status

At any time you can view request status:
```php
$request = $authRequest->to('client@yourfirm.com')
                       ->mode('push')
                       ->send();

//show request hash
print_r($request);
// will return Request Hash ex. 1232dwfef31x4xfcf34c2x4

//Show push request information
print_r($authRequest->requestStatus($request));
/*
will return array:
['answer'=>true,
'response_dt'=>'Time....',
'response_code'=>200,
'response_message'=>'Success answer received']
*/
```

### Show QR-code

Generate QR-code for client reading and auth:
```php
$qr_url = $authRequest->qrconfig([
            'margin'=>'5',
            'size'=>'256',
            'color'=>'121,0,121'
        ])->qr();

if ($authRequest->isAccept()) { //Make LogIn action }
if ($authRequest->isAccept() == false) { //Make Access Denied action }
if ($authRequest->isAccept() == Null) { //No answer from client }
```
                            
### Documentation

Please see: https://dashboard.pushauth.io/support/api

### Support

Please see: http://dashboard.pushauth.dev/support/request/create

