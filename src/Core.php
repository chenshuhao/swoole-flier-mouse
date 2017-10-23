<?php

namespace SwooleFlierMouseBase;

use SwooleFlierMouseBase\command\command;
use SwooleFlierMouseBase\interfaces\Core as CoreI;
use SwooleFlierMouseBase\serv\Server;

class Core implements CoreI
{
	static protected $instance  = NULL;
	static protected $debug     = FALSE;
	static protected $conf_path = FALSE;

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
		command::cmd();

		return $this;

	}

	public function run ()
	{
		try {
			$this->initialize();
			Server::start();
		} catch (SwooleFlierMouseBaseException $e) {
			command::line($e->getMessage());
		}
	}

	public function tempDirIsExist ()
	{

	}


}