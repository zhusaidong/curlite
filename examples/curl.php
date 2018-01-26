<?php
/**
* CurlLite demo
* @author zhusaidong [zhusaidong@gmail.com]
*/
require_once './vendor/autoload.php';

use CurLite\Request,
	CurLite\Curl;

$request = new Request('https://github.com/search');
$request->postFields = ['q'=>'php curl','type'=>''];
$request->referer = 'https://github.com/';
$cl = new Curl($request);
$response = $cl->getResponse();
if($response->error === FALSE)
{
	echo $response->body;
}
else
{
	echo 'error:'.$response->error;
}
