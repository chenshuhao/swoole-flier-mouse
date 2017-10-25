<?php

namespace SwooleFlierMouseBase\serv;

use SwooleFlierMouseBase\abstracts\Serv;
use SwooleFlierMouseBase\command\Command;
use SwooleFlierMouseBase\Conf;
use SwooleFlierMouseBase\Core;
use SwooleFlierMouseBase\http\RequestEvent;
use SwooleFlierMouseBase\http\Response;
use SwooleFlierMouseBase\RequestException;
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

		foreach ($servers as $server_id => $server_conf) {
			self::$instances [] = new self($server_conf, $server_id);
		}


		self::$serv->start();
	}

	public function httpServ ($server, $fd, $reactor_id, $data)
	{
		try {
			$request  = (new RequestEvent())->unpack($data)->content()->setGlobal($server, $fd);
			$response = new Response($request, $this);

			if ($this->server_id && isset(Core::$bind_exec[ $this->server_id ])) {
				$call             = Core::$bind_exec[ $this->server_id ];
				$response_context = $call($request, $response);
				if ($response->bodyIsNull()) {
					$response->setBody($response_context);
				}
			}
			else {
				$response->setStatusCode('404');
				$response->setBody('<h1><center>This is the default page! <br />You do not configure the current address frame!</center></h1>');
			}

			$response->send($server, $fd);
		} catch (RequestException $e) {
			$server->send($fd, "HTTP/1.1 400 Bad Request\r\n\r\n");
			$server->colose($fd);
			Command::line($e->getMessage());
		}

	}
}