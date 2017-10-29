<?php
/**
 * Created by PhpStorm.
 * User: shuhao
 * Date: 2017/10/23
 * Time: 下午8:28
 */

include './vendor/autoload.php';

$before = function () {
	$app = new \SwooleFlierMouseFramework\App();

	$router = require 'router.php';
	$loader = require 'loader.php';
	$db     = require 'db.php';


	return $app->setDi('loader', $loader)
		->setDi('router', $router)
		->setDi('db', $db)
		->LoadComponents();
};

$callback = function ($request, $response, $before_result) {
	try {
		return $before_result->dispatcher($request, $response);
	} catch (\SwooleFlierMouseFramework\AppException $app_exception) {
		$app_exception->response($response);
	}
};


\SwooleFlierMouseBase\Core::getInstance()
	->setConf('./conf.yaml')
	->bindServerHandler('http', $callback, $before)
	->bindServerHandler('http2', function ($req, $res) {
		return '<h1>hello word2</h1>';
	})
	->run();