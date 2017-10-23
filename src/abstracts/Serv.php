<?php

namespace SwooleFlierMouseBase\abstracts;

use SwooleFlierMouseBase\interfaces\Serv as ServI;
use SwooleFlierMouseBase\SwooleFlierMouseBaseException;

abstract class Serv implements ServI
{
	public static $serv         = NULL;
	public        $server_type  = NULL;
	public        $host         = NULL;
	public        $domain       = NULL;
	public        $conf         = NULL;
	public        $serv_conf    = NULL;
	public        $port         = NULL;
	public        $ssl          = FALSE;
	public        $ssl_cert_pem = NULL;
	public        $ssl_cert_key = NULL;
	public        $sock_type    = SWOOLE_SOCK_TCP;
	public        $mode         = SWOOLE_PROCESS;

	public function __construct (array $conf)
	{
		$this->conf = $conf;

		$this->host      = $conf['host'];
		$this->domain    = $conf['domain']??NULL;
		$this->port      = $conf['port'];
		$this->serv_conf = $conf['serv_conf']??[];
		$this->ssl       = $conf['ssl']??FALSE;
		$this->mode      = $conf['mode']??SWOOLE_PROCESS;
		if ($this->ssl) {
			if (!isset($conf['ssl_cert_pem']) || !isset($conf['ssl_cert_key'])) {
				throw new SwooleFlierMouseBaseException('You open the SSL not set secret key and certificate');
			}
			else {
				$this->ssl_cert_pem = $conf['ssl_cert_pem'];
				$this->ssl_cert_key = $conf['ssl_cert_key'];
				$this->sock_type    = $conf['sock_type']?:(SWOOLE_SOCK_TCP | SWOOLE_SSL);
			}
		}


		if (NULL == self::$serv) {
			self::$serv = new \swoole_server($this->host, $this->port, $this->mode, $this->sock_type);
			self::$serv->on('Packet', [$this, 'onPacket']);
			self::$serv->on('Start', [$this, 'onStart']);
			self::$serv->on('WorkerStart', [$this, 'onWorkerStart']);
			self::$serv->on('WorkerStop', [$this, 'onWorkerStop']);
			self::$serv->on('Receive', [$this, 'onReceive']);
			self::$serv->on('connect', [$this, 'onConnect']);
			self::$serv->on('close', [$this, 'onClose']);
			if (!$this->serv_conf) self::$serv->set($this->serv_conf);
		}
		else {
			$new_listen = self::$serv->addListener($this->host, $this->port, $this->sock_type);
			$new_listen->on('Packet', [$this, 'onPacket']);
			$new_listen->on('Receive', [$this, 'onReceive']);
			$new_listen->on('connect', [$this, 'onConnect']);
			$new_listen->on('close', [$this, 'onClose']);
			if (!$this->serv_conf) $new_listen->set($this->serv_conf);
		}
	}

	public function onStart ()
	{
	}

	public function onShutdown ()
	{
	}

	public function onWorkerStart ()
	{
	}

	public function onWorkerStop ()
	{
	}

	public function onTimer ()
	{
	}

	public function onConnect ()
	{
	}

	public function onReceive ($server, $fd, $reactor_id, $data)
	{
		var_dump($this);
		$server->close($fd);
	}

	public function onPacket ()
	{
	}

	public function onClose ()
	{
	}

	public function onBufferFull ()
	{
	}

	public function onBufferEmpty ()
	{
	}

	public function onTask ()
	{
	}

	public function onFinish ()
	{
	}

	public function onPipeMessage ()
	{
	}

	public function onWorkerError ()
	{
	}

	public function onManagerStart ()
	{
	}

	public function onManagerStop ()
	{
	}
}