# JazzCash Rest API PHP

#### Pre-Requisites
- PHP 7.x
- Curl Extension / Enabled

#### Usage
Add these constants to your file
> Most of these constants can be retrieved from JazzCash Merchant Portal
```php
// Merchant ID
define('JC_MERCHANT_ID', "");
// Password (auto generated)
define('JC_PASS', "");
// Integrity Salt (auto generated)
define('JC_SALT', "");
// Change only if URLs are different
define('JC_LIVE_URL', 'https://production.jazzcash.com.pk');
define('JC_SANDBOX_URL', 'https://sandbox.jazzcash.com.pk');
```
### 1. Authorize Request

```php
// include file
require 'jazzcash/jazzcash.php';

// initialize jazzcash rest api library
$jc = new Jazzcash;

// request type 
$jc->reqType = 'Authorize';
// request data
$jc->set_data([
    "pp_Amount" => "", // Amount 
    "pp_InstrToken" => "", // If saved card/or details otherwise leave empty
    "pp_InstrumentType" => "CARD", // card
    "pp_TxnDateTime"    => date("YmdHisu"), // transaction date time
    "pp_TxnRefNo" => "T".date('YmdHisu'), // transaction reference no
    "pp_Frequency"          => "SINGLE",
    "InstrumentDTO" => [
        "pp_CustomerCardNumber" => "", 
        "pp_CustomerCardExpiry" => "",
        "pp_CustomerCardCvv"    => ""
    ],
]);
$jc->send(); // json response
```

### 2. Capture Request
```php
// include file
require 'jazzcash/jazzcash.php';

// initialize jazzcash rest api library
$jc = new Jazzcash;

// request type 
$jc->reqType = 'Capture';
$jc->set_data([
    'pp_TxnRefNo' => '', // transaction number generated from authorized request
    'pp_Amount'   => '', // amount sent from authorized request
]);
$jc->send(); // json response
```

### Refund Request
```php
// include file
require 'jazzcash/jazzcash.php';

// initialize jazzcash rest api library
$jc = new Jazzcash;

// request type 
$jc->reqType = 'Refund';
$jc->set_data([
    'pp_TxnRefNo' => '', // transaction number generated from authorized request
    'pp_Amount'   => '', // amount sent from authorized request
]);
$jc->send(); // json response
```

### Payment Inquiry
```php
// include file
require 'jazzcash/jazzcash.php';

// initialize jazzcash rest api library
$jc = new Jazzcash;

// request type 
$jc->reqType = 'PaymentInquiry';
$jc->set_data([
    'pp_TxnRefNo' => '', // transaction reference number generated from authorized request
]);
```

### Void
```php
// include file
require 'jazzcash/jazzcash.php';

// initialize jazzcash rest api library
$jc = new Jazzcash;

// request type 
$jc->reqType = 'Void';
$jc->set_data([
    'pp_TxnRefNo' => '' // transaction reference number generated from authorized request
]);
```

> for any bugs or inquiry feel free to report issue in repository or mail bugs@rafayhingoro.me


