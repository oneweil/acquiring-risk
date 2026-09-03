# 多模块和多应用

## 多模块

`V8.1+`版本开始，已经可以更好的支持多模块，多应用和多模块的区别主要体现在：

- 多应用的目录、路由、配置都是完全独立（比较适用于彼此独立的应用）；
- 多模块只是控制器分级，路由、配置则统一定义（比较适用于一个应用下的多个关联模块）；
- 多应用支持应用中间件，多模块支持模块中间件；
- 多应用不支持`think-swoole`，多模块则可以良好支持；
- 多应用和多模块可以同时使用，并不冲突；

> 请根据实际项目需求选择是否采用多模块还是多应用，通常情况下建议使用多模块。

开启多模块模式的话，只需要在路由定义文件的最后添加下面的一行代码

```
// 开启多模块URL自动解析 `8.1+`版本开始支持
Route::auto();
```

默认的URL解析规则更改为

```
https://domainName/moduleName/controllerName/actionName
```

多模块默认的目录结构为

```
├─app 应用目录
│  ├─controller         控制器目录
│  │  ├─module1         模块1目录
│  │  ├─module2         模块2目录
│  │  └─ ...            更多模块 目录
│  ├─model              模型目录
│  ├─view               视图目录
│  └─ ...               更多类库目录
│
├─public                WEB目录（对外访问目录）
│  ├─index.php          入口文件
│  ├─router.php         快速测试文件
│  └─.htaccess          用于apache的重写
│
├─view                  视图目录
├─config                应用配置目录
├─route                 路由定义目录
├─runtime               应用的运行时目录
```

