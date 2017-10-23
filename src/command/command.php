<?php

namespace SwooleFlierMouseBase\command;

use SwooleFlierMouseBase\Conf;

class command
{
	static $pid_file = NULL;

	static public function cmd ()
	{
		global $argv;
		@list($start_file, $command, $param) = $argv;

		self::$pid_file = Conf::$temp . DIRECTORY_SEPARATOR . md5($start_file) . '.pid';

		if (file_exists(self::$pid_file)) {
			$pids            = explode(',', file_get_contents(self::$pid_file));
			$master_pid      = $pids[0];
			$manager_pid     = $pids[1];
			$master_is_alive = $master_pid && @posix_kill($master_pid, 0);
		}
		else {
			$master_is_alive = FALSE;
		}

		if ($master_is_alive) {
			if ($command === 'start') {
				echo("system [$start_file] already running\n");
				exit;
			}
		}
		elseif ($command !== 'start') {
			echo("system [$start_file] not run\n");
			exit;
		}


		switch ($command) {
			case 'start';
				break;
			case 'stop';
				@unlink(self::$pid_file);
				echo("system [$start_file] is stoping ...\n");
				// Send stop signal to master process.
				$master_pid && posix_kill($master_pid, SIGTERM);
				// Timeout.
				$timeout    = 5;
				$start_time = time();
				// Check master process is still alive?
				while (1) {
					$master_is_alive = $master_pid && posix_kill($master_pid, 0);
					if ($master_is_alive) {
						// Timeout?
						if (time() - $start_time >= $timeout) {
							echo("system [$start_file] stop fail\n");
							exit;
						}
						// Waiting amoment.
						usleep(10000);
						continue;
					}
					// Stop success.
					echo("system [$start_file] stop success\n");
					break;
				}
				exit(0);
				break;
			case 'restart';
				@unlink(self::$pid_file);
				echo("Swoole[$start_file] is stoping ...\n");
				// Send stop signal to master process.
				$master_pid && posix_kill($master_pid, SIGTERM);
				// Timeout.
				$timeout    = 5;
				$start_time = time();
				// Check master process is still alive?
				while (1) {
					$master_is_alive = $master_pid && posix_kill($master_pid, 0);
					if ($master_is_alive) {
						// Timeout?
						if (time() - $start_time >= $timeout) {
							echo("system [$start_file] stop fail\n");
							exit;
						}
						// Waiting amoment.
						usleep(10000);
						continue;
					}
					// Stop success.
					echo("system [$start_file] stop success\n");
					break;
				}
				break;
			case 'reload';
				posix_kill($manager_pid, SIGUSR1);
				echo("system [$start_file] reload\n");
				exit;
				break;
		}
	}

	static public function line ($message, $color = "\033")
	{
		if (!is_array($message)) {
			$messages[] = $message;
		}else{
			$messages = $message;
		}

		echo "{$color}[31m " .
		     str_pad(
			     str_pad('SWOOLE-FLIER-MOUSE-BASE-MESSAGE', 10+31, '-', STR_PAD_LEFT),
			     10+10+31, '-', STR_PAD_RIGHT) . "{$color}[0m"
		     . PHP_EOL;

		foreach ($messages as $k => $v) {
			echo "{$color}[31m {$v} {$color}[0m"
			     . PHP_EOL;

		}

		echo "{$color}[31m " .
		     str_pad(
			     str_pad('SWOOLE-FLIER-MOUSE-BASE-MESSAGE', 10+31, '-', STR_PAD_LEFT),
			     10+10+31, '-', STR_PAD_RIGHT) . "{$color}[0m"
		     . PHP_EOL;

	}


}