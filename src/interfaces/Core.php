<?php

	namespace SwooleFlierMouseBase\interfaces;

	interface Core
	{
		static public function getInstance();

		public function debug ($off = TRUE);

		function checkSystem();

		public function initialize();

		public function setConf($conf_path);

		public function run ();

	}