如果你需要自定义多模块的目录结构，可以参考这个教程：[如何使用路由灵活定义目录结构](https://doc.thinkphp.cn/@wiki/custom-directory-with-route.html)

## 多应用

安装后默认使用**单应用模式部署**，目录结构如下：

```
├─app 应用目录
│  ├─controller         控制器目录
│  ├─model              模型目录
│  ├─view               视图目录
│  └─ ...               更多类库目录
│
├─public                WEB目录（对外访问目录）
│  ├─index.php          入口文件
│  ├─router.php         快速测试文件
│  └─.htaccess          用于apache的重写
│
├─view                  视图目录
├─config                应用配置目录
├─route                 路由定义目录
├─runtime               应用的运行时目录
```

> 单应用模式的优势是简单灵活，URL地址完全通过路由可控。配合路由分组功能可以实现类似多应用的灵活机制。

> 如果要使用多应用模式，你需要安装多应用模式扩展`think-multi-app`。

```
composer require topthink/think-multi-app
```

然后你的应用目录结构需要做如下调整，主要区别在`app`目录增加了应用子目录，然后配置文件和路由定义文件都纳入应用目录下。

```
├─app 应用目录
│  ├─index              主应用
│  │  ├─controller      控制器目录
│  │  ├─model           模型目录
│  │  ├─view            视图目录
│  │  ├─config          配置目录
│  │  ├─route           路由目录
│  │  └─ ...            更多类库目录
│  │ 
│  ├─admin              后台应用
│  │  ├─controller      控制器目录
│  │  ├─model           模型目录
│  │  ├─view            视图目录
│  │  ├─config          配置目录
│  │  ├─route           路由目录
│  │  └─ ...            更多类库目录
│
├─public                WEB目录（对外访问目录）
│  ├─admin.php          后台入口文件
│  ├─index.php          入口文件
│  ├─router.php         快速测试文件
│  └─.htaccess          用于apache的重写
│
├─config                全局应用配置目录
├─runtime               运行时目录
│  ├─index              index应用运行时目录
│  └─admin              admin应用运行时目录
```

从目录结构可以看出来，每个应用相对保持独立，并且可以支持多个入口文件，应用下面还可以通过多级控制器来维护控制器分组。

## 自动多应用部署

支持在同一个入口文件中访问多个应用，并且支持应用的映射关系以及自定义。如果你通过`index.php`入口文件访问的话，并且没有设置应用`name`，系统自动采用自动多应用模式。

自动多应用模式的URL地址默认使用

```
// 访问admin应用
http://serverName/index.php/admin
// 访问shop应用
http://serverName/index.php/shop
```

> 也就是说`pathinfo`地址的第一个参数就表示当前的应用名，后面才是该应用的路由或者控制器/操作。

如果直接访问

```
http://serverName/index.php
```

访问的其实是`index`默认应用，可以通过`app.php`配置文件的`default_app`配置参数指定默认应用。

```
// 设置默认应用名称
'default_app' => 'home',
```

接着访问

```
http://serverName/index.php
```

其实访问的是`home`应用。

> 自动多应用模式下，路由是每个应用独立的，所以你没法省略URL里面的应用参数。但可以使用域名绑定解决。

## 多应用智能识别

如果没有绑定入口或者域名的情况下，URL里面的应用不存在，例如访问：

```
http://serverName/index.php/think
```

假设并不存在`think`应用，这个时候系统会自动切换到单应用模式，如果有定义全局的路由，也会进行路由匹配检查。

如果我们在`route/route.php`全局路由中定义了：

```
Route::get('think', function () {
    return 'hello,ThinkPHP!';
});
```

访问上面的URL就会输出

```
hello,ThinkPHP!
```

如果你希望`think`应用不存在的时候，直接访问默认应用的路由，可以在`app.php`中配置

```
// 开启应用快速访问
'app_express'    =>    true,
// 默认应用
'default_app'    =>    'home',
```

这个时候就会访问`home`应用下的路由。

## 增加应用入口

允许为每个应用创建单独的入口文件而不通过`index.php`入口文件访问多个应用，例如创建一个`admin.php`入口文件来访问`admin`应用。

```
// [ 应用入口文件 ]
namespace think;

require __DIR__ . '/../vendor/autoload.php';

// 执行HTTP应用并响应
$http = (new  App())->http;
$response = $http->run();
$response->send();
$http->end($response);
```

> 多应用使用不同的入口的情况下，每个入口文件的内容都是一样的，默认入口文件名（不含后缀）就是应用名。

使用下面的方式访问`admin`应用

```
http://serverName/admin.php
```

如果你的入口文件名和应用不一致，例如你的后台`admin`应用，入口文件名使用了`test.php`，那么入口文件需要改成：

```
// [ 应用入口文件 ]
namespace think;

require __DIR__ . '/../vendor/autoload.php';

// 执行HTTP应用并响应
$http = (new  App())->http;
$response = $http->name('admin')->run();
$response->send();
$http->end($response);
```

## 获取当前应用

如果需要获取当前的应用名，可以使用

```
app('http')->getName();
```

## 应用目录获取

单应用和多应用模式会影响一些系统路径的值，为了更好的理解本手册的内容，你可能需要理解下面几个系统路径所表示的位置。

|目录位置|目录说明|获取方法（助手函数）|
|---|---|---|
|根目录|项目所在的目录，默认自动获取，可以在入口文件实例化`App`类的时候传入。|`root_path()`|
|基础目录|根目录下的`app`目录|`base_path()`|
|应用目录|当前应用所在的目录，如果是单应用模式则同基础目录，如果是多应用模式，则是`app`/应用子目录|`app_path()`|
|配置目录|根目录下的`config`目录|`config_path()`|
|运行时目录|框架运行时的目录，单应用模式就是根目录的`runtime`目录，多应用模式为`runtime`/应用子目录|`runtime_path()`|

> 注意：应用支持使用`composer`包，这个时候目录可能是`composer`包的类库所在目录。

对于非自动多应用部署的情况，如果要加载`composer`应用，需要在入口文件中设置应用路径：

```
// [ 应用入口文件 ]
namespace think;

require __DIR__ . '/../vendor/autoload.php';

// 执行HTTP应用并响应
$http = (new  App())->http;
$response = $http->path('path/to/app')->run();
$response->send();
$http->end($response);
```

## 应用映射

自动多应用模式下，支持应用的别名映射，例如：

```
'app_map' => [
    'think'  =>  'admin',  // 把admin应用映射为think
],
```

应用映射后，原来的应用名将不能被访问，例如上面的`admin`应用不能直接访问，只能通过`think`应用访问。

应用映射支持泛解析，例如：

```
'app_map' => [
    'think' =>  'admin',  
    'home'  =>  'index',  
    '*'     =>  'index',  
],
```

表示如果URL访问的应用不在当前设置的映射里面，则自动映射为`index`应用。

如果要使用`composer`加载应用，需要设置

```
'app_map'    =>    [
    'think' => function($app) {
        $app->http->path('path/to/composer/app');
    },
],
```

## 域名绑定应用

如果你的多应用使用多个子域名或者独立域名访问，你可以在`config/app.php`配置文件中定义域名和应用的绑定。

```
'domain_bind' => [
    'blog'        =>  'blog',  //  blog子域名绑定到blog应用
    'shop.tp.com' =>  'shop',  //  完整域名绑定
    '*'           =>  'home', // 二级泛域名绑定到home应用
],
```

## 禁止应用访问

你如果不希望某个应用通过URL访问，例如，你增加了一个`common`子目录用于放置一些公共类库，你可以设置

```
'deny_app_list' =>    ['common']
```

> 多应用模式并非核心内置模式，官方提供的多应用扩展更多是抛砖引玉，你完全可以通过中间件来扩展适合自己的多应用模式