<?php

namespace SwooleFlierMouseBase\abstracts;

use SwooleFlierMouseBase\command\Command;
use SwooleFlierMouseBase\Conf;
use SwooleFlierMouseBase\interfaces\Serv as ServI;
use SwooleFlierMouseBase\serv\Server;
use SwooleFlierMouseBase\SwooleFlierMouseBaseException;

abstract class Serv implements ServI
{
	public static $serv         = NULL;
	public        $server_type  = NULL;
	public        $protocol     = NULL;
	public        $host         = NULL;
	public        $domain       = NULL;
	public        $conf         = NULL;
	public        $serv_conf    = NULL;
	public        $port         = NULL;
	public        $ssl          = FALSE;
	public        $ssl_cert_pem = NULL;
	public        $ssl_cert_key = NULL;
	public        $session      = FALSE;
	public        $server_id    = FALSE;
	public        $sock_type    = SWOOLE_SOCK_TCP;
	public        $mode         = SWOOLE_PROCESS;

	static public $ssl_protocols
		= [
			'https',
			'wss'
		];

	static public $default_options
		= [
			'heartbeat_check_interval' => 60,
			'heartbeat_idle_time'      => 600,
			//		'daemonize' => 1,
			//			'package_max_length'       => 1024 * 1024 * 1024 * 1024 * 1024,
			//			'open_http_protocol'       => TRUE
		];

	public function __construct (array $conf, $server_id)
	{
		$this->conf = $conf;

		$this->host      = $conf['host'];
		$this->server_id = $server_id;
		$this->protocol  = $conf['protocol'];
		$this->session   = $conf['session']??FALSE;
		$this->domain    = $conf['domain']??NULL;
		$this->port      = $conf['port'];
		$this->serv_conf = $conf['serv_conf']??[];
		$this->ssl       = $conf['ssl']??(in_array($this->protocol, self::$ssl_protocols) ? TRUE : FALSE);
		$this->mode      = $conf['mode']??SWOOLE_PROCESS;
		if ($this->ssl) {
			if (!isset($conf['ssl_cert_pem']) || !isset($conf['ssl_cert_key'])) {
				throw new SwooleFlierMouseBaseException('You open the SSL not set secret key and certificate');
			}
			else {
				$this->ssl_cert_pem = $conf['ssl_cert_pem'];
				$this->ssl_cert_key = $conf['ssl_cert_key'];
				$this->sock_type    = $conf['sock_type'] ?: (SWOOLE_SOCK_TCP | SWOOLE_SSL);
			}
		}

		if (in_array($this->protocol, ['http', 'https'])) {
			$this->serv_conf['open_http_protocol'] = TRUE;
		}

		$this->serv_conf = array_merge(self::$default_options, $this->serv_conf);

		if (NULL == self::$serv) {
			self::$serv = new \swoole_server($this->host, $this->port, $this->mode, $this->sock_type);
			self::$serv->on('Packet', [$this, 'onPacket']);
			self::$serv->on('Start', [$this, 'onStart']);
			self::$serv->on('WorkerStart', [$this, 'onWorkerStart']);
			self::$serv->on('WorkerStop', [$this, 'onWorkerStop']);
			self::$serv->on('Receive', [$this, 'onReceive']);
			self::$serv->on('Shutdown', [$this, 'onShutdown']);
			self::$serv->on('connect', [$this, 'onConnect']);
			self::$serv->on('close', [$this, 'onClose']);

			if (Conf::isDaemonize()) {
				$this->serv_conf['daemonize'] = TRUE;
			}

			if ($this->serv_conf) self::$serv->set($this->serv_conf);
		}
		else {
			$new_listen = self::$serv->addListener($this->host, $this->port, $this->sock_type);
			$new_listen->on('Packet', [$this, 'onPacket']);
			$new_listen->on('Receive', [$this, 'onReceive']);
			$new_listen->on('connect', [$this, 'onConnect']);
			$new_listen->on('close', [$this, 'onClose']);
			if ($this->serv_conf) $new_listen->set($this->serv_conf);
		}
	}

	public function onStart ($server)
	{
		$messages = [];
		foreach (Server::$instances as $instance) {
			$messages[] = sprintf(" \e[41;37m RUNING \e[0m   HOST:%s://%s:%s[%s] ",
				$instance->protocol,
				$instance->domain,
				$instance->port,
				$instance->host
			);
		}

		Command::line($messages);

		file_put_contents(Command::$pid_file, $server->master_pid);
		file_put_contents(Command::$pid_file, ',' . $server->manager_pid, FILE_APPEND);
		Command::setProcessTitle('master');
	}

	public function onShutdown ($server)
	{
//		Command::line('onShutdown');
	}

	public function onWorkerStart ($server, $worker_id)
	{
		file_put_contents(Command::$pid_file, ',' . $server->worker_pid, FILE_APPEND);

		if ($server->taskworker) {
			Command::setProcessTitle('tasker');
		}
		else {
			Command::setProcessTitle('worker');
		}
	}

	public function onWorkerStop ()
	{
//		Command::line('onWorkerStop');
	}

	public function onTimer ()
	{
	}

	public function onConnect ($server, $fd, $from_id)
	{
//		Command::line('onConnect' . json_encode($server->connection_info($fd)));
	}

	public function onReceive ($server, $fd, $reactor_id, $data)
	{
		switch ($this->protocol) {
			case 'http';
				$this->httpServ($server, $fd, $reactor_id, $data);
				break;
			case 'https';
				$this->httpServ($server, $fd, $reactor_id, $data);
				break;
		}
	}

	public function onPacket ()
	{
	}

	public function onClose ()
	{
//		Command::line('onClose');
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