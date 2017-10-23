<?php
	namespace SwooleFlierMouseBase\interfaces;

	interface Serv {
//		public function __construct ();
		public function onStart();
		public function onShutdown();
		public function onWorkerStart();
		public function onWorkerStop();
		public function onTimer();
		public function onConnect();
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

