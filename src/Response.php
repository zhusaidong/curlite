<?php
/**
 * Response
 *
 * @author zhusaidong [zhusaidong@gmail.com]
 */
namespace zhusaidong\CurLite;

class Response
{
	/**
	 * @var array $header HTTP-HEADER
	 */
	public $header = [];
	/**
	 * @var string $body response body
	 */
	public $body = '';
	/**
	 * @var string $location intercept location
	 */
	public $location = '';
	/**
	 * @var int $httpCode http code
	 */
	public $httpCode = 0;
	/**
	 * @var string $cookie cookie
	 */
	public $cookie = '';
	/**
	 * @var array $serverInfo SERVER-INFO
	 */
	public $serverInfo = [];
	/**
	 * @var bool|string $error error
	 *    if curl successed, the `response->error` will equal `FALSE`.
	 */
	public $error = FALSE;
	
	/**
	 * get server info
	 *
	 * @return Response
	 */
	public function getServerInfo()
	{
		preg_match_all('/(.*): (.*)\n/', implode("\n", $this->header) . "\n", $data);
		$data = array_combine(isset($data[1]) ? $data[1] : [], isset($data[2]) ? $data[2] : []);
		unset($data['Set-Cookie']);
		$this->serverInfo = $data;
		
		return $this;
	}
	
	/**
	 * get cookie
	 *
	 * @return Response
	 */
	public function getCookie()
	{
		preg_match_all('/Set-Cookie:(.*)\n/', implode("\n", $this->header) . "\n", $data);
		$this->cookie = trim(implode(';', isset($data[1]) ? $data[1] : []));
		
		return $this;
	}
	
	/**
	 * json decode the body
	 *    if the body is json
	 *
	 * @param bool $isArray is decoded to array, default is TRUE
	 *
	 * @return Response
	 */
	public function jsonBody($isArray = TRUE)
	{
		$this->body = json_decode($this->body, $isArray);
		
		return $this;
	}
}
