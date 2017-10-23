<?php

	namespace SwooleFlierMouseBase\interfaces;

	interface Conf
	{
		static public function read ($conf_path);

		static public function get ($key);

	}