<?php

namespace SwooleFlierMouseBase\serv;

use SwooleFlierMouseBase\Core;
use SwooleFlierMouseBase\command\Command;
use SwooleFlierMouseBase\http\RequestEvent;
use SwooleFlierMouseBase\http\Response;
use SwooleFlierMouseBase\RequestException;
use SwooleFlierMouseBase\abstracts\Serv;

class HttpServer extends Serv
{
	public function httpServ ($server, $fd, $reactor_id, $data)
	{
		try {
			$request  = (new RequestEvent())->unpack($data)->content()->setGlobal($server, $fd, $this->server_name);
			$response = new Response($request, $this);

			if ($this->server_id && isset(Core::$bind_exec[ $this->server_id ])) {
				$call             = Core::$bind_exec[ $this->server_id ];
				$response_context = $call($request, $response, $server->before_result);
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