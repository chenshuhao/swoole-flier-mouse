<?php
	namespace SwooleFlierMouseBase\interfaces;

	interface Serv {
//		public function __construct ();
		public function onStart($server);
		public function onShutdown($server);
		public function onWorkerStart($server, $worker_id);
		public function onWorkerStop();
		public function onTimer();
		public function onConnect($server,  $fd,  $from_id);
		public function onReceive($server, $fd, $reactor_id, $data);
		public function onPacket();
		public function onClose();
		public function onBufferFull();
		public function onBufferEmpty();
		public function onTask();
		public function onFinish();
		public function onPipeMessage();
		public function onWorkerError();
		public function onManagerStart();
		public function onManagerStop();
	}

