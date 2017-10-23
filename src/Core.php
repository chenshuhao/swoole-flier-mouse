<?php

	namespace SwooleFlierMouseBase;

	use SwooleFlierMouseBase\interfaces\Core as CoreI;


	class Core implements CoreI
	{
		static protected $instance = NULL;
		static protected $debug    = FALSE;

		static public function getInstance ()
		{
			if (NULL === self::$instance) {
				self::$instance = new static();
			}

			return self::$instance;
		}

		public function setConf ($conf_path)
		{
			Conf::read($conf_path);

			return $this;
		}

		public function checkSystem ()
		{
			if (PHP_VERSION < 7) {
				throw new SwooleFlierMouseBaseException('PHP version <= 7 ?');
			}

			if (count(get_extension_funcs('swoole')) < 1) {
				throw new SwooleFlierMouseBaseException('swoole extension 没有 ?');
			}

			return self::$instance;

		}

		public function debug ($off = TRUE)
		{
			self::$debug = $off;

			return $this;
		}

		public function initialize ()
		{

			$this->checkSystem();

			return $this;

		}

		public function run ()
		{

			return $this;
		}


	}