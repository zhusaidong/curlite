# CurLite

## About

### a Light-weight php curl

## Usage

```php
composer require zhusaidong/curlite:dev-master
```

## Examples

```php
require_once './vendor/autoload.php';

use CurLite\Request,
	CurLite\Curl;

$request = new Request('https://github.com/search');
$request->postFields = ['q'=>'php curl','type'=>''];
$request->referer = 'https://github.com/';
$cl = new Curl($request);
$response = $cl->getResponse();
//if curl successed, the `$response->error` will equal `FALSE`.
if($response->error === FALSE)
{
	echo $response->body;
}
else
{
	echo 'error:'.$response->error;
}
```

## Available Properties

### Response Properties

```php
/**
* @var array $header response header
*/
$header = [];
/**
* @var string $body response body
*/
$body = '';
/**
* @var int $httpCode http code
*/
$httpCode = '';
/**
* @var string $cookie cookie
*/
$cookie = '';
/**
* @var array $serverInfo server info
*/
$serverInfo = [];
/**
* @var string|booean $error error msg, if curl successed, the $error is FALSE
*/
$error = '';
```

### Request Properties

```php
/**
* @const method get
*/
const METHOD_GET = 1;
/**
* @const method post
*/
const METHOD_POST = 2;

/**
* @var string $url request url
*/
$url = '';
/**
* @var int $method request method, default is GET
*/
$method = self::METHOD_GET;
/**
* @var array $postFields request post data
*/
$postFields = [];
/**
* @var array $header request header
*/
$header = [];
/**
* @var string $referer request referer
*/
$referer = '';
/**
* @var string $cookie cookie
*/
$cookie = '';
/**
* @var string $userAgent user-agent
*/
$userAgent = '';
/**
* @var boolean $isRandomIP is curl use random IP, default is FALSE
*/
$isRandomIP = FALSE;
/**
* @var string $caPath cafile path
*/
$caPath = '';
/**
* @var int $timeout request timeout
*/
$timeout = 3;
```
