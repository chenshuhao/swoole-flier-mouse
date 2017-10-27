<?php

namespace SwooleFlierMouseBase\http;

use SwooleFlierMouseBase\Conf;
use SwooleFlierMouseBase\RequestException;

abstract class Request
{
	protected $method;
	protected $request_uri;
	protected $http_protocol;
	protected $raw_context;
	protected $header;
	protected $body;
	protected $get;
	protected $files;
	protected $cookie;

	protected $data;
	protected $session_id;

	public function checkPack ($recv_buffer)
	{
		if (!strpos($recv_buffer, "\r\n\r\n")) {
			throw new RequestException('recv_buffer error');
		}

		return $this;
	}


	public function unpack ($recv_buffer)
	{
		$this->checkPack($recv_buffer);

		$this->data = explode("\r\n\r\n", $recv_buffer);
		$header     = explode("\r\n", $this->data[0]);
		unset($this->data[0]);

		$this->raw_context = join("\r\n\r\n", $this->data);
		foreach ($header as $key => $val) {
			if ($key === 0) {
				@list($this->method, $this->request_uri, $this->http_protocol) = explode(" ", $val, 3);
			}
			else {
				$header_row = explode(":", $val);
				if (isset($header_row[1])) $this->header[ strtolower($header_row[0]) ] = trim($header_row[1], ' ');
			}
		}

		if (@$this->header['content-type']) {
			$content_type                 = explode(';', $this->header['content-type']);
			$this->header['content-type'] = $content_type[0];
			if (isset($content_type[1])) $content_type_params = @explode('=', $content_type[1]);
			if (isset($content_type_params[1])) $this->header['content-type-params'][ trim($content_type_params[0]) ] = $content_type_params[1];
		}

		if (!$this->method || !$this->http_protocol) {
			throw new RequestException('http request error!');
		}

		return $this;
	}


	/**
	 *  格式化payload
	 */
	public function content ()
	{
		$contentType = @$this->header['content-type'];

		$this->deCookie();
		$this->deUrls();

		switch (strtolower($contentType)) {
			case "application/x-www-form-urlencoded":
				parse_str($this->raw_context, $output);
				$this->body = $output ?: [];
				break;
			case "multipart/form-data":
				$this->multipartFromData();
				break;
			default:

				break;
		}


		return $this;
	}

	/**
	 * 解析cookie
	 *
	 * @return bool
	 */
	public function deCookie ()
	{
		if (isset($this->header['cookie'])) {
			$cookies = explode(';', $this->header['cookie']);
			foreach ($cookies as $val) {
				$_tmp                           = explode('=', $val);
				$this->cookie[ trim($_tmp[0]) ] = $_tmp[1];
			}
		}

		if (isset($this->cookie['SFMBSESSION'])) {
			$this->session_id = $this->cookie['SFMBSESSION'];
		}
		else {
			$this->session_id = md5(uniqid());
		}
	}


	/**
	 * 解析url 参数
	 */
	public function deUrls ()
	{
		$url_params = @explode('?', $this->request_uri)[1];
		if (strstr($url_params, '#')) {
			$url_params = explode('#', $url_params)[0];
		}
		parse_str($url_params, $this->get);
	}


	/**
	 * 文件上传 或者 form-data
	 */
	private function multipartFromData ()
	{
		$body = explode('--' . $this->header['content-type-params']['boundary'], $this->raw_context);
		unset($body[0], $body[ count($body) ]);

		foreach ($body as $value) {
			$_     = explode("\r\n\r\n", $value);
			$_info = explode("\r\n", $_[0]);

			$_tmp = [];

			$_tmp_arr = explode(';', $_info[1]);

			foreach ($_tmp_arr as $k => $v) {
				if (0 === $k) {
					$__tmp                   = explode(':', $v);
					$_tmp[ trim($__tmp[0]) ] = trim($__tmp[1]);
				}
				else {
					$__tmp                   = explode('=', $v);
					$_tmp[ trim($__tmp[0]) ] = trim($__tmp[1], '"');
				}
			}
			if (isset($_tmp['filename'])) {
				$file_path     = Conf::createTempFile($this->session_id, 'haoge.tmpfile.' . uniqid(), $_[ count($_) - 1 ]);
				$this->files[] = [
					'size'  => filesize($file_path),
					'name'  => $_tmp['filename'],
					'tmp'   => $file_path,
					//					'tmp_name' => $file_path,
					'error' => !(bool)$file_path,
					'type'  => trim(explode(':', $_info[2])[1])
				];
			}
			else {
				$this->body[ $_tmp['name'] ] = trim($_[ count($_) - 1 ]);
			}

		}
	}

	public function destroy ()
	{
		$this->method        = NULL;
		$this->request_uri   = NULL;
		$this->http_protocol = NULL;
		$this->raw_context   = NULL;
		$this->header        = NULL;
		$this->body          = NULL;
		$this->get           = NULL;
		$this->files         = NULL;
		$this->cookie        = NULL;
		$this->data          = NULL;
		$this->session_id    = NULL;
		$_GET                = NULL;
		$_POST               = NULL;
		$_FILES              = NULL;
		$_COOKIE             = NULL;
		$_REQUEST            = NULL;
		$_SERVER             = NULL;
	}


}
