<?php

	namespace SwooleFlierMouseBase;

	use SwooleFlierMouseBase\interfaces\Conf as ConfI;
	use Symfony\Component\Yaml\Yaml;

	class Conf implements ConfI
	{
		static protected $conf      = NULL;
		static protected $conf_path = NULL;

		static public function get ($key = FALSE)
		{
			if (NULL === self::$conf) {
				self::read();
			}

			return self::$conf['key'];
		}

		static public function read ($conf_path = FALSE)
		{
			if (FALSE === $conf_path && NULL === self::$conf_path) {
				throw new SwooleFlierMouseBaseException('conf file not set!');
			}

			if ($conf_path) {
				if (!file_exists($conf_path)) {
					throw new SwooleFlierMouseBaseException("conf file {$conf_path} not exists!");
				}
			}

			self::$conf = Yaml::parse(file_get_contents($conf_path));
		}

	}