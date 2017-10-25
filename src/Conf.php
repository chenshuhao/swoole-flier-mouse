<?php

namespace SwooleFlierMouseBase;

use SwooleFlierMouseBase\http\Request;
use SwooleFlierMouseBase\interfaces\Conf as ConfI;
use Symfony\Component\Yaml\Yaml;

class Conf implements ConfI
{
	static public    $conf      = NULL;
	static protected $conf_path = NULL;
	static public    $temp      = NULL;
	static public    $root      = NULL;
	static protected $daemonize = FALSE;



	static public function get ($key = FALSE)
	{
		if (NULL === self::$conf) {
			self::read();
		}

		return self::$conf[ $key ];
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


		self::$conf_path = $conf_path;
		self::$conf      = Yaml::parse(file_get_contents(self::$conf_path));

		self::$root = realpath(__DIR__ . '/../');
		self::$temp = self::$root . DIRECTORY_SEPARATOR . self::get('temp');

		if (!is_dir(self::$temp)) {
			mkdir(self::$temp, 0777, TRUE);
		}
	}

	/**
	 * 创建临时文件
	 *
	 * @param $session_id
	 * @param $name
	 * @param $context
	 *
	 * @return bool|string
	 */
	static public function createTempFile ($session_id, $name, $context)
	{
		$dir = self::$temp . DIRECTORY_SEPARATOR . $session_id;
		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
		}
		$file_path_name = $dir . DIRECTORY_SEPARATOR . uniqid() . "-{$name}";
		if (file_put_contents($file_path_name, $context)) {
			return $file_path_name;
		}
		else {
			return FALSE;
		}
	}

	/**
	 * 删除目录
	 *
	 * @param $dir
	 *
	 * @return bool
	 */
	public static function deleteDir ($dir)
	{
		if (!is_dir($dir)) {
			return FALSE;
		}

		$dh = opendir($dir);
		while ($file = readdir($dh)) {
			if ($file != "." && $file != "..") {
				$full_path = $dir . "/" . $file;
				if (!is_dir($full_path)) {
					unlink($full_path);
				}
				else {
					self::delete_dir($full_path);
				}
			}
		}
		closedir($dh);
		if (rmdir($dir)) {
			return TRUE;
		}
		else {
			return FALSE;
		}
	}

	public static function setDaemonize ($daemonize = TRUE)
	{
		self::$daemonize = $daemonize;
	}

	public static function isDaemonize ()
	{
		return self::$daemonize;
	}

}