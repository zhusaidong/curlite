<?php
/**
 * CurLite
 *
 * @author zhusaidong [zhusaidong@gmail.com]
 */
namespace zhusaidong\CurLite;

class Curl
{
	/**
	 * @var Request $request request
	 */
	private $request = NULL;
	/**
	 * @var Response $response response
	 */
	private $response = NULL;
	
	/**
	 * __construct
	 *
	 * @param Request $request
	 */
	public function __construct(Request $request)
	{
		$this->request  = $request;
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
	 * @return bool
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
		$request->isRandomIP and $request->header['X-REAL-IP'] = $request->header['CLIENT-IP'] = $request->header['X-FORWARDED-FOR'] = $request->getRandomIP();
		array_walk($request->header, function(&$v, $k)
		{
			$v = $k . ':' . $v;
		});
		
		$request->postFields = is_array($request->postFields) ? http_build_query($request->postFields) : $request->postFields;
		
		if($request->method === Request::METHOD_POST)
		{
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $request->postFields);
		}
		else
		{
			curl_setopt($curl, CURLOPT_HTTPGET, 1);
			!empty($request->postFields) and $request->url .= (strpos($request->url,'?') !== FALSE ? '&' : '?').$request->postFields;
		}
		curl_setopt($curl, CURLOPT_URL, $request->url);
		
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, !empty($request->caPath));
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, !empty($request->caPath));
		if(!empty($request->caPath))
		{
			$caPathInfo = pathinfo($request->caPath);
			curl_setopt($curl, CURLOPT_CAPATH, $caPathInfo['dirname']);
			curl_setopt($curl, CURLOPT_CAINFO, $caPathInfo['basename']);
		}
		
		curl_setopt($curl, CURLOPT_PROXY, $request->proxy);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $request->header);
		curl_setopt($curl, CURLOPT_USERAGENT, $request->userAgent);
		curl_setopt($curl, CURLOPT_REFERER, $request->referer);
		curl_setopt($curl, CURLOPT_COOKIE, $request->cookie);
		curl_setopt($curl, CURLOPT_TIMEOUT, $request->timeout);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, $request->followLocation);
		
		if($request->followLocation === 0)
		{
			curl_setopt($curl, CURLOPT_NOBODY, 1);
			curl_setopt($curl, CURLOPT_HEADER, 1);
		}
		
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_DEFAULT);
		
		//get response header
		$that = $this;
		curl_setopt($curl, CURLOPT_HEADERFUNCTION, function($ch, $header) use ($that)
		{
			$header_trim = trim($header);
			!empty($header_trim) and $that->response->header[] = $header_trim;
			
			return strlen($header);
		});
		
		$result = curl_exec($curl);
		
		$this->response->httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		
		if($result !== FALSE)
		{
			if($request->followLocation === 0)
			{
				preg_match('/Location: (.*)'.PHP_EOL.'/', $result, $match);
				$this->response->location = isset($match[1]) ? trim($match[1]) : NULL;
			}
			else
			{
				$this->response->body = trim($result);
			}
		}
		else
		{
			$this->response->error = curl_error($curl);
		}
		
		curl_close($curl);
	}
}
