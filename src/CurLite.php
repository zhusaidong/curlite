<?php
/**
* CurLite
* @author zhusaidong [zhusaidong@gmail.com]
* @version 0.1.2
*/
namespace CurLite;

class Curl
{
	/**
	* @var $request Request
	*/
	private $request = null;
	/**
	* @var $response Response
	*/
	private $response = null;
	
	/**
	* __construct
	* 
	* @param Request $request
	*/
	public function __construct(Request $request)
	{
		$this->request = $request;
		$this->response = new Response;
		!$this->isSupported() and exit('your server not supported curl.');
		$this->curlExec();
	}
	/**
	* get response object
	* 
	* @return Response response
	*/
	public function getResponse()
	{
		return $this->response;
	}
	
	/**
	* is supported
	* 
	* @return boolean
	*/
	private function isSupported()
	{
		return function_exists("curl_init");
	}
	/**
	* curl exec
	*/
	private function curlExec()
	{
		$curl = curl_init();
		
		//avoid variable pollution
		$request = clone $this->request;
		
		//randomIP
		$request->isRandomIP and 
			$request->header['X-REAL-IP'] = 
			$request->header['CLIENT-IP'] = 
			$request->header['X-FORWARDED-FOR'] = 
			$request->getRandomIP();
		array_walk($request->header,function(&$v,$k){$v = $k.':'.$v;});
		
		$request->postFields = is_array($request->postFields) ? http_build_query($request->postFields) : $request->postFields;
		
		if($request->method === Request::METHOD_POST)
		{
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $request->postFields);
		}
		else
		{
			curl_setopt($curl, CURLOPT_HTTPGET, 1);
			$request->url .= (strpos('?',$request->url) !== FALSE ? '&' : '?').$request->postFields;
		}
		curl_setopt($curl, CURLOPT_URL, $request->url);
		
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, !empty($request->caPath));
		if(!empty($request->caPath))
		{
			$caPathInfo = pathinfo($request->caPath);
			curl_setopt($curl, CURLOPT_CAPATH, $caPathInfo['dirname']);
			curl_setopt($curl, CURLOPT_CAINFO, $caPathInfo['basename']);
		}
		
		curl_setopt($curl, CURLOPT_PROXY, 		$request->proxy);
		curl_setopt($curl, CURLOPT_HTTPHEADER, 	$request->header);
		curl_setopt($curl, CURLOPT_USERAGENT, 	$request->userAgent);
		curl_setopt($curl, CURLOPT_REFERER, 	$request->referer);
		curl_setopt($curl, CURLOPT_COOKIE, 		$request->cookie);
		curl_setopt($curl, CURLOPT_TIMEOUT, 	$request->timeout);
		
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_DEFAULT);
		
		//get response header
		$that = $this;
		curl_setopt($curl,CURLOPT_HEADERFUNCTION,
			function($ch,$header) use($that)
			{
				$header_trim = trim($header);
				!empty($header_trim) and $that->response->header[] = $header_trim;
				return strlen($header);
			});
		
		$result = curl_exec($curl);
		if($result !== FALSE)
		{
			$this->response->body = trim($result);
		}
		else
		{
			$this->response->error = curl_error($curl);
		}
		$this->response->httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
	}
}

/**
* Request
*/
class Request
{
	/**
	* @const method-get
	*/
	const METHOD_GET = 1;
	/**
	* @const method-post
	*/
	const METHOD_POST = 2;
	
	/**
	* @var url URL
	*/
	public $url = '';
	/**
	* @var method method
	*/
	public $method = self::METHOD_GET;
	/**
	* @var $postFields post data
	*/
	public $postFields = '';
	/**
	* @var $header HTTP-HEADER
	*/
	public $header = [];
	/**
	* @var $referer HTTP-REFERER
	*/
	public $referer = '';
	/**
	* @var $cookie COOKIE
	*/
	public $cookie = '';
	/**
	* @var $userAgent USER-AGENT
	*/
	public $userAgent = 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36';
	/**
	* @var $isRandomIP randomIP
	*/
	public $isRandomIP = FALSE;
	/**
	* @var $caPath ca file path
	*/
	public $caPath = '';
	/**
	* @var $timeout timeout
	*/
	public $timeout = 3;
	/**
	* @var $proxy proxy like:http://0.0.0.0:000
	*/
	public $proxy = '';
	
	/**
	* __construct
	* 
	* @param string $requestUrl Request Url 
	* @param int $requestMethod Request Method
	*/
	public function __construct($requestUrl,$requestMethod = self::METHOD_GET)
	{
		$this->url = $requestUrl;
		$this->method = $requestMethod;
	}
	/**
	* get Random IP
	*/
	public function getRandomIP()
	{
		$ip = implode('.',array_map(function(){return mt_rand(60, 255);},range(0,3)));
		//validate ip
		while(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === FALSE)
		{
			$ip = $this->getRandomIP();
		}
		return $ip;
	}
}

/**
* Response
*/
class Response
{
	/**
	* @var $header HTTP-HEADER
	*/
	public $header = [];
	/**
	* @var $body response body
	*/
	public $body = '';
	/**
	* @var $httpCode HTTPCODE
	*/
	public $httpCode = '';
	/**
	* @var $cookie COOKIE
	*/
	public $cookie = '';
	/**
	* @var $serverInfo SERVER-INFO
	*/
	public $serverInfo = [];
	/**
	* @var $error error
	* 	if curl successed, the `response->error` will equal `FALSE`.
	*/
	public $error = FALSE;
	
	/**
	* get server info
	* 
	* @return Response
	*/
	public function getServerInfo()
	{
		preg_match_all('/(.*): (.*)\n/',implode("\n",$this->header)."\n",$data);
		$data = array_combine(isset($data[1]) ? $data[1] : [],isset($data[2]) ? $data[2] : []);
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
		preg_match_all('/Set-Cookie:(.*)\n/',implode("\n",$this->header)."\n",$data);
		$this->cookie = trim(implode(';',isset($data[1]) ? $data[1] : []));
		return $this;
	}
	/**
	* json decode the body
	* 	if the body is json
	* 
	* @param boolean $isArray is decoded to array, default is TRUE
	* 
	* @return Response
	*/
	public function jsonBody($isArray = TRUE)
	{
		$this->body = json_decode($this->body,$isArray);
		return $this;
	}
}
