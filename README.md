# swoole-flier-mouse

  基于swoole 的框架
  
#install

composer require chenshuhao/swoole-flier-mouse-base
  
#demo

test.php
```php
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

php ./test.php start [-d] 

#conf.yaml 

```yaml
temp: tmp //临时文件路径
servers:
  http:
    host: 0.0.0.0
    protocol: http
    domain: test.dazhiwulian.com
    port: 80
    session: true
  http2:
    host: 0.0.0.0
    protocol: http
    domain: test2.dazhiwulian.com
    port: 9091
```
