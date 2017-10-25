# swoole-flier-mouse

  基于swoole 的框架
  
#demo

```angular2html
    	include './vendor/autoload.php';
    
    	SwooleFlierMouseBase\Core::getInstance()
    		->setConf('./conf.yaml')
    		->bindExec('http',function($req,$res){
    			return '<h1>hello word</h1>';
    		})
    		->bindExec('http2',function($req,$res){
    			return '<h1>hello word2</h1>';
    		})
    		->run();
```
