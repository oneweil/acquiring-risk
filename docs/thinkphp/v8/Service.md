# 服务

## 系统服务

系统服务的概念是指在执行框架的某些组件或者功能的时候需要依赖的一些基础服务，服务类通常可以继承系统的`think\Service`类，但并不强制（如果继承`think\Service`的话可以直接调用`this->app`获取应用实例）。

你可以在系统服务中注册一个对象到容器，或者对某些对象进行相关的依赖注入。由于系统服务的执行优先级问题，可以确保相关组件在执行的时候已经完成相关依赖注入。

## 服务定义

你可以通过命令行生成一个服务类，例如：

```
php think make:service  FileSystemService
```

默认生成的服务类会继承系统的`think\Service`，并且自动生成了系统服务类最常用的两个空方法：`register`和`boot`方法。

### 注册方法

`register`方法通常用于注册系统服务，也就是将服务绑定到容器中，例如：

```
<?php
namespace app\service;

use my\util\FileSystem;

class FileSystemService extends Service
{
    public function register()
    {
        $this->app->bind('file_system', FileSystem::class);
    }
}
```

`register`方法不需要任何的参数，如果你只是简单的绑定容器对象的话，可以直接使用`bind`属性。

```
<?php
namespace app\service;

use my\util\FileSystem;

class FileSystemService extends Service
{
    public $bind = [
        'file_system'    =>    FileSystem::class,
    ];
}
```

### 启动方法

`boot`方法是在所有的系统服务注册完成之后调用，用于定义启动某个系统服务之前需要做的操作。例如：

```
<?php
namespace think\captcha;

use think\Route;
use think\Service;
use think\Validate;

class CaptchaService extends Service
{
    public function boot(Route $route)
    {
        $route->get('captcha/[:config]', "\\think\\captcha\\CaptchaController@index");

        Validate::maker(function ($validate) {
            $validate->extend('captcha', function ($value) {
                return captcha_check($value);
            }, ':attribute错误!');
        });
    }
}
```

`boot`方法支持依赖注入，你可以直接使用其它的依赖服务。

## 服务注册

定义好系统服务后，你还需要注册服务到你的应用实例中。

可以在应用的全局公共文件`service.php`中定义需要注册的系统服务，系统会自动完成注册以及启动。例如：

```
return [
    '\app\service\ConfigService',
    '\app\service\CacheService',
];
```

如果你需要在你的扩展中注册系统服务，首先在扩展中增加一个服务类，然后在扩展的`composer.json`文件中增加如下定义：

```
"extra": {
    "think": {
        "services": [
            "think\\captcha\\CaptchaService"
        ]
    }
},
```

在安装扩展后会系统会自动执行`service:discover`指令用于生成服务列表，并在系统初始化过程中自动注册。

## 内置服务

为了更好的完成核心组件的单元测试，框架内置了一些系统服务类，主要都是用于核心类的依赖注入，包括`ModelService`、`PaginatorService`和`ValidateService`类。这些服务不需要注册，并且也不能卸载。