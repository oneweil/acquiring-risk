# 入口文件

ThinkPHP`8.0`采用**单一入口模式**进行项目部署和访问，一个应用都有一个统一（但不一定是唯一）的入口。如果采用自动多应用部署的话，一个入口文件还可以自动对应多个应用。

## 入口文件定义

默认的应用入口文件位于`public/index.php`，默认内容如下：

```
// [ 应用入口文件 ]
namespace think;

require __DIR__ . '/../vendor/autoload.php';

// 执行HTTP应用并响应
$http = (new App())->http;
$response = $http->run();
$response->send();
$http->end($response);
```

> 如果你没有特殊的自定义需求，无需对入口文件做任何的更改。

> 入口文件位置的设计是为了让应用部署更安全，请尽量遵循`public`目录为唯一的`web`可访问目录，其他的文件都可以放到非WEB访问目录下面。

## 控制台入口文件

除了应用入口文件外，系统还提供了一个控制台入口文件，位于项目根目录的`think`（注意该文件没有任何的后缀）。

该入口文件代码如下：

```
#!/usr/bin/env php
<?php
namespace think;

// 加载基础文件
require __DIR__ . '/vendor/autoload.php';

// 应用初始化
(new App())->console->run();
```

控制台入口文件用于执行控制台指令，例如：

```
php think version
```

> 系统内置了一些常用的控制台指令，如果你安装了额外的扩展，也会增加相应的控制台指令，都是通过该入口文件执行的。