# CurLite

---

## About
### a Light-weight php curl

## Examples

```php
require_once('./CurLite.php');
$request = new \CurLite\Request('http://www.baidu.com/s');
$request->postFields = ['wd'=>'php curl'];
$request->referer = 'http://www.baidu.com/';
$cl = new \CurLite\Curl($request);
$response = $cl->getResponse();
echo $response->body;
```
