# 配置

## 配置目录

### 单应用模式

对于单应用模式来说，配置文件和目录很简单，根目录下的`config`目录下面就是所有的配置文件。每个配置文件对应不同的组件，当然你也可以增加自定义的配置文件。

```
├─config（配置目录）
│  ├─app.php            应用配置
│  ├─cache.php          缓存配置
│  ├─console.php        控制台配置
│  ├─cookie.php         Cookie配置
│  ├─database.php       数据库配置
│  ├─filesystem.php     文件磁盘配置
│  ├─lang.php           多语言配置
│  ├─log.php            日志配置
│  ├─middleware.php     中间件配置
│  ├─route.php          URL和路由配置
│  ├─session.php        Session配置
│  ├─trace.php          Trace配置
│  ├─view.php           视图配置
│  └─ ...               更多配置文件
│  
```

单应用模式的`config`目录下的所有配置文件系统都会自动读取，不需要手动加载。如果存在子目录，你可以通过`Config`类的`load`方法手动加载，例如：

```
// 加载config/extra/config.php 配置文件 读取到extra
\think\facade\Config::load('extra/config', 'extra');
```

### 多应用模式

在多应用模式下，配置分为全局配置和应用配置。

- **全局配置**：`config`目录下面的文件就是项目的全局配置文件，对所有应用有效。
- **应用配置**：每个应用可以有独立配置文件，相同的配置参数会覆盖全局配置。

```
├─app（应用目录）
│  ├─app1 （应用1）
│  │   └─config（应用配置）
│  │   	 ├─app.php            应用配置
│  │  	 ├─cache.php          缓存配置
│  │   	 ├─cookie.php         Cookie配置
│  │   	 ├─database.php       数据库配置
│  │  	 ├─lang.php           多语言配置
│  │  	 ├─log.php            日志配置
│  │     ├─route.php          URL和路由配置
│  │   	 ├─session.php        Session配置
│  │ 	 ├─view.php           视图及模板引擎配置
│  │   	 ├─trace.php          Trace配置
│  │ 	 └─ ...               更多配置文件
│  │ 
│  └─ app2... （更多应用）
│
├─config（全局配置）
│  ├─app.php            应用配置
│  ├─cache.php          缓存配置
│  ├─console.php        控制台配置
│  ├─cookie.php         Cookie配置
│  ├─database.php       数据库配置
│  ├─filesystem.php     文件磁盘配置
│  ├─lang.php           多语言配置
│  ├─log.php            日志配置
│  ├─middleware.php     中间件配置
│  ├─route.php          URL和路由配置
│  ├─session.php        Session配置
│  ├─trace.php          Trace配置
│  ├─view.php           视图配置
│  └─ ...               更多配置文件
│  
```

## 配置定义

可以直接在相应的全局或应用配置文件中修改或者增加配置参数，如果你要增加额外的配置文件，直接放入配置目录即可（文件名小写）。

> 除了一级配置外，配置参数名严格区分大小写，建议是使用小写定义配置参数的规范。

由于架构设计原因，下面的配置只能在环境变量中修改。

|配置参数|描述|
|---|---|
|app_debug|应用调试模式|
|config_ext|配置文件后缀|

### 环境变量定义

可以在应用的根目录下定义一个特殊的`.env`环境变量文件，用于在开发过程中模拟环境变量配置（该文件建议在服务器部署的时候忽略），`.env`文件中的配置参数定义格式采用`ini`方式，例如：

```
APP_DEBUG =  true
```

> 默认安装后的根目录有一个`.example.env`环境变量示例文件，你可以直接改成`.env`文件后进行修改。

> 如果你的部署环境单独配置了环境变量（ 环境变量的前缀使用`PHP_`），那么请删除`.env`配置文件，避免冲突。

环境变量配置的参数会全部转换为大写，值为 `off`，`no` 和 `false` 等效于 布尔值`false`，值为 `yes` 、`on`和 `true` 等效于 布尔值的`true`。

注意，环境变量不支持数组参数，如果需要使用数组参数可以使用下面的方式

```
[DATABASE]
USERNAME =  root
PASSWORD =  123456
```

如果要设置一个没有键值的数组参数，可以使用

```
PATHINFO_PATH[] =  ORIG_PATH_INFO
PATHINFO_PATH[] =  REDIRECT_PATH_INFO
PATHINFO_PATH[] =  REDIRECT_URL
```

获取环境变量的值可以使用下面的方式获取：

```
Env::get('database.username');
Env::get('database.password');
Env::get('PATHINFO_PATH');
```

