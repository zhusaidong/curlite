# CurLite

---

## About
### a Light-weight php curl

## Usage

```
composer require zhusaidong/curlite:dev-master
```

## Examples

```php
require_once './vendor/autoload.php';

use CurLite\Request,
	CurLite\Curl;

$request = new Request('http://www.baidu.com/s');
$request->postFields = ['wd'=>'php curl'];
$request->referer = 'http://www.baidu.com/';
$cl = new Curl($request);
$response = $cl->getResponse();
echo $response->body;
```
