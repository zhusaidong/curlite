<?php
/**
* CurLite
* @author Zsdroid [635925926@qq.com]
* @version 0.1.1
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
	* is supported
	* 
	* @return boolean
	*/
	private function isSupported()
	{
		return function_exists("curl_init") ? TRUE : FALSE;
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
	* curl exec
	*/
	private function curlExec()
	{
		$curl = curl_init();
		
		//avoid variable pollution
		$request = clone $this->request;
		
		$this->request->isRandomIP == TRUE and $request->header['CLIENT-IP'] = $request->header['X-FORWARDED-FOR'] = $this->request->getRandomIP();
		$request->postFields = is_array($request->postFields) ? http_build_query($request->postFields) : $request->postFields;
		
		if($this->request->method === Request::METHOD_POST)
		{
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $request->postFields);
		}
		else
		{
			curl_setopt($curl, CURLOPT_HTTPGET, 1);
			$request->url .= (strpos('?',$request->url) !== FALSE ? '&' : '?').$request->postFields;
		}
		curl_setopt($curl, CURLOPT_URL,$request->url);
		if(!empty($request->caPath))
		{
			$caPathInfo = pathinfo($request->caPath);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, TRUE);
			curl_setopt($curl, CURLOPT_CAPATH, $caPathInfo['dirname']);
			curl_setopt($curl, CURLOPT_CAINFO, $caPathInfo['basename']);
		}
		else
		{
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		}
		
		curl_setopt($curl, CURLOPT_HTTPHEADER,$request->header);
		curl_setopt($curl, CURLOPT_USERAGENT, $request->userAgent);
		curl_setopt($curl, CURLOPT_REFERER, $request->referer);
		curl_setopt($curl, CURLOPT_COOKIE, $request->cookie);
		curl_setopt($curl, CURLOPT_TIMEOUT, $request->timeout);
		
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
			$this->response->body = $result;
			//if curl successed, the `response->error` will equal `FALSE`.
			$this->response->error = FALSE;
		}
		else
		{
			$this->response->error = curl_error($curl);
		}
		$this->response->httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		$this->response->getHeader()->getCookie();
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
	* __construct
	* @param string $requestUrl Request Url 
	* @param string $requestMethod Request Method
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
		$ipArr = [];
		$ipArr[] = mt_rand(60, 255);
		$ipArr[] = mt_rand(60, 255);
		$ipArr[] = mt_rand(60, 255);
		$ipArr[] = mt_rand(60, 255);
		$ip = implode('.',$ipArr);
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
	*/
	public $error = '';
	
	/**
	* parse header
	* 
	* @return Response
	*/
	public function getHeader()
	{
		$header = implode("\n",$this->header);
		preg_match_all('/(.*): (.*)\n/',$header,$data);
		$key = isset($data[1]) ? $data[1] : [];
		$value = isset($data[2]) ? $data[2] : [];
		$data = array_combine($key,$value);
		unset($data['Set-Cookie']);
		$this->serverInfo = $data;
		return $this;
	}
	/**
	* parse cookie
	* 
	* @return Response
	*/
	public function getCookie()
	{
		$header = implode("\n",$this->header)."\n";
		preg_match_all('/Set-Cookie:(.*)\n/',$header,$data);
		$cookie = isset($data[1]) ? $data[1] : [];
		$this->cookie = trim(implode(';',$data[1]));
		return $this;
	}
}
