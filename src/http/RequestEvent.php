<?php

namespace SwooleFlierMouseBase\http;

use SwooleFlierMouseBase\Core;

class RequestEvent extends Request
{

	public function get ($key = FALSE)
	{
		return $key ? (isset($this->get[ $key ]) ? $this->get[ $key ] : NULL) : $this->get;
	}

	public function getPost ($key = FALSE)
	{
		return $key ? (isset($this->body[ $key ]) ? $this->body[ $key ] : NULL) : $this->body;
	}

	public function getRaw ()
	{
		return $this->raw_context;
	}

	public function getHeader ($key = FALSE)
	{
		return $key ? (isset($this->header[ $key ]) ? $this->header[ $key ] : NULL) : $this->header;
	}

	public function getCookie ($key = FALSE)
	{
		return $key ? (isset($this->cookie[ $key ]) ? $this->cookie[ $key ] : NULL) : $this->cookie;
	}

	public function getSessionId ()
	{
		return $this->session_id;
	}

	public function getMethod ()
	{
		return $this->method;
	}

	public function setGlobal ($server, $fd, $server_name)
	{
		$_GET     = $this->get ?: [];
		$_POST    = $this->body ?: [];
		$_FILES   = $this->files ?: [];
		$_COOKIE  = $this->cookie ?: [];
		$_REQUEST = @array_merge($_GET, $_POST, $_COOKIE);

		$server_info = $server->connection_info($fd);
		$request_uri = explode('?', $this->request_uri);
		$_SERVER     = [
			'REQUEST_METHOD'       => $this->method,
			'PATH_INFO'            => $request_uri[0]??'',
			'REMOTE_ADDR'          => $server_info['remote_ip'],
			'REMOTE_PORT'          => $server_info['remote_port'],
			'SERVER_PORT'          => $server_info['server_port'],
			'QUERY_STRING'         => $request_uri[1]??'',
			'REQUEST_URI'          => $this->request_uri,
			'SERVER_PROTOCOL'      => $this->http_protocol,
			'SERVER_SOFTWARE'      => sprintf('%s/%s %s.%s.%s', Core::SERVER_SOFT_NAME, Core::SERVER_SOFT_VERSION_NAME, Core::SERVER_SOFT_VERSION, Core::SERVER_SOFT_MINOR_VERSION, Core::SERVER_SOFT_BUILD_VERSION),
			'SERVER_NAME'          => $server_name,
			'HTTP_HOST'            => $this->getHeader('host') . ($server_info['server_port'] != 80 ? ':' . $server_info['server_port'] : NULL),
			'HTTP_USER_AGENT'      => $this->getHeader('user-agent'),
			'HTTP_ACCEPT'          => $this->getHeader('accept'),
			'HTTP_ACCEPT_LANGUAGE' => $this->getHeader('accept-encoding'),
			'HTTP_ACCEPT_ENCODING' => $this->getHeader('accept-language'),
			'HTTP_COOKIE'          => $this->getHeader('cookie'),
			'HTTP_CONNECTION'      => $this->getHeader('connection'),
			'REQUEST_TIME'         => time()
		];

		return $this;
	}
}