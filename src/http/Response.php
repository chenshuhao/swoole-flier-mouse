<?php

namespace SwooleFlierMouseBase\http;

use SwooleFlierMouseBase\Conf;
use SwooleFlierMouseBase\Core;

class Response
{

	private $http_protocol = 'HTTP/1.1';
	private $status_code   = '200';

	private $head   = [];
	private $body   = NULL;
	private $cookie = NULL;

	private $request;
	private $serv;

	private $HTTP_HEADERS
		= [
			100 => "100 Continue",
			101 => "101 Switching Protocols",
			200 => "200 OK",
			201 => "201 Created",
			204 => "204 No Content",
			206 => "206 Partial Content",
			300 => "300 Multiple Choices",
			301 => "301 Moved Permanently",
			302 => "302 Found",
			303 => "303 See Other",
			304 => "304 Not Modified",
			307 => "307 Temporary Redirect",
			400 => "400 Bad Request",
			401 => "401 Unauthorized",
			403 => "403 Forbidden",
			404 => "404 Not Found",
			405 => "405 Method Not Allowed",
			406 => "406 Not Acceptable",
			408 => "408 Request Timeout",
			410 => "410 Gone",
			413 => "413 Request Entity Too Large",
			414 => "414 Request URI Too Long",
			415 => "415 Unsupported Media Type",
			416 => "416 Requested Range Not Satisfiable",
			417 => "417 Expectation Failed",
			500 => "500 Internal Server Error",
			501 => "501 Method Not Implemented",
			503 => "503 Service Unavailable",
			506 => "506 Variant Also Negotiates"
		];

	/**
	 * 静态文件类型
	 *
	 * @var array
	 */
	static public $ASSET_TYPES
		= [
			'js'    => 'application/x-javascript',
			'css'   => 'text/css',
			'png'   => 'image/png',
			'jpg'   => 'image/jpeg',
			'jpeg'  => 'image/jpeg',
			'gif'   => 'image/gif',
			'json'  => 'application/json',
			'xml'   => 'application/xml',
			'svg'   => 'image/svg+xml',
			'woff'  => 'application/font-woff',
			'woff2' => 'application/font-woff2',
			'ttf'   => 'application/x-font-ttf',
			'eot'   => 'application/vnd.ms-fontobject',
			'html'  => 'text/html',
			'zip'   => 'application/x-zip-compressed',
		];

	function __construct (RequestEvent $request, $serv)
	{
		$this->request = $request;
		$this->serv    = $serv;
	}

	public function bodyIsNull ()
	{
		return $this->body === NULL;
	}

	/**
	 * 自定义tcp_http 使用
	 *
	 * @return string
	 */
	public function toString ()
	{
		$this->setHeader('Server', sprintf('%s/%s %s.%s.%s', Core::SERVER_SOFT_NAME, Core::SERVER_SOFT_VERSION_NAME, Core::SERVER_SOFT_VERSION, Core::SERVER_SOFT_MINOR_VERSION, Core::SERVER_SOFT_BUILD_VERSION));

		if (!isset($this->head['Cache-Control'])) {
			$this->setHeader('Cache-Control', 'no-cache');
		}
		if (!isset($this->head['Connection'])) {
			$this->setHeader('Connection', 'Keep-Alive');
		}

		if ($this->bodyIsNull() && in_array((int)$this->status_code, $this->HTTP_HEADERS) && (int)$this->status_code != 200) {
			$this->setBody($this->HTTP_HEADERS[ $this->status_code ]);
		}

		if (is_array($this->body)) {
			$this->setHeader('Content-Type', 'application/json');
			$this->body = json_encode($this->body, 256);
		}

		if (!isset($this->head['Content-Type'])) {
			$this->setContentType('html');
		}

		if (!isset($this->head['Content-Length'])) {
			$this->setHeader('Content-Length', strlen($this->body));
		}

		if (is_array($this->head)) {
			foreach ($this->head as $key => $val) {
				$head [] = "{$key}: {$val}";
			}
		}

		$head[] = 'Date: ' . date('r', time());

		if ($this->serv->session) {
			$this->setCookie('SFMBSESSION', $this->request->getSessionId());
		}

		$cookie = [];
		if (!empty($this->cookie) and is_array($this->cookie)) {
			foreach ($this->cookie as $v) {
				$cookie[] = "Set-Cookie: $v";
			}
		}

		$head = join("\r\n", array_merge($head, $cookie));

		$responseBody
			= <<<CONTEXT
$this->http_protocol {$this->HTTP_HEADERS[$this->status_code]}\r\n$head\r\n\r\n$this->body
CONTEXT;

		return $responseBody;

	}