要使用`Env`类，必须先引入`think\facade\Env`。

> 环境变量的获取不区分大小写

可以支持默认值，例如：

```
// 获取环境变量 如果不存在则使用默认值root
Env::get('database.username', 'root');
```

可以直接在配置文件中使用环境变量进行本地环境和服务器的自动配置，例如：

```
return [
    'hostname'  =>  Env::get('hostname','127.0.0.1'),
];
```

### 多环境变量配置支持

支持定义多个环境变量配置文件，配置文件命名规范为

```
.env.example
.env.testing
.env.develop
```

然后，需要在入口文件中指定部署使用的环境变量名称：

```
// 执行HTTP应用并响应
$http = (new App())->setEnvName('develop')->http;

$response = $http->run();

$response->send();

$http->end($response);
```

`V8.1.0+`开始，可以设置一个公共环境变量文件，会优先加载，然后再这个公共环境变量文件中定义部署的环境变量名称

```
// 执行HTTP应用并响应
$http = (new App())->setBaseEnvName('base')->http;

$response = $http->run();

$response->send();

$http->end($response);
```

或者你可以继承`App`类 然后重载`loadEnv`方法实现 动态切换环境变量配置。

### 其它配置格式支持

默认的配置文件都是PHP数组方式，如果你需要使用其它格式的配置文件，你可以通过改变`CONFIG_EXT`环境变量的方式来更改配置类型。

在应用根目录的`.env`或者系统环境变量中设置

```
CONFIG_EXT=".ini"
```

支持的配置类型包括`.ini`、`.xml`、`.json` 、`.yaml`和 `.php` 在内的格式支持，配置后全局或应用配置必须统一使用相同的配置类型。

## 配置获取

要使用`Config`类，首先需要在你的类文件中引入

```
use think\facade\Config;
```

然后就可以使用下面的方法读取某个配置参数的值：

读取一级配置的所有参数（每个配置文件都是独立的一级配置）

```
Config::get('app');
Config::get('route');
```

读取单个配置参数

```
Config::get('app.app_name');
Config::get('route.url_domain_root');
```

读取数组配置（理论上支持无限级配置参数读取）

```
Config::get('database.default.host');
```

判断是否存在某个设置参数：

```
Config::has('template');
Config::has('route.route_rule_merge');
```

## 配置获取器

`8.1+`开始，系统支持配置获取器功能用于远程配置中心，你可以注册一个获取器

```
think\facade\Config::hook(function($name, $value) {
    // 对配置参数和值进行处理后返回最终的配置值
    // ...
    return $value;
});
```

## 参数批量设置

`Config`类不再支持动态设置某个配置参数，但可以支持批量设置更新配置参数。

```
// 批量设置参数
Config::set(['name1' => 'value1', 'name2' => 'value2'], 'config');
// 获取配置
Config::get('config');
```

## 系统配置文件

下面系统自带的配置文件列表及其作用：

|配置文件名|描述|
|---|---|
|app.php|应用配置|
|cache.php|缓存配置|
|console.php|控制台配置|
|cookie.php|Cookie配置|
|database.php|数据库配置|
|filesystem.php|磁盘配置|
|lang.php|多语言配置|
|log.php|日志配置|
|middleware.php|中间件配置|
|route.php|路由和URL配置|
|session.php|Session配置|
|trace.php|页面Trace配置|
|view.php|视图配置|

具体的配置参数及默认值可以直接查看应用`config`目录下面的相关文件内容。

> 如果是多应用模式的话配置文件可能同时存在全局和应用配置文件两个同名文件

## 使用`Yaconf`定义

可以支持使用`Yaconf`统一定义配置，但不支持动态设置。

> 安装了`yaconf`扩展之后，项目里面的配置文件不再有效。而且不再区分全局和应用配置。

首先需要安装`topthink/think-yaconf`扩展，

```
composer require topthink/think-yaconf
```

然后在`app`目录下的`provider.php`文件中添加：

```
'think\Config'	=>	'think\Yaconf',
```

使用`setYaconf`方法指定`Yaconf`使用的独立配置文件，例如：

```
// 建议在应用的公共函数文件中进行设置
think\facade\Config::setYaconf('thinkphp');
```

设置后，你只需要在`thinkphp.ini`一个文件里进行项目的配置，而无需分开多个文件，避免和其它项目冲突。

> 关于`Yaconf`的安装和配置用法可以[参考这里](http://www.laruence.com/2015/06/12/3051.html)。