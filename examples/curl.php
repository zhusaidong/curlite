<?php
/**
 * CurlLite demo
 *
 * @author zhusaidong [zhusaidong@gmail.com]
 */
require('./../vendor/autoload.php');

use zhusaidong\CurLite\Request;
use zhusaidong\CurLite\Curl;

$request             = new Request('https://github.com/search');
$request->postFields = ['q' => 'php curl', 'type' => ''];
$request->referer    = 'https://github.com/';
$cl                  = new Curl($request);
$response            = $cl->getResponse();
if($response->error === FALSE)
{
	echo $response->body;
}
else
{
	echo 'error:' . $response->error;
}
