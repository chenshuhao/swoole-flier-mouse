<?php

namespace SwooleFlierMouseBase\serv;

use SwooleFlierMouseBase\Conf;

use SwooleFlierMouseBase\SwooleFlierMouseBaseException;

class Server extends HttpServer
{
	static public $instances = [];

	static public function start ()
	{
		$servers = Conf::get('servers');

		if (!is_array($servers) || count($servers) < 1) {
			throw new SwooleFlierMouseBaseException('Server not setting');
		}

		foreach ($servers as $server_id => $server_conf) {
			self::$instances [] = new self($server_conf, $server_id);
		}


		self::$serv->start();
	}


}