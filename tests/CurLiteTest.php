<?php
/**
 * CurLite Test
 *
 * @author Zsdroid [635925926@qq.com]
 */

use zhusaidong\CurLite\Request, zhusaidong\CurLite\Curl;
use PHPUnit\Framework\TestCase;

class CurLiteTest extends TestCase
{
	public function testCurlDone()
	{
		$request             = new Request('https://github.com/search');
		$request->postFields = ['q' => 'php curl', 'type' => ''];
		$request->referer    = 'https://github.com/';
		
		$cl       = new Curl($request);
		$response = $cl->getResponse();
		
		$this->assertEquals(FALSE, $response->error);
	}
	
	public function testCurlBody()
	{
		$request             = new Request('http://127.0.0.1/curlite/examples/test_body.php', Request::METHOD_POST);
		$request->postFields = ['test' => 'test curl body'];
		$request->referer    = 'https://github.com/';
		
		$cl       = new Curl($request);
		$response = $cl->getResponse();
		
		$this->assertEquals('test curl body', $response->body);
	}
	
	public function testCurlIntercept()
	{
		$request                 = new Request('http://127.0.0.1/curlite/examples/test_intercept.php');
		$request->referer        = 'https://github.com/';
		$request->followLocation = 0;
		
		$cl       = new Curl($request);
		$response = $cl->getResponse();
		
		$this->assertEquals('https://www.github.com', $response->location);
	}
}
