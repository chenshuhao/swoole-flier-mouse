<?php

namespace SwooleFlierMouseBase\serv;

use SwooleFlierMouseBase\abstracts\Serv;
use SwooleFlierMouseBase\Conf;
use SwooleFlierMouseBase\SwooleFlierMouseBaseException;

class Server extends Serv
{
	static public $instances = [];

	static public function start ()
	{
		$servers = Conf::get('servers');

		if (!is_array($servers) || count($servers) < 1) {
			throw new SwooleFlierMouseBaseException('Server not setting');
		}

		foreach ($servers as $key => $val) {
			$instances [] = new self($val);
		}


		self::$serv->start();
	}
}