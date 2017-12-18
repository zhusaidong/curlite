# CurLite

---

## About
### a Light-weight php curl

## Examples

```php
require_once('../src/CurLite.php');

use CurLite\Request,
	CurLite\Curl;

$request = new Request('http://www.baidu.com/s');
$request->postFields = ['wd'=>'php curl'];
$request->referer = 'http://www.baidu.com/';
$cl = new Curl($request);
$response = $cl->getResponse();
echo $response->body;
```