	public function setHeader ($name, $value)
	{
		$this->head[ $name ] = $value;

		return $this;
	}

	public function setBody ($raw_body)
	{
		$this->body = $raw_body;

		return $this;
	}

	public function setStatusCode ($code)
	{
		$this->status_code = $code;

		return $this;
	}

	public function setContentType ($content_type)
	{

		$this->setHeader('Content-Type', isset(self::$ASSET_TYPES[ $content_type ]) ? self::$ASSET_TYPES[ $content_type ] : $content_type);

		return $this;
	}

	public function with ($string)
	{
		$this->withHtml($string);
	}

	public function withHtml ($html)
	{
		$this->setHeader('Content-Type', self::$ASSET_TYPES['html']);
		$this->body = $html;

		$this->httpResponseEnd();
	}


	public function withJson ($data)
	{
		if (isset($data['error_code'])) {
			$data['error_code'] = (int)$data['error_code'];
		}
		else {
			$data['error_code'] = 0;
		}

		$this->body = json_encode($data);
		$this->setHeader('Content-Type', 'application/json');

		$this->httpResponseEnd();
	}

	public function crossAllow ($access_control_allow_origin = '*', $access_control_allow_headers = 'content-type , Content-Type')
	{
		$this->setHeader('Access-Control-Allow-Origin', $access_control_allow_origin);
		$this->setHeader('Access-Control-Allow-Headers', $access_control_allow_headers);

		return $this;
	}

	function noCache ()
	{
		$this->head['Cache-Control'] = 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0';
		$this->head['Pragma']        = 'no-cache';

		return $this;
	}


	/**
	 * cookie
	 *
	 * @param         $name
	 * @param null    $value
	 * @param null    $expire
	 * @param string  $path
	 * @param null    $domain
	 * @param null    $secure
	 * @param boolean $http_only
	 *
	 * @return object
	 */
	function setCookie ($name, $value = NULL, $expire = NULL, $path = '/', $domain = NULL, $secure = NULL, $http_only = FALSE)
	{
		$cookie[] = "{$name}={$value}";
		if ($expire) $cookie[] = "expires=Tue, " . date("D, d-M-Y H:i:s T", $expire) . "";
		if ($path) $cookie[] = "path={$path}";
		if ($domain) $cookie[] = "domain={$domain}";
		if ($http_only) $cookie[] = " httponly";

		$this->cookie[] = join(';', $cookie);

		return $this;
	}


	function download ($file_type, $file_name, $file_path)
	{
		return $this->noCache()
			->setContentType($file_type)
			->setHeader('Expires', 0)
			->setHeader('Content-Disposition', 'attachment; filename*=UTF-8\'\'' . rawurlencode($file_name))
			->setBody(file_get_contents($file_path));
	}

	public function destroy ()
	{
		$this->head    = [];
		$this->body    = NULL;
		$this->cookie  = NULL;
		$this->request = NULL;
		$this->serv    = NULL;
	}

	function send ($server, $fd)
	{
		$server->send($fd, $this->toString());
		$connection = $this->request->getHeader('connection');
		if (!$connection || !in_array($connection, ['keep-alive', 'keepalive', 'KEEP-ALIVE', 'KEEPALIVE'])) {
			$server->close($fd);
		}

		Conf::deleteDir(Conf::$temp . DIRECTORY_SEPARATOR . $this->request->getSessionId());
		$this->request->destroy();
		$this->destroy();
	}
}

