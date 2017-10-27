<?php

namespace SwooleFlierMouseBase;

use SwooleFlierMouseBase\command\Command;
use SwooleFlierMouseBase\interfaces\Core as CoreI;
use SwooleFlierMouseBase\serv\Server;

class Core implements CoreI
{
	const SERVER_SOFT_NAME          = 'flier_mouse';
	const SERVER_SOFT_VERSION       = '1';//主版本号：当你做了不兼容的 API 修改，
	const SERVER_SOFT_VERSION_NAME  = 'alpha';//当前版本名称
	const SERVER_SOFT_MINOR_VERSION = '2';//次版本号：当你做了向下兼容的功能性新增，
	const SERVER_SOFT_BUILD_VERSION = '20';//修订号：当你做了向下兼容的问题修正。

	static protected $instance         = NULL;
	static protected $debug            = FALSE;
	static protected $conf_path        = FALSE;
	static public    $bind_exec        = [];
	static public    $bind_exec_before = [];

	static public function getInstance ()
	{
		if (NULL === self::$instance) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	public function setConf ($conf_path)
	{
		self::$conf_path = $conf_path;

		return $this;
	}

	protected function checkSystem ()
	{
		if (php_sapi_name() != "cli") {
			throw new SwooleFlierMouseBaseException('START ERROR: only run in command line mode');
		}

		if (PHP_VERSION < 7) {
			throw new SwooleFlierMouseBaseException('START ERROR: PHP version <= 7 ??');
		}

		if (!get_extension_funcs('swoole')) {
			throw new SwooleFlierMouseBaseException('START ERROR: swoole extension ??');
		}

		return $this;

	}

	public function debug ($off = TRUE)
	{
		self::$debug = $off;

		return $this;
	}

	protected function initialize ()
	{
		$this->checkSystem();
		Conf::read(self::$conf_path);
		Command::cmd();

		return $this;

	}

	public function run ()
	{
		try {
			$this->initialize();
			Server::start();
		} catch (SwooleFlierMouseBaseException $e) {
			Command::line($e->getMessage());
		}
	}

	public function bindServerHandler ($server_index, callable $callback, $before = FALSE)
	{
		self::$bind_exec [ $server_index ] = $callback;

		if (FALSE !== $before) self::$bind_exec_before [ $server_index ] = $before;

		return $this;
	}


}