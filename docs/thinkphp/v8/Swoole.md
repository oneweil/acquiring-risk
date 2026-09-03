# Swoole

本篇内容主要讲述了最新的`think-swoole`扩展的使用。目前仅支持Linux环境或者MacOs下运行，要求`swoole`版本为`4.3.1+`。

> 由于 `think-swoole`是基于`swoole`的，要了解这个扩展如何使用，首先需要对`swoole`有一定的了解，这也是本文阅读的前提，具体可以参考 Swoole官方文档内容：[https://wiki.swoole.com](https://wiki.swoole.com/)

## 安装

首先按照`Swoole`官网说明安装`swoole`扩展，然后使用

```
composer require topthink/think-swoole
```

安装`think-swoole`扩展。

> 由于`Swoole`不支持`windows`环境，所以你无法在`windows`环境下测试，只能使用虚拟机或者`WSL`环境测试。

## `HTTP`服务

直接在命令行下启动HTTP服务端。

```
php think swoole
```

启动完成后，默认会在`0.0.0.0:80`启动一个HTTP Server，可以直接访问当前的应用。相关配置参数可以在`config/swoole.php`里面配置（具体参考配置文件内容）。

支持的其它操作包括：

启动HTTP服务（默认）

```
php think swoole start
```

停止服务

```
php think swoole stop
```

重启服务

```
php think swoole restart
```

`reload`服务

```
php think swoole reload
```

### 守护进程模式

如果需要使用守护进程方式运行，可以配置

```
'options'   =>  [
    'daemonize' =>  true
]
```

## 热更新

由于`Swoole`服务运行过程中PHP文件是常驻内存运行的，这样可以避免重复读取磁盘、重复解释编译PHP，以便达到最高性能。所以更改业务代码后必须手动`reload`或者`restart`才能生效。

`think-swoole`扩展提供了热更新功能，在检测到相关目录的文件有更新后会自动`reload`，从而不需要手动进行`reload`操作，方便开发调试。

如果你的应用开启了调试模式，默认是开启热更新的。原则上，在部署模式下不建议开启文件监控，一方面有性能损耗，另外一方面对文件所做的任何修改都需要确认无误才能进行更新部署。

热更新的默认配置如下：

![](https://img.kancloud.cn/52/18/52181406361448d118fe78f5777199e9_932x242.png)

当我们在应用的根目录下定义一个特殊的`.env`环境变量文件，里面设置了`APP_DEBUG = true`会默认开启热更新，你也可以直接把`enable`设置为true。

参数说明：

|参数|说明|
|---|---|
|enable|是否开启热更新|
|name|简单点说就是监控那些类型的文件变动|
|include|简单点说就是监控那些路径下的文件变动|
|exclude|排除目录|

## 连接池

`think-swoole` 默认有实现数据库和缓存连接池功能，涵盖了日常开发的主要场景。

最新的`swoole`版本支持[一键协程](https://wiki.swoole.com/#/runtime)，比如`redis`、`mysql`等等，很方便。连接池是在这个基础上，解决一些问题和对性能的再一次提升。

要开启一键协程，需要配置如下参数  
![](https://img.kancloud.cn/66/c5/66c57507adea3a39dbe4495abd2d2e29_546x168.png)

这里需要设置为true，默认已经打开，flags默认即可。


参数说明：

|参数|说明|
|---|---|
|enable|开关，不需要设置false|
|max_active|最大连接数，超过将不再新建连接|
|max_wait_time|超时时间|

其中的`max_active`和`max_wait_time`需要根据自身业务和环境进行适当调整，最大化提高系统负载。