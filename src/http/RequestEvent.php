<?php

namespace SwooleFlierMouseBase\http;

class RequestEvent extends Request
{

	public function get ($key)
	{
		return isset($this->get[ $key ]) ? $this->get[ $key ] : NULL;
	}

	public function getPost ($key)
	{
		return isset($this->body[ $key ]) ? $this->body[ $key ] : NULL;
	}

	public function getRaw ()
	{
		return $this->raw_context;
	}

	public function getHeader ($key)
	{
		return isset($this->header[ $key ]) ? $this->header[ $key ] : NULL;
	}

	public function getCookie ($key)
	{
		return isset($this->cookie[ $key ]) ? $this->cookie[ $key ] : NULL;
	}

	public function getSessionId ()
	{
		return $this->session_id;
	}

	public function getMethod ()
	{
		return $this->method;
	}
}