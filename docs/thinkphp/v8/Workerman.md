# Workerman

## Workerman

> Workerman是一款纯PHP开发的开源高性能的PHP socket 服务器框架。被广泛的用于手机app、手游服务端、网络游戏服务器、聊天室服务器、硬件通讯服务器、智能家居、车联网、物联网等领域的开发。 支持TCP长连接，支持Websocket、HTTP等协议，支持自定义协议。基于workerman开发者可以更专注于业务逻辑开发，不必再为PHP Socket底层开发而烦恼。

## 安装

首先通过 composer 安装

```
composer require topthink/think-worker
```

## 使用

### 使用`Workerman`作为`HttpServer`

在命令行启动服务端

```
php think worker
```

然后就可以通过浏览器直接访问当前应用

```
http://localhost:2346
```

linux下面可以支持下面指令

```
php think worker [start|stop|reload|restart|status]
```

`workerman`的参数可以在应用配置目录下的`worker.php`里面配置。

> 由于`onWorkerStart`运行的时候没有`HTTP_HOST`，因此最好在应用配置文件中设置`app_host`

### SocketServer

在命令行启动服务端（需要`2.0.5+`版本）

```
php think worker:server
```

默认会在0.0.0.0:2345开启一个`websocket`服务。

如果需要自定义参数，可以在`config/worker_server.php`中进行配置，包括：

|配置参数|描述|
|---|---|
|protocol|协议|
|host|监听地址|
|port|监听端口|
|socket|完整的socket地址|

并且支持`workerman`所有的参数（包括全局静态参数）。  
也支持使用闭包方式定义相关事件回调。

```
return [
	'socket' 	=>	'http://127.0.0.1:8000',
	'name'		=>	'thinkphp',
	'count'		=>	4,
	'onMessage'	=>	function($connection, $data) {
		$connection->send(json_encode($data));
	},
];
```

也支持使用自定义类作为`Worker`服务入口文件类。例如，我们可以创建一个服务类（必须要继承 `think\worker\Server`），然后设置属性和添加回调方法

```
<?php
namespace app\http;

use think\worker\Server;

class Worker extends Server
{
	protected $socket = 'http://0.0.0.0:2346';

	public function onMessage($connection,$data)
	{
		$connection->send(json_encode($data));
	}
}
```

支持`workerman`所有的回调方法定义（回调方法必须是public类型）

然后在`worker_server.php`中增加配置参数：

```
return [
	'worker_class'	=>	'app\http\Worker',
];
```

定义该参数后，其它配置参数均不再有效。

在命令行启动服务端

```
php think worker:server
```

然后在浏览器里面访问

```
http://localhost:2346
```

如果在Linux下面，同样支持reload|restart|stop|status 操作

```
php think worker:server reload
```

> 注意:使用默认worker做服务器时,`dump`会打印到命令行.

### 关于上传文件

当按照默认的worker做http服务器时,并不能直接使用`request()->file('image')`来获得上传的文件,具体可以参考[workerman的上传文件第6点](http://doc.workerman.net/web-server.html).因此只能迂回的使用`Filesystem`.无论怎样,不影响其`getMime()`等方法的正确性.

```
// $file = request()->file('image');
$file_data  =  $_FILES[0]['file_data'];
//$tmp_file  = tempnam('','tm_');  这种写法最终保存时扩展名为.tmp
$tmp_file  = sys_get_temp_dir().'/'.uniqid().'.'.explode('/',$_FILES[0]['file_type'])[1];
file_put_contents($tmp_file,$file);
$file  =  new  File($tmp_file);
$savename  =  Filesystem::putFile('upload',$file);
echo  $savename;
```

### 自定义workerman指令

有时候我们希望使用think的命令行运行workerman,这里做一个介绍,  
1:先新建一个指令,参考文档:[自定义指令](https://www.kancloud.cn/manual/thinkphp6_0/1037651),比如新建命令:

```
php think make:command Hello hello
```

2:复制下面的代码到指令里,覆盖原始的`configure`和`execute`方法

```
  protected function configure()
  {
    // 指令配置
    $this->setName('convert')
      ->addArgument('action', Argument::OPTIONAL, "start|stop|restart|reload|status|connections", 'start')
      ->addOption('mode', 'm', Option::VALUE_OPTIONAL, 'Run the workerman server in daemon mode.')
      ->setDescription('the workerman command');
  }

  protected function execute(Input $input, Output $output)
  {
    // 指令输出
    $output->writeln('convert start');

    $action = $input->getArgument('action');
    $mode = $input->getOption('mode');


    // 重新构造命令行参数,以便兼容workerman的命令
    global $argv;

    $argv = [];

    array_unshift($argv, 'think', $action);
    if ($mode == 'd') {
      $argv[] = '-d';
    } else if ($mode == 'g') {
      $argv[] = '-g';
    }

    // 在这里放心的实例化worker,
    // 就像参照workerman文档写一样,
    // 无非在workerman的文档里,代码是新建纯php文件,但在这里,写到了一个方法里.
    $worker_1 = new Worker();
    $worker_2 = new Worker();

    Worker::runAll();
  }
```

3:运行的时候,使用如下命令:

```
//临时运行
php think hello start
//后台运行
php think hello start --mode d
```