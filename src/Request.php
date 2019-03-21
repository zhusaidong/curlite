<?php
/**
 * Request
 *
 * @author zhusaidong [zhusaidong@gmail.com]
 */
namespace zhusaidong\CurLite;

class Request
{
	/**
	 * @const int method-get
	 */
	const METHOD_GET = 1;
	/**
	 * @const int method-post
	 */
	const METHOD_POST = 2;
	/**
	 * @var string url URL
	 */
	public $url = '';
	/**
	 * @var int method method
	 */
	public $method = self::METHOD_GET;
	/**
	 * @var array $postFields post data
	 */
	public $postFields = [];
	/**
	 * @var array $header HTTP-HEADER
	 */
	public $header = [];
	/**
	 * @var $referer HTTP-REFERER
	 */
	public $referer = '';
	/**
	 * @var string $cookie COOKIE
	 */
	public $cookie = '';
	/**
	 * @var string $userAgent USER-AGENT
	 */
	public $userAgent = 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36';
	/**
	 * @var bool $isRandomIP randomIP
	 */
	public $isRandomIP = FALSE;
	/**
	 * @var string $caPath ca file path
	 */
	public $caPath = '';
	/**
	 * @var int $timeout timeout
	 */
	public $timeout = 3;
	/**
	 * @var string $proxy proxy addr,
	 *      like:http://0.0.0.0:000
	 */
	public $proxy = '';
	/**
	 * @var int $followLocation follow the location,
	 *      set it zero can intercept the redirect
	 */
	public $followLocation = 1;
	
	/**
	 * __construct
	 *
	 * @param string $requestUrl    Request Url
	 * @param int    $requestMethod Request Method
	 */
	public function __construct($requestUrl, $requestMethod = self::METHOD_GET)
	{
		$this->url    = $requestUrl;
		$this->method = $requestMethod;
	}
	
	/**
	 * get Random IP
	 */
	public function getRandomIP()
	{
		$ip = implode('.', array_map(function()
		{
			return mt_rand(60, 255);
		}, range(0, 3)));
		//validate ip
		while(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === FALSE)
		{
			$ip = $this->getRandomIP();
		}
		
		return $ip;
	}
}
