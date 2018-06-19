<?php
/**
* CurLite Test
*
* @author Zsdroid [635925926@qq.com]
*/
use zhusaidong\CurLite\Request,
	zhusaidong\CurLite\Curl;
use PHPUnit\Framework\TestCase;

class CurLiteTest extends TestCase
{
	public function testCurlDone()
	{
		$request = new Request('https://github.com/search');
		$request->postFields = ['q'=>'php curl','type'=>''];
		$request->referer = 'https://github.com/';

		$cl       = new Curl($request);
		$response = $cl->getResponse();

		$this->assertEquals(FALSE, $response->error);
	}
}